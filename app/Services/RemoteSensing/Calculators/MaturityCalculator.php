<?php

namespace App\Services\RemoteSensing\Calculators;

use App\Models\PlotRemoteSensing;
use Carbon\Carbon;

/**
 * Maturity Index Calculator for Vineyards
 *
 * Predicts grape maturity (°Brix, sugars) based on:
 * - NDVI evolution
 * - Growing Degree Days (GDD)
 * - Weather patterns
 * - Historical data
 */
class MaturityCalculator
{
    /**
     * Calculate maturity index (0-100)
     * Based on multiple factors
     *
     * @param PlotRemoteSensing $currentData    Current data
     * @param array             $historicalData Historical NDVI data
     * @param float             $gdd            Accumulated Growing Degree Days
     * @param Carbon            $veraison       Estimated veraison date
     *
     * @return array Maturity analysis
     */
    public function calculateMaturityIndex(
        PlotRemoteSensing $currentData,
        array $historicalData,
        float $gdd,
        Carbon $veraison
    ): array {
        $now = now();
        $daysFromVeraison = max(0, $now->diffInDays($veraison, false));

        // Component 1: NDVI decline rate (25% weight)
        // After veraison, NDVI should gradually decline
        $ndviScore = $this->calculateNDVIMaturityScore($currentData, $historicalData);

        // Component 2: GDD accumulation (35% weight)
        // Typical veraison to harvest: 900-1200 GDD
        $gddScore = $this->calculateGDDMaturityScore($gdd, $daysFromVeraison);

        // Component 3: Days from veraison (25% weight)
        // Typical: 40-60 days from veraison to optimal harvest
        $timeScore = $this->calculateTimeMaturityScore($daysFromVeraison);

        // Component 4: Weather stress (15% weight)
        // Heat stress can accelerate maturation
        $stressScore = $this->calculateStressMaturityScore($currentData);

        // Weighted average
        $maturityIndex = (
            ($ndviScore * 0.25) +
            ($gddScore * 0.35) +
            ($timeScore * 0.25) +
            ($stressScore * 0.15)
        );

        // Predict °Brix
        $predictedBrix = $this->predictBrix($maturityIndex);

        return [
            'maturity_index' => round($maturityIndex, 1),
            'predicted_brix' => $predictedBrix,
            'days_from_veraison' => (int) $daysFromVeraison,
            'estimated_days_to_harvest' => $this->estimateDaysToHarvest($maturityIndex),
            'optimal_harvest_date' => $this->estimateHarvestDate($maturityIndex),
            'classification' => $this->classifyMaturity($maturityIndex),
            'components' => [
                'ndvi_score' => round($ndviScore, 1),
                'gdd_score' => round($gddScore, 1),
                'time_score' => round($timeScore, 1),
                'stress_score' => round($stressScore, 1),
            ],
            'recommendations' => $this->getMaturityRecommendations($maturityIndex, $predictedBrix),
        ];
    }

    /**
     * Calculate quality index based on maturity + weather
     * Predicts potential wine quality
     */
    public function calculateQualityPotential(
        float $maturityIndex,
        array $weatherHistory,
        float $avgNDVI
    ): array {
        $qualityScore = 0;
        $maxScore = 100;

        // Factor 1: Maturity timing (30 points)
        if ($maturityIndex >= 80 && $maturityIndex <= 90) {
            $qualityScore += 30;
        } elseif ($maturityIndex >= 70 && $maturityIndex < 95) {
            $qualityScore += 20;
        } else {
            $qualityScore += 10;
        }

        // Factor 2: Consistent growth (NDVI) (30 points)
        if ($avgNDVI >= 0.6 && $avgNDVI <= 0.8) {
            $qualityScore += 30;
        } elseif ($avgNDVI >= 0.5 && $avgNDVI < 0.9) {
            $qualityScore += 20;
        } else {
            $qualityScore += 10;
        }

        // Factor 3: Weather conditions (40 points)
        // Ideal: warm days, cool nights, no excessive rain
        $weatherScore = $this->evaluateWeatherForQuality($weatherHistory);
        $qualityScore += $weatherScore;

        return [
            'quality_score' => round($qualityScore, 1),
            'classification' => match (true) {
                $qualityScore >= 85 => 'exceptional',
                $qualityScore >= 75 => 'excellent',
                $qualityScore >= 65 => 'very_good',
                $qualityScore >= 50 => 'good',
                default => 'average',
            },
            'label' => match (true) {
                $qualityScore >= 85 => __('Excepcional'),
                $qualityScore >= 75 => __('Excelente'),
                $qualityScore >= 65 => __('Muy Bueno'),
                $qualityScore >= 50 => __('Bueno'),
                default => __('Estándar'),
            },
        ];
    }

