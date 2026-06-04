<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera un ciclo de vida completo para una parcela específica (por defecto plot_id=1521).
 *
 * Crea ~120+ registros distribuidos coherentemente en el ciclo vegetativo
 * de la vid a lo largo de 3 campañas (2023-2025):
 *
 *  - Plantaciones (plot_plantings) con harvest_limit_kg
 *  - Actividades de campo con datos de detalle:
 *    · Poda (cultural_works con pruning_type)
 *    · Tratamientos fitosanitarios (phytosanitary_treatments + productos)
 *    · Fertilización (fertilizations)
 *    · Riego (irrigations)
 *    · Labores culturales (cultural_works)
 *    · Observaciones (observations)
 *    · Vendimia (harvests + grape_reception_batches)
 *    · Post-vendimia (post_harvest_treatments)
 *  - Observaciones fenológicas (phenology_observations)
 *  - Aforos / estimaciones de rendimiento (estimated_yields)
 *  - Recepciones de uva (grape_reception_batches + harvests)
 *
 * Depende de: WineryGrapeReceptionsSeeder (campañas de bodega).
 */
class WineryPlotLifecycleSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const PLOT_ID = 1521;

    // ── Ciclo vegetativo completo por mes ────────────────────────────────────
    // Cada entrada genera 1 actividad con su registro de detalle asociado
    private const MONTHLY_PLAN = [
        // Mes => [[type, subdata], ...]
        1 => [
            ['pruning',       ['pruning_type' => 'vaso',       'buds' => 32000, 'hours' => 6.5, 'workers' => 3]],
            ['observation',   ['type' => 'plaga',     'desc' => 'Inspección invernal de yesca. Sin síntomas visibles.', 'severity' => 'none']],
        ],
        2 => [
            ['pruning',       ['pruning_type' => 'cordon',     'buds' => 28000, 'hours' => 8.0, 'workers' => 4]],
            ['fertilization', ['ftype' => 'orgánico',  'fname' => 'Compost de orujo',       'qty' => 2000, 'method' => 'incorporación', 'n' => 40, 'p' => 20, 'k' => 30]],
            ['cultural',      ['wtype' => 'acolchado', 'desc' => 'Acolchado con restos de poda triturados. BCAM 6.', 'hours' => 4.0, 'workers' => 2, 'residue' => 'triturado_incorporado']],
        ],
        3 => [
            ['phytosanitary', ['product' => 'Caldo bordelés',        'ingredient' => 'Cobre',           'dose' => 3.0, 'area' => null, 'method' => 'pulverización', 'target' => 'Mildiu',     'wind' => 8.0, 'hum' => 72.0]],
            ['observation',   ['type' => 'fenología', 'desc' => 'Brotación iniciada. Estadio BBCH 07-09. Punta verde visible.', 'severity' => 'none']],
            ['fertilization', ['ftype' => 'mineral',   'fname' => 'NPK 12-8-16 (Fertiberia)', 'qty' => 350, 'method' => 'localizada', 'n' => 42, 'p' => 28, 'k' => 56]],
        ],
        4 => [
            ['phytosanitary', ['product' => 'Azufre en polvo',       'ingredient' => 'Azufre',          'dose' => 25.0, 'area' => null, 'method' => 'espolvoreo',     'target' => 'Oídio',      'wind' => 5.0, 'hum' => 55.0]],
            ['irrigation',    ['volume' => 1200, 'unit' => 'L', 'method' => 'goteo', 'duration' => 120, 'source' => 'pozo', 'moisture_b' => 18.0, 'moisture_a' => 32.0, 'fertirrigation' => false]],
            ['cultural',      ['wtype' => 'desniete', 'desc' => 'Eliminación de brotes no productivos y chupones.', 'hours' => 5.0, 'workers' => 3, 'residue' => null]],
        ],
        5 => [
            ['phytosanitary', ['product' => 'Azufre mojable',        'ingredient' => 'Azufre',          'dose' => 4.0, 'area' => null, 'method' => 'pulverización', 'target' => 'Oídio',      'wind' => 6.5, 'hum' => 60.0]],
            ['irrigation',    ['volume' => 1500, 'unit' => 'L', 'method' => 'goteo', 'duration' => 150, 'source' => 'pozo', 'moisture_b' => 15.0, 'moisture_a' => 30.0, 'fertirrigation' => true, 'fert_product' => 'Ácidos húmicos', 'fert_dose' => 2.5]],
            ['observation',   ['type' => 'fenología', 'desc' => 'Inicio de floración (BBCH 61-65). Buen cuajado esperado.', 'severity' => 'none']],
        ],
        6 => [
            ['phytosanitary', ['product' => 'Bacillus thuringiensis', 'ingredient' => 'Bt kurstaki',     'dose' => 1.5, 'area' => null, 'method' => 'pulverización', 'target' => 'Polilla del racimo', 'wind' => 4.0, 'hum' => 50.0]],
            ['irrigation',    ['volume' => 2000, 'unit' => 'L', 'method' => 'goteo', 'duration' => 180, 'source' => 'pozo', 'moisture_b' => 12.0, 'moisture_a' => 28.0, 'fertirrigation' => false]],
            ['cultural',      ['wtype' => 'deshojado', 'desc' => 'Deshojado cara norte. Mejorar aireación racimos.', 'hours' => 6.0, 'workers' => 4, 'residue' => null]],
            ['observation',   ['type' => 'plaga',     'desc' => 'Revisión de trampas cromáticas. Capturas de polilla bajas (3/trampa/semana).', 'severity' => 'low']],
        ],
        7 => [
            ['irrigation',    ['volume' => 2500, 'unit' => 'L', 'method' => 'goteo', 'duration' => 200, 'source' => 'pozo', 'moisture_b' => 10.0, 'moisture_a' => 25.0, 'fertirrigation' => true, 'fert_product' => 'Sulfato potásico', 'fert_dose' => 3.0]],
            ['phytosanitary', ['product' => 'Confusión sexual (difusores)', 'ingredient' => 'Feromonas', 'dose' => 0.5, 'area' => null, 'method' => 'difusores',     'target' => 'Polilla del racimo', 'wind' => null, 'hum' => null]],
            ['observation',   ['type' => 'fenología', 'desc' => 'Inicio envero (BBCH 81). 30% de bayas cambiando color.', 'severity' => 'none']],
            ['fertilization', ['ftype' => 'mineral',   'fname' => 'Sulfato potásico',          'qty' => 200, 'method' => 'fertirrigación', 'n' => 0, 'p' => 0, 'k' => 100]],
        ],
        8 => [
            ['observation',   ['type' => 'maduración', 'desc' => 'Control madurez: 11.8° Baumé, pH 3.32, acidez 6.5 g/L. 10 días para óptimo.', 'severity' => 'none']],
            ['irrigation',    ['volume' => 800, 'unit' => 'L', 'method' => 'goteo', 'duration' => 90, 'source' => 'pozo', 'moisture_b' => 14.0, 'moisture_a' => 22.0, 'fertirrigation' => false]],
            ['harvest',       []],  // Handled specially
        ],
        9 => [
            ['harvest',       []],  // Second harvest pass
            ['observation',   ['type' => 'maduración', 'desc' => 'Control post-vendimia parcial: zonas altas aún en maduración. pH 3.45, 13.2° Baumé.', 'severity' => 'none']],
        ],
        10 => [
            ['harvest',       []],  // Final harvest
            ['post_harvest',  ['app_type' => 'copper_treatment',   'dose' => 3.5, 'dose_unit' => 'kg/ha', 'water' => 400, 'reentry' => 48]],
            ['observation',   ['type' => 'general', 'desc' => 'Viñedo tras vendimia. Hojas en senescencia. Sin síntomas de estrés.', 'severity' => 'none']],
        ],
        11 => [
            ['post_harvest',  ['app_type' => 'wound_sealing',     'dose' => 1.0, 'dose_unit' => 'L/ha',  'water' => 200, 'reentry' => 0]],
            ['fertilization', ['ftype' => 'orgánico',  'fname' => 'Abono orgánico NPK 3-2-5', 'qty' => 1500, 'method' => 'incorporación', 'n' => 45, 'p' => 30, 'k' => 75]],
            ['cultural',      ['wtype' => 'laboreo', 'desc' => 'Laboreo superficial entre calles. Incorporación restos vegetales.', 'hours' => 3.0, 'workers' => 1, 'residue' => 'triturado_superficie']],
        ],
        12 => [
            ['cultural',      ['wtype' => 'prepoda', 'desc' => 'Prepoda mecánica para facilitar poda invernal.', 'hours' => 4.0, 'workers' => 2, 'residue' => 'triturado_superficie']],
            ['observation',   ['type' => 'general', 'desc' => 'Inspección fin de campaña. Estado sanitario bueno. Madera sana.', 'severity' => 'none']],
        ],
    ];

    // ── Fenología BBCH por evento ────────────────────────────────────────────
    private const PHENOLOGY_EVENTS = [
        ['event' => 'budbreak',     'month' => 3,  'day_range' => [5, 15],  'bbch' => 7,  'dd' => 120],
        ['event' => 'shoot_growth', 'month' => 4,  'day_range' => [1, 10],  'bbch' => 16, 'dd' => 250],
        ['event' => 'flowering',    'month' => 5,  'day_range' => [10, 25], 'bbch' => 65, 'dd' => 450],
        ['event' => 'fruit_set',    'month' => 6,  'day_range' => [5, 15],  'bbch' => 71, 'dd' => 600],
        ['event' => 'veraison',     'month' => 7,  'day_range' => [15, 28], 'bbch' => 81, 'dd' => 950],
        ['event' => 'pre_harvest',  'month' => 8,  'day_range' => [10, 20], 'bbch' => 85, 'dd' => 1200],
        ['event' => 'harvest',      'month' => 9,  'day_range' => [1, 15],  'bbch' => 89, 'dd' => 1400],
    ];

    // ── Variedades a plantar en la parcela ───────────────────────────────────
    private const PLANTINGS = [
        ['variety' => 'Listán Negro',       'area_pct' => 0.40, 'year' => 2008],
        ['variety' => 'Malvasía Volcánica', 'area_pct' => 0.30, 'year' => 2012],
        ['variety' => 'Negramoll',          'area_pct' => 0.30, 'year' => 2010],
    ];

    public function run(): void
    {
        $now = now();

        // ── Verificar que la parcela existe ──────────────────────────────────
        $plot = DB::table('plots')->find(self::PLOT_ID);
        if (! $plot) {
            $this->command->error('  ❌ No existe la parcela con ID '.self::PLOT_ID);

            return;
        }

        $vitId = $plot->viticulturist_id;
        $area = (float) $plot->area;

        $this->command->info("  📍 Parcela: {$plot->name} (ID {$plot->id}), viticultor #{$vitId}, {$area} ha");

        $this->cleanup($vitId);

        // ── Asegurar vínculo winery + notebook_access ────────────────────────
        $this->ensureWineryLink($vitId, $now);

        // ── Asegurar campañas del viticultor ─────────────────────────────────
        $vitCampaigns = $this->ensureViticulturistCampaigns($vitId, $now);

        // ── Asegurar campañas de bodega (para grape_reception_batches) ───────
        $wineryCampaigns = $this->ensureWineryCampaigns($now);

        // ── Asegurar productos fitosanitarios ────────────────────────────────
        $phytoProducts = $this->ensurePhytosanitaryProducts($now);

        // ── Crear/asegurar plantaciones ──────────────────────────────────────
        $plantings = $this->ensurePlantings($area, $now);
        if (empty($plantings)) {
            $this->command->error('  ❌ No se pudieron crear plantaciones.');

            return;
        }

        // ── Generar datos por campaña ────────────────────────────────────────
        $totalActivities = 0;
        $totalPhenology = 0;
        $totalEstimates = 0;
        $totalHarvests = 0;
        $totalBatches = 0;

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        foreach ([2023, 2024, 2025] as $vintage) {
            $vitCampaignId = $vitCampaigns[$vintage] ?? null;
            $wineryCampaignId = $wineryCampaigns[$vintage] ?? null;
            if (! $vitCampaignId || ! $wineryCampaignId) {
                continue;
            }

            // Variation factors per vintage
            $vintageQuality = match ($vintage) {
                2023 => ['offset' => -0.1, 'yield_factor' => 0.90, 'label' => 'normal'],
                2024 => ['offset' => 0.2, 'yield_factor' => 1.10, 'label' => 'excelente'],
                2025 => ['offset' => 0.0, 'yield_factor' => 0.95, 'label' => 'en curso'],
            };

            $harvestIdx = 0;

            // ── Actividades por mes ──────────────────────────────────────
            foreach (self::MONTHLY_PLAN as $month => $activities) {
                // Skip future months for 2025
                if ($vintage === 2025 && $month > 9) {
                    continue;
                }

                foreach ($activities as [$actType, $subData]) {
                    // Pick planting for this activity
                    $planting = $plantings[$totalActivities % count($plantings)];

                    $day = mt_rand(1, 28);
                    $date = sprintf('%04d-%02d-%02d', $vintage, $month, $day);

                    // Temperature coherent with month (Canarias)
                    $temp = match (true) {
                        $month <= 2 || $month >= 11 => round(mt_rand(140, 200) / 10, 2),
                        $month >= 6 && $month <= 9 => round(mt_rand(240, 340) / 10, 2),
                        default => round(mt_rand(180, 260) / 10, 2),
                    };

                    $weather = ['soleado', 'parcialmente nublado', 'nublado', 'despejado', 'bruma matinal', 'viento moderado'][$totalActivities % 6];

                    $phenoStage = $this->phenoStageForMonth($month);

                    if ($actType === 'harvest') {
                        // Harvests: create activity + harvest record + batch
                        $result = $this->createHarvestActivity(
                            $planting, $vitId, $vitCampaignId, $wineryCampaignId,
                            $vintage, $date, $temp, $weather, $phenoStage,
                            $vintageQuality, $containers, $harvestIdx, $now
                        );
                        $totalActivities++;
                        $totalHarvests++;
                        if ($result['new_batch']) {
                            $totalBatches++;
                        }
                        $harvestIdx++;
                    } else {
                        // Regular activity + detail record
                        $activityId = $this->createActivity(
                            $actType, $planting, $vitId, $vitCampaignId,
                            $date, $temp, $weather, $phenoStage, $vintage, $now
                        );

                        $this->createDetailRecord($actType, $activityId, $subData, $planting, $area, $phytoProducts, $now);
                        $totalActivities++;
                    }
                }
            }

            // ── Fenología por plantación ─────────────────────────────────
            foreach ($plantings as $planting) {
                foreach (self::PHENOLOGY_EVENTS as $pheno) {
                    if ($vintage === 2025 && $pheno['month'] > 9) {
                        continue;
                    }

                    $day = mt_rand($pheno['day_range'][0], $pheno['day_range'][1]);
                    // Slight DD variation per vintage
                    $dd = round($pheno['dd'] + ($vintageQuality['offset'] * 50) + mt_rand(-20, 20), 2);

                    DB::table('phenology_observations')->insert([
                        'plot_planting_id' => $planting['id'],
                        'campaign_id' => $vitCampaignId,
                        'viticulturist_id' => $vitId,
                        'event' => $pheno['event'],
                        'obs_date' => sprintf('%04d-%02d-%02d', $vintage, $pheno['month'], $day),
                        'source' => 'manual',
                        'confidence' => mt_rand(80, 100),
                        'degree_days_accumulated' => max(0, $dd),
                        'bbch_code' => $pheno['bbch'],
                        'notes' => "Registro fenológico {$pheno['event']} — campaña {$vintage}.",
                        'active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $totalPhenology++;
                }
            }

            // ── Aforos (estimated_yields) por plantación ─────────────────
            foreach ($plantings as $planting) {
                $yieldPerHa = round(mt_rand(4000, 7000) * $vintageQuality['yield_factor']);
                $plantingArea = (float) $planting['area'];
                $totalYield = round($yieldPerHa * $plantingArea, 2);

                // Check unique constraint (plot_planting_id, campaign_id, estimation_round)
                $exists = DB::table('estimated_yields')
                    ->where('plot_planting_id', $planting['id'])
                    ->where('campaign_id', $vitCampaignId)
                    ->where('estimation_round', $vintage < 2025 ? 4 : 3)
                    ->exists();

                if (! $exists) {
                    DB::table('estimated_yields')->insert([
                        'plot_planting_id' => $planting['id'],
                        'campaign_id' => $vitCampaignId,
                        'estimated_by' => $vitId,
                        'vintage' => $vintage,
                        'estimated_yield_per_hectare' => $yieldPerHa,
                        'estimated_total_yield' => $totalYield,
                        'estimation_date' => sprintf('%04d-07-%02d', $vintage, mt_rand(10, 25)),
                        'estimation_method' => 'sampling',
                        'estimation_round' => $vintage < 2025 ? 4 : 3,
                        'status' => $vintage < 2025 ? 'confirmed' : 'confirmed',
                        'active' => true,
                        'bunches_per_plant' => mt_rand(10, 16),
                        'bunch_weight_grams' => mt_rand(150, 260),
                        'total_plants_sampled' => mt_rand(20, 40),
                        'health_percentage' => round(mt_rand(88, 100), 1),
                        'health_status' => mt_rand(0, 5) === 0 ? 'good' : 'excellent',
                        'potential_alcohol' => round(mt_rand(118, 142) / 10, 1),
                        'notes' => "Aforo parcela {$plot->name}, variedad {$planting['variety']}. Campaña {$vintage}.",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $totalEstimates++;
                }
            }
        }

        $this->command->info("  ✅ Ciclo de vida parcela #{$plot->id}:");
        $this->command->info('     Plantaciones:  '.count($plantings));
        $this->command->info("     Actividades:   {$totalActivities} (con registros de detalle)");
        $this->command->info("     Fenología:     {$totalPhenology} observaciones");
        $this->command->info("     Aforos:        {$totalEstimates} estimaciones");
        $this->command->info("     Vendimias:     {$totalHarvests} recepciones en {$totalBatches} lotes");
        $total = count($plantings) + $totalActivities + $totalPhenology + $totalEstimates;
        $this->command->info("     TOTAL:         {$total} registros");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SETUP HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    private function ensureWineryLink(int $vitId, $now): void
    {
        $linked = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->where('viticulturist_id', $vitId)
            ->exists();

        if (! $linked) {
            DB::table('winery_viticulturist')->insert([
                'winery_id' => self::WINERY_USER_ID,
                'viticulturist_id' => $vitId,
                'assigned_by' => self::WINERY_USER_ID,
                'source' => 'own',
                'notebook_access' => true,
                'notebook_granted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('winery_viticulturist')
                ->where('winery_id', self::WINERY_USER_ID)
                ->where('viticulturist_id', $vitId)
                ->update(['notebook_access' => true, 'notebook_granted_at' => $now]);
        }
    }

    private function ensureViticulturistCampaigns(int $vitId, $now): array
    {
        $campaigns = [];
        foreach ([2023 => false, 2024 => false, 2025 => true] as $year => $active) {
            $campId = DB::table('campaigns')
                ->where('viticulturist_id', $vitId)
                ->where('year', $year)
                ->value('id');
            if (! $campId) {
                $campId = DB::table('campaigns')->insertGetId([
                    'name' => "Campaña {$year}",
                    'year' => $year,
                    'viticulturist_id' => $vitId,
                    'start_date' => "{$year}-01-01",
                    'end_date' => "{$year}-12-31",
                    'active' => $active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $campaigns[$year] = $campId;
        }

        return $campaigns;
    }

    private function ensureWineryCampaigns($now): array
    {
        $campaigns = [];
        foreach ([2023, 2024, 2025] as $year) {
            $campId = DB::table('campaigns')
                ->where('viticulturist_id', self::WINERY_USER_ID)
                ->where('year', $year)
                ->value('id');
            if ($campId) {
                $campaigns[$year] = $campId;
            }
        }
        // If no winery campaigns, create them
        if (empty($campaigns)) {
            foreach ([2023 => false, 2024 => false, 2025 => true] as $year => $active) {
                $campaigns[$year] = DB::table('campaigns')->insertGetId([
                    'name' => "Vendimia {$year}",
                    'year' => $year,
                    'viticulturist_id' => self::WINERY_USER_ID,
                    'start_date' => "{$year}-08-01",
                    'end_date' => "{$year}-11-30",
                    'active' => $active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $campaigns;
    }

    private function ensurePhytosanitaryProducts($now): array
    {
        $products = [
            ['name' => 'Caldo bordelés',              'ingredient' => 'Cobre (hidróxido cúprico)', 'type' => 'fungicida',   'tox' => 'III', 'withdrawal' => 14],
            ['name' => 'Azufre en polvo',              'ingredient' => 'Azufre',                    'type' => 'fungicida',   'tox' => 'IV',  'withdrawal' => 5],
            ['name' => 'Azufre mojable',               'ingredient' => 'Azufre (SC)',               'type' => 'fungicida',   'tox' => 'IV',  'withdrawal' => 5],
            ['name' => 'Bacillus thuringiensis',       'ingredient' => 'Bt kurstaki',               'type' => 'insecticida', 'tox' => 'IV',  'withdrawal' => 0],
            ['name' => 'Confusión sexual (difusores)', 'ingredient' => 'Feromonas (E,Z)-7,9-dodecadienilo', 'type' => 'insecticida', 'tox' => 'IV', 'withdrawal' => 0],
        ];

        $ids = [];
        foreach ($products as $prod) {
            $id = DB::table('phytosanitary_products')
                ->where('name', $prod['name'])
                ->value('id');
            if (! $id) {
                $id = DB::table('phytosanitary_products')->insertGetId([
                    'name' => $prod['name'],
                    'active_ingredient' => $prod['ingredient'],
                    'type' => $prod['type'],
                    'toxicity_class' => $prod['tox'],
                    'withdrawal_period_days' => $prod['withdrawal'],
                    'registration_number' => 'ES-'.str_pad(mt_rand(10000, 99999), 8, '0', STR_PAD_LEFT),
                    'registration_status' => 'active',
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $ids[$prod['name']] = $id;
        }

        return $ids;
    }

    private function ensurePlantings(float $plotArea, $now): array
    {
        $result = [];
        foreach (self::PLANTINGS as $def) {
            $varietyId = DB::table('grape_varieties')
                ->where('name', $def['variety'])
                ->value('id');

            if (! $varietyId) {
                continue;
            } // Variety should exist from WineryGrapeReceptionsSeeder

            $plantingId = DB::table('plot_plantings')
                ->where('plot_id', self::PLOT_ID)
                ->where('grape_variety_id', $varietyId)
                ->value('id');

            $plantingArea = round($plotArea * $def['area_pct'], 3);
            $harvestLimit = round($plantingArea * mt_rand(7000, 8500));

            if (! $plantingId) {
                $plantingId = DB::table('plot_plantings')->insertGetId([
                    'plot_id' => self::PLOT_ID,
                    'grape_variety_id' => $varietyId,
                    'area_planted' => $plantingArea,
                    'planting_year' => $def['year'],
                    'harvest_limit_kg' => $harvestLimit,
                    'status' => 'active',
                    'irrigated' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                // Update harvest_limit_kg if not set
                DB::table('plot_plantings')
                    ->where('id', $plantingId)
                    ->whereNull('harvest_limit_kg')
                    ->update(['harvest_limit_kg' => $harvestLimit, 'area_planted' => $plantingArea]);
            }

            $result[] = [
                'id' => $plantingId,
                'variety' => $def['variety'],
                'area' => $plantingArea,
            ];
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ACTIVITY CREATION
    // ═══════════════════════════════════════════════════════════════════════════

    private function createActivity(
        string $type, array $planting, int $vitId, int $campaignId,
        string $date, float $temp, string $weather, ?string $phenoStage,
        int $vintage, $now
    ): int {
        return DB::table('agricultural_activities')->insertGetId([
            'plot_id' => self::PLOT_ID,
            'plot_planting_id' => $planting['id'],
            'viticulturist_id' => $vitId,
            'campaign_id' => $campaignId,
            'activity_type' => $type,
            'phenological_stage' => $phenoStage,
            'activity_date' => $date,
            'weather_conditions' => $weather,
            'temperature' => $temp,
            'notes' => null, // Detail record has the description
            'is_locked' => $vintage < 2025,
            'locked_at' => $vintage < 2025 ? $now : null,
            'locked_by' => $vintage < 2025 ? $vitId : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createDetailRecord(
        string $type, int $activityId, array $data,
        array $planting, float $plotArea, array $phytoProducts, $now
    ): void {
        $plantingArea = (float) $planting['area'];

        match ($type) {
            'pruning' => DB::table('cultural_works')->insert([
                'activity_id' => $activityId,
                'work_type' => 'poda',
                'pruning_type' => $data['pruning_type'],
                'productive_buds_per_hectare' => $data['buds'],
                'hours_worked' => $data['hours'],
                'workers_count' => $data['workers'],
                'residue_management' => 'triturado_superficie',
                'description' => "Poda tipo {$data['pruning_type']}. {$data['buds']} yemas/ha.",
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            'cultural' => DB::table('cultural_works')->insert([
                'activity_id' => $activityId,
                'work_type' => $data['wtype'],
                'hours_worked' => $data['hours'],
                'workers_count' => $data['workers'],
                'residue_management' => $data['residue'],
                'description' => $data['desc'],
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            'phytosanitary' => DB::table('phytosanitary_treatments')->insert([
                'activity_id' => $activityId,
                'product_id' => $phytoProducts[$data['product']] ?? null,
                'dose_per_hectare' => $data['dose'],
                'total_dose' => round($data['dose'] * $plantingArea, 3),
                'area_treated' => $data['area'] ?? $plantingArea,
                'application_method' => $data['method'],
                'wind_speed' => $data['wind'],
                'humidity' => $data['hum'],
                'buffer_zone_respected' => true,
                'prior_non_chemical_methods' => $data['method'] === 'difusores',
                'plague_monitoring' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            'fertilization' => DB::table('fertilizations')->insert([
                'activity_id' => $activityId,
                'fertilizer_type' => $data['ftype'],
                'fertilizer_name' => $data['fname'],
                'quantity' => $data['qty'],
                'application_method' => $data['method'],
                'area_applied' => $plantingArea,
                'nitrogen_uf' => $data['n'],
                'phosphorus_uf' => $data['p'],
                'potassium_uf' => $data['k'],
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            'irrigation' => DB::table('irrigations')->insert([
                'activity_id' => $activityId,
                'water_volume' => $data['volume'],
                'water_volume_unit' => $data['unit'],
                'irrigation_method' => $data['method'],
                'duration_minutes' => $data['duration'],
                'water_source' => $data['source'],
                'soil_moisture_before' => $data['moisture_b'],
                'soil_moisture_after' => $data['moisture_a'],
                'is_fertirrigation' => $data['fertirrigation'],
                'fertilizer_product' => $data['fert_product'] ?? null,
                'fertilizer_dose_per_ha' => $data['fert_dose'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            'observation' => DB::table('observations')->insert([
                'activity_id' => $activityId,
                'observation_type' => $data['type'],
                'description' => $data['desc'],
                'severity' => $data['severity'],
                'affected_area_percentage' => $data['severity'] === 'none' ? 0 : mt_rand(5, 25),
                'threshold_exceeded' => $data['severity'] !== 'none' && $data['severity'] !== 'low',
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            'post_harvest' => DB::table('post_harvest_treatments')->insert([
                'activity_id' => $activityId,
                'application_type' => $data['app_type'],
                'treated_area_ha' => $plantingArea,
                'dose_per_hectare' => $data['dose'],
                'dose_unit' => $data['dose_unit'],
                'water_volume_liters' => $data['water'],
                'reentry_interval_hours' => $data['reentry'],
                'created_at' => $now,
                'updated_at' => $now,
            ]),

            default => null,
        };
    }

    private function createHarvestActivity(
        array $planting, int $vitId, int $vitCampaignId, int $wineryCampaignId,
        int $vintage, string $date, float $temp, string $weather, ?string $phenoStage,
        array $quality, array $containers, int $harvestIdx, $now
    ): array {
        $plantingArea = (float) $planting['area'];
        $baseYield = mt_rand(3500, 7000) * $quality['yield_factor'];
        $weight = round($plantingArea * $baseYield, 1);

        $baume = round(12.5 + $quality['offset'] + mt_rand(-5, 5) / 10, 2);
        $brix = round($baume * 1.8, 2);
        $ph = round(3.40 + mt_rand(-8, 8) / 100, 2);
        $acid = round(5.8 - $quality['offset'] * 0.5 + mt_rand(-5, 5) / 10, 2);
        $alcohol = round($baume * 1.035, 2);

        // Activity record
        $activityId = DB::table('agricultural_activities')->insertGetId([
            'plot_id' => self::PLOT_ID,
            'plot_planting_id' => $planting['id'],
            'viticulturist_id' => $vitId,
            'campaign_id' => $vitCampaignId,
            'activity_type' => 'harvest',
            'phenological_stage' => $phenoStage,
            'activity_date' => $date,
            'weather_conditions' => $weather,
            'temperature' => $temp,
            'notes' => "Vendimia {$planting['variety']} — {$quality['label']} {$vintage}.",
            'is_locked' => $vintage < 2025,
            'locked_at' => $vintage < 2025 ? $now : null,
            'locked_by' => $vintage < 2025 ? $vitId : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Grape reception batch (one per planting per campaign)
        $batchKey = $planting['id'].'_'.$wineryCampaignId;
        $batchId = DB::table('grape_reception_batches')
            ->where('winery_id', self::WINERY_USER_ID)
            ->where('plot_planting_id', $planting['id'])
            ->where('campaign_id', $wineryCampaignId)
            ->value('id');

        $newBatch = false;
        if (! $batchId) {
            $batchId = DB::table('grape_reception_batches')->insertGetId([
                'winery_id' => self::WINERY_USER_ID,
                'viticulturist_id' => $vitId,
                'plot_planting_id' => $planting['id'],
                'campaign_id' => $wineryCampaignId,
                'vintage_year' => $vintage,
                'total_weight_kg' => 0,
                'status' => $vintage < 2025 ? 'invoiced' : 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $newBatch = true;
        }

        $containerId = ! empty($containers) ? $containers[$harvestIdx % count($containers)] : null;

        // Harvest record (reception at winery)
        DB::table('harvests')->insert([
            'winery_id' => self::WINERY_USER_ID,
            'activity_id' => $activityId,
            'batch_id' => $batchId,
            'plot_planting_id' => $planting['id'],
            'container_id' => $containerId,
            'harvest_start_date' => $date,
            'total_weight' => $weight,
            'vintage' => $vintage,
            'yield_per_hectare' => $plantingArea > 0 ? round($weight / $plantingArea, 3) : null,
            'baume_degree' => $baume,
            'brix_degree' => $brix,
            'ph_level' => $ph,
            'acidity_level' => $acid,
            'potential_alcohol' => $alcohol,
            'health_status' => $harvestIdx % 5 === 0 ? 'daño_leve' : 'sano',
            'destination_type' => 'winery',
            'destination' => 'Bodega Agaete',
            'price_per_kg' => round(0.90 + mt_rand(0, 40) / 100, 4),
            'status' => 'active',
            'disqualified' => false,
            'harvest_ticket_number' => 'REC-P'.self::PLOT_ID.'-'.$vintage.'-'.str_pad($harvestIdx + 1, 3, '0', STR_PAD_LEFT),
            'sanitary_state_grapes' => $harvestIdx % 5 === 0 ? 92.0 : 100.0,
            'notes' => "Recepción {$planting['variety']} parcela #".self::PLOT_ID.", vendimia {$vintage}.",
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('grape_reception_batches')
            ->where('id', $batchId)
            ->increment('total_weight_kg', $weight);

        return ['new_batch' => $newBatch];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    private function phenoStageForMonth(int $month): ?string
    {
        return match (true) {
            $month <= 2 => 'reposo_invernal',
            $month === 3 => 'brotacion',
            $month === 4 => 'desarrollo_vegetativo',
            $month === 5 => 'floracion',
            $month === 6 => 'cuajado',
            $month === 7 => 'envero',
            $month >= 8 && $month <= 9 => 'maduracion',
            $month === 10 => 'post_vendimia',
            default => 'reposo_invernal',
        };
    }

    private function cleanup(int $vitId): void
    {
        // Clean activities and their detail records for this plot
        $activityIds = DB::table('agricultural_activities')
            ->where('plot_id', self::PLOT_ID)
            ->pluck('id');

        if ($activityIds->isNotEmpty()) {
            DB::table('phytosanitary_treatments')->whereIn('activity_id', $activityIds)->delete();
            DB::table('fertilizations')->whereIn('activity_id', $activityIds)->delete();
            DB::table('irrigations')->whereIn('activity_id', $activityIds)->delete();
            DB::table('cultural_works')->whereIn('activity_id', $activityIds)->delete();
            DB::table('observations')->whereIn('activity_id', $activityIds)->delete();
            DB::table('post_harvest_treatments')->whereIn('activity_id', $activityIds)->delete();
            DB::table('agricultural_activities')->whereIn('id', $activityIds)->delete();
        }

        // Clean plantings-related data
        $plantingIds = DB::table('plot_plantings')
            ->where('plot_id', self::PLOT_ID)
            ->pluck('id');

        if ($plantingIds->isNotEmpty()) {
            // Phenology
            DB::table('phenology_observations')->whereIn('plot_planting_id', $plantingIds)->delete();

            // Estimated yields
            DB::table('estimated_yields')->whereIn('plot_planting_id', $plantingIds)->delete();

            // Harvests from this plot (winery receptions)
            $harvestIds = DB::table('harvests')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('plot_planting_id', $plantingIds)
                ->pluck('id');

            if ($harvestIds->isNotEmpty()) {
                DB::table('harvest_stocks')->whereIn('harvest_id', $harvestIds)->delete();
                DB::table('harvests')->whereIn('id', $harvestIds)->delete();
            }

            // Batches
            DB::table('grape_reception_batches')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('plot_planting_id', $plantingIds)
                ->delete();

            // Yield forecasts
            DB::table('winery_yield_forecasts')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('plot_planting_id', $plantingIds)
                ->delete();
        }
    }
}
