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
    private bool $useMockData;

    private RateLimitService $rateLimitService;

    public function __construct(RateLimitService $rateLimitService)
    {
        $this->useMockData = config('services.nasa_earthdata.mock', true);
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Fetch official ET from MODIS
     */
    public function fetchEvapotranspiration(Plot $plot, string $token, ?int $plotSigpacId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockET($plot);
        }

        if (! $this->rateLimitService->canMakeNasaRequest()) {
            return $this->generateMockET($plot);
        }

        try {
            $coords = CoordinatesHelper::getCoordinates($plot, $plotSigpacId);
            $startJulian = 'A'.now()->subDays(8)->format('Y').str_pad((string) now()->subDays(8)->dayOfYear, 3, '0', STR_PAD_LEFT);
            $endJulian = 'A'.now()->format('Y').str_pad((string) now()->dayOfYear, 3, '0', STR_PAD_LEFT);

            // MOD16A2: MODIS ET 500m, 8-day
            $response = Http::timeout(30)
                ->get('https://modis.ornl.gov/rst/api/v1/MOD16A2/subset', [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'startDate' => $startJulian,
                    'endDate' => $endJulian,
                    'kmAboveBelow' => 0,
                    'kmLeftRight' => 0,
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseETResponse($response->json());
            }

            Log::warning('NASA ET API failed — using estimated data', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
                'body' => substr($response->body(), 0, 200),
            ]);

        } catch (\Exception $e) {
            Log::warning('NASA ET API error — using estimated data', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);
        }

        return $this->generateMockET($plot);
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
            $message = __('NASA y Open-Meteo coinciden');
            $recommendation = __('Ambos datos fiables');
        } elseif ($percentDiff < 20) {
            $status = 'acceptable';
            $message = __('Ligera diferencia');
            $recommendation = __('Preferir NASA (más específico para vegetación)');
        } else {
            $status = 'divergent';
            $message = __('Gran diferencia');
            $recommendation = __('Usar NASA ET - más preciso para viñedo');
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
                'message' => __('PET es 0'),
            ];
        }

        $kc = $et / $pet;

        // Typical Kc for vineyards: 0.3-0.7
        if ($kc < 0.3) {
            $status = 'low';
            $label = __('Bajo');
            $description = __('Planta bajo estrés o dormante');
        } elseif ($kc < 0.5) {
            $status = 'moderate';
            $label = __('Moderado');
            $description = __('Desarrollo vegetativo moderado');
        } elseif ($kc < 0.7) {
            $status = 'optimal';
            $label = __('Óptimo');
            $description = __('Desarrollo vegetativo pleno');
        } else {
            $status = 'high';
            $label = __('Alto');
            $description = __('Posible exceso de vigor');
        }

        return [
            'kc' => round($kc, 2),
            'status' => $status,
            'label' => $label,
            'description' => $description,
        ];
    }

    /**
     * Parse ET response
     */
    private function parseETResponse(array $response): array
    {
        $nodata = $response['header']['NODATA_value'] ?? 32767;
        $subset = collect($response['subset'] ?? []);

        $etBand = $subset->firstWhere('band', 'ET_500m');
        $petBand = $subset->firstWhere('band', 'PET_500m');

        $etRaw = $etBand['data'][0] ?? null;
        $petRaw = $petBand['data'][0] ?? null;

        // Scale 0.1 (kg/m²/8day = mm/8day), NODATA typically 32767
        $et8day = ($etRaw !== null && $etRaw != $nodata && $etRaw > 0) ? $etRaw * 0.1 : null;
        $pet8day = ($petRaw !== null && $petRaw != $nodata && $petRaw > 0) ? $petRaw * 0.1 : null;

        return [
            'et_daily' => $et8day ? round($et8day / 8, 2) : null,
            'pet_daily' => $pet8day ? round($pet8day / 8, 2) : null,
            'et_8day' => $et8day ? round($et8day, 1) : null,
            'et_source' => __('NASA MODIS MOD16A2.061'),
        ];
    }

    /**
     * Generate mock ET
     */
    private function generateMockET(Plot $plot, ?array $coords = null): array
    {
        $month = now()->month;
        $seed = $plot->id * 5500 + now()->dayOfYear;
        mt_srand($seed);

        $lat = $coords['lat'] ?? CoordinatesHelper::getCoordinates($plot)['lat'];
        $isCanary = $lat < 30.0;

        // Seasonal ET for vineyards (mm/day)
        if ($isCanary) {
            if ($month >= 6 && $month <= 9) {
                $etBase = 5.0;
                $petBase = 7.0;
            } elseif ($month >= 10 || $month <= 2) {
                $etBase = 2.5;
                $petBase = 4.0;
            } else {
                $etBase = 3.5;
                $petBase = 5.5;
            }
        } else {
            if ($month >= 6 && $month <= 8) {
                $etBase = 4.5;
                $petBase = 6.0;
            } elseif ($month >= 4 && $month <= 5) {
                $etBase = 2.8;
                $petBase = 4.5;
            } elseif ($month >= 9 && $month <= 10) {
                $etBase = 2.0;
                $petBase = 3.5;
            } else {
                $etBase = 0.8;
                $petBase = 1.5;
            }
        }

        $etDaily = $etBase + (mt_rand(-50, 50) / 100);
        $petDaily = $petBase + (mt_rand(-50, 50) / 100);

        mt_srand();

        return [
            'et_daily' => round($etDaily, 2),
            'pet_daily' => round($petDaily, 2),
            'et_8day' => round($etDaily * 8, 1),
            'et_source' => __('NASA MODIS MOD16A2.061 (Mock)'),
        ];
    }
}