    /**
     * Calculate NDVI-based maturity score
     * Looks for characteristic NDVI decline after veraison
     */
    private function calculateNDVIMaturityScore(
        PlotRemoteSensing $current,
        array $historical
    ): float {
        if (empty($historical)) {
            return 50.0; // Neutral if no history
        }

        // Get NDVI from 2-3 weeks ago
        $recentNDVI = array_slice($historical, -3, 3);
        if (empty($recentNDVI)) {
            return 50.0;
        }

        $avgRecent = array_sum($recentNDVI) / count($recentNDVI);
        $currentNDVI = $current->ndvi_mean;

        // Calculate decline rate
        $decline = $avgRecent - $currentNDVI;
        $declinePercent = $avgRecent > 0 ? ($decline / $avgRecent) * 100 : 0;

        // Optimal decline: 10-20% from peak
        // More decline = more mature
        return match (true) {
            $declinePercent > 25 => 100,    // Heavy decline = very mature
            $declinePercent > 20 => 90,
            $declinePercent > 15 => 75,
            $declinePercent > 10 => 60,
            $declinePercent > 5 => 40,
            $declinePercent > 0 => 25,
            default => 10,                   // No decline = immature
        };
    }

    /**
     * Calculate GDD-based maturity score
     * Post-veraison GDD accumulation
     */
    private function calculateGDDMaturityScore(float $gdd, int $daysFromVeraison): float
    {
        // If before veraison
        if ($daysFromVeraison < 0) {
            return 0;
        }

        // Typical: 900-1200 GDD from veraison to harvest
        // Early varieties: 900 GDD
        // Late varieties: 1200 GDD
        $targetGDD = 1050; // Average target

        $progress = ($gdd / $targetGDD) * 100;

        return min(100, max(0, $progress));
    }

    /**
     * Calculate time-based maturity score
     */
    private function calculateTimeMaturityScore(int $daysFromVeraison): float
    {
        // Typical: 40-60 days from veraison to harvest
        if ($daysFromVeraison < 0) {
            return 0;
        }

        return match (true) {
            $daysFromVeraison >= 60 => 100,
            $daysFromVeraison >= 50 => 80 + (($daysFromVeraison - 50) / 10) * 20,
            $daysFromVeraison >= 40 => 60 + (($daysFromVeraison - 40) / 10) * 20,
            $daysFromVeraison >= 30 => 40 + (($daysFromVeraison - 30) / 10) * 20,
            $daysFromVeraison >= 20 => 20 + (($daysFromVeraison - 20) / 10) * 20,
            default => ($daysFromVeraison / 20) * 20,
        };
    }

    /**
     * Calculate stress-based maturity score
     * Heat/water stress can accelerate maturation
     */
    private function calculateStressMaturityScore(PlotRemoteSensing $data): float
    {
        $stressIndicators = 0;
        $maxIndicators = 3;

        // High temperature
        if ($data->temperature_mean > 32) {
            $stressIndicators++;
        }

        // Low soil moisture
        if ($data->soil_moisture_mean < 0.15) {
            $stressIndicators++;
        }

        // Low NDWI (water stress)
        if ($data->ndwi_mean < 0.2) {
            $stressIndicators++;
        }

        // More stress = faster maturation (not always good!)
        return ($stressIndicators / $maxIndicators) * 100;
    }

    /**
     * Predict °Brix from maturity index
     * Empirical relationship
     */
    private function predictBrix(float $maturityIndex): array
    {
        // Typical ranges:
        // Red wine: 22-26 °Brix optimal
        // White wine: 19-23 °Brix optimal

        $brix = match (true) {
            $maturityIndex >= 95 => 26.0,
            $maturityIndex >= 90 => 24.0 + (($maturityIndex - 90) / 5) * 2,
            $maturityIndex >= 80 => 22.0 + (($maturityIndex - 80) / 10) * 2,
            $maturityIndex >= 70 => 20.0 + (($maturityIndex - 70) / 10) * 2,
            $maturityIndex >= 60 => 18.0 + (($maturityIndex - 60) / 10) * 2,
            $maturityIndex >= 50 => 16.0 + (($maturityIndex - 50) / 10) * 2,
            default => 14.0 + ($maturityIndex / 50) * 2,
        };

        // Add uncertainty range
        $uncertainty = max(0.5, (100 - $maturityIndex) / 20);

        return [
            'value' => round($brix, 1),
            'min' => round($brix - $uncertainty, 1),
            'max' => round($brix + $uncertainty, 1),
            'confidence' => $maturityIndex > 60 ? 'high' : 'medium',
        ];
    }

    /**
     * Estimate days to optimal harvest
     */
    private function estimateDaysToHarvest(float $maturityIndex): int
    {
        return match (true) {
            $maturityIndex >= 85 => 0,       // Harvest now!
            $maturityIndex >= 75 => 5,
            $maturityIndex >= 65 => 10,
            $maturityIndex >= 55 => 15,
            $maturityIndex >= 45 => 20,
            $maturityIndex >= 35 => 25,
            default => 30,
        };
    }

