<?php

namespace App\Services\Tools;

/**
 * Cálculo de caldo fitosanitario: litros totales, número de cubas y cantidad
 * de cada producto por cuba. Sin estado ni BD — lo usa la herramienta pública.
 *
 * Modos de dosis (como aparecen en las etiquetas):
 *  - per_ha:  L o kg de producto por hectárea
 *  - percent: % de concentración en el caldo (0,3 % = 0,3 L o kg por cada 100 L)
 *  - per_hl:  cc o g de producto por hectolitro (100 L) de caldo
 */
class SprayMixCalculator
{
    public const MODES = [
        'per_ha' => 'L o kg / ha',
        'percent' => '% (concentración)',
        'per_hl' => 'cc o g / hL',
    ];

    /**
     * @param array<int, array{name?: string|null, dose: float|int|string, mode: string}> $products
     *
     * @return array{
     *     total_litres: float,
     *     tanks: int,
     *     full_tanks: int,
     *     last_tank_litres: float,
     *     hectares_per_tank: float,
     *     products: array<int, array{name: string, per_hectare: float, per_tank: float, last_tank: float, total: float}>
     * }
     */
    public function calculate(float $hectares, float $litresPerHectare, float $tankLitres, array $products): array
    {
        $totalLitres = $hectares * $litresPerHectare;
        $fullTanks = (int) floor($totalLitres / $tankLitres + 1e-9);
        $lastTank = $totalLitres - $fullTanks * $tankLitres;

        // Restos de menos de medio litro son ruido de redondeo, no una cuba más.
        if ($lastTank < 0.5) {
            $lastTank = 0.0;
        }

        $rows = [];
        foreach (array_values($products) as $i => $product) {
            $perLitre = $this->productPerLitre((float) $product['dose'], $product['mode'], $litresPerHectare);
            $name = trim((string) ($product['name'] ?? ''));

            $rows[] = [
                'name' => $name !== '' ? $name : 'Producto '.($i + 1),
                'per_hectare' => $perLitre * $litresPerHectare,
                'per_tank' => $perLitre * $tankLitres,
                'last_tank' => $perLitre * $lastTank,
                'total' => $perLitre * $totalLitres,
            ];
        }

        return [
            'total_litres' => $totalLitres,
            'tanks' => $fullTanks + ($lastTank > 0 ? 1 : 0),
            'full_tanks' => $fullTanks,
            'last_tank_litres' => $lastTank,
            'hectares_per_tank' => $tankLitres / $litresPerHectare,
            'products' => $rows,
        ];
    }

    /** Cantidad de producto (L o kg) por cada litro de caldo. */
    private function productPerLitre(float $dose, string $mode, float $litresPerHectare): float
    {
        return match ($mode) {
            'per_ha' => $dose / $litresPerHectare,
            'percent' => $dose / 100,
            'per_hl' => $dose / 1000 / 100,
            default => throw new \InvalidArgumentException("Modo de dosis desconocido: {$mode}"),
        };
    }
}
