<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NASA Official Evapotranspiration Service
 * 
 * MOD16A2 - More accurate than Open-Meteo for specific vegetation types
 */
class NasaETService
{
    private string $baseUrl;
    private bool $useMockData;
    private RateLimitService $rateLimitService;

    public function __construct(RateLimitService $rateLimitService)
    {
        $this->baseUrl = config('services.nasa_earthdata.api_url');
        $this->useMockData = config('services.nasa_earthdata.mock', true);
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Fetch official ET from MODIS
     */
    public function fetchEvapotranspiration(Plot $plot, string $token): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockET($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached for ET', ['plot_id' => $plot->id]);
            return null;
        }

        try {
            $coords = $this->getPlotCoordinates($plot);

            // MOD16A2: MODIS ET 500m, 8-day
            $response = Http::withToken($token)
                ->timeout(60)
                ->get("{$this->baseUrl}/bundle/MOD16A2.061/point", [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'startDate' => now()->subDays(8)->format('m-d-Y'),
                    'endDate' => now()->format('m-d-Y'),
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseETResponse($response->json());
            }

            Log::warning('NASA ET API request failed', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
            ]);

            if (config('app.env') !== 'production') {
                return $this->generateMockET($plot);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('NASA ET API error', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);

            if (config('app.env') !== 'production') {
                return $this->generateMockET($plot);
            }

            return null;
        }
    }

    /**
     * Parse ET response
     */
    private function parseETResponse(array $response): array
    {
        // ET scaling: multiply by 0.1 (kg/m²/8day to mm/8day)
        $etRaw = $response['ET_500m'] ?? null;
        $petRaw = $response['PET_500m'] ?? null;
        
        $et8day = $etRaw ? $etRaw * 0.1 : null;
        $pet8day = $petRaw ? $petRaw * 0.1 : null;
        
        // Convert to daily
        $etDaily = $et8day ? $et8day / 8 : null;
        $petDaily = $pet8day ? $pet8day / 8 : null;

        return [
            'et_daily' => $etDaily,
            'pet_daily' => $petDaily, // Potential ET
            'et_8day' => $et8day,
            'et_source' => 'NASA MODIS MOD16A2.061',
        ];
    }

    /**
     * Compare NASA ET vs Open-Meteo
     */
    public function compareWithOpenMeteo(float $nasaET, float $openMeteoET): array
    {
        $diff = abs($nasaET - $openMeteoET);
        $percentDiff = ($diff / $openMeteoET) * 100;
        
        if ($percentDiff < 10) {
            $status = 'consistent';
            $message = 'NASA y Open-Meteo coinciden';
            $recommendation = 'Ambos datos fiables';
        } elseif ($percentDiff < 20) {
            $status = 'acceptable';
            $message = 'Ligera diferencia';
            $recommendation = 'Preferir NASA (más específico para vegetación)';
        } else {
            $status = 'divergent';
            $message = 'Gran diferencia';
            $recommendation = 'Usar NASA ET - más preciso para viñedo';
        }

        return [
            'status' => $status,
            'difference_mm' => round($diff, 2),
            'difference_percent' => round($percentDiff, 1),
            'message' => $message,
            'recommendation' => $recommendation,
            'nasa_et' => $nasaET,
            'openmeteo_et' => $openMeteoET,
        ];
    }

    /**
     * Calculate crop coefficient (Kc) from ET/PET ratio
     */
    public function calculateKc(float $et, float $pet): array
    {
        if ($pet == 0) {
            return [
                'kc' => 0,
                'status' => 'error',
                'message' => 'PET es 0',
            ];
        }

        $kc = $et / $pet;
        
        // Typical Kc for vineyards: 0.3-0.7
        if ($kc < 0.3) {
            $status = 'low';
            $label = 'Bajo';
            $description = 'Planta bajo estrés o dormante';
        } elseif ($kc < 0.5) {
            $status = 'moderate';
            $label = 'Moderado';
            $description = 'Desarrollo vegetativo moderado';
        } elseif ($kc < 0.7) {
            $status = 'optimal';
            $label = 'Óptimo';
            $description = 'Desarrollo vegetativo pleno';
        } else {
            $status = 'high';
            $label = 'Alto';
            $description = 'Posible exceso de vigor';
        }

        return [
            'kc' => round($kc, 2),
            'status' => $status,
            'label' => $label,
            'description' => $description,
        ];
    }

    /**
     * Generate mock ET
     */
    private function generateMockET(Plot $plot): array
    {
        $month = now()->month;
        $seed = $plot->id * 5500 + now()->dayOfYear;
        mt_srand($seed);

        // Seasonal ET for vineyards (mm/day)
        if ($month >= 6 && $month <= 8) {
            $etBase = 4.5; // Summer: high
            $petBase = 6.0;
        } elseif ($month >= 4 && $month <= 5) {
            $etBase = 2.8;
            $petBase = 4.5;
        } elseif ($month >= 9 && $month <= 10) {
            $etBase = 2.0;
            $petBase = 3.5;
        } else {
            $etBase = 0.8; // Winter: low
            $petBase = 1.5;
        }

        $etDaily = $etBase + (mt_rand(-50, 50) / 100);
        $petDaily = $petBase + (mt_rand(-50, 50) / 100);

        mt_srand();

        return [
            'et_daily' => round($etDaily, 2),
            'pet_daily' => round($petDaily, 2),
            'et_8day' => round($etDaily * 8, 1),
            'et_source' => 'NASA MODIS MOD16A2.061 (Mock)',
        ];
    }

    /**
     * Get plot coordinates
     */
    private function getPlotCoordinates(Plot $plot): array
    {
        return [
            'lat' => $plot->latitude ?? 40.4168,
            'lon' => $plot->longitude ?? -3.7038,
        ];
    }
}
