<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;

class IrrigationRecommendationService
{
    // Thresholds for irrigation recommendations
    private const NDVI_STRESS_THRESHOLD = 0.4;
    private const NDWI_STRESS_THRESHOLD = -0.1;
    private const SOIL_MOISTURE_LOW = 20;
    private const SOIL_MOISTURE_CRITICAL = 10;
    private const ET0_HIGH = 6.0; // mm/day

    /**
     * Get irrigation recommendation for a plot
     */
    public function getRecommendation(Plot $plot): array
    {
        $latestData = PlotRemoteSensing::where('plot_id', $plot->id)
            ->orderBy('image_date', 'desc')
            ->first();

        if (!$latestData) {
            return $this->noDataRecommendation();
        }

        return $this->calculateRecommendation($latestData);
    }

    /**
     * Calculate irrigation recommendation based on remote sensing data
     */
    private function calculateRecommendation(PlotRemoteSensing $data): array
    {
        $stressFactors = [];
        $urgencyScore = 0;

        // Check NDVI stress
        if ($data->ndvi_mean !== null && $data->ndvi_mean < self::NDVI_STRESS_THRESHOLD) {
            $stressFactors[] = 'Vegetación estresada (NDVI bajo)';
            $urgencyScore += $data->ndvi_mean < 0.3 ? 30 : 20;
        }

        // Check NDWI (water stress)
        if ($data->ndwi_mean !== null && $data->ndwi_mean < self::NDWI_STRESS_THRESHOLD) {
            $stressFactors[] = 'Contenido hídrico bajo (NDWI)';
            $urgencyScore += $data->ndwi_mean < -0.2 ? 30 : 20;
        }

        // Check soil moisture
        if ($data->soil_moisture !== null) {
            if ($data->soil_moisture < self::SOIL_MOISTURE_CRITICAL) {
                $stressFactors[] = 'Humedad del suelo crítica';
                $urgencyScore += 35;
            } elseif ($data->soil_moisture < self::SOIL_MOISTURE_LOW) {
                $stressFactors[] = 'Humedad del suelo baja';
                $urgencyScore += 20;
            }
        }

        // Check evapotranspiration
        if ($data->et0 !== null && $data->et0 > self::ET0_HIGH) {
            $stressFactors[] = 'Alta evapotranspiración';
            $urgencyScore += 15;
        }

        // Check temperature
        if ($data->temperature !== null && $data->temperature > 35) {
            $stressFactors[] = 'Temperatura muy alta';
            $urgencyScore += 15;
        }

        // Calculate recommendation
        $recommendation = $this->getRecommendationLevel($urgencyScore);
        $waterAmount = $this->calculateWaterAmount($data, $urgencyScore);

        return [
            'level' => $recommendation['level'],
            'level_text' => $recommendation['text'],
            'level_color' => $recommendation['color'],
            'level_icon' => $recommendation['icon'],
            'urgency_score' => $urgencyScore,
            'stress_factors' => $stressFactors,
            'water_amount_mm' => $waterAmount,
            'water_amount_text' => $this->formatWaterAmount($waterAmount),
            'last_updated' => $data->image_date->format('d/m/Y'),
            'ndvi' => $data->ndvi_mean,
            'ndwi' => $data->ndwi_mean,
            'soil_moisture' => $data->soil_moisture,
            'temperature' => $data->temperature,
            'et0' => $data->et0,
        ];
    }

    /**
     * Get recommendation level based on urgency score
     */
    private function getRecommendationLevel(int $score): array
    {
        return match (true) {
            $score >= 70 => [
                'level' => 'urgent',
                'text' => 'Riego urgente',
                'color' => 'red',
                'icon' => '🚨',
            ],
            $score >= 50 => [
                'level' => 'high',
                'text' => 'Riego recomendado',
                'color' => 'orange',
                'icon' => '💧',
            ],
            $score >= 30 => [
                'level' => 'moderate',
                'text' => 'Riego moderado',
                'color' => 'yellow',
                'icon' => '🌱',
            ],
            $score >= 15 => [
                'level' => 'low',
                'text' => 'Riego ligero',
                'color' => 'blue',
                'icon' => '💦',
            ],
            default => [
                'level' => 'none',
                'text' => 'Sin necesidad de riego',
                'color' => 'green',
                'icon' => '✅',
            ],
        };
    }

    /**
     * Calculate estimated water amount needed
     */
    private function calculateWaterAmount(PlotRemoteSensing $data, int $urgencyScore): float
    {
        $baseAmount = 0;

        // Base on ET0 if available
        if ($data->et0 !== null) {
            $baseAmount = $data->et0 * 1.1; // 110% of ET0
        }

        // Adjust based on soil moisture
        if ($data->soil_moisture !== null) {
            $moistureDeficit = max(0, 30 - $data->soil_moisture);
            $baseAmount += $moistureDeficit * 0.2;
        }

        // Minimum based on urgency
        $minAmount = match (true) {
            $urgencyScore >= 70 => 15,
            $urgencyScore >= 50 => 10,
            $urgencyScore >= 30 => 5,
            $urgencyScore >= 15 => 3,
            default => 0,
        };

        return max($baseAmount, $minAmount);
    }

    /**
     * Format water amount for display
     */
    private function formatWaterAmount(float $amount): string
    {
        if ($amount <= 0) {
            return 'No necesario';
        }

        return number_format($amount, 1) . ' mm/día';
    }

    /**
     * No data recommendation
     */
    private function noDataRecommendation(): array
    {
        return [
            'level' => 'unknown',
            'level_text' => 'Sin datos',
            'level_color' => 'gray',
            'level_icon' => '❓',
            'urgency_score' => 0,
            'stress_factors' => [],
            'water_amount_mm' => 0,
            'water_amount_text' => 'Sin datos disponibles',
            'last_updated' => null,
            'ndvi' => null,
            'ndwi' => null,
            'soil_moisture' => null,
            'temperature' => null,
            'et0' => null,
        ];
    }
}
