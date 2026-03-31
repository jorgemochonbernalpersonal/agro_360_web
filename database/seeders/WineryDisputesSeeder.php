<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea harvest_deliveries con status 'disputed' y 'resolved' vinculadas a
 * recepciones de bodega (harvests con winery_id = 1).
 * El DisputeController filtra: whereHas('harvest', fn => winery_id = 1).
 */
class WineryDisputesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        // Obtener recepciones de bodega creadas por WineryGrapeReceptionsSeeder
        $wineryHarvests = DB::table('harvests')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereNull('activity_id')
            ->orderBy('id')
            ->get();

        if ($wineryHarvests->isEmpty()) {
            $this->command->warn('  ⚠️  Sin recepciones de uva. Saltando disputas.');
            return;
        }

        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores vinculados. Saltando disputas.');
            return;
        }

        $now         = now();
        $vintageYear = (int) now()->format('Y');
        $rows        = [];

        // Usamos 3 recepciones: 2 con disputa activa, 1 resuelta
        $disputeData = [
            [
                'harvest_idx'    => 0,
                'vitic_idx'      => 0,
                'delivered_kg'   => null, // se calculará desde harvest
                'discrepancy_pct'=> 8.5,  // > 5% → disputed
                'status'         => 'disputed',
                'dispute_note'   => 'El viticultor indica 3480 kg pero se recibieron 3200 kg. Diferencia de 280 kg sin justificación.',
                'days_ago_submit'=> 20,
                'resolution'     => null,
                'resolved_at'    => null,
            ],
            [
                'harvest_idx'    => 2,
                'vitic_idx'      => 1,
                'delivered_kg'   => null,
                'discrepancy_pct'=> 6.0,
                'status'         => 'disputed',
                'dispute_note'   => 'Discrepancia en peso: viticultor declara 1590 kg, bodega registra 1500 kg.',
                'days_ago_submit'=> 15,
                'resolution'     => null,
                'resolved_at'    => null,
            ],
            [
                'harvest_idx'    => 4,
                'vitic_idx'      => count($viticulturistIds) > 1 ? 1 : 0,
                'delivered_kg'   => null,
                'discrepancy_pct'=> 7.2,
                'status'         => 'resolved',
                'dispute_note'   => 'Viticultor alega 966 kg, báscula registró 900 kg. Se revisó calibración.',
                'days_ago_submit'=> 45,
                'resolution'     => 'Calibración de báscula verificada. Se acepta el peso registrado por bodega (900 kg). Viticultor conforme.',
                'resolved_at'    => now()->subDays(40)->toIso8601String(),
            ],
        ];

        foreach ($disputeData as $disp) {
            $harvestIdx = $disp['harvest_idx'] % $wineryHarvests->count();
            $harvest    = $wineryHarvests[$harvestIdx];
            $vitId      = $viticulturistIds[$disp['vitic_idx'] % count($viticulturistIds)];

            // El viticultor afirma haber entregado más de lo que la bodega registró
            $deliveredKg  = round($harvest->total_weight * (1 + $disp['discrepancy_pct'] / 100), 2);
            $discrepancyKg = round($deliveredKg - $harvest->total_weight, 2);

            $rows[] = [
                'viticulturist_id'        => $vitId,
                'harvest_id'              => $harvest->id,
                'vintage_year'            => $vintageYear,
                'buyer_name'              => 'Bodega Agaete Demo',
                'delivered_kg'            => $deliveredKg,
                'price_per_kg'            => $harvest->price_per_kg ?? 0.90,
                'total_price'             => round($deliveredKg * ($harvest->price_per_kg ?? 0.90), 2),
                'delivery_date'           => $harvest->harvest_start_date,
                'ticket_number'           => 'TKT-2025-' . str_pad($disp['harvest_idx'] + 1, 3, '0', STR_PAD_LEFT),
                'status'                  => $disp['status'],
                'discrepancy_kg'          => $discrepancyKg,
                'dispute_note'            => $disp['dispute_note'],
                'dispute_submitted_at'    => now()->subDays($disp['days_ago_submit'])->toIso8601String(),
                'dispute_resolution_note' => $disp['resolution'],
                'dispute_resolved_at'     => $disp['resolved_at'],
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        DB::table('harvest_deliveries')->insert($rows);
        $this->command->info('✅ Disputas: ' . count($rows) . ' registros (2 activas, 1 resuelta)');
    }

    private function cleanup(): void
    {
        // Solo eliminar las deliveries vinculadas a harvests de esta bodega
        $wineryHarvestIds = DB::table('harvests')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereNull('activity_id')
            ->pluck('id');

        if ($wineryHarvestIds->isNotEmpty()) {
            DB::table('harvest_deliveries')
                ->whereIn('harvest_id', $wineryHarvestIds)
                ->delete();
        }
    }
}
