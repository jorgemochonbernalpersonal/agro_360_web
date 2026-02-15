<?php

namespace App\Services\RemoteSensing\Calculators;

use App\Models\PlotRemoteSensing;

/**
 * Green NDVI and Chlorophyll Content Calculator
 * 
 * GNDVI is more sensitive to chlorophyll content than NDVI
 * Used for: Early detection of nitrogen deficiency, nutritional status
 */
class ChlorophyllCalculator
{
    /**
     * Calculate GNDVI (Green Normalized Difference Vegetation Index)
     * GNDVI = (NIR - Green) / (NIR + Green)
     *
     * @param float $nir Near-Infrared reflectance (B08 in Sentinel-2)
     * @param float $green Green reflectance (B03 in Sentinel-2)
     * @return float GNDVI value (-1 to 1)
     */
    public function calculateGNDVI(float $nir, float $green): float
    {
        $sum = $nir + $green;
        
        if ($sum === 0.0) {
            return 0.0;
        }
        
        return ($nir - $green) / $sum;
    }

    /**
     * Calculate NDRE (Normalized Difference Red Edge)
     * More sensitive to chlorophyll than NDVI
     *
     * @param float $nir Near-Infrared (B08)
     * @param float $redEdge Red Edge (B05 in Sentinel-2)
     * @return float NDRE value
     */
    public function calculateNDRE(float $nir, float $redEdge): float
    {
        $sum = $nir + $redEdge;
        
        if ($sum === 0.0) {
            return 0.0;
        }
        
        return ($nir - $redEdge) / $sum;
    }

    /**
     * Estimate relative chlorophyll content from GNDVI
     * Returns value 0-100 (percentage of optimal)
     *
     * @param float $gndvi GNDVI value
     * @return float Relative chlorophyll content (0-100)
     */
    public function estimateChlorophyllContent(float $gndvi): float
    {
        // Empirical relationship for vineyards
        // GNDVI 0.3-0.7 typical range
        // 0.6+ = high chlorophyll (80-100%)
        // 0.4-0.6 = moderate (50-80%)
        // <0.4 = low (<50%)
        
        return match (true) {
            $gndvi >= 0.65 => 100,
            $gndvi >= 0.60 => 80 + (($gndvi - 0.60) / 0.05) * 20,
            $gndvi >= 0.40 => 50 + (($gndvi - 0.40) / 0.20) * 30,
            $gndvi >= 0.20 => 20 + (($gndvi - 0.20) / 0.20) * 30,
            default => max(0, $gndvi * 100),
        };
    }

    /**
     * Diagnose nutritional status by comparing GNDVI and NDVI
     *
     * @param float $gndvi GNDVI value
     * @param float $ndvi NDVI value
     * @return array Diagnosis with status, nutrient, and recommendation
     */
    public function diagnoseNutritionalStatus(float $gndvi, float $ndvi): array
    {
        // Calculate ratio (GNDVI is typically 0.9-1.1x NDVI)
        $ratio = $ndvi > 0 ? $gndvi / $ndvi : 0;
        $chlorophyll = $this->estimateChlorophyllContent($gndvi);

        return match (true) {
            $ratio > 1.1 && $chlorophyll > 80 => [
                'status' => 'excellent',
                'color' => 'green',
                'label' => 'Excelente',
                'chlorophyll_percent' => round($chlorophyll, 1),
                'diagnosis' => 'Estado nutricional óptimo',
                'recommendation' => 'Mantener programa de fertilización actual',
                'icon' => '✅',
            ],
            $ratio > 1.0 && $chlorophyll > 60 => [
                'status' => 'good',
                'color' => 'emerald',
                'label' => 'Bueno',
                'chlorophyll_percent' => round($chlorophyll, 1),
                'diagnosis' => 'Estado nutricional adecuado',
                'recommendation' => 'Continuar monitoreo estacional',
                'icon' => '🌱',
            ],
            $ratio > 0.9 && $chlorophyll > 40 => [
                'status' => 'moderate',
                'color' => 'yellow',
                'label' => 'Moderado',
                'chlorophyll_percent' => round($chlorophyll, 1),
                'diagnosis' => 'Posible deficiencia leve de nitrógeno',
                'recommendation' => 'Considera aplicación foliar de nitrógeno (10-15 kg/ha)',
                'icon' => '⚠️',
            ],
            $chlorophyll < 40 => [
                'status' => 'deficient',
                'color' => 'orange',
                'label' => 'Deficiente',
                'chlorophyll_percent' => round($chlorophyll, 1),
                'diagnosis' => 'Deficiencia de nitrógeno confirmada',
                'recommendation' => 'Aplicar fertilizante nitrogenado urgente (20-30 kg/ha de N)',
                'icon' => '🚨',
            ],
            default => [
                'status' => 'severe_deficiency',
                'color' => 'red',
                'label' => 'Deficiencia Severa',
                'chlorophyll_percent' => round($chlorophyll, 1),
                'diagnosis' => 'Deficiencia severa - múltiples nutrientes',
                'recommendation' => 'Análisis foliar urgente + fertilización correctiva completa',
                'icon' => '🆘',
            ],
        };
    }