    /**
     * Estimate harvest date
     */
    private function estimateHarvestDate(float $maturityIndex): Carbon
    {
        $days = $this->estimateDaysToHarvest($maturityIndex);

        return now()->addDays($days);
    }

    /**
     * Classify maturity level
     */
    private function classifyMaturity(float $maturityIndex): array
    {
        return match (true) {
            $maturityIndex >= 90 => [
                'level' => 'overripe',
                'label' => __('Sobremaduración'),
                'color' => 'red',
                'icon' => '🍷',
                'description' => __('Maduración excesiva - Riesgo de pérdida calidad'),
            ],
            $maturityIndex >= 80 => [
                'level' => 'optimal',
                'label' => __('Óptimo'),
                'color' => 'green',
                'icon' => '🎯',
                'description' => __('Momento ideal para vendimia'),
            ],
            $maturityIndex >= 70 => [
                'level' => 'approaching',
                'label' => __('Próximo'),
                'color' => 'emerald',
                'icon' => '🌟',
                'description' => __('Maduración avanzada - Preparar vendimia'),
            ],
            $maturityIndex >= 60 => [
                'level' => 'maturing',
                'label' => __('Madurando'),
                'color' => 'yellow',
                'icon' => '⏳',
                'description' => __('En proceso de maduración'),
            ],
            $maturityIndex >= 40 => [
                'level' => 'veraison',
                'label' => __('Envero'),
                'color' => 'orange',
                'icon' => '🔄',
                'description' => __('Inicio de maduración'),
            ],
            default => [
                'level' => 'immature',
                'label' => __('Inmaduro'),
                'color' => 'gray',
                'icon' => '🌱',
                'description' => __('Fase vegetativa - Lejos de vendimia'),
            ],
        };
    }

    /**
     * Get recommendations based on maturity
     */
    private function getMaturityRecommendations(float $maturityIndex, array $brixData): array
    {
        $recommendations = [];

        if ($maturityIndex >= 85 && $maturityIndex < 90) {
            $recommendations[] = [
                'type' => 'success',
                'icon' => '🎯',
                'title' => __('Momento óptimo de vendimia'),
                'text' => sprintf(
                    'Madurez al %.0f%%. Azúcar estimado: %.1f °Brix. Proceder con vendimia.',
                    $maturityIndex,
                    $brixData['value']
                ),
            ];
        }

        if ($maturityIndex >= 90) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => __('Sobremaduración detectada'),
                'text' => sprintf(
                    '°Brix muy alto (%.1f). Riesgo de pérdida de acidez. Vendimiar urgente.',
                    $brixData['value']
                ),
            ];
        }

        if ($maturityIndex >= 70 && $maturityIndex < 85) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '📊',
                'title' => __('Monitoreo intensivo'),
                'text' => sprintf(
                    'Madurez al %.0f%%. Muestreo de bayas cada 3-5 días recomendado.',
                    $maturityIndex
                ),
            ];
        }

        if ($maturityIndex < 60) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '⏱️',
                'title' => __('Maduración en curso'),
                'text' => sprintf(
                    'Madurez al %.0f%%. Estimado %.0f días para vendimia.',
                    $maturityIndex,
                    $this->estimateDaysToHarvest($maturityIndex)
                ),
            ];
        }

        return $recommendations;
    }

    /**
     * Evaluate weather history for wine quality
     */
    private function evaluateWeatherForQuality(array $weatherHistory): float
    {
        if (empty($weatherHistory)) {
            return 20; // Neutral
        }

        $score = 0;

        // Look for ideal conditions (last 30 days)
        $recent = array_slice($weatherHistory, -30);

        $avgTemp = array_sum(array_column($recent, 'temp_mean')) / count($recent);
        $totalRain = array_sum(array_column($recent, 'precipitation'));
        $hotDays = count(array_filter($recent, fn ($d) => ($d['temp_max'] ?? 0) > 30));
        $coolNights = count(array_filter($recent, fn ($d) => ($d['temp_min'] ?? 20) < 15));

        // Warm but not too hot (20-28°C avg)
        if ($avgTemp >= 20 && $avgTemp <= 28) {
            $score += 15;
        } elseif ($avgTemp >= 18 && $avgTemp <= 30) {
            $score += 10;
        } else {
            $score += 5;
        }

        // Moderate rainfall (<40mm/month in maturation)
        if ($totalRain < 40) {
            $score += 10;
        } elseif ($totalRain < 60) {
            $score += 5;
        }

        // Cool nights benefit quality
        if ($coolNights > 10) {
            $score += 10;
        } elseif ($coolNights > 5) {
            $score += 5;
        }

        // Not too many hot days
        if ($hotDays < 5) {
            $score += 5;
        }

        return $score;
    }
}
