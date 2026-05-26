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
     * @param array|null $coordinates Override for sigpac parcel ['lat','lng'|'lon']
     */
    public function fetchLSTData(Plot $plot, string $token, ?array $coordinates = null, ?int $plotSigpacId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockLST($plot);
        }

        $coords = CoordinatesHelper::getCoordinates($plot, $plotSigpacId, $coordinates);

        // Cache 24h — MODIS LST updates every 8 days
        $cacheKey = "nasa_lst_{$plot->id}_" . ($plotSigpacId ?? 0);
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            return $this->fetchFromOpenMeteo($coords, $plot->id);
        }

        try {
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
                $result = $this->parseLSTResponse($response->json());
                \Illuminate\Support\Facades\Cache::put($cacheKey, $result, now()->addHours(24));
                return $result;
            }

            Log::warning('NASA LST API failed — falling back to Open-Meteo', [
                'status'  => $response->status(),
                'plot_id' => $plot->id,
                'body'    => substr($response->body(), 0, 300),
            ]);

        } catch (\Exception $e) {
            Log::warning('NASA LST API error — falling back to Open-Meteo', [
                'error'   => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);
        }

        $fallback = $this->fetchFromOpenMeteo($coords, $plot->id);
        if ($fallback !== null) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, $fallback, now()->addHour());
        }
        return $fallback;
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
     * Fetch real temperature data from Open-Meteo as LST fallback
     * Free, no auth, no IP rate limit, covers Canary Islands and all Spain
     */
    private function fetchFromOpenMeteo(array $coords, int $plotId): array
    {
        try {
            $response = Http::timeout(15)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'      => $coords['lat'],
                    'longitude'     => $coords['lon'],
                    'daily'         => 'temperature_2m_max,temperature_2m_min',
                    'past_days'     => 1,
                    'forecast_days' => 0,
                    'timezone'      => 'auto',
                ]);

            if ($response->successful()) {
                $daily   = $response->json('daily') ?? [];
                $tMax    = collect($daily['temperature_2m_max'] ?? [])->filter(fn($v) => $v !== null)->last();
                $tMin    = collect($daily['temperature_2m_min'] ?? [])->filter(fn($v) => $v !== null)->last();

                if ($tMax !== null && $tMin !== null) {
                    // LST day is ~4-7°C warmer than 2m air temp on sunny days
                    $lstDay   = round($tMax + 5.0, 2);
                    $lstNight = round($tMin, 2);

                    return [
                        'lst_day'           => $lstDay,
                        'lst_night'         => $lstNight,
                        'lst_diff'          => round($lstDay - $lstNight, 2),
                        'pixel_reliability' => null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Open-Meteo LST fallback failed', ['error' => $e->getMessage(), 'plot_id' => $plotId]);
        }

        // Last resort: static seasonal mock (should rarely reach here)
        return $this->generateMockLSTForCoords($coords);
    }

    /**
     * Coordinate-aware mock: uses latitude to distinguish Canary Islands from peninsula
     */
    private function generateMockLSTForCoords(array $coords): array
    {
        $month    = now()->month;
        $isCanary = $coords['lat'] < 30.0;

        if ($isCanary) {
            // Canary Islands: subtropical, mild year-round
            $lstDayBase   = match (true) {
                $month >= 6 && $month <= 9  => 32,
                $month >= 10 || $month <= 2 => 22,
                default                     => 26,
            };
            $lstNightBase = $lstDayBase - 8;
        } else {
            // Peninsula
            $lstDayBase = match (true) {
                $month >= 6 && $month <= 8  => 38,
                $month >= 4 && $month <= 5  => 28,
                $month >= 9 && $month <= 10 => 25,
                default                     => 12,
            };
            $lstNightBase = match (true) {
                $month >= 6 && $month <= 8  => 22,
                $month >= 4 && $month <= 5  => 15,
                $month >= 9 && $month <= 10 => 13,
                default                     => 2,
            };
        }

        return [
            'lst_day'           => round($lstDayBase + mt_rand(-3, 3), 2),
            'lst_night'         => round($lstNightBase + mt_rand(-2, 2), 2),
            'lst_diff'          => round($lstDayBase - $lstNightBase, 2),
            'pixel_reliability' => null,
        ];
    }

    /**
     * Generate mock LST data
     */
    private function generateMockLST(Plot $plot): array
    {
        $month    = now()->month;
        $seed     = (int) $plot->getKey() * 2000 + now()->dayOfYear;
        mt_srand($seed);

        $lat      = CoordinatesHelper::getCoordinates($plot)['lat'];
        $isCanary = $lat < 30.0;

        if ($isCanary) {
            // Canary Islands: subtropical, mild year-round
            $lstDayBase = match (true) {
                $month >= 6 && $month <= 9  => 32,
                $month >= 10 || $month <= 2 => 22,
                default                     => 26,
            };
            $lstNightBase = $lstDayBase - 8;
        } else {
            // Peninsula
            $lstDayBase = match (true) {
                $month >= 6 && $month <= 8  => 38,
                $month >= 4 && $month <= 5  => 28,
                $month >= 9 && $month <= 10 => 25,
                default                     => 12,
            };
            $lstNightBase = match (true) {
                $month >= 6 && $month <= 8  => 22,
                $month >= 4 && $month <= 5  => 15,
                $month >= 9 && $month <= 10 => 13,
                default                     => 2,
            };
        }

        $lstDay   = $lstDayBase + mt_rand(-5, 5);
        $lstNight = $lstNightBase + mt_rand(-3, 3);
        $lstDiff  = $lstDay - $lstNight;

        mt_srand();

        return [
            'lst_day'           => round($lstDay, 2),
            'lst_night'         => round($lstNight, 2),
            'lst_diff'          => round($lstDiff, 2),
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
                $recommendation = __('Daño por calor inminente - Riego refrigerante urgente');
            } elseif ($lstDay > $threshold + 5) {
                $severity = 'high';
                $recommendation = __('Estrés térmico alto - Incrementar riego');
            } else {
                $severity = 'moderate';
                $recommendation = __('Monitorear temperatura y considerar riego');
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

        // Spring (Mar-May): alert even at mild temps (budbreak/flowering vulnerable)
        // Rest of year: only alert on confirmed or near-confirmed frost (≤1°C)
        $activeThreshold = ($month >= 3 && $month <= 5) ? $warningTemp : 1;

        if ($lstNight <= $activeThreshold) {
            $severity = $lstNight <= $criticalTemp ? 'critical' : 'high';

            if ($lstNight <= $criticalTemp) {
                $riskLevel = __('Helada confirmada');
            } elseif ($lstNight <= 2) {
                $riskLevel = __('Riesgo muy alto');
            } else {
                $riskLevel = __('Riesgo moderado');
            }

            $phenologicalRisk = match (true) {
                $month === 3 || $month === 4 => 'Riesgo en brotación (daño crítico)',
                $month === 5                 => 'Riesgo en floración (pérdida cosecha)',
                $month >= 10                 => 'Posible daño en maduración tardía',
                $month <= 2                  => 'Viña en reposo — daño en yemas si < -15°C',
                default                      => 'Riesgo general',
            };

            return [
                'detected'          => true,
                'severity'          => $severity,
                'lst_night'         => $lstNight,
                'risk_level'        => $riskLevel,
                'phenological_risk' => $phenologicalRisk,
                'recommendation'    => 'Activar métodos anti-helada (aspersión, calefactores)',
            ];
        }

        return null;
    }

}
