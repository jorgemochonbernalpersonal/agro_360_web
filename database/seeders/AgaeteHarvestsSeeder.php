<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgaeteHarvestsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;
    private const TARGET_HARVESTS = 75;

    // Distribución de vendimias por añada
    private const VINTAGES = [
        2022 => 18,
        2023 => 19,
        2024 => 19,
        2026 => 19,
    ];

    // Solo contenedores aptos para recibir uva (no barricas ni ánforas pequeñas)
    // type_id: 2=Depósito, 3=Tanque, 4=Tina
    private const HARVEST_CONTAINER_TYPES = [2, 3, 4];

    // Canarias: vendimia temprana, julio–octubre
    private const HARVEST_WINDOW = ['07-15', '10-10'];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ── 1. Obtener plantaciones de los viticultores Agaete ─────────────────
        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->pluck('id');

        if ($vitIds->isEmpty()) {
            $this->command->warn('No se encontraron viticultores Agaete. Ejecuta AgaetePlotsSeeder primero.');
            return;
        }

        $plotIds = DB::table('plots')
            ->whereIn('viticulturist_id', $vitIds)
            ->pluck('id');

        $plantings = DB::table('plot_plantings as pp')
            ->join('plots as p', 'p.id', '=', 'pp.plot_id')
            ->whereIn('pp.plot_id', $plotIds)
            ->where('pp.active', true)
            ->get([
                'pp.id as planting_id',
                'pp.plot_id',
                'pp.area_planted',
                'pp.harvest_limit_kg',
                'p.viticulturist_id',
            ])
            ->toArray();

        if (empty($plantings)) {
            $this->command->warn('No se encontraron plantaciones activas. Ejecuta AgaetePlantingsSeeder primero.');
            return;
        }

        // ── 2. Obtener contenedores de bodega aptos (Depósito, Tanque, Tina) ───
        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('type_id', self::HARVEST_CONTAINER_TYPES)
            ->where('archived', false)
            ->orderBy('capacity', 'desc')
            ->get(['id', 'capacity', 'used_capacity'])
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('No se encontraron contenedores aptos. Ejecuta WineryContainersSeeder primero.');
            return;
        }

        // Índice de capacidad disponible por contenedor (mutable durante el proceso)
        $containerAvailable = [];
        foreach ($containers as $c) {
            $containerAvailable[$c->id] = (float) $c->capacity - (float) $c->used_capacity;
        }

        $this->command->info(sprintf(
            'Usando %d plantaciones y %d contenedores disponibles.',
            count($plantings),
            count($containers)
        ));

        // ── 3. Muestrear plantaciones distribuidas por añada ───────────────────
        shuffle($plantings);
        $selected = array_slice($plantings, 0, self::TARGET_HARVESTS);

        // Asignar añada secuencialmente según la distribución definida
        $allAssigned = [];
        $offset = 0;
        foreach (self::VINTAGES as $vintage => $count) {
            for ($i = 0; $i < $count; $i++) {
                if (isset($selected[$offset + $i])) {
                    $allAssigned[] = [$vintage, $selected[$offset + $i]];
                }
            }
            $offset += $count;
        }

        // ── 4. Insertar vendimias ──────────────────────────────────────────────
        $activityRows = [];
        $harvestRows  = [];
        $stockRows    = [];

        // Acumuladores para actualizar contenedores al final
        $containerWeightAdded = []; // container_id → kg acumulado
        $containerLastHarvest = []; // container_id → harvest_id (el más reciente)

        // Insertamos de uno en uno para obtener IDs (necesitamos activity_id y harvest_id)
        $total = 0;

        foreach ($allAssigned as [$vintage, $planting]) {
            $area        = max(0.05, (float) $planting->area_planted);
            $limitKg     = (float) ($planting->harvest_limit_kg ?? ($area * 8000));
            $weightKg    = round($limitKg * (mt_rand(55, 88) / 100), 2);
            $weightKg    = max(50.00, $weightKg);

            // Fecha de vendimia dentro de la ventana de la añada
            $harvestDate = $this->randomDate($vintage, self::HARVEST_WINDOW[0], self::HARVEST_WINDOW[1]);
            $harvestTime = sprintf('%02d:%02d:00', mt_rand(6, 14), [0, 15, 30, 45][mt_rand(0, 3)]);

            // Calidad
            $brix     = round(mt_rand(190, 255) / 10, 1); // 19.0–25.5 ºBrix
            $baume    = round($brix * 0.55, 1);            // aprox
            $ph       = round(mt_rand(310, 380) / 100, 2); // 3.10–3.80
            $acidity  = round(mt_rand(45, 85) / 10, 1);    // 4.5–8.5 g/L
            $alcohol  = round($brix * 0.575, 1);           // potencial alcohólico

            $ratings     = ['excelente', 'bueno', 'aceptable'];
            $healthOpts  = ['sano', 'daño_leve', 'daño_moderado'];
            $healthWeights = [70, 20, 10];
            $healthStatus = $this->weightedRand(array_combine($healthOpts, $healthWeights));

            // Precio €/kg (uvas de Canarias, DO Gran Canaria)
            $priceKg    = round(mt_rand(70, 130) / 100, 4); // 0.70–1.30
            $totalValue = round($weightKg * $priceKg, 2);

            $ticketNum = 'VND-' . $vintage . '-' . str_pad($total + 1, 4, '0', STR_PAD_LEFT);

            // ── 4a. agricultural_activity ─────────────────────────────────────
            $activityId = DB::table('agricultural_activities')->insertGetId([
                'plot_id'         => $planting->plot_id,
                'viticulturist_id'=> $planting->viticulturist_id,
                'activity_type'   => 'harvest',
                'activity_date'   => $harvestDate,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // ── 4b. harvest ───────────────────────────────────────────────────
            $harvestId = DB::table('harvests')->insertGetId([
                'activity_id'        => $activityId,
                'plot_planting_id'   => $planting->planting_id,
                'container_id'       => null,  // FK apunta a harvest_containers (campo); el vínculo a depósito va en container_current_states
                'harvest_start_date' => $harvestDate,
                'harvest_time'       => $harvestTime,
                'harvest_end_date'   => null,
                'total_weight'       => $weightKg,
                'yield_per_hectare'  => round($weightKg / $area, 2),
                'baume_degree'       => $baume,
                'brix_degree'        => $brix,
                'acidity_level'      => $acidity,
                'ph_level'           => $ph,
                'potential_alcohol'  => $alcohol,
                'color_rating'       => $ratings[mt_rand(0, 1)],
                'aroma_rating'       => $ratings[mt_rand(0, 1)],
                'health_status'      => $healthStatus,
                'destination_type'   => 'winery',
                'destination'        => 'Bodega Agaete',
                'buyer_name'         => null,
                'price_per_kg'       => $priceKg,
                'total_value'        => $totalValue,
                'status'             => 'active',
                'vintage'            => $vintage,
                'disqualified'       => false,
                'disqualified_reason'=> null,
                'harvest_ticket_number' => $ticketNum,
                'sanitary_state_grapes'  => round(mt_rand(75, 100), 0),
                'sanitary_state_botrytis'=> round(mt_rand(0, 8), 0),
                'sanitary_state_oidium'  => round(mt_rand(0, 5), 0),
                'sanitary_state_mildew'  => round(mt_rand(0, 5), 0),
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            // ── 4c. harvest_stock (registro inicial) ──────────────────────────
            DB::table('harvest_stocks')->insert([
                'harvest_id'      => $harvestId,
                'container_id'    => null, // FK apunta a harvest_containers
                'user_id'         => self::WINERY_USER_ID,
                'movement_type'   => 'initial',
                'quantity_change' => $weightKg,
                'quantity_after'  => $weightKg,
                'available_qty'   => $weightKg,
                'reserved_qty'    => 0,
                'sold_qty'        => 0,
                'gifted_qty'      => 0,
                'lost_qty'        => 0,
                'notes'           => 'Registro inicial de cosecha (seeder)',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // ── 4d. Asignar contenedor de bodega ──────────────────────────────
            $assignedContainerId = $this->pickContainer($containerAvailable, $weightKg);

            if ($assignedContainerId !== null) {
                $containerWeightAdded[$assignedContainerId] =
                    ($containerWeightAdded[$assignedContainerId] ?? 0) + $weightKg;
                $containerLastHarvest[$assignedContainerId] = $harvestId;

                // container_histories (entry: fill)
                DB::table('container_histories')->insert([
                    'container_id'    => $assignedContainerId,
                    'harvest_id'      => $harvestId,
                    'field_activity_id'=> $activityId,
                    'operation_type'  => 'fill',
                    'quantity'        => $weightKg,
                    'start_date'      => $harvestDate . ' ' . $harvestTime,
                    'end_date'        => null,
                    'created_by'      => self::WINERY_USER_ID,
                    'has_subproducts' => false,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            $total++;
        }

        // ── 5. Actualizar containers.used_capacity y container_current_states ──
        foreach ($containerWeightAdded as $containerId => $addedKg) {
            DB::table('containers')
                ->where('id', $containerId)
                ->increment('used_capacity', $addedKg);

            $finalQty   = $addedKg; // El seeder parte de used_capacity=0 (recién creados)
            $lastHarvest = $containerLastHarvest[$containerId];

            DB::table('container_current_states')->updateOrInsert(
                ['container_id' => $containerId],
                [
                    'harvest_id'       => $lastHarvest,
                    'current_quantity' => $finalQty,
                    'available_qty'    => $finalQty,
                    'reserved_qty'     => 0,
                    'sold_qty'         => 0,
                    'has_subproducts'  => false,
                    'last_movement_at' => $now,
                    'last_movement_by' => self::WINERY_USER_ID,
                    'updated_at'       => $now,
                    'created_at'       => $now,
                ]
            );
        }

        $usedContainers = count($containerWeightAdded);
        $this->command->info("✓ {$total} vendimias insertadas distribuidas en {$usedContainers} contenedores.");

        foreach (self::VINTAGES as $vintage => $expected) {
            $actual = count(array_filter($allAssigned, fn($v) => $v[0] === $vintage));
            $this->command->line("  Añada {$vintage}: {$actual} vendimias");
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Elige un contenedor con capacidad suficiente.
     * Prioriza los que tienen más espacio libre para distribuir mejor la carga.
     */
    private function pickContainer(array &$available, float $weightKg): ?int
    {
        // Filtrar los que tienen capacidad suficiente
        $candidates = array_filter($available, fn($cap) => $cap >= $weightKg);

        if (empty($candidates)) {
            // Si no hay contenedor con espacio suficiente, usar el que más tiene
            arsort($available);
            $id = array_key_first($available);
            if ($available[$id] <= 0) return null;
        } else {
            // Ordenar por capacidad disponible desc y tomar el primero
            arsort($candidates);
            $id = array_key_first($candidates);
        }

        $available[$id] -= $weightKg;
        return $id;
    }

    /**
     * Genera una fecha aleatoria dentro de la ventana de la añada.
     * $from / $to en formato 'MM-DD'
     */
    private function randomDate(int $year, string $from, string $to): string
    {
        $start = strtotime("{$year}-{$from}");
        $end   = strtotime("{$year}-{$to}");
        return date('Y-m-d', mt_rand($start, $end));
    }

    /**
     * Selección aleatoria ponderada.
     *
     * @param array<string,int> $weights
     */
    private function weightedRand(array $weights): string
    {
        $total = array_sum($weights);
        $rand  = mt_rand(1, $total);
        $cumul = 0;
        foreach ($weights as $key => $weight) {
            $cumul += $weight;
            if ($rand <= $cumul) return $key;
        }
        return array_key_first($weights);
    }

    private function cleanup(): void
    {
        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->pluck('id');

        if ($vitIds->isEmpty()) return;

        $plotIds = DB::table('plots')
            ->whereIn('viticulturist_id', $vitIds)
            ->pluck('id');

        if ($plotIds->isEmpty()) return;

        $plantingIds = DB::table('plot_plantings')
            ->whereIn('plot_id', $plotIds)
            ->pluck('id');

        // Obtener activity IDs de tipo harvest para estas parcelas
        $activityIds = DB::table('agricultural_activities')
            ->whereIn('plot_id', $plotIds)
            ->where('activity_type', 'harvest')
            ->pluck('id');

        if ($activityIds->isNotEmpty()) {
            $harvestIds = DB::table('harvests')
                ->whereIn('activity_id', $activityIds)
                ->pluck('id');

            if ($harvestIds->isNotEmpty()) {
                // Contenedores afectados: revertir used_capacity y borrar estados
                $affectedContainerIds = DB::table('container_histories')
                    ->whereIn('harvest_id', $harvestIds)
                    ->pluck('container_id')
                    ->unique();

                foreach ($affectedContainerIds as $cid) {
                    $totalAdded = DB::table('container_histories')
                        ->where('container_id', $cid)
                        ->whereIn('harvest_id', $harvestIds)
                        ->where('operation_type', 'fill')
                        ->sum('quantity');

                    if ($totalAdded > 0) {
                        DB::table('containers')
                            ->where('id', $cid)
                            ->decrement('used_capacity', $totalAdded);
                    }
                }

                DB::table('container_current_states')
                    ->whereIn('harvest_id', $harvestIds)
                    ->delete();

                DB::table('container_histories')
                    ->whereIn('harvest_id', $harvestIds)
                    ->delete();

                DB::table('harvest_stocks')
                    ->whereIn('harvest_id', $harvestIds)
                    ->delete();

                $deleted = DB::table('harvests')
                    ->whereIn('activity_id', $activityIds)
                    ->delete();

                if ($deleted > 0) {
                    $this->command->line("  Eliminadas {$deleted} vendimias anteriores del seeder.");
                }
            }

            DB::table('agricultural_activities')
                ->whereIn('id', $activityIds)
                ->delete();
        }
    }
}
