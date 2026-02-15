<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NASA LST (Land Surface Temperature) Service
 * 
 * Fetches thermal data from MODIS MOD11A2.061
 * Used for: Heat stress detection, CWSI calculation, frost risk
 */
class NasaLSTService
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
     * Fetch LST data for a plot
     */
    public function fetchLSTData(Plot $plot, string $token): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockLST($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached for LST', ['plot_id' => $plot->id]);
            return null;
        }

        try {
            $coords = $this->getPlotCoordinates($plot);

            $response = Http::withToken($token)
                ->timeout(60)
                ->get("{$this->baseUrl}/bundle/MOD11A2.061/point", [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'startDate' => now()->subDays(8)->format('m-d-Y'),
                    'endDate' => now()->format('m-d-Y'),
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseLSTResponse($response->json());
            }

            Log::warning('NASA LST API request failed', ['status' => $response->status(), 'plot_id' => $plot->id]);

            if (config('app.env') !== 'production') {
                return $this->generateMockLST($plot);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('NASA LST API error', ['error' => $e->getMessage(), 'plot_id' => $plot->id]);

            if (config('app.env') !== 'production') {
                return $this->generateMockLST($plot);
            }

            return null;
        }
    }

    /**
     * Parse LST response from NASA API
     */
    private function parseLSTResponse(array $response): array
    {
        $lstDayRaw = $response['LST_Day_1km'] ?? null;
        $lstNightRaw = $response['LST_Night_1km'] ?? null;
        
        $lstDay = $lstDayRaw ? ($lstDayRaw * 0.02) - 273.15 : null;
        $lstNight = $lstNightRaw ? ($lstNightRaw * 0.02) - 273.15 : null;
        
        $lstDiff = ($lstDay && $lstNight) ? $lstDay - $lstNight : null;

        return [
            'lst_day' => $lstDay,
            'lst_night' => $lstNight,
            'lst_diff' => $lstDiff,
            'pixel_reliability' => $response['QC_Day'] ?? null,
        ];
    }

    /**
     * Generate mock LST data
     */
    private function generateMockLST(Plot $plot): array
    {
        $month = now()->month;
        $seed = $plot->id * 2000 + now()->dayOfYear;
        mt_srand($seed);

        // Seasonal LST for Spain
        if ($month >= 6 && $month <= 8) {
            $lstDayBase = 38;
            $lstNightBase = 22;
        } elseif ($month >= 4 && $month <= 5) {
            $lstDayBase = 28;
            $lstNightBase = 15;
        } elseif ($month >= 9 && $month <= 10) {
            $lstDayBase = 25;
            $lstNightBase = 13;
        } else {
            $lstDayBase = 12;
            $lstNightBase = 2;
        }

        $lstDay = $lstDayBase + mt_rand(-5, 5);
        $lstNight = $lstNightBase + mt_rand(-3, 3);
        $lstDiff = $lstDay - $lstNight;

        mt_srand();

        return [
            'lst_day' => round($lstDay, 2),
            'lst_night' => round($lstNight, 2),
            'lst_diff' => round($lstDiff, 2),
            'pixel_reliability' => 0,
        ];
    }

    /**
     * Calculate CWSI (Crop Water Stress Index) using LST
     */
    public function calculateCWSI(float $lstDay, float $airTemp, float $humidity): float
    {
        $vpdEffect = (100 - $humidity) / 100;
        $twet = $airTemp - (4 * (1 - $vpdEffect));
        $tdry = $airTemp + 8;
        $cwsi = ($lstDay - $twet) / ($tdry - $twet);
        
        return max(0, min(1, $cwsi));
    }

    /**
     * Classify CWSI level
     */
    public function classifyCWSI(float $cwsi): array
    {
        if ($cwsi < 0.2) {
            return [
                'status' => 'no_stress',
                'label' => 'Sin Estrés',
                'color' => 'green',
                'icon' => '✅',
                'description' => 'Planta bien hidratada',
            ];
        }
        
        if ($cwsi < 0.4) {
            return [
                'status' => 'mild_stress',
                'label' => 'Estrés Leve',
                'color' => 'yellow',
                'icon' => '⚠️',
                'description' => 'Leve estrés hídrico',
            ];
        }
        
        if ($cwsi < 0.6) {
            return [
                'status' => 'moderate_stress',
                'label' => 'Estrés Moderado',
                'color' => 'orange',
                'icon' => '⚠️',
                'description' => 'Estrés moderado - Considerar riego',
            ];
        }
        
        if ($cwsi < 0.8) {
            return [
                'status' => 'high_stress',
                'label' => 'Estrés Alto',
                'color' => 'red',
                'icon' => '🚨',
                'description' => 'Estrés severo - Riego urgente',
            ];
        }
        
        return [
            'status' => 'critical_stress',
            'label' => 'Estrés Crítico',
            'color' => 'red',
            'icon' => '🆘',
            'description' => 'Estrés crítico - Daño permanente posible',
        ];
    }

    /**
     * Detect heat stress
     */
    public function detectHeatStress(float $lstDay, int $month): ?array
    {
        // Heat stress thresholds for vineyards
        if ($month >= 6 && $month <= 8) {
            $threshold = 42; // Summer: higher tolerance
        } else {
            $threshold = 38; // Other months: lower threshold
        }

        if ($lstDay > $threshold) {
            $excess = $lstDay - $threshold;
            
            if ($lstDay > $threshold + 10) {
                $severity = 'critical';
                $recommendation = 'Daño por calor inminente - Riego refrigerante urgente';
            } elseif ($lstDay > $threshold + 5) {
                $severity = 'high';
                $recommendation = 'Estrés térmico alto - Incrementar riego';
            } else {
                $severity = 'moderate';
                $recommendation = 'Monitorear temperatura y considerar riego';
            }
            
            return [
                'detected' => true,
                'severity' => $severity,
                'lst_day' => $lstDay,
                'threshold' => $threshold,
                'excess' => round($excess, 1),
                'recommendation' => $recommendation,
            ];
        }

        return null;
    }

    /**
     * Detect frost risk
     */
    public function detectFrostRisk(float $lstNight, int $month): ?array
    {
        $criticalTemp = 0;
        $warningTemp = 3;

        if ($lstNight <= $warningTemp && $month >= 3 && $month <= 5) {
            $severity = $lstNight <= $criticalTemp ? 'critical' : 'high';
            
            if ($lstNight <= $criticalTemp) {
                $riskLevel = 'Helada confirmada';
            } elseif ($lstNight <= 2) {
                $riskLevel = 'Riesgo muy alto';
            } else {
                $riskLevel = 'Riesgo moderado';
            }
            
            if ($month === 3 || $month === 4) {
                $phenologicalRisk = 'Riesgo en brotación (daño crítico)';
            } elseif ($month === 5) {
                $phenologicalRisk = 'Riesgo en floración (pérdida cosecha)';
            } else {
                $phenologicalRisk = 'Riesgo general';
            }
            
            return [
                'detected' => true,
                'severity' => $severity,
                'lst_night' => $lstNight,
                'risk_level' => $riskLevel,
                'phenological_risk' => $phenologicalRisk,
                'recommendation' => 'Activar métodos anti-helada (aspersión, calefactores)',
            ];
        }

        return null;
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
