<?php

namespace App\Services\Tools;

/**
 * Estimación de vendimia por conteo de racimos (método clásico de aforo):
 * cepas/ha × racimos/cepa × peso medio del racimo, descontando marras.
 */
class HarvestYieldEstimator
{
    /**
     * @return array{
     *     vines_per_hectare: float,
     *     productive_vines_per_hectare: float,
     *     kg_per_vine: float,
     *     kg_per_hectare: float,
     *     total_kg: float,
     *     wine_litres: float,
     *     limit_ratio: float|null
     * }
     */
    public function estimate(
        float $hectares,
        float $rowSpacing,
        float $vineSpacing,
        float $clustersPerVine,
        float $clusterWeightGrams,
        float $missingVinesPercent = 0,
        float $litresPer100Kg = 70,
        ?float $limitKgPerHectare = null,
    ): array {
        $vinesPerHectare = 10000 / ($rowSpacing * $vineSpacing);
        $productiveVines = $vinesPerHectare * (1 - $missingVinesPercent / 100);
        $kgPerVine = $clustersPerVine * $clusterWeightGrams / 1000;
        $kgPerHectare = $productiveVines * $kgPerVine;
        $totalKg = $kgPerHectare * $hectares;

        return [
            'vines_per_hectare' => $vinesPerHectare,
            'productive_vines_per_hectare' => $productiveVines,
            'kg_per_vine' => $kgPerVine,
            'kg_per_hectare' => $kgPerHectare,
            'total_kg' => $totalKg,
            'wine_litres' => $totalKg * $litresPer100Kg / 100,
            'limit_ratio' => $limitKgPerHectare ? $kgPerHectare / $limitKgPerHectare : null,
        ];
    }
}
