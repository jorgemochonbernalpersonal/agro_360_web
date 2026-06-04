<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Recalculates container stock fields from actual seeded operation records.
 *
 * Must run AFTER all operation seeders:
 *   - WineryWineTransfersSeeder  → wine_volume_liters (in/out)
 *   - WineryWineLossesSeeder     → wine_volume_liters (deduct)
 *   - WineryGrapeReceptionsSeeder (harvests) → used_capacity (kg)
 *
 * wine_volume_liters = MAX(0, transfers_in - transfers_out - losses)
 * used_capacity      = SUM(harvests.total_weight)
 *
 * Also populates container_current_states to link containers → wines
 * (needed by SILICIE "Litros en bodega" and Existencias tab).
 *
 * Values are clamped to [0, container.capacity] to ensure data integrity.
 */
class WineryRecalculateContainerStockSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->get(['id', 'capacity'])
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('  ⚠️  No hay contenedores activos para recalcular.');

            return;
        }

        $containerIds = array_column($containers, 'id');

        // ── Transfers: net liters per container ──────────────────────────────
        $transfersIn = DB::table('wine_transfers')
            ->whereIn('to_container_id', $containerIds)
            ->selectRaw('to_container_id as container_id, SUM(quantity) as total')
            ->groupBy('to_container_id')
            ->pluck('total', 'container_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        $transfersOut = DB::table('wine_transfers')
            ->whereIn('from_container_id', $containerIds)
            ->selectRaw('from_container_id as container_id, SUM(quantity) as total')
            ->groupBy('from_container_id')
            ->pluck('total', 'container_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // ── Losses: liters deducted per container ────────────────────────────
        $losses = DB::table('wine_losses')
            ->whereIn('container_id', $containerIds)
            ->selectRaw('container_id, SUM(quantity) as total')
            ->groupBy('container_id')
            ->pluck('total', 'container_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // ── Harvests: kg of grapes per container (used_capacity) ─────────────
        $harvestWeights = DB::table('harvests')
            ->whereIn('container_id', $containerIds)
            ->whereNull('activity_id')                 // winery receptions only
            ->selectRaw('container_id, SUM(total_weight) as total')
            ->groupBy('container_id')
            ->pluck('total', 'container_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // ── Build capacity map for clamping ──────────────────────────────────
        $capacityMap = array_column($containers, 'capacity', 'id');

        $updated = 0;
        $nonZero = 0;
        $wineTotal = 0.0;

        foreach ($containers as $container) {
            $id = $container->id;
            $capacity = (float) ($capacityMap[$id] ?? 0);

            $in = $transfersIn[$id] ?? 0.0;
            $out = $transfersOut[$id] ?? 0.0;
            $lost = $losses[$id] ?? 0.0;

            $wineVolume = max(0.0, $in - $out - $lost);
            $wineVolume = $capacity > 0 ? min($wineVolume, $capacity) : $wineVolume;

            $usedCapacity = $harvestWeights[$id] ?? 0.0;
            $usedCapacity = $capacity > 0 ? min($usedCapacity, $capacity) : $usedCapacity;

            DB::table('containers')->where('id', $id)->update([
                'wine_volume_liters' => round($wineVolume, 3),
                'used_capacity' => round($usedCapacity, 2),
                'updated_at' => now(),
            ]);

            $updated++;
            if ($wineVolume > 0 || $usedCapacity > 0) {
                $nonZero++;
                $wineTotal += $wineVolume;
            }
        }

        // ── Populate container_current_states (wine ↔ container link) ────────
        // This is needed by SILICIE dashboard (litrosBodega, Existencias tab)
        // and takeSnapshot() for INFOVI balance.
        $this->populateContainerCurrentStates($containerIds);

        $this->command->info(
            sprintf(
                '✅ Stock recalculado: %d contenedores actualizados, %d con stock > 0, %.0f L totales de vino',
                $updated,
                $nonZero,
                $wineTotal
            )
        );
    }

    /**
     * Create container_current_states records by finding the last wine
     * transferred INTO each container (via wine_transfers).
     */
    private function populateContainerCurrentStates(array $containerIds): void
    {
        // Clean existing states for winery containers
        DB::table('container_current_states')
            ->whereIn('container_id', $containerIds)
            ->delete();

        // For each container, find the most recent transfer IN with its wine_id
        // and the net quantity currently in the container
        $latestTransfers = DB::table('wine_transfers as wt')
            ->whereIn('wt.to_container_id', $containerIds)
            ->whereNotNull('wt.wine_id')
            ->select([
                'wt.to_container_id as container_id',
                'wt.wine_id',
                'wt.transfer_date',
            ])
            ->orderByDesc('wt.transfer_date')
            ->get()
            ->unique('container_id'); // Keep only the latest per container

        if ($latestTransfers->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];
        $created = 0;

        foreach ($latestTransfers as $transfer) {
            $containerId = $transfer->container_id;

            // Get the current wine_volume_liters from the container (already calculated above)
            $container = DB::table('containers')
                ->where('id', $containerId)
                ->first(['wine_volume_liters']);

            $qty = (float) ($container->wine_volume_liters ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $rows[] = [
                'container_id' => $containerId,
                'wine_id' => $transfer->wine_id,
                'current_quantity' => $qty,
                'available_qty' => $qty,
                'reserved_qty' => 0,
                'sold_qty' => 0,
                'last_movement_at' => $transfer->transfer_date,
                'last_movement_by' => self::WINERY_USER_ID,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $created++;
        }

        if (! empty($rows)) {
            foreach (array_chunk($rows, 50) as $chunk) {
                DB::table('container_current_states')->insert($chunk);
            }
        }

        $totalLiters = array_sum(array_column($rows, 'current_quantity'));
        $this->command->info(
            sprintf('  ↳ container_current_states: %d registros, %.0f L vinculados a vinos', $created, $totalLiters)
        );
    }
}
