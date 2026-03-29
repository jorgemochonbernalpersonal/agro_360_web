<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Alimenta el módulo de Vendimias (winery-side) para user_id=1.
 *
 * Depende de:
 *   - AgaetePlotsSeeder       → viticulturists + plots
 *   - AgaetePlantingsSeeder   → plot_plantings
 *   - AgaeteHarvestsSeeder    → harvests viticultor (activity_id set, winery_id null)
 *
 * Crea:
 *   1. campaigns              → 1 por viticultor × añada (50 × 4 = 200)
 *   2. estimated_yields       → estimación viticultor antes de vendimia
 *   3. winery_yield_forecasts → previsión de la bodega por plantación
 *   4. grape_reception_batches→ acumulador bodega por (winery + plantación + campaña)
 *   5. harvests (winery-side) → recepciones en bodega (winery_id=1, activity_id=null)
 *   6. harvest_deliveries     → albarán viticultor emparejado con la recepción
 */
class AgaeteReceptionSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;
    private const VINTAGES       = [2022, 2023, 2024, 2026];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ── 0. Verificar datos previos ─────────────────────────────────────────
        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($vitIds)) {
            $this->command->warn('No se encontraron viticultores Agaete. Ejecuta AgaetePlotsSeeder primero.');
            return;
        }

        // Leer las 75 vendimias viticultor ya creadas por AgaeteHarvestsSeeder
        $vitHarvests = DB::table('harvests as h')
            ->join('agricultural_activities as aa', 'aa.id', '=', 'h.activity_id')
            ->join('plot_plantings as pp', 'pp.id', '=', 'h.plot_planting_id')
            ->join('plots as p', 'p.id', '=', 'pp.plot_id')
            ->whereIn('aa.viticulturist_id', $vitIds)
            ->whereNotNull('h.vintage')
            ->get([
                'h.id as harvest_id',
                'h.plot_planting_id',
                'h.vintage',
                'h.total_weight',
                'h.brix_degree',
                'h.baume_degree',
                'h.ph_level',
                'h.acidity_level',
                'h.potential_alcohol',
                'h.harvest_start_date',
                'h.harvest_time',
                'h.price_per_kg',
                'h.harvest_ticket_number',
                'h.destination_type',
                'pp.area_planted',
                'pp.harvest_limit_kg',
                'pp.designation_of_origin',
                'p.viticulturist_id',
            ])
            ->toArray();

        if (empty($vitHarvests)) {
            $this->command->warn('No se encontraron vendimias del seeder. Ejecuta AgaeteHarvestsSeeder primero.');
            return;
        }

        $this->command->info(sprintf('Procesando %d vendimias del viticultor.', count($vitHarvests)));

        // ── 0b. Contenedores disponibles para recepciones (Depósito/Tanque/Tina) ─
        $receptionContainers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('type_id', [2, 3, 4])
            ->where('archived', false)
            ->orderBy('capacity', 'desc')
            ->get(['id', 'capacity', 'used_capacity'])
            ->toArray();

        $containerAvailable = [];
        foreach ($receptionContainers as $c) {
            $containerAvailable[$c->id] = (float) $c->capacity - (float) $c->used_capacity;
        }

        // ── 1. Campaigns ───────────────────────────────────────────────────────
        // Una por viticultor × añada. La más reciente (2026) queda activa.
        $campaignRows = [];
        foreach ($vitIds as $vitId) {
            foreach (self::VINTAGES as $vintage) {
                $isActive = ($vintage === max(self::VINTAGES));
                $campaignRows[] = [
                    'name'           => "Campaña {$vintage}",
                    'year'           => $vintage,
                    'viticulturist_id'=> $vitId,
                    'start_date'     => "{$vintage}-07-01",
                    'end_date'       => "{$vintage}-11-30",
                    'active'         => $isActive,
                    'description'    => "Campaña de vendimia {$vintage} — Agaete, Gran Canaria",
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }
        DB::table('campaigns')->insert($campaignRows);
        $this->command->line('  ✓ ' . count($campaignRows) . ' campañas creadas.');

        // Índice: [viticulturist_id][vintage] → campaign_id
        $campaignIndex = [];
        DB::table('campaigns')
            ->whereIn('viticulturist_id', $vitIds)
            ->whereIn('year', self::VINTAGES)
            ->get(['id', 'viticulturist_id', 'year'])
            ->each(function ($c) use (&$campaignIndex) {
                $campaignIndex[$c->viticulturist_id][$c->year] = $c->id;
            });

        // ── 2. Estimated Yields ────────────────────────────────────────────────
        // Una estimación por plantación × campaña (viticultor estima antes de cosechar).
        $estimatedRows = [];
        foreach ($vitHarvests as $h) {
            $campaignId = $campaignIndex[$h->viticulturist_id][$h->vintage] ?? null;
            if (!$campaignId) continue;

            $area           = max(0.05, (float) $h->area_planted);
            $limitKg        = (float) ($h->harvest_limit_kg ?? $area * 8000);
            $estimatedTotal = round($limitKg * (mt_rand(70, 95) / 100), 2);
            $estimatedPerHa = round($estimatedTotal / $area, 2);
            $actualTotal    = (float) $h->total_weight;
            $actualPerHa    = round($actualTotal / $area, 2);
            $variance       = $estimatedTotal > 0
                ? round((($actualTotal - $estimatedTotal) / $estimatedTotal) * 100, 2)
                : 0;

            // Fecha de estimación: 30-60 días antes de la vendimia
            $estimationDate = date('Y-m-d', strtotime($h->harvest_start_date . ' -' . mt_rand(30, 60) . ' days'));

            $estimatedRows[] = [
                'plot_planting_id'          => $h->plot_planting_id,
                'campaign_id'               => $campaignId,
                'estimated_by'              => $h->viticulturist_id,
                'estimated_yield_per_hectare'=> $estimatedPerHa,
                'estimated_total_yield'     => $estimatedTotal,
                'estimation_date'           => $estimationDate,
                'estimation_method'         => ['visual', 'sampling', 'historical'][mt_rand(0, 2)],
                'status'                    => 'confirmed',
                'actual_yield_per_hectare'  => $actualPerHa,
                'actual_total_yield'        => $actualTotal,
                'variance_percentage'       => $variance,
                'vintage'                   => $h->vintage,
                'health_percentage'         => mt_rand(82, 100),
                'potential_alcohol'         => $h->potential_alcohol,
                'estimation_round'          => 1,
                'other_wineries'            => false,
                'notes'                     => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ];
        }
        DB::table('estimated_yields')->insert($estimatedRows);
        $this->command->line('  ✓ ' . count($estimatedRows) . ' estimaciones de viticultores creadas.');

        // ── 3. Winery Yield Forecasts ──────────────────────────────────────────
        // La bodega hace su propia previsión antes de la vendimia.
        $forecastRows = [];
        foreach ($vitHarvests as $h) {
            $campaignId = $campaignIndex[$h->viticulturist_id][$h->vintage] ?? null;
            if (!$campaignId) continue;

            $area          = max(0.05, (float) $h->area_planted);
            $limitKg       = (float) ($h->harvest_limit_kg ?? $area * 8000);
            $forecastKg    = round($limitKg * (mt_rand(65, 90) / 100), 3);
            $estimationDate = date('Y-m-d', strtotime($h->harvest_start_date . ' -' . mt_rand(45, 75) . ' days'));

            $forecastRows[] = [
                'winery_id'         => self::WINERY_USER_ID,
                'viticulturist_id'  => $h->viticulturist_id,
                'plot_planting_id'  => $h->plot_planting_id,
                'campaign_id'       => $campaignId,
                'vintage_year'      => $h->vintage,
                'estimated_kg'      => $forecastKg,
                'estimation_date'   => $estimationDate,
                'status'            => 'confirmed',
                'notes'             => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }
        DB::table('winery_yield_forecasts')->insert($forecastRows);
        $this->command->line('  ✓ ' . count($forecastRows) . ' previsiones de bodega creadas.');

        // ── 4. Grape Reception Batches ─────────────────────────────────────────
        // Un batch acumulador por (bodega + plantación + campaña).
        $batchRows = [];
        foreach ($vitHarvests as $h) {
            $campaignId = $campaignIndex[$h->viticulturist_id][$h->vintage] ?? null;
            if (!$campaignId) continue;

            $batchRows[] = [
                'winery_id'            => self::WINERY_USER_ID,
                'viticulturist_id'     => $h->viticulturist_id,
                'plot_planting_id'     => $h->plot_planting_id,
                'campaign_id'          => $campaignId,
                'vintage_year'         => $h->vintage,
                'total_weight_kg'      => $h->total_weight,
                'designation_of_origin'=> $h->designation_of_origin,
                'status'               => 'closed',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }
        DB::table('grape_reception_batches')->insert($batchRows);
        $this->command->line('  ✓ ' . count($batchRows) . ' lotes de recepción creados.');

        // Índice: [planting_id][campaign_id] → batch_id
        $batchIndex = [];
        DB::table('grape_reception_batches')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereIn('viticulturist_id', $vitIds)
            ->get(['id', 'plot_planting_id', 'campaign_id'])
            ->each(function ($b) use (&$batchIndex) {
                $batchIndex[$b->plot_planting_id][$b->campaign_id] = $b->id;
            });

        // ── 5. Harvests winery-side + Harvest Deliveries ──────────────────────
        // Por cada batch: 1 harvest de bodega (recepción) + 1 delivery del viticultor.
        $wineryHarvestCount   = 0;
        $deliveryCount        = 0;

        foreach ($vitHarvests as $h) {
            $campaignId = $campaignIndex[$h->viticulturist_id][$h->vintage] ?? null;
            if (!$campaignId) continue;

            $batchId = $batchIndex[$h->plot_planting_id][$campaignId] ?? null;
            if (!$batchId) continue;

            // Pequeña variación de peso en la recepción (báscula de bodega vs. campo)
            $variation     = mt_rand(-30, 20);    // -30 a +20 kg
            $receivedKg    = max(10.0, (float) $h->total_weight + $variation);
            $discrepancyKg = round($receivedKg - (float) $h->total_weight, 2);

            // Estado: 90% matched, 10% disputed
            $isDisputed = (mt_rand(1, 100) <= 10);
            $status     = $isDisputed ? 'disputed' : 'matched';

            // Asignar contenedor de bodega para esta recepción
            $assignedContainerId = $this->pickContainer($containerAvailable, $receivedKg);

            // ── 5a. Harvest winery-side ──
            $wineryHarvestId = DB::table('harvests')->insertGetId([
                'activity_id'         => null,
                'winery_id'           => self::WINERY_USER_ID,
                'batch_id'            => $batchId,
                'plot_planting_id'    => $h->plot_planting_id,
                'container_id'        => $assignedContainerId,
                'harvest_start_date'  => $h->harvest_start_date,
                'harvest_time'        => $h->harvest_time,
                'harvest_end_date'    => null,
                'total_weight'        => $receivedKg,
                'yield_per_hectare'   => round($receivedKg / max(0.05, (float) $h->area_planted), 2),
                'baume_degree'        => $h->baume_degree,
                'brix_degree'         => $h->brix_degree,
                'acidity_level'       => $h->acidity_level,
                'ph_level'            => $h->ph_level,
                'potential_alcohol'   => $h->potential_alcohol,
                'color_rating'        => 'bueno',
                'aroma_rating'        => 'bueno',
                'health_status'       => 'sano',
                'destination_type'    => 'winery',
                'destination'         => 'Bodega Agaete',
                'buyer_name'          => null,
                'price_per_kg'        => $h->price_per_kg,
                'total_value'         => round($receivedKg * (float) $h->price_per_kg, 2),
                'status'              => 'active',
                'vintage'             => $h->vintage,
                'disqualified'        => false,
                'disqualified_reason' => null,
                'harvest_ticket_number' => 'REC-' . $h->vintage . '-' . str_pad($wineryHarvestCount + 1, 4, '0', STR_PAD_LEFT),
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $wineryHarvestCount++;

            // ── 5b. Harvest Delivery (albarán del viticultor) ────────────────
            DB::table('harvest_deliveries')->insert([
                'viticulturist_id'    => $h->viticulturist_id,
                'plot_planting_id'    => $h->plot_planting_id,
                'harvest_id'          => $wineryHarvestId,
                'vintage_year'        => $h->vintage,
                'buyer_name'          => 'Bodega Agaete',
                'delivered_kg'        => $h->total_weight,
                'price_per_kg'        => $h->price_per_kg,
                'total_price'         => round((float) $h->total_weight * (float) $h->price_per_kg, 2),
                'delivery_date'       => $h->harvest_start_date,
                'harvest_time'        => $h->harvest_time,
                'ticket_number'       => $h->harvest_ticket_number,
                'destination_rega_code'=> null,
                'vehicle_plate'       => $this->randomPlate(),
                'baume_degree'        => $h->baume_degree,
                'brix_degree'         => $h->brix_degree,
                'potential_alcohol'   => $h->potential_alcohol,
                'acidity_level'       => $h->acidity_level,
                'ph_level'            => $h->ph_level,
                'status'              => $status,
                'discrepancy_kg'      => abs($discrepancyKg) >= 5 ? $discrepancyKg : null,
                'disqualified'        => false,
                'disqualified_reason' => null,
                'dispute_note'        => $isDisputed ? 'Diferencia de peso superior a tolerancia.' : null,
                'dispute_submitted_at'=> $isDisputed ? $now : null,
                'dispute_resolution_note' => null,
                'dispute_resolved_at' => null,
                'notes'               => null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $deliveryCount++;
        }

        $this->command->line("  ✓ {$wineryHarvestCount} recepciones de bodega creadas.");
        $this->command->line("  ✓ {$deliveryCount} albaranes de viticultor creados.");

        $this->command->info(sprintf(
            '✅ AgaeteReceptionSeeder completado: %d campañas | %d estimaciones | %d previsiones | %d batches | %d recepciones',
            count($campaignRows),
            count($estimatedRows),
            count($forecastRows),
            count($batchRows),
            $wineryHarvestCount
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function pickContainer(array &$available, float $weightKg): ?int
    {
        $candidates = array_filter($available, fn($cap) => $cap >= $weightKg);

        if (empty($candidates)) {
            arsort($available);
            $id = array_key_first($available);
            if (empty($available) || $available[$id] <= 0) return null;
        } else {
            arsort($candidates);
            $id = array_key_first($candidates);
        }

        $available[$id] -= $weightKg;
        return $id;
    }

    private function randomPlate(): string
    {
        $digits  = str_pad((string) mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $letters = substr(str_shuffle('BCDFGHJKLMNPRSTUVWXYZ'), 0, 3);
        return $digits . $letters;
    }

    private function cleanup(): void
    {
        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->pluck('id');

        if ($vitIds->isEmpty()) return;

        // Campaigns de estos viticultores (y todo lo que depende de ellas en cascada)
        $campaignIds = DB::table('campaigns')
            ->whereIn('viticulturist_id', $vitIds)
            ->whereIn('year', self::VINTAGES)
            ->pluck('id');

        if ($campaignIds->isEmpty()) {
            $this->command->line('  Nada que limpiar.');
            return;
        }

        // Harvest deliveries (no tiene cascade a campaigns, hay que borrar antes)
        $batchIds = DB::table('grape_reception_batches')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereIn('campaign_id', $campaignIds)
            ->pluck('id');

        if ($batchIds->isNotEmpty()) {
            $wineryHarvestIds = DB::table('harvests')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('batch_id', $batchIds)
                ->pluck('id');

            if ($wineryHarvestIds->isNotEmpty()) {
                DB::table('harvest_deliveries')
                    ->whereIn('harvest_id', $wineryHarvestIds)
                    ->delete();

                DB::table('harvests')
                    ->whereIn('id', $wineryHarvestIds)
                    ->delete();
            }
        }

        // Borrar en orden de dependencias
        DB::table('winery_yield_forecasts')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereIn('campaign_id', $campaignIds)
            ->delete();

        DB::table('grape_reception_batches')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereIn('campaign_id', $campaignIds)
            ->delete();

        DB::table('estimated_yields')
            ->whereIn('campaign_id', $campaignIds)
            ->delete();

        $deleted = DB::table('campaigns')
            ->whereIn('viticulturist_id', $vitIds)
            ->whereIn('year', self::VINTAGES)
            ->delete();

        $this->command->line("  Eliminadas {$deleted} campañas y sus datos asociados.");
    }
}