    /**
     * Calculate nitrogen application recommendation
     *
     * @param float $gndvi Current GNDVI
     * @param float $targetGNDVI Target GNDVI for optimal growth
     * @param float $areaHa Plot area
     * @return array Nitrogen recommendation
     */
    public function calculateNitrogenNeed(
        float $gndvi,
        float $targetGNDVI = 0.65,
        float $areaHa = 1.0
    ): array {
        // Calculate deficit
        $deficit = $targetGNDVI - $gndvi;
        
        if ($deficit <= 0) {
            return [
                'nitrogen_kg_ha' => 0,
                'total_nitrogen_kg' => 0,
                'recommendation' => 'No se requiere nitrógeno adicional',
                'status' => 'sufficient',
            ];
        }

        // Empirical relationship: 0.1 GNDVI deficit ≈ 30 kg N/ha
        $nitrogenPerHa = $deficit * 300;
        
        // Apply realistic limits
        $nitrogenPerHa = max(0, min(60, $nitrogenPerHa));
        $totalNitrogen = $nitrogenPerHa * $areaHa;

        return [
            'gndvi_deficit' => round($deficit, 3),
            'nitrogen_kg_ha' => round($nitrogenPerHa, 1),
            'total_nitrogen_kg' => round($totalNitrogen, 1),
            'recommendation' => $this->getNitrogenRecommendationText($nitrogenPerHa),
            'status' => match (true) {
                $nitrogenPerHa < 15 => 'slight',
                $nitrogenPerHa < 30 => 'moderate',
                default => 'high',
            },
            'application_method' => match (true) {
                $nitrogenPerHa < 20 => 'Aplicación foliar (urea 2-3%)',
                default => 'Aplicación al suelo (urea o nitrato amónico)',
            },
        ];
    }

    /**
     * Get nitrogen recommendation text
     */
    private function getNitrogenRecommendationText(float $kgPerHa): string
    {
        return match (true) {
            $kgPerHa <= 0 => 'Sin necesidad de nitrógeno',
            $kgPerHa < 15 => sprintf('Aplicación foliar ligera: %.0f kg N/ha', $kgPerHa),
            $kgPerHa < 30 => sprintf('Aplicación moderada: %.0f kg N/ha en 2 veces', $kgPerHa),
            default => sprintf('Aplicación fuerte: %.0f kg N/ha fraccionada en 3 aplicaciones', $kgPerHa),
        };
    }

    /**
     * Detect early signs of chlorosis
     *
     * @param float $gndvi Current GNDVI
     * @param array $historicalGNDVI Historical GNDVI values
     * @return array Chlorosis detection result
     */
    public function detectChlorosis(float $gndvi, array $historicalGNDVI): array
    {
        if (empty($historicalGNDVI)) {
            return [
                'detected' => false,
                'confidence' => 'unknown',
                'message' => 'Datos históricos insuficientes',
            ];
        }

        // Calculate baseline
        $baseline = array_sum($historicalGNDVI) / count($historicalGNDVI);
        $drop = $baseline - $gndvi;
        $dropPercent = $baseline > 0 ? ($drop / $baseline) * 100 : 0;

        $detected = $dropPercent > 10; // 10% drop = likely chlorosis

        return [
            'detected' => $detected,
            'severity' => match (true) {
                $dropPercent > 30 => 'severe',
                $dropPercent > 20 => 'moderate',
                $dropPercent > 10 => 'mild',
                default => 'none',
            },
            'drop_percent' => round($dropPercent, 1),
            'confidence' => count($historicalGNDVI) > 5 ? 'high' : 'medium',
            'probable_cause' => match (true) {
                $dropPercent > 20 => 'Deficiencia severa de N o Fe (clorosis férrica)',
                $dropPercent > 10 => 'Deficiencia de nitrógeno o estrés',
                default => 'Variación estacional normal',
            },
            'action' => match (true) {
                $dropPercent > 20 => 'Análisis foliar urgente + corrección inmediata',
                $dropPercent > 10 => 'Aplicación preventiva de quelatos de hierro',
                default => 'Monitoreo continuo',
            },
        ];
    }
}
