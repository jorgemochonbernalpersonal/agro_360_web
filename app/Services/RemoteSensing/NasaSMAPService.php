<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NASA SMAP Soil Moisture Service
 * 
 * SMAP (Soil Moisture Active Passive)
 * - Resolution: 9km (regional coverage)
 * - Depth: 0-5cm
 * - Real satellite measurement vs model estimation
 */
class NasaSMAPService
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
     * Fetch SMAP soil moisture
     */
    public function fetchSoilMoisture(Plot $plot, string $token): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockSMAP($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached for SMAP', ['plot_id' => $plot->id]);
            return null;
        }

        try {
            $coords = $this->getPlotCoordinates($plot);

            // SPL4SMGP: SMAP L4 Global 9km daily
            $response = Http::withToken($token)
                ->timeout(60)
                ->get("{$this->baseUrl}/bundle/SPL4SMGP.007/point", [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'startDate' => now()->subDay()->format('m-d-Y'),
                    'endDate' => now()->format('m-d-Y'),
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseSMAPResponse($response->json());
            }

            Log::warning('NASA SMAP API request failed', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
            ]);

            if (config('app.env') !== 'production') {
                return $this->generateMockSMAP($plot);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('NASA SMAP API error', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);

            if (config('app.env') !== 'production') {
                return $this->generateMockSMAP($plot);
            }

            return null;
        }
    }

    /**
     * Parse SMAP response
     */
    private function parseSMAPResponse(array $response): array
    {
        // Soil moisture in m³/m³ (convert to %)
        $smSurface = ($response['sm_surface'] ?? null);
        $smRootzone = ($response['sm_rootzone'] ?? null);
        
        // Convert to percentage
        $soilMoistureSurface = $smSurface ? $smSurface * 100 : null;
        $soilMoistureRootzone = $smRootzone ? $smRootzone * 100 : null;

        return [
            'soil_moisture_surface' => $soilMoistureSurface,
            'soil_moisture_rootzone' => $soilMoistureRootzone,
            'soil_moisture_source' => 'NASA SMAP Satellite',
            'resolution' => '9km',
        ];
    }

    /**
     * Compare SMAP vs Open-Meteo
     */
    public function compareWithModel(float $smapValue, float $modelValue): array
    {
        $diff = abs($smapValue - $modelValue);
        
        if ($diff < 5) {
            $status = 'consistent';
            $message = 'Satélite y modelo coinciden';
            $reliability = 'high';
        } elseif ($diff < 10) {
            $status = 'acceptable';
            $message = 'Ligera diferencia';
            $reliability = 'medium';
        } else {
            $status = 'divergent';
            $message = 'Gran diferencia - revisar';
            $reliability = 'low';
        }

        return [
            'status' => $status,
            'difference' => round($diff, 1),
            'message' => $message,
            'reliability' => $reliability,
            'smap_value' => $smapValue,
            'model_value' => $modelValue,
            'recommendation' => $reliability === 'low' 
                ? 'Usar dato satelital (SMAP) - más fiable'
                : 'Ambos datos fiables',
        ];
    }

    /**
     * Classify soil moisture status
     */
    public function classifySoilMoisture(float $soilMoisture): array
    {
        if ($soilMoisture < 10) {
            return [
                'status' => 'very_dry',
                'label' => 'Muy Seco',
                'color' => 'red',
                'icon' => '🔴',
                'description' => 'Suelo extremadamente seco',
                'recommendation' => 'Riego urgente necesario',
                'stress_level' => 'critical',
            ];
        }
        
        if ($soilMoisture < 20) {
            return [
                'status' => 'dry',
                'label' => 'Seco',
                'color' => 'orange',
                'icon' => '🟠',
                'description' => 'Suelo seco',
                'recommendation' => 'Considerar riego pronto',
                'stress_level' => 'high',
            ];
        }
        
        if ($soilMoisture < 35) {
            return [
                'status' => 'optimal',
                'label' => 'Óptimo',
                'color' => 'green',
                'icon' => '🟢',
                'description' => 'Humedad ideal',
                'recommendation' => 'No requiere riego',
                'stress_level' => 'none',
            ];
        }
        
        if ($soilMoisture < 45) {
            return [
                'status' => 'wet',
                'label' => 'Húmedo',
                'color' => 'blue',
                'icon' => '🔵',
                'description' => 'Suelo muy húmedo',
                'recommendation' => 'Suspender riego',
                'stress_level' => 'low',
            ];
        }
        
        return [
            'status' => 'saturated',
            'label' => 'Saturado',
            'color' => 'purple',
            'icon' => '🟣',
            'description' => 'Suelo saturado',
            'recommendation' => 'Riesgo encharcamiento - mejorar drenaje',
            'stress_level' => 'moderate',
        ];
    }

    /**
     * Generate mock SMAP data
     */
    private function generateMockSMAP(Plot $plot): array
    {
        $month = now()->month;
        $seed = $plot->id * 4500 + now()->dayOfYear;
        mt_srand($seed);

        // Seasonal soil moisture
        if ($month >= 5 && $month <= 9) {
            $smBase = 15; // Summer: dry
        } elseif ($month >= 10 || $month <= 2) {
            $smBase = 30; // Winter: wet
        } else {
            $smBase = 22; // Spring: moderate
        }

        $smSurface = $smBase + mt_rand(-5, 5);
        $smRootzone = $smSurface + mt_rand(2, 8); // Rootzone slightly higher

        mt_srand();

        return [
            'soil_moisture_surface' => round($smSurface, 1),
            'soil_moisture_rootzone' => round($smRootzone, 1),
            'soil_moisture_source' => 'NASA SMAP Satellite (Mock)',
            'resolution' => '9km',
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
