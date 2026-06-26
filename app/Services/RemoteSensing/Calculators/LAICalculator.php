<?php

namespace App\Services\RemoteSensing\Calculators;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;

/**
 * Leaf Area Index (LAI) Calculator
 *
 * LAI represents the total one-sided leaf area per unit ground area
 * Used for: Yield prediction, canopy management, treatment optimization
 */
class LAICalculator
{
    // Calibration constants for vineyards
    private const K_EXTINCTION = 0.5;        // Light extinction coefficient for vineyards

    private const NDVI_MIN = 0.2;            // Bare soil NDVI

    private const NDVI_MAX = 0.9;            // Dense vegetation NDVI

    private const MAX_LAI_VINEYARD = 5.0;    // Maximum LAI for vineyards

    /**
     * Calculate LAI from NDVI
     * Uses empirical relationship: LAI = -ln(1 - fCover) / k
     *
     * @param float $ndvi NDVI value (-1 to 1)
     *
     * @return float LAI value (0 to ~5 for vineyards)
     */
    public function calculateFromNDVI(float $ndvi): float
    {
        // NDVI below minimum = no vegetation
        if ($ndvi <= self::NDVI_MIN) {
            return 0.0;
        }

        // Normalize NDVI to fractional vegetation cover (fCover)
        $fCover = ($ndvi - self::NDVI_MIN) / (self::NDVI_MAX - self::NDVI_MIN);
        $fCover = max(0, min(1, $fCover)); // Clamp to [0, 1]

        // Calculate LAI using Beer's Law
        // LAI = -ln(1 - fCover) / k
        if ($fCover >= 0.99) {
            return self::MAX_LAI_VINEYARD;
        }

        $lai = -log(1 - $fCover) / self::K_EXTINCTION;

        // Cap at realistic maximum for vineyards
        return min($lai, self::MAX_LAI_VINEYARD);
    }

    /**
     * Get LAI classification for vineyards
     *
     * @param float $lai LAI value
     *
     * @return array Classification with status, color, and description
     */
    public function classifyLAI(float $lai): array
    {
        return match (true) {
            $lai < 0.5 => [
                'status' => 'very_low',
                'label' => __('Muy Bajo'),
                'color' => 'red',
                'description' => __('Vegetación escasa - Posible problema'),
                'icon' => '🥀',
            ],
            $lai < 1.5 => [
                'status' => 'low',
                'label' => __('Bajo'),
                'color' => 'orange',
                'description' => __('Vigor bajo - Revisar nutrición'),
                'icon' => '🍂',
            ],
            $lai < 2.5 => [
                'status' => 'moderate',
                'label' => __('Moderado'),
                'color' => 'yellow',
                'description' => __('Vigor moderado - Normal para viñedo'),
                'icon' => '🌾',
            ],
            $lai < 3.5 => [
                'status' => 'good',
                'label' => __('Bueno'),
                'color' => 'green',
                'description' => __('Vigor saludable - Óptimo'),
                'icon' => '🌱',
            ],
            default => [
                'status' => 'very_high',
                'label' => __('Muy Alto'),
                'color' => 'emerald',
                'description' => __('Vigor muy alto - Considerar poda'),
                'icon' => '🌿',
            ],
        };
    }

    /**
     * Estimate yield based on LAI
     * Empirical relationship for vineyards
     *
     * @param float       $lai         LAI value
     * @param float       $areaHa      Plot area in hectares
     * @param string|null $varietyType Vine variety type (red/white)
     *
     * @return array Yield estimation
     */
    public function estimateYield(float $lai, float $areaHa, ?string $varietyType = 'red'): array
    {
        // Base yield per LAI unit (kg/ha per LAI unit)
        // Red varieties typically: 2500-3500 kg/ha per LAI unit
        // White varieties: 2800-4000 kg/ha per LAI unit
        $baseYieldPerLAI = $varietyType === 'white' ? 3400 : 3000;

        // Calculate estimated yield
        $yieldPerHa = $lai * $baseYieldPerLAI;

        // Apply realistic caps based on variety
        $maxYield = $varietyType === 'white' ? 15000 : 12000;
        $minYield = 1000;

        $yieldPerHa = max($minYield, min($maxYield, $yieldPerHa));
        $totalYield = $yieldPerHa * $areaHa;

        return [
            'lai' => round($lai, 2),
            'yield_per_ha' => round($yieldPerHa),
            'total_yield_kg' => round($totalYield),
            'total_yield_tons' => round($totalYield / 1000, 2),
            'confidence' => $this->getConfidence($lai),
            'variety_type' => $varietyType,
        ];
    }

