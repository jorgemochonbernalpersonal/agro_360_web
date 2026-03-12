<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\RemoteSensing\CoordinatesHelper;

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
     *
     * @param array|null $coordinates Override del recinto ['lat','lng'|'lon']
     */
    public function fetchLSTData(Plot $plot, string $token, ?array $coordinates = null, ?int $recintoId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockLST($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            return $this->generateMockLST($plot);
        }

        try {
            $coords = CoordinatesHelper::getCoordinates($plot, $recintoId, $coordinates);
            $startJulian = 'A' . now()->subDays(8)->format('Y') . str_pad(now()->subDays(8)->dayOfYear, 3, '0', STR_PAD_LEFT);
            $endJulian   = 'A' . now()->format('Y') . str_pad(now()->dayOfYear, 3, '0', STR_PAD_LEFT);

            $response = Http::timeout(30)
                ->get('https://modis.ornl.gov/rst/api/v1/MOD11A2/subset', [
                    'latitude'     => $coords['lat'],
                    'longitude'    => $coords['lon'],
                    'startDate'    => $startJulian,
                    'endDate'      => $endJulian,
                    'kmAboveBelow' => 0,
                    'kmLeftRight'  => 0,
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseLSTResponse($response->json());
            }

            Log::warning('NASA LST API failed — using estimated data', [
                'status'  => $response->status(),
                'plot_id' => $plot->id,
                'body'    => substr($response->body(), 0, 200),
            ]);

        } catch (\Exception $e) {
            Log::warning('NASA LST API error — using estimated data', [
                'error'   => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);
        }

        return $this->generateMockLST($plot);
    }

    /**
     * Parse LST response from NASA API
     */
    private function parseLSTResponse(array $response): array
    {
        $nodata = $response['header']['NODATA_value'] ?? 0;
        $subset = collect($response['subset'] ?? []);

        $dayBand   = $subset->firstWhere('band', 'LST_Day_1km');
        $nightBand = $subset->firstWhere('band', 'LST_Night_1km');

        $lstDayRaw   = $dayBand['data'][0]   ?? null;
        $lstNightRaw = $nightBand['data'][0] ?? null;

        // Scale 0.02, Kelvin → Celsius. Valid raw range > 0 (NODATA = 0)
        $lstDay = ($lstDayRaw && $lstDayRaw != $nodata && $lstDayRaw > 0)
            ? round(($lstDayRaw * 0.02) - 273.15, 2)
            : null;

        $lstNight = ($lstNightRaw && $lstNightRaw != $nodata && $lstNightRaw > 0)
            ? round(($lstNightRaw * 0.02) - 273.15, 2)
            : null;

        return [
            'lst_day'           => $lstDay,
            'lst_night'         => $lstNight,
            'lst_diff'          => ($lstDay && $lstNight) ? round($lstDay - $lstNight, 2) : null,
            'pixel_reliability' => null,
        ];
    }

    /**
     * Generate mock LST data
     */
    private function generateMockLST(Plot $plot): array
    {
        $month = now()->month;
        $seed = (int) $plot->getKey() * 2000 + now()->dayOfYear;
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

}
