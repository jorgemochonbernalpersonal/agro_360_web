<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea lotes comerciales de producto (wine_lots) para gestión de ventas.
 * Depende de: WineryWinesSeeder, WineryBottlingSeeder
 */
class WineryProductLotsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // Vinos ya embotellados o vendidos → tienen lotes comerciales
        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('status', ['bottled', 'sold', 'aged'])
            ->get(['id', 'name', 'wine_type', 'vintage', 'internal_code', 'is_organic', 'status'])
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('No hay vinos. Ejecuta WineryWinesSeeder primero.');

            return;
        }

        // Mapeo wine_type (inglés) → wine_type de wine_lots (español)
        $typeMap = [
            'red' => 'tinto',
            'white' => 'blanco',
            'rose' => 'rosado',
            'sparkling' => 'espumoso',
            'sweet' => 'tinto',   // Malvasía dulce → categoría especial, usamos 'otro' si hay
            'fortified' => 'tinto',
            'other' => 'otro',
        ];

        // Precios orientativos por tipo y añada
        $priceMap = [
            'tinto' => ['joven' => 8.50,  'crianza' => 14.00, 'reserva' => 22.00, 'barrica' => 16.00],
            'blanco' => ['joven' => 7.50,  'crianza' => 12.00, 'reserva' => 18.00, 'barrica' => 15.00],
            'rosado' => ['joven' => 7.00,  'crianza' => 10.00, 'reserva' => 15.00, 'barrica' => 12.00],
            'espumoso' => ['joven' => 12.00, 'crianza' => 18.00, 'reserva' => 28.00, 'barrica' => 20.00],
        ];

        $rows = [];

        foreach ($wines as $wine) {
            $wineTypeLot = $typeMap[$wine->wine_type] ?? 'otro';
            $aging = 'joven'; // default

            // Intentar extraer aging del nombre del vino
            if (stripos($wine->name, 'crianza') !== false) {
                $aging = 'crianza';
            } elseif (stripos($wine->name, 'reserva') !== false) {
                $aging = 'reserva';
            } elseif (stripos($wine->name, 'barrica') !== false) {
                $aging = 'barrica';
            }

            $basePrice = $priceMap[$wineTypeLot][$aging] ?? 9.00;
            // Añadas más antiguas tienen premium
            if ($wine->vintage && $wine->vintage <= 2022) {
                $basePrice *= 1.3;
            }
            if ($wine->vintage && $wine->vintage === 2023) {
                $basePrice *= 1.15;
            }
            if ($wine->is_organic) {
                $basePrice *= 1.10;
            }

            // Obtener botellas del embotellado si existe
            $bottlingData = DB::table('wine_bottlings')
                ->where('wine_id', $wine->id)
                ->first(['quantity_bottles', 'bottling_date']);

            $quantityBotellas = $bottlingData ? $bottlingData->quantity_bottles : mt_rand(2000, 8000);
            $soldPct = $wine->status === 'sold' ? 1.0 : mt_rand(10, 80) / 100;
            $soldQty = (int) ($quantityBotellas * $soldPct);
            $availableQty = $quantityBotellas - $soldQty;
            $bottlingDate = $bottlingData?->bottling_date ?? now()->subMonths(6)->format('Y-m-d');

            $rows[] = [
                'user_id' => self::WINERY_USER_ID,
                'name' => $wine->name,
                'vintage' => $wine->vintage,
                'wine_type' => $wineTypeLot,
                'quantity' => $quantityBotellas,
                'unit' => 'botellas',
                'available_quantity' => $availableQty,
                'price_per_unit' => round($basePrice, 4),
                'notes' => $wine->is_organic
                    ? 'Producción ecológica certificada. Sin herbicidas ni pesticidas de síntesis.'
                    : null,
                'archived' => $wine->status === 'sold',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('wine_lots')->insert($rows);

        $this->command->info('✅ Lotes de producto: '.count($rows).' registros');
    }

    private function cleanup(): void
    {
        DB::table('wine_lots')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
