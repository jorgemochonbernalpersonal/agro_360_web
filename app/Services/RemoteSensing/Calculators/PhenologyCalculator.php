<?php

namespace App\Services\RemoteSensing\Calculators;

use Carbon\Carbon;

/**
 * Calculator for Growing Degree Days (GDD) and phenological stages
 * Used for harvest prediction and crop development tracking
 */
class PhenologyCalculator
{
    private const BASE_TEMP_GRAPE = 10; // Base temperature for grapes (°C)

    private const GDD_TARGET_HARVEST = 1600; // Target GDD for harvest

    /**
     * Calculate Growing Degree Days for a single day
     *
     * @param float $tempMax  Maximum temperature (°C)
     * @param float $tempMin  Minimum temperature (°C)
     * @param float $baseTemp Base temperature (default: 10°C for grapes)
     *
     * @return float GDD for the day
     */
    public function calculateDailyGDD(
        float $tempMax,
        float $tempMin,
        float $baseTemp = self::BASE_TEMP_GRAPE
    ): float {
        // GDD = ((Tmax + Tmin) / 2) - Tbase
        $avgTemp = ($tempMax + $tempMin) / 2;

        return max(0, $avgTemp - $baseTemp);
    }

    /**
     * Calculate accumulated GDD from forecast
     *
     * @param array $forecast Array of daily forecast with temp_max and temp_min
     * @param float $baseTemp Base temperature
     *
     * @return float Total GDD
     */
    public function calculateForecastGDD(array $forecast, float $baseTemp = self::BASE_TEMP_GRAPE): float
    {
        $totalGDD = 0;

        foreach ($forecast as $day) {
            $tempMax = $day['temp_max'] ?? 20;
            $tempMin = $day['temp_min'] ?? 10;
            $totalGDD += $this->calculateDailyGDD($tempMax, $tempMin, $baseTemp);
        }

        return $totalGDD;
    }

    /**
     * Estimate accumulated GDD since growing season start (April 1st)
     * This is a simplified simulation
     *
     * @param float  $currentDailyGDD Current daily GDD
     * @param Carbon $currentDate     Current date
     *
     * @return float Estimated accumulated GDD
     */
    public function estimateAccumulatedGDD(float $currentDailyGDD, Carbon $currentDate): float
    {
        $seasonStart = Carbon::create($currentDate->year, 4, 1);

        if ($currentDate->lt($seasonStart)) {
            // Before growing season
            return 0;
        }

        $daysSinceStart = $currentDate->diffInDays($seasonStart);

        // Simplified estimation: current daily GDD * days * seasonal factor
        // Seasonal factor accounts for temperature variation throughout season
        $seasonalFactor = 0.7; // Average over the season

        return round($currentDailyGDD * max(1, $daysSinceStart) * $seasonalFactor);
    }

    /**
     * Get phenological stage based on accumulated GDD
     *
     * @param float $accumulatedGDD Accumulated GDD
     *
     * @return array Stage information [name, icon, progress]
     */
    public function getPhenologicalStage(float $accumulatedGDD): array
    {
        return match (true) {
            $accumulatedGDD < 100 => [
                'name' => __('Brotación'),
                'icon' => 'sprout',
                'progress' => 10,
                'description' => __('Bud break - Inicio del ciclo vegetativo'),
            ],
            $accumulatedGDD < 300 => [
                'name' => __('Floración'),
                'icon' => 'flower',
                'progress' => 25,
                'description' => __('Flowering - Formación de flores'),
            ],
            $accumulatedGDD < 700 => [
                'name' => __('Cuajado'),
                'icon' => 'grape',
                'progress' => 40,
                'description' => __('Fruit set - Formación inicial del fruto'),
            ],
            $accumulatedGDD < 1200 => [
                'name' => __('Envero'),
                'icon' => 'green',
                'progress' => 60,
                'description' => __('Veraison - Cambio de color de la uva'),
            ],
            $accumulatedGDD < 1600 => [
                'name' => __('Maduración'),
                'icon' => 'purple',
                'progress' => 80,
                'description' => __('Ripening - Acumulación de azúcares'),
            ],
            default => [
                'name' => __('Vendimia'),
                'icon' => 'wine',
                'progress' => 100,
                'description' => __('Harvest - Listo para cosecha'),
            ],
        };
    }

    /**
     * Estimate days to harvest
     *
     * @param float $accumulatedGDD Current accumulated GDD
     * @param float $avgDailyGDD    Average daily GDD from forecast
     * @param float $targetGDD      Target GDD for harvest (default: 1600)
     *
     * @return int|null Days to harvest, or null if already reached
     */
    public function estimateDaysToHarvest(
        float $accumulatedGDD,
        float $avgDailyGDD,
        float $targetGDD = self::GDD_TARGET_HARVEST
    ): ?int {
        if ($accumulatedGDD >= $targetGDD) {
            return null; // Already at harvest
        }

        if ($avgDailyGDD <= 0) {
            return null; // Cannot estimate
        }

        $remainingGDD = $targetGDD - $accumulatedGDD;

        return (int) round($remainingGDD / $avgDailyGDD);
    }

    /**
     * Get complete GDD analysis
     *
     * @param float  $tempMax     Maximum temperature
     * @param float  $tempMin     Minimum temperature
     * @param array  $forecast    Weather forecast
     * @param Carbon $currentDate Current date
     *
     * @return array Complete GDD analysis
     */
    public function getCompleteAnalysis(
        float $tempMax,
        float $tempMin,
        array $forecast,
        Carbon $currentDate
    ): array {
        $dailyGDD = $this->calculateDailyGDD($tempMax, $tempMin);
        $forecastGDD = $this->calculateForecastGDD($forecast);
        $avgDailyGDD = count($forecast) > 0 ? $forecastGDD / count($forecast) : $dailyGDD;
        $accumulatedGDD = $this->estimateAccumulatedGDD($dailyGDD, $currentDate);

        $stage = $this->getPhenologicalStage($accumulatedGDD);
        $daysToHarvest = $this->estimateDaysToHarvest($accumulatedGDD, $avgDailyGDD);

        return [
            'gdd_today' => round($dailyGDD, 1),
            'gdd_week_forecast' => round($forecastGDD, 1),
            'gdd_accumulated' => round($accumulatedGDD),
            'gdd_target' => self::GDD_TARGET_HARVEST,
            'avg_daily_gdd' => round($avgDailyGDD, 1),
            'stage' => $stage,
            'days_to_harvest' => $daysToHarvest,
            'estimated_harvest_date' => $daysToHarvest
                ? $currentDate->copy()->addDays($daysToHarvest)->format('d/m/Y')
                : null,
        ];
    }
}
