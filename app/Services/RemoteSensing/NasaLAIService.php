<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\RemoteSensing\CoordinatesHelper;

/**
 * NASA Official LAI Service
 * 
 * Fetches LAI (Leaf Area Index) directly from MODIS
 * More accurate than calculated LAI for vineyards
 */
class NasaLAIService
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
     * Fetch official LAI from MODIS
     */
    public function fetchOfficialLAI(Plot $plot, string $token, ?int $recintoId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockLAI($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            return $this->generateMockLAI($plot);
        }

        try {
            $coords = CoordinatesHelper::getCoordinates($plot, $recintoId);
            $startJulian = 'A' . now()->subDays(8)->format('Y') . str_pad(now()->subDays(8)->dayOfYear, 3, '0', STR_PAD_LEFT);
            $endJulian   = 'A' . now()->format('Y') . str_pad(now()->dayOfYear, 3, '0', STR_PAD_LEFT);

            // MCD15A2H: MODIS LAI/FPAR 500m, 8-day
            $response = Http::timeout(30)
                ->get('https://modis.ornl.gov/rst/api/v1/MCD15A2H/subset', [
                    'latitude'     => $coords['lat'],
                    'longitude'    => $coords['lon'],
                    'startDate'    => $startJulian,
                    'endDate'      => $endJulian,
                    'kmAboveBelow' => 0,
                    'kmLeftRight'  => 0,
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseLAIResponse($response->json());
            }

            Log::warning('NASA LAI API failed — using estimated data', [
                'status'  => $response->status(),
                'plot_id' => $plot->id,
                'body'    => substr($response->body(), 0, 200),
            ]);

        } catch (\Exception $e) {
            Log::warning('NASA LAI API error — using estimated data', [
                'error'   => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);
        }

        return $this->generateMockLAI($plot);
    }

    /**
     * Parse LAI response from NASA
     */
    private function parseLAIResponse(array $response): array
    {
        $nodata = $response['header']['NODATA_value'] ?? 255;
        $subset = collect($response['subset'] ?? []);

        $laiBand  = $subset->firstWhere('band', 'Lai_500m');
        $fparBand = $subset->firstWhere('band', 'Fpar_500m');
        $qcBand   = $subset->firstWhere('band', 'FparLai_QC');

        $laiRaw  = $laiBand['data'][0]  ?? null;
        $fparRaw = $fparBand['data'][0] ?? null;
        $qcRaw   = $qcBand['data'][0]   ?? null;

        // LAI scale 0.1, FPAR scale 0.01
        $lai  = ($laiRaw  !== null && $laiRaw  != $nodata && $laiRaw  > 0) ? round($laiRaw  * 0.1,  2) : null;
        $fpar = ($fparRaw !== null && $fparRaw != $nodata && $fparRaw > 0) ? round($fparRaw * 0.01, 3) : null;

        return [
            'lai'        => $lai,
            'fpar'       => $fpar,
            'lai_quality'=> $qcRaw,
            'lai_source' => 'NASA MODIS MCD15A2H.061',
        ];
    }

    /**
     * Classify LAI for vineyards (official data)
     */
    public function classifyLAI(float $lai): array
    {
        if ($lai < 0.5) {
            return [
                'category' => 'very_low',
                'label' => 'Muy Bajo',
                'color' => 'red',
                'description' => 'Vegetación escasa o estrés severo',
                'icon' => '🔴',
                'recommendation' => 'Investigar causa de bajo vigor',
            ];
        }
        
        if ($lai < 1.5) {
            return [
                'category' => 'low',
                'label' => 'Bajo',
                'color' => 'orange',
                'description' => 'Desarrollo vegetativo limitado',
                'icon' => '🟠',
                'recommendation' => 'Mejorar manejo nutricional/hídrico',
            ];
        }
        
        if ($lai < 3.0) {
            return [
                'category' => 'optimal',
                'label' => 'Óptimo',
                'color' => 'green',
                'description' => 'LAI ideal para viñedo',
                'icon' => '🟢',
                'recommendation' => 'Mantener manejo actual',
            ];
        }
        
        if ($lai < 4.0) {
            return [
                'category' => 'high',
                'label' => 'Alto',
                'color' => 'yellow',
                'description' => 'Vigor excesivo',
                'icon' => '🟡',
                'recommendation' => 'Considerar poda en verde o reducir N',
            ];
        }
        
        return [
            'category' => 'very_high',
            'label' => 'Muy Alto',
            'color' => 'blue',
            'description' => 'Vigor excesivo - riesgo enfermedades',
            'icon' => '🔵',
            'recommendation' => 'Poda severa y ajuste fertilización',
        ];
    }

    /**
     * Estimate yield from official LAI
     */
    public function estimateYield(float $lai, float $areaHa, ?string $varietyType = 'red'): array
    {
        // Calibrated relationship for vineyards (tons/ha)
        // Red: 5-8 tons/ha optimal, White: 8-12 tons/ha optimal
        $baseYield = $varietyType === 'red' ? 6.5 : 10;
        
        // LAI factor (optimal LAI = 2.5 for vineyards)
        $laiFactor = min(1.5, $lai / 2.5);
        
        $yieldPerHa = $baseYield * $laiFactor;
        $totalYield = $yieldPerHa * $areaHa;

        return [
            'yield_per_ha' => round($yieldPerHa, 2),
            'total_yield_tons' => round($totalYield, 2),
            'total_yield_kg' => round($totalYield * 1000, 0),
            'confidence' => $this->calculateYieldConfidence($lai),
            'variety_type' => $varietyType,
        ];
    }

    /**
     * Calculate yield prediction confidence
     */
    private function calculateYieldConfidence(float $lai): string
    {
        if ($lai >= 1.5 && $lai <= 3.5) {
            return 'high'; // Within typical range
        } elseif ($lai >= 1.0 && $lai <= 4.5) {
            return 'medium';
        } else {
            return 'low'; // Outside typical range
        }
    }

    /**
     * Calculate FPAR (Fraction of PAR absorbed)
     * Indicates photosynthetic potential
     */
    public function analyzeFPAR(?float $fpar): ?array
    {
        if ($fpar === null) {
            return null;
        }

        if ($fpar < 0.3) {
            $status = 'low';
            $label = 'Baja Absorción';
            $description = 'Baja capacidad fotosintética';
        } elseif ($fpar < 0.6) {
            $status = 'moderate';
            $label = 'Absorción Moderada';
            $description = 'Capacidad fotosintética normal';
        } elseif ($fpar < 0.8) {
            $status = 'high';
            $label = 'Alta Absorción';
            $description = 'Óptima capacidad fotosintética';
        } else {
            $status = 'very_high';
            $label = 'Absorción Muy Alta';
            $description = 'Máxima actividad fotosintética';
        }

        return [
            'fpar' => round($fpar, 3),
            'status' => $status,
            'label' => $label,
            'description' => $description,
            'photosynthetic_efficiency' => round($fpar * 100, 1) . '%',
        ];
    }

    /**
     * Generate mock LAI (realistic for vineyards)
     */
    private function generateMockLAI(Plot $plot): array
    {
        $month = now()->month;
        $seed = $plot->id * 3500 + now()->dayOfYear;
        mt_srand($seed);

        // Seasonal LAI for vineyards
        if ($month >= 5 && $month <= 9) {
            $laiBase = 2.5; // Growing season
            $fparBase = 0.65;
        } elseif ($month >= 3 && $month <= 4) {
            $laiBase = 1.2; // Early growth
            $fparBase = 0.40;
        } elseif ($month >= 10 && $month <= 11) {
            $laiBase = 1.5; // Senescence
            $fparBase = 0.45;
        } else {
            $laiBase = 0.3; // Dormant
            $fparBase = 0.15;
        }

        $lai = $laiBase + (mt_rand(-30, 30) / 100);
        $fpar = $fparBase + (mt_rand(-10, 10) / 100);

        mt_srand();

        return [
            'lai' => round(max(0, $lai), 2),
            'fpar' => round(max(0, min(1, $fpar)), 3),
            'lai_quality' => 0,
            'lai_source' => 'NASA MODIS MCD15A2H.061 (Mock)',
        ];
    }

}
