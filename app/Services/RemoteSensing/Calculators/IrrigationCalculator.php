<?php

namespace App\Services\RemoteSensing\Calculators;

use App\DataTransferObjects\RemoteSensing\IrrigationNeedDTO;

/**
 * Calculator for irrigation needs based on ET0, soil moisture, and weather
 * Uses FAO-56 methodology
 */
class IrrigationCalculator
{
    /**
     * Calculate irrigation needs for vineyards
     *
     * @param float      $et0           Reference evapotranspiration (mm/day)
     * @param float      $soilMoisture  Soil moisture percentage
     * @param float      $precipitation Expected precipitation (mm)
     * @param int        $month         Current month (1-12)
     * @param float|null $plotArea      Plot area in hectares (optional)
     */
    public function calculateNeed(
        float $et0,
        float $soilMoisture,
        float $precipitation,
        int $month,
        ?float $plotArea = null
    ): IrrigationNeedDTO {
        // Crop coefficient for vineyards (varies by season)
        $kc = $this->getCropCoefficient($month);

        // ETc = ET0 * Kc (crop evapotranspiration)
        $etc = $et0 * $kc;

        // Weekly water need (mm)
        $weeklyNeed = $etc * 7;

        // Effective precipitation (only 80% is useful)
        $effectivePrecip = $precipitation * 0.8;

        // Irrigation need = ETc - effective precipitation - soil reserve
        $soilReserve = max(0, ($soilMoisture - 20) * 0.5); // Available water above wilting point
        $irrigationNeed = max(0, $weeklyNeed - $effectivePrecip - $soilReserve);

        // Convert to liters per hectare (1mm = 10,000 L/ha)
        $litersPerHa = (int) round($irrigationNeed * 10000);

        // Generate recommendation
        [$text, $color, $bg] = $this->getRecommendation($irrigationNeed);

        return new IrrigationNeedDTO(
            et0: $et0,
            kc: $kc,
            etc: $etc,
            weeklyNeedMm: $weeklyNeed,
            expectedRainMm: $effectivePrecip,
            soilReserveMm: $soilReserve,
            irrigationNeedMm: $irrigationNeed,
            litersPerHa: $litersPerHa,
            recommendationText: $text,
            recommendationColor: $color,
            recommendationBg: $bg,
        );
    }

    /**
     * Calculate total water need for a plot
     *
     * @param float $irrigationNeedMm Irrigation need in mm
     * @param float $areaHa           Area in hectares
     *
     * @return float Total liters needed
     */
    public function calculateTotalLiters(float $irrigationNeedMm, float $areaHa): float
    {
        return $irrigationNeedMm * 10000 * $areaHa;
    }

    /**
     * Get crop coefficient based on phenological stage (month)
     *
     * @param int $month Month (1-12)
     *
     * @return float Kc value
     */
    private function getCropCoefficient(int $month): float
    {
        return match (true) {
            $month >= 6 && $month <= 8 => 0.85,  // Peak season (verano)
            $month >= 4 && $month <= 5 => 0.60,  // Growing (primavera)
            $month >= 9 && $month <= 10 => 0.70, // Harvest (otoño)
            default => 0.30,                      // Dormant (invierno)
        };
    }

    /**
     * Get irrigation recommendation based on need
     *
     * @param float $irrigationNeed Irrigation need in mm
     *
     * @return array [text, color, bg]
     */
    private function getRecommendation(float $irrigationNeed): array
    {
        return match (true) {
            $irrigationNeed <= 0 => [
                'No regar',
                'text-green-600',
                'bg-green-100',
            ],
            $irrigationNeed <= 10 => [
                'Riego ligero',
                'text-yellow-600',
                'bg-yellow-100',
            ],
            $irrigationNeed <= 25 => [
                'Riego moderado',
                'text-orange-600',
                'bg-orange-100',
            ],
            default => [
                'Riego urgente',
                'text-red-600',
                'bg-red-100',
            ],
        };
    }
}