    /**
     * Calculate LAI for a plot with historical context
     *
     * @param PlotRemoteSensing      $current  Current data
     * @param PlotRemoteSensing|null $lastYear Data from same period last year
     *
     * @return array Complete LAI analysis
     */
    public function getCompleteAnalysis(
        PlotRemoteSensing $current,
        ?PlotRemoteSensing $lastYear = null
    ): array {
        $currentLAI = $this->calculateFromNDVI((float) ($current->ndvi_mean ?? 0));
        $classification = $this->classifyLAI($currentLAI);

        $result = [
            'lai' => round($currentLAI, 2),
            'classification' => $classification,
            'year_comparison' => null,
        ];

        // Compare with last year if available
        if ($lastYear) {
            $lastYearLAI = $this->calculateFromNDVI((float) ($lastYear->ndvi_mean ?? 0));
            $change = $currentLAI - $lastYearLAI;
            $changePercent = $lastYearLAI > 0
                ? (($change / $lastYearLAI) * 100)
                : 0;

            $result['year_comparison'] = [
                'last_year_lai' => round($lastYearLAI, 2),
                'change' => round($change, 2),
                'change_percent' => round($changePercent, 1),
                'trend' => match (true) {
                    $changePercent > 5 => 'improving',
                    $changePercent < -5 => 'declining',
                    default => 'stable',
                },
            ];
        }

        return $result;
    }

    /**
     * Get LAI-based recommendations for vineyard management
     *
     * @param float $lai   Current LAI
     * @param int   $month Current month
     *
     * @return array Recommendations
     */
    public function getManagementRecommendations(float $lai, int $month): array
    {
        $recommendations = [];

        // Summer management (June-August)
        if ($month >= 6 && $month <= 8) {
            if ($lai > 4.0) {
                $recommendations[] = [
                    'type' => 'warning',
                    'icon' => '✂️',
                    'title' => __('Exceso de vigor'),
                    'text' => sprintf(
                        'LAI muy alto (%.1f). Considera deshojado para mejorar aireación y exposición solar.',
                        $lai
                    ),
                ];
            } elseif ($lai < 1.5) {
                $recommendations[] = [
                    'type' => 'warning',
                    'icon' => '🌱',
                    'title' => __('Vigor bajo'),
                    'text' => sprintf(
                        'LAI bajo (%.1f). Verifica riego y nutrición para optimizar producción.',
                        $lai
                    ),
                ];
            }
        }

        // Spring management (April-May)
        if ($month >= 4 && $month <= 5) {
            if ($lai < 1.0) {
                $recommendations[] = [
                    'type' => 'info',
                    'icon' => '🌿',
                    'title' => __('Desarrollo inicial'),
                    'text' => sprintf(
                        'LAI en crecimiento (%.1f). Asegurar disponibilidad de agua y nutrientes.',
                        $lai
                    ),
                ];
            }
        }

        // Fall management (September-October)
        if ($month >= 9 && $month <= 10) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '🍇',
                'title' => __('Maduración'),
                'text' => sprintf(
                    'LAI actual: %.1f. Monitoriza para vendimia. Densidad óptima del dosel.',
                    $lai
                ),
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate optimal treatment dose based on LAI
     * Higher LAI = more leaf area = more product needed
     *
     * @param float $lai                 Current LAI
     * @param float $baseDoseLitersPerHa Base dose (L/ha)
     *
     * @return float Adjusted dose
     */
    public function adjustTreatmentDose(float $lai, float $baseDoseLitersPerHa): float
    {
        // Reference LAI for standard dose
        $referenceLAI = 2.5;

        // Adjustment factor
        $factor = $lai / $referenceLAI;

        // Apply limits (don't go below 50% or above 150% of base)
        $factor = max(0.5, min(1.5, $factor));

        return round($baseDoseLitersPerHa * $factor, 1);
    }

    /**
     * Get confidence level for prediction
     *
     * @param float $lai LAI value
     *
     * @return string Confidence level
     */
    private function getConfidence(float $lai): string
    {
        return match (true) {
            $lai < 0.5 => 'very_low',    // Too little vegetation
            $lai < 1.0 => 'low',
            $lai < 4.0 => 'high',        // Sweet spot for vineyards
            default => 'medium',         // Very dense, might have issues
        };
    }
}
