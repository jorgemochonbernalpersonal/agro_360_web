<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Http\Client\Response;
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
    private bool $useMockData;

    public function __construct()
    {
        $this->useMockData = config('services.nasa_earthdata.mock', true);
    }

    /**
     * Fetch SMAP soil moisture
     */
    public function fetchSoilMoisture(Plot $plot, string $token, ?int $plotSigpacId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockSMAP($plot);
        }

        try {
            $coords = CoordinatesHelper::getCoordinates($plot, $plotSigpacId);

            // Open-Meteo soil moisture: free, no auth, no IP rate limit
            $response = Http::timeout(30)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'hourly' => 'soil_moisture_0_to_1cm,soil_moisture_9_to_27cm',
                    'past_days' => 1,
                    'forecast_days' => 0,
                    'timezone' => 'UTC',
                ]);

            if ($response->successful()) {
                $parsed = $this->parseSMAPResponse($response->json());
                if ($parsed !== null) {
                    return $parsed;
                }
            }

            Log::warning('Open-Meteo soil moisture failed — using estimated data', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
            ]);

        } catch (\Exception $e) {
            Log::warning('Soil moisture error — using estimated data', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);
        }

        return $this->generateMockSMAP($plot);
    }

    /**
     * Compare SMAP vs Open-Meteo
     */
    public function compareWithModel(float $smapValue, float $modelValue): array
    {
        $diff = abs($smapValue - $modelValue);

        if ($diff < 5) {
            $status = 'consistent';
            $message = __('Satélite y modelo coinciden');
            $reliability = 'high';
        } elseif ($diff < 10) {
            $status = 'acceptable';
            $message = __('Ligera diferencia');
            $reliability = 'medium';
        } else {
            $status = 'divergent';
            $message = __('Gran diferencia - revisar');
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
                'label' => __('Muy Seco'),
                'color' => 'red',
                'icon' => '🔴',
                'description' => __('Suelo extremadamente seco'),
                'recommendation' => __('Riego urgente necesario'),
                'stress_level' => 'critical',
            ];
        }

        if ($soilMoisture < 20) {
            return [
                'status' => 'dry',
                'label' => __('Seco'),
                'color' => 'orange',
                'icon' => '🟠',
                'description' => __('Suelo seco'),
                'recommendation' => __('Considerar riego pronto'),
                'stress_level' => 'high',
            ];
        }

        if ($soilMoisture < 35) {
            return [
                'status' => 'optimal',
                'label' => __('Óptimo'),
                'color' => 'green',
                'icon' => '🟢',
                'description' => __('Humedad ideal'),
                'recommendation' => __('No requiere riego'),
                'stress_level' => 'none',
            ];
        }

        if ($soilMoisture < 45) {
            return [
                'status' => 'wet',
                'label' => __('Húmedo'),
                'color' => 'blue',
                'icon' => '🔵',
                'description' => __('Suelo muy húmedo'),
                'recommendation' => __('Suspender riego'),
                'stress_level' => 'low',
            ];
        }

        return [
            'status' => 'saturated',
            'label' => __('Saturado'),
            'color' => 'purple',
            'icon' => '🟣',
            'description' => __('Suelo saturado'),
            'recommendation' => __('Riesgo encharcamiento - mejorar drenaje'),
            'stress_level' => 'moderate',
        ];
    }

    /**
     * Parse Open-Meteo soil moisture response
     */
    private function parseSMAPResponse(array $response): ?array
    {
        $hourly = $response['hourly'] ?? [];

        // Take last non-null value from the past_days window
        $surface = collect($hourly['soil_moisture_0_to_1cm'] ?? [])->filter(fn ($v) => $v !== null)->last();
        $rootzone = collect($hourly['soil_moisture_9_to_27cm'] ?? [])->filter(fn ($v) => $v !== null)->last();

        if ($surface === null) {
            return null;
        }

        // Open-Meteo returns m³/m³ → convert to %
        return [
            'soil_moisture_surface' => round($surface * 100, 1),
            'soil_moisture_rootzone' => $rootzone !== null ? round($rootzone * 100, 1) : null,
            'soil_moisture_source' => __('Open-Meteo Soil Model'),
            'resolution' => '1km',
        ];
    }

    /**
     * Generate mock SMAP data
     */
    private function generateMockSMAP(Plot $plot, ?array $coords = null): array
    {
        $month = now()->month;
        $seed = $plot->id * 4500 + now()->dayOfYear;
        mt_srand($seed);

        $lat = $coords['lat'] ?? CoordinatesHelper::getCoordinates($plot)['lat'];
        $isCanary = $lat < 30.0;

        // Seasonal soil moisture
        if ($isCanary) {
            // Canary Islands: semi-arid, drier overall
            if ($month >= 5 && $month <= 9) {
                $smBase = 8;   // Very dry summer
            } elseif ($month >= 10 || $month <= 2) {
                $smBase = 18;  // Wetter winter
            } else {
                $smBase = 12;
            }
        } else {
            if ($month >= 5 && $month <= 9) {
                $smBase = 15;
            } elseif ($month >= 10 || $month <= 2) {
                $smBase = 30;
            } else {
                $smBase = 22;
            }
        }

        $smSurface = $smBase + mt_rand(-5, 5);
        $smRootzone = $smSurface + mt_rand(2, 8); // Rootzone slightly higher

        mt_srand();

        return [
            'soil_moisture_surface' => round($smSurface, 1),
            'soil_moisture_rootzone' => round($smRootzone, 1),
            'soil_moisture_source' => __('NASA SMAP Satellite (Mock)'),
            'resolution' => '9km',
        ];
    }
}
