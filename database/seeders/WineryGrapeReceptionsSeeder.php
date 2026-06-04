<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Genera recepciones de uva para la bodega (user_id=1).
 *
 * Auto-suficiente: si no hay viticultores vinculados crea 6 demo viticulturists
 * con sus parcelas, plantaciones y campañas (2023-2025).
 *
 * Produce ~95 recepciones distribuidas en 3 vendimias.
 *
 * También crea campañas para el usuario bodega (id=1) para que
 * los filtros de harvest-quality y harvest-summary funcionen.
 */
class WineryGrapeReceptionsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const DEMO_EMAIL_SUFFIX = '@viticultor.agro365.demo';

    // ── Geografía (Gran Canaria) ─────────────────────────────────────────────────
    private const AC_ID = 5;    // Canarias

    private const PROVINCE_ID = 14;   // Las Palmas

    private const MUN_DB_IDS = [
        'Agaete' => 5243,
        'Artenara' => 5246,
        'Arucas' => 5247,
        'Firgas' => 5249,
        'Gáldar' => 5250,
        'Guía' => 5251,
        'Ingenio' => 5253,
    ];

    private const PREFIJOS = ['Finca', 'Parcela', 'Viña', 'Viñedo', 'Pago', 'Suerte', 'Lote'];

    // ── Demo viticulturists ──────────────────────────────────────────────────────

    private const VITICULTURISTS = [
        [
            'name' => 'Pedro González Álvarez',
            'email' => 'pedro.gonzalez@viticultor.agro365.demo',
            'plots' => [
                ['name' => 'Finca Las Cumbres',   'area' => 2.850, 'varieties' => ['Listán Negro', 'Negramoll']],
                ['name' => 'Parcela El Carrizal', 'area' => 1.320, 'varieties' => ['Vijariego Negro']],
            ],
        ],
        [
            'name' => 'María Suárez Cabrera',
            'email' => 'maria.suarez@viticultor.agro365.demo',
            'plots' => [
                ['name' => 'Viña La Caldera',     'area' => 1.750, 'varieties' => ['Listán Blanco', 'Marmajuelo']],
                ['name' => 'Parcela Montaña Alta', 'area' => 0.980, 'varieties' => ['Malvasía Volcánica']],
            ],
        ],
        [
            'name' => 'José Luis Hernández Ramos',
            'email' => 'joseluis.hernandez@viticultor.agro365.demo',
            'plots' => [
                ['name' => 'Finca El Rincón',     'area' => 3.200, 'varieties' => ['Listán Negro', 'Listán Blanco']],
                ['name' => 'Viña Las Palomeras',  'area' => 1.600, 'varieties' => ['Negramoll', 'Gual']],
            ],
        ],
        [
            'name' => 'Carmen Falcón Betancor',
            'email' => 'carmen.falcon@viticultor.agro365.demo',
            'plots' => [
                ['name' => 'Parcela Agaete Norte', 'area' => 1.200, 'varieties' => ['Malvasía Volcánica', 'Gual']],
                ['name' => 'Finca Barranco Hondo', 'area' => 0.750, 'varieties' => ['Listán Blanco']],
            ],
        ],
        [
            'name' => 'Antonio Medina Santana',
            'email' => 'antonio.medina@viticultor.agro365.demo',
            'plots' => [
                ['name' => 'Viña Los Berrazales',  'area' => 2.100, 'varieties' => ['Listán Negro', 'Malvasía Volcánica']],
                ['name' => 'Parcela La Aldea Alta', 'area' => 1.450, 'varieties' => ['Negramoll']],
            ],
        ],
        [
            'name' => 'Laura Déniz Rodríguez',
            'email' => 'laura.deniz@viticultor.agro365.demo',
            'plots' => [
                ['name' => 'Finca Tamadaba',      'area' => 1.900, 'varieties' => ['Vijariego Negro', 'Marmajuelo']],
                ['name' => 'Parcela Cruz de Tejeda', 'area' => 1.050, 'varieties' => ['Gual']],
            ],
        ],
    ];

    // Parámetros base por variedad
    private const VARIETY_PARAMS = [
        'Listán Negro' => ['color' => 'red',   'code' => 'LN',  'baume' => 13.0, 'brix' => 23.5, 'ph' => 3.42, 'acid' => 5.9, 'price' => 0.90],
        'Negramoll' => ['color' => 'red',   'code' => 'NGM', 'baume' => 13.3, 'brix' => 24.1, 'ph' => 3.48, 'acid' => 5.6, 'price' => 0.95],
        'Vijariego Negro' => ['color' => 'red',   'code' => 'VJN', 'baume' => 13.8, 'brix' => 25.0, 'ph' => 3.52, 'acid' => 5.3, 'price' => 1.20],
        'Listán Blanco' => ['color' => 'white', 'code' => 'LB',  'baume' => 12.5, 'brix' => 22.5, 'ph' => 3.38, 'acid' => 6.2, 'price' => 0.88],
        'Malvasía Volcánica' => ['color' => 'white', 'code' => 'MV',  'baume' => 12.8, 'brix' => 23.2, 'ph' => 3.40, 'acid' => 6.0, 'price' => 1.35],
        'Marmajuelo' => ['color' => 'white', 'code' => 'MRM', 'baume' => 12.0, 'brix' => 21.8, 'ph' => 3.32, 'acid' => 6.8, 'price' => 0.92],
        'Gual' => ['color' => 'white', 'code' => 'GUL', 'baume' => 13.0, 'brix' => 23.5, 'ph' => 3.42, 'acid' => 6.1, 'price' => 1.10],
    ];

    // ─────────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ── Locate geography (Gran Canaria / Las Palmas, fallback to any) ────────
        $province = DB::table('provinces')
            ->where('name', 'LIKE', '%Las Palmas%')
            ->first()
            ?? DB::table('provinces')->first();

        if (! $province) {
            $this->command->warn('  ⚠️  No hay provincias en la base de datos. Saltando recepciones de uva.');

            return;
        }

        $community = DB::table('autonomous_communities')->find($province->autonomous_community_id ?? 1)
            ?? DB::table('autonomous_communities')->first();

        $municipality = DB::table('municipalities')
            ->where('province_id', $province->id)
            ->where('name', 'LIKE', '%Agaete%')
            ->first()
            ?? DB::table('municipalities')->where('province_id', $province->id)->first()
            ?? DB::table('municipalities')->first();

        if (! $municipality) {
            $this->command->warn('  ⚠️  No hay municipios en la base de datos. Saltando recepciones de uva.');

            return;
        }

        // ── Ensure grape varieties exist ─────────────────────────────────────────
        $varietyIds = $this->ensureGrapeVarieties($now);

        // ── Ensure demo viticulturists, plots, plantings, campaigns ─────────────
        $viticulturistIds = $this->ensureViticulturists(
            $now,
            $province,
            $community,
            $municipality,
            $varietyIds
        );

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  No se pudieron crear viticultores demo.');

            return;
        }

        // ── Ensure winery-level campaigns (needed for harvest-quality/summary filters) ──
        $wineryCampaigns = $this->ensureWineryCampaigns($now);

        // ── Create batches and harvests ───────────────────────────────────────────
        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        $totalHarvests = 0;
        $totalBatches = 0;
        $cidx = 0;

        foreach ($viticulturistIds as $vitData) {
            $vitId = $vitData['user_id'];
            $plantings = $vitData['plantings'];  // [{id, variety_name, variety_params, area}]

            foreach ([2023, 2024, 2025] as $vintage) {
                $campaignId = $wineryCampaigns[$vintage] ?? null;
                if (! $campaignId) {
                    continue;
                }

                foreach ($plantings as $planting) {
                    // One batch per (winery + plot_planting + campaign) — unique constraint
                    $batchId = DB::table('grape_reception_batches')->insertGetId([
                        'winery_id' => self::WINERY_USER_ID,
                        'viticulturist_id' => $vitId,
                        'plot_planting_id' => $planting['id'],
                        'campaign_id' => $campaignId,
                        'vintage_year' => $vintage,
                        'total_weight_kg' => 0,
                        'status' => $vintage < 2025 ? 'invoiced' : 'open',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $totalBatches++;

                    // Generate 2-3 harvest entries per batch
                    $numReceptions = $this->receptionsForVintage($vintage);

                    for ($r = 0; $r < $numReceptions; $r++) {
                        $params = $planting['variety_params'];
                        $area = (float) $planting['area'];
                        $weight = $this->harvestWeight($area, $vintage, $r);
                        $quality = $this->qualityParams($params, $vintage, $cidx);

                        $pricePerKg = round($params['price'] + ($cidx % 3) * 0.02, 4);
                        $totalValue = round($weight * $pricePerKg, 3);
                        $daysAgo = $this->harvestDaysAgo($vintage, $r);

                        $containerId = ! empty($containers) ? $containers[$cidx % count($containers)] : null;

                        DB::table('harvests')->insertGetId([
                            'winery_id' => self::WINERY_USER_ID,
                            'batch_id' => $batchId,
                            'plot_planting_id' => $planting['id'],
                            'container_id' => $containerId,
                            'harvest_start_date' => now()->subDays($daysAgo)->toDateString(),
                            'total_weight' => $weight,
                            'vintage' => $vintage,
                            'yield_per_hectare' => $area > 0 ? round($weight / $area, 3) : null,
                            'baume_degree' => $quality['baume'],
                            'brix_degree' => $quality['brix'],
                            'ph_level' => $quality['ph'],
                            'acidity_level' => $quality['acid'],
                            'potential_alcohol' => $quality['alcohol'],
                            'color_rating' => $quality['color_rating'],
                            'aroma_rating' => $quality['aroma_rating'],
                            'health_status' => $quality['health'],
                            'destination_type' => 'winery',
                            'destination' => 'Bodega Agaete',
                            'price_per_kg' => $pricePerKg,
                            'total_value' => $totalValue,
                            'harvest_ticket_number' => 'REC-'.$vintage.'-'.str_pad($cidx + 1, 4, '0', STR_PAD_LEFT),
                            'sanitary_state_grapes' => $quality['sanitary_grapes'],
                            'sanitary_state_botrytis' => $quality['botrytis'],
                            'sanitary_state_oidium' => $quality['oidium'],
                            'status' => 'active',
                            'disqualified' => false,
                            'notes' => $this->receptionNote($planting['variety_name'], $vintage, $quality['health']),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        // Update batch accumulated weight
                        DB::table('grape_reception_batches')
                            ->where('id', $batchId)
                            ->increment('total_weight_kg', $weight);

                        $totalHarvests++;
                        $cidx++;
                    }
                }
            }
        }

        $totalPlots = DB::table('plots')
            ->whereIn('viticulturist_id', array_column($viticulturistIds, 'user_id'))
            ->count();
        $this->command->info("✅ Parcelas SIGPAC: {$totalPlots} recintos Gran Canaria (6 viticultores, ~76-77 cada uno)");
        $this->command->info("✅ Recepciones de uva: {$totalHarvests} recepciones en {$totalBatches} lotes (6 viticultores, vendimias 2023-2025)");
    }

    // ── Setup helpers ─────────────────────────────────────────────────────────────

    private function ensureGrapeVarieties(\Carbon\Carbon $now): array
    {
        $ids = [];
        foreach (self::VARIETY_PARAMS as $name => $params) {
            $id = DB::table('grape_varieties')->where('name', $name)->value('id');
            if (! $id) {
                $id = DB::table('grape_varieties')->insertGetId([
                    'name' => $name,
                    'code' => $params['code'],
                    'color' => $params['color'],
                    'description' => "Variedad autóctona canaria — {$name}",
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $ids[$name] = $id;
        }

        return $ids;
    }

    /**
     * Crea campañas para el usuario bodega (id=1).
     * Esto permite que Campaign::forViticulturist(Auth::id()) devuelva registros
     * y los filtros de harvest-quality / harvest-summary funcionen correctamente.
     */
    private function ensureWineryCampaigns(\Carbon\Carbon $now): array
    {
        $campaigns = [];
        foreach ([2023 => false, 2024 => false, 2025 => true] as $year => $active) {
            $campId = DB::table('campaigns')
                ->where('viticulturist_id', self::WINERY_USER_ID)
                ->where('year', $year)
                ->value('id');
            if (! $campId) {
                $campId = DB::table('campaigns')->insertGetId([
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
            $campaigns[$year] = $campId;
        }

        return $campaigns;
    }

    private function ensureViticulturists(
        \Carbon\Carbon $now,
        object $province,
        object $community,
        object $municipality,
        array $varietyIds
    ): array {
        // ── Pass 1: users + winery links + campaigns ─────────────────────────────
        $configs = [];
        foreach (self::VITICULTURISTS as $vitDef) {
            $userId = DB::table('users')
                ->where('email', $vitDef['email'])
                ->value('id');

            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $vitDef['name'],
                    'email' => $vitDef['email'],
                    'email_verified_at' => $now,
                    'password' => Hash::make('agro365-demo'),
                    'role' => 'viticulturist',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $linked = DB::table('winery_viticulturist')
                ->where('winery_id', self::WINERY_USER_ID)
                ->where('viticulturist_id', $userId)
                ->exists();

            if (! $linked) {
                DB::table('winery_viticulturist')->insert([
                    'winery_id' => self::WINERY_USER_ID,
                    'viticulturist_id' => $userId,
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
                    ->where('viticulturist_id', $userId)
                    ->update(['notebook_access' => true, 'notebook_granted_at' => $now]);
            }

            $campaigns = [];
            foreach ([2023 => false, 2024 => false, 2025 => true] as $year => $active) {
                $campId = DB::table('campaigns')
                    ->where('viticulturist_id', $userId)
                    ->where('year', $year)
                    ->value('id');
                if (! $campId) {
                    $campId = DB::table('campaigns')->insertGetId([
                        'name' => "Campaña {$year}",
                        'year' => $year,
                        'viticulturist_id' => $userId,
                        'start_date' => "{$year}-08-01",
                        'end_date' => "{$year}-11-30",
                        'active' => $active,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $campaigns[$year] = $campId;
            }

            $configs[] = [
                'user_id' => $userId,
                'vitDef' => $vitDef,
                'campaigns' => $campaigns,
            ];
        }

        // ── Pass 2: 460 SIGPAC plots distributed round-robin among viticulturists ─
        $userIds = array_column($configs, 'user_id');
        $userPlotIds = $this->createSigpacPlotsForViticulturists(
            $userIds, $province, $community, $municipality, $now
        );

        // ── Pass 3: plantings for first 2 SIGPAC plots per viticulturist ────────
        $result = [];
        foreach ($configs as $config) {
            $userId = $config['user_id'];
            $vitDef = $config['vitDef'];
            $myPlotIds = $userPlotIds[$userId] ?? [];

            $plantings = [];
            foreach ($vitDef['plots'] as $plotIdx => $plotDef) {
                $plotId = $myPlotIds[$plotIdx] ?? null;
                if (! $plotId) {
                    continue;
                }

                $areaPerVariety = round($plotDef['area'] / count($plotDef['varieties']), 3);

                foreach ($plotDef['varieties'] as $varietyName) {
                    $varietyId = $varietyIds[$varietyName] ?? null;
                    if (! $varietyId) {
                        continue;
                    }

                    $plantingId = DB::table('plot_plantings')
                        ->where('plot_id', $plotId)
                        ->where('grape_variety_id', $varietyId)
                        ->value('id');

                    if (! $plantingId) {
                        $harvestLimitKg = round($areaPerVariety * mt_rand(7000, 8500));
                        $plantingId = DB::table('plot_plantings')->insertGetId([
                            'plot_id' => $plotId,
                            'grape_variety_id' => $varietyId,
                            'area_planted' => $areaPerVariety,
                            'planting_year' => mt_rand(2005, 2018),
                            'harvest_limit_kg' => $harvestLimitKg,
                            'status' => 'active',
                            'irrigated' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $plantings[] = [
                        'id' => $plantingId,
                        'variety_name' => $varietyName,
                        'variety_params' => self::VARIETY_PARAMS[$varietyName],
                        'area' => $areaPerVariety,
                    ];
                }
            }

            $result[] = [
                'user_id' => $userId,
                'name' => $vitDef['name'],
                'plantings' => $plantings,
                'campaigns' => $config['campaigns'],
            ];
        }

        return $result;
    }

    /**
     * Carga sigpac_gran_canaria.json y distribuye los 460 recintos de forma
     * round-robin entre los viticultores dados. Crea plot, sigpac_code (idempotente),
     * plot_geometry y multipart_plot_sigpac para cada recinto.
     *
     * @return array<int,int[]> [ userId => [plotId, ...], ... ]
     */
    private function createSigpacPlotsForViticulturists(
        array $userIds,
        object $province,
        object $community,
        object $municipality,
        \Carbon\Carbon $now
    ): array {
        $jsonPath = database_path('seeders/data/sigpac_gran_canaria.json');
        $recs = json_decode(file_get_contents($jsonPath), true);

        $userPlotIds = array_fill_keys($userIds, []);
        $count = count($userIds);

        foreach ($recs as $index => $rec) {
            $userId = $userIds[$index % $count];
            $ineCode = $rec['ine_code'];
            $polygon = str_pad($rec['polygon'], 3, '0', STR_PAD_LEFT);
            $parcel = str_pad($rec['parcel'], 5, '0', STR_PAD_LEFT);
            $enclosure = str_pad($rec['recinto'], 3, '0', STR_PAD_LEFT);

            $code = sprintf(
                '05%02d%03d000000%03d%05d%03d',
                35,
                (int) $ineCode,
                $rec['polygon'],
                $rec['parcel'],
                $rec['recinto']
            );

            // sigpac_code — idempotente: puede existir del ViticulturistDemoSeeder
            $existing = DB::table('sigpac_code')
                ->where('code_province', '35')
                ->where('code_municipality', str_pad($ineCode, 3, '0', STR_PAD_LEFT))
                ->where('code_polygon', $polygon)
                ->where('code_plot', $parcel)
                ->where('code_enclosure', $enclosure)
                ->first();

            $sigpacId = $existing
                ? $existing->id
                : DB::table('sigpac_code')->insertGetId([
                    'code_autonomous_community' => '05',
                    'code_province' => '35',
                    'code_municipality' => str_pad($ineCode, 3, '0', STR_PAD_LEFT),
                    'code_aggregate' => '000',
                    'code_zone' => '000',
                    'code_polygon' => $polygon,
                    'code_plot' => $parcel,
                    'code_enclosure' => $enclosure,
                    'code' => $code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // plot_geometry con WKT real
            $geomId = DB::table('plot_geometry')->insertGetId([
                'coordinates' => DB::raw("ST_GeomFromText('".$rec['wkt']."', 4326)"),
                'centroid' => DB::raw("ST_Centroid(ST_GeomFromText('".$rec['wkt']."', 4326))"),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $prefijo = self::PREFIJOS[$index % count(self::PREFIJOS)];
            $munShort = explode(' ', $rec['mun_name'])[0];
            $plotName = "{$prefijo} {$munShort} ".str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $munDbId = self::MUN_DB_IDS[$rec['mun_name']] ?? $municipality->id;

            $plotId = DB::table('plots')->insertGetId([
                'name' => $plotName,
                'viticulturist_id' => $userId,
                'area' => $rec['area_ha'] > 0 ? $rec['area_ha'] : round(mt_rand(10, 350) / 100, 2),
                'active' => true,
                'autonomous_community_id' => $community->id,
                'province_id' => $province->id,
                'municipality_id' => $munDbId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('multipart_plot_sigpac')->insert([
                'plot_id' => $plotId,
                'sigpac_code_id' => $sigpacId,
                'plot_geometry_id' => $geomId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $userPlotIds[$userId][] = $plotId;
        }

        return $userPlotIds;
    }

    // ── Data generation helpers ───────────────────────────────────────────────────

    private function receptionsForVintage(int $vintage): int
    {
        return match ($vintage) {
            2023 => 2,   // campaign closed — 2 receptions (early/late harvest)
            2024 => 2,   // excellent year, 2 batches per planting
            2025 => 2,   // current vintage, 2 receptions already
        };
    }

    private function harvestWeight(float $area, int $vintage, int $reception): float
    {
        // ~4000-8000 kg/ha for Canarian vineyards, less in older vintages
        $baseYield = match ($vintage) {
            2023 => mt_rand(3500, 6500),
            2024 => mt_rand(4000, 7500),
            2025 => mt_rand(3000, 6000),
        };
        $weight = round($area * $baseYield / 1.0, 1);

        return round(max(200, $weight), 1);
    }

    private function qualityParams(array $params, int $vintage, int $idx): array
    {
        // Vintage variation: 2024 was an excellent year, 2023 average, 2025 pending
        $vintageOffset = match ($vintage) {
            2023 => -0.1,
            2024 => 0.2,
            2025 => 0.0,
        };

        $variation = (($idx % 7) - 3) * 0.05; // ±0.15 variation

        $baume = round(max(10.0, $params['baume'] + $vintageOffset + $variation), 2);
        $brix = round(max(18.0, $params['brix'] + $vintageOffset * 2 + $variation * 2), 2);
        $ph = round(max(2.9, $params['ph'] + $variation * 0.1), 2);
        $acid = round(max(3.5, $params['acid'] - $variation * 0.3), 2);
        $alcohol = round($baume * 1.035, 2);

        // Ratings based on baume
        $colorRating = $baume >= 13.5 ? 'excelente' : ($baume >= 12.5 ? 'bueno' : 'aceptable');
        $aromaRating = $brix >= 24.0 ? 'excelente' : ($brix >= 22.0 ? 'bueno' : 'aceptable');

        // Health status: mostly sano, occasional daño_leve
        $health = ($idx % 9 === 0) ? 'daño_leve' : 'sano';

        // Sanitary state (0-100% affected)
        $botrytis = $health === 'sano' ? round(mt_rand(0, 3) / 10, 1) : round(mt_rand(5, 15) / 10, 1);
        $oidium = round(mt_rand(0, 2) / 10, 1);

        return [
            'baume' => $baume,
            'brix' => $brix,
            'ph' => $ph,
            'acid' => $acid,
            'alcohol' => $alcohol,
            'color_rating' => $colorRating,
            'aroma_rating' => $aromaRating,
            'health' => $health,
            'sanitary_grapes' => $health === 'sano' ? 100.0 : round(mt_rand(82, 95) / 1.0, 1),
            'botrytis' => $botrytis,
            'oidium' => $oidium,
        ];
    }

    private function harvestDaysAgo(int $vintage, int $reception): int
    {
        // Harvest season: mid-August to end-October, spread by reception index
        $harvestDate = \Carbon\Carbon::create($vintage, 8, 20)
            ->addDays($reception * 15 + mt_rand(0, 10));

        return (int) $harvestDate->diffInDays(now());
    }

    private function receptionNote(string $variety, int $vintage, string $health): string
    {
        $qualityNote = match ($vintage) {
            2023 => 'Vendimia 2023 con condiciones climáticas normales.',
            2024 => 'Excelente campaña 2024. Maduración óptima con veranos secos.',
            2025 => 'Vendimia 2025 en curso. Recepciones de la campaña.',
        };

        $healthNote = $health === 'sano'
            ? 'Estado sanitario perfecto.'
            : 'Presencia leve de botrytis en parcelas bajas. Selección manual aplicada.';

        return "{$variety} — {$qualityNote} {$healthNote}";
    }

    // ── Cleanup ───────────────────────────────────────────────────────────────────

    private function cleanup(): void
    {
        // Harvest receptions (winery, no activity_id)
        $harvestIds = DB::table('harvests')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereNull('activity_id')
            ->pluck('id');

        if ($harvestIds->isNotEmpty()) {
            DB::table('harvest_stocks')->whereIn('harvest_id', $harvestIds)->delete();
            DB::table('harvests')->whereIn('id', $harvestIds)->delete();
        }

        // Batches
        DB::table('grape_reception_batches')
            ->where('winery_id', self::WINERY_USER_ID)
            ->delete();

        // Winery campaigns
        DB::table('campaigns')
            ->where('viticulturist_id', self::WINERY_USER_ID)
            ->delete();

        // Remove winery_viticulturist links only for THIS seeder's 6 demo viticulturists.
        // Do NOT delete all links — WineryViticulturistsSeeder also creates links for winery_id=1.
        $demoVitIdsForLink = DB::table('users')
            ->where('email', 'LIKE', '%'.self::DEMO_EMAIL_SUFFIX)
            ->pluck('id');

        if ($demoVitIdsForLink->isNotEmpty()) {
            DB::table('winery_viticulturist')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('viticulturist_id', $demoVitIdsForLink)
                ->delete();
        }

        // Demo viticulturist infrastructure
        $demoUserIds = DB::table('users')
            ->where('email', 'LIKE', '%'.self::DEMO_EMAIL_SUFFIX)
            ->pluck('id');

        if ($demoUserIds->isNotEmpty()) {
            // winery_viticulturist already cleared above

            // Remove campaigns
            DB::table('campaigns')
                ->whereIn('viticulturist_id', $demoUserIds)
                ->delete();

            // Remove plot_plantings and plots
            $plotIds = DB::table('plots')
                ->whereIn('viticulturist_id', $demoUserIds)
                ->pluck('id');

            if ($plotIds->isNotEmpty()) {
                // Collect geometry IDs before removing the join records
                $geomIds = DB::table('multipart_plot_sigpac')
                    ->whereIn('plot_id', $plotIds)
                    ->pluck('plot_geometry_id');

                DB::table('multipart_plot_sigpac')->whereIn('plot_id', $plotIds)->delete();

                if ($geomIds->isNotEmpty()) {
                    DB::table('plot_geometry')->whereIn('id', $geomIds)->delete();
                }

                DB::table('agricultural_activities')->whereIn('plot_id', $plotIds)->delete();
                DB::table('plot_plantings')->whereIn('plot_id', $plotIds)->delete();
                DB::table('plots')->whereIn('id', $plotIds)->delete();
            }

            // Remove demo users
            DB::table('users')
                ->whereIn('id', $demoUserIds)
                ->delete();
        }
    }
}
