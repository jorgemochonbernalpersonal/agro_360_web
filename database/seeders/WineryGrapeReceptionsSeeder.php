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
    private const WINERY_USER_ID   = 1;
    private const DEMO_EMAIL_SUFFIX = '@viticultor.agro365.demo';

    // ── Demo viticulturists ──────────────────────────────────────────────────────

    private const VITICULTURISTS = [
        [
            'name'      => 'Pedro González Álvarez',
            'email'     => 'pedro.gonzalez@viticultor.agro365.demo',
            'plots'     => [
                ['name' => 'Finca Las Cumbres',   'area' => 2.850, 'varieties' => ['Listán Negro', 'Negramoll']],
                ['name' => 'Parcela El Carrizal', 'area' => 1.320, 'varieties' => ['Vijariego Negro']],
            ],
        ],
        [
            'name'      => 'María Suárez Cabrera',
            'email'     => 'maria.suarez@viticultor.agro365.demo',
            'plots'     => [
                ['name' => 'Viña La Caldera',     'area' => 1.750, 'varieties' => ['Listán Blanco', 'Marmajuelo']],
                ['name' => 'Parcela Montaña Alta','area' => 0.980, 'varieties' => ['Malvasía Volcánica']],
            ],
        ],
        [
            'name'      => 'José Luis Hernández Ramos',
            'email'     => 'joseluis.hernandez@viticultor.agro365.demo',
            'plots'     => [
                ['name' => 'Finca El Rincón',     'area' => 3.200, 'varieties' => ['Listán Negro', 'Listán Blanco']],
                ['name' => 'Viña Las Palomeras',  'area' => 1.600, 'varieties' => ['Negramoll', 'Gual']],
            ],
        ],
        [
            'name'      => 'Carmen Falcón Betancor',
            'email'     => 'carmen.falcon@viticultor.agro365.demo',
            'plots'     => [
                ['name' => 'Parcela Agaete Norte','area' => 1.200, 'varieties' => ['Malvasía Volcánica', 'Gual']],
                ['name' => 'Finca Barranco Hondo','area' => 0.750, 'varieties' => ['Listán Blanco']],
            ],
        ],
        [
            'name'      => 'Antonio Medina Santana',
            'email'     => 'antonio.medina@viticultor.agro365.demo',
            'plots'     => [
                ['name' => 'Viña Los Berrazales',  'area' => 2.100, 'varieties' => ['Listán Negro', 'Malvasía Volcánica']],
                ['name' => 'Parcela La Aldea Alta', 'area' => 1.450, 'varieties' => ['Negramoll']],
            ],
        ],
        [
            'name'      => 'Laura Déniz Rodríguez',
            'email'     => 'laura.deniz@viticultor.agro365.demo',
            'plots'     => [
                ['name' => 'Finca Tamadaba',      'area' => 1.900, 'varieties' => ['Vijariego Negro', 'Marmajuelo']],
                ['name' => 'Parcela Cruz de Tejeda','area' => 1.050, 'varieties' => ['Gual']],
            ],
        ],
    ];

    // Parámetros base por variedad
    private const VARIETY_PARAMS = [
        'Listán Negro'       => ['color' => 'red',   'code' => 'LN',  'baume' => 13.0, 'brix' => 23.5, 'ph' => 3.42, 'acid' => 5.9, 'price' => 0.90],
        'Negramoll'          => ['color' => 'red',   'code' => 'NGM', 'baume' => 13.3, 'brix' => 24.1, 'ph' => 3.48, 'acid' => 5.6, 'price' => 0.95],
        'Vijariego Negro'    => ['color' => 'red',   'code' => 'VJN', 'baume' => 13.8, 'brix' => 25.0, 'ph' => 3.52, 'acid' => 5.3, 'price' => 1.20],
        'Listán Blanco'      => ['color' => 'white', 'code' => 'LB',  'baume' => 12.5, 'brix' => 22.5, 'ph' => 3.38, 'acid' => 6.2, 'price' => 0.88],
        'Malvasía Volcánica' => ['color' => 'white', 'code' => 'MV',  'baume' => 12.8, 'brix' => 23.2, 'ph' => 3.40, 'acid' => 6.0, 'price' => 1.35],
        'Marmajuelo'         => ['color' => 'white', 'code' => 'MRM', 'baume' => 12.0, 'brix' => 21.8, 'ph' => 3.32, 'acid' => 6.8, 'price' => 0.92],
        'Gual'               => ['color' => 'white', 'code' => 'GUL', 'baume' => 13.0, 'brix' => 23.5, 'ph' => 3.42, 'acid' => 6.1, 'price' => 1.10],
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

        if (!$province) {
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

        if (!$municipality) {
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
        $containers   = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        $totalHarvests = 0;
        $totalBatches  = 0;
        $cidx = 0;

        foreach ($viticulturistIds as $vitData) {
            $vitId      = $vitData['user_id'];
            $plantings  = $vitData['plantings'];  // [{id, variety_name, variety_params, area}]

            foreach ([2023, 2024, 2025] as $vintage) {
                $campaignId = $wineryCampaigns[$vintage] ?? null;
                if (!$campaignId) {
                    continue;
                }

                foreach ($plantings as $planting) {
                    // One batch per (winery + plot_planting + campaign) — unique constraint
                    $batchId = DB::table('grape_reception_batches')->insertGetId([
                        'winery_id'          => self::WINERY_USER_ID,
                        'viticulturist_id'   => $vitId,
                        'plot_planting_id'   => $planting['id'],
                        'campaign_id'        => $campaignId,
                        'vintage_year'       => $vintage,
                        'total_weight_kg'    => 0,
                        'status'             => $vintage < 2025 ? 'invoiced' : 'open',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                    $totalBatches++;

                    // Generate 2-3 harvest entries per batch
                    $numReceptions = $this->receptionsForVintage($vintage);

                    for ($r = 0; $r < $numReceptions; $r++) {
                        $params  = $planting['variety_params'];
                        $area    = (float) $planting['area'];
                        $weight  = $this->harvestWeight($area, $vintage, $r);
                        $quality = $this->qualityParams($params, $vintage, $cidx);

                        $pricePerKg = round($params['price'] + ($cidx % 3) * 0.02, 4);
                        $totalValue = round($weight * $pricePerKg, 3);
                        $daysAgo    = $this->harvestDaysAgo($vintage, $r);

                        $containerId = !empty($containers) ? $containers[$cidx % count($containers)] : null;

                        DB::table('harvests')->insertGetId([
                            'winery_id'              => self::WINERY_USER_ID,
                            'batch_id'               => $batchId,
                            'plot_planting_id'       => $planting['id'],
                            'container_id'           => $containerId,
                            'harvest_start_date'     => now()->subDays($daysAgo)->toDateString(),
                            'total_weight'           => $weight,
                            'vintage'                => $vintage,
                            'yield_per_hectare'      => $area > 0 ? round($weight / $area, 3) : null,
                            'baume_degree'           => $quality['baume'],
                            'brix_degree'            => $quality['brix'],
                            'ph_level'               => $quality['ph'],
                            'acidity_level'          => $quality['acid'],
                            'potential_alcohol'      => $quality['alcohol'],
                            'color_rating'           => $quality['color_rating'],
                            'aroma_rating'           => $quality['aroma_rating'],
                            'health_status'          => $quality['health'],
                            'destination_type'       => 'winery',
                            'destination'            => 'Bodega Agaete',
                            'price_per_kg'           => $pricePerKg,
                            'total_value'            => $totalValue,
                            'harvest_ticket_number'  => 'REC-' . $vintage . '-' . str_pad($cidx + 1, 4, '0', STR_PAD_LEFT),
                            'sanitary_state_grapes'  => $quality['sanitary_grapes'],
                            'sanitary_state_botrytis'=> $quality['botrytis'],
                            'sanitary_state_oidium'  => $quality['oidium'],
                            'status'                 => 'active',
                            'disqualified'           => false,
                            'notes'                  => $this->receptionNote($planting['variety_name'], $vintage, $quality['health']),
                            'created_at'             => $now,
                            'updated_at'             => $now,
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

        $this->command->info("✅ Recepciones de uva: {$totalHarvests} recepciones en {$totalBatches} lotes (6 viticultores, vendimias 2023-2025)");
    }

    // ── Setup helpers ─────────────────────────────────────────────────────────────

    private function ensureGrapeVarieties(\Carbon\Carbon $now): array
    {
        $ids = [];
        foreach (self::VARIETY_PARAMS as $name => $params) {
            $id = DB::table('grape_varieties')->where('name', $name)->value('id');
            if (!$id) {
                $id = DB::table('grape_varieties')->insertGetId([
                    'name'        => $name,
                    'code'        => $params['code'],
                    'color'       => $params['color'],
                    'description' => "Variedad autóctona canaria — {$name}",
                    'active'      => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
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
            if (!$campId) {
                $campId = DB::table('campaigns')->insertGetId([
                    'name'              => "Vendimia {$year}",
                    'year'              => $year,
                    'viticulturist_id'  => self::WINERY_USER_ID,
                    'start_date'        => "{$year}-08-01",
                    'end_date'          => "{$year}-11-30",
                    'active'            => $active,
                    'created_at'        => $now,
                    'updated_at'        => $now,
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
        $result = [];

        foreach (self::VITICULTURISTS as $vitDef) {
            // Find or create user
            $userId = DB::table('users')
                ->where('email', $vitDef['email'])
                ->value('id');

            if (!$userId) {
                $userId = DB::table('users')->insertGetId([
                    'name'              => $vitDef['name'],
                    'email'             => $vitDef['email'],
                    'email_verified_at' => $now,
                    'password'          => Hash::make('agro365-demo'),
                    'role'              => 'viticulturist',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }

            // Link to winery if not already linked
            $linked = DB::table('winery_viticulturist')
                ->where('winery_id', self::WINERY_USER_ID)
                ->where('viticulturist_id', $userId)
                ->exists();

            if (!$linked) {
                DB::table('winery_viticulturist')->insert([
                    'winery_id'          => self::WINERY_USER_ID,
                    'viticulturist_id'   => $userId,
                    'assigned_by'        => self::WINERY_USER_ID,
                    'source'             => 'own',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }

            // Campaigns per viticulturist: 2023 (inactive), 2024 (inactive), 2025 (active)
            $campaigns = [];
            foreach ([2023 => false, 2024 => false, 2025 => true] as $year => $active) {
                $campId = DB::table('campaigns')
                    ->where('viticulturist_id', $userId)
                    ->where('year', $year)
                    ->value('id');
                if (!$campId) {
                    $campId = DB::table('campaigns')->insertGetId([
                        'name'              => "Campaña {$year}",
                        'year'              => $year,
                        'viticulturist_id'  => $userId,
                        'start_date'        => "{$year}-08-01",
                        'end_date'          => "{$year}-11-30",
                        'active'            => $active,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ]);
                }
                $campaigns[$year] = $campId;
            }

            // Plots and plantings
            $plantings = [];
            foreach ($vitDef['plots'] as $plotDef) {
                $plotId = DB::table('plots')
                    ->where('viticulturist_id', $userId)
                    ->where('name', $plotDef['name'])
                    ->value('id');

                if (!$plotId) {
                    $plotId = DB::table('plots')->insertGetId([
                        'name'                   => $plotDef['name'],
                        'viticulturist_id'        => $userId,
                        'area'                    => $plotDef['area'],
                        'active'                  => true,
                        'autonomous_community_id' => $community->id,
                        'province_id'             => $province->id,
                        'municipality_id'         => $municipality->id,
                        'created_at'              => $now,
                        'updated_at'              => $now,
                    ]);
                }

                $areaPerVariety = round($plotDef['area'] / count($plotDef['varieties']), 3);

                foreach ($plotDef['varieties'] as $varietyName) {
                    $varietyId = $varietyIds[$varietyName] ?? null;
                    if (!$varietyId) {
                        continue;
                    }

                    $plantingId = DB::table('plot_plantings')
                        ->where('plot_id', $plotId)
                        ->where('grape_variety_id', $varietyId)
                        ->value('id');

                    if (!$plantingId) {
                        $plantingYear = mt_rand(2005, 2018);
                        // harvest_limit_kg = area × rendimiento regulado DO (~7000-8500 kg/ha)
                        $harvestLimitKg = round($areaPerVariety * mt_rand(7000, 8500));

                        $plantingId = DB::table('plot_plantings')->insertGetId([
                            'plot_id'           => $plotId,
                            'grape_variety_id'  => $varietyId,
                            'area_planted'      => $areaPerVariety,
                            'planting_year'     => $plantingYear,
                            'harvest_limit_kg'  => $harvestLimitKg,
                            'status'            => 'active',
                            'irrigated'         => false,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]);
                    }

                    $plantings[] = [
                        'id'            => $plantingId,
                        'variety_name'  => $varietyName,
                        'variety_params'=> self::VARIETY_PARAMS[$varietyName],
                        'area'          => $areaPerVariety,
                    ];
                }
            }

            $result[] = [
                'user_id'   => $userId,
                'name'      => $vitDef['name'],
                'plantings' => $plantings,
                'campaigns' => $campaigns,
            ];
        }

        return $result;
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
            2024 =>  0.2,
            2025 =>  0.0,
        };

        $variation = (($idx % 7) - 3) * 0.05; // ±0.15 variation

        $baume   = round(max(10.0, $params['baume'] + $vintageOffset + $variation), 2);
        $brix    = round(max(18.0, $params['brix']  + $vintageOffset * 2 + $variation * 2), 2);
        $ph      = round(max(2.9, $params['ph']     + $variation * 0.1), 2);
        $acid    = round(max(3.5, $params['acid']   - $variation * 0.3), 2);
        $alcohol = round($baume * 1.035, 2);

        // Ratings based on baume
        $colorRating = $baume >= 13.5 ? 'excelente' : ($baume >= 12.5 ? 'bueno' : 'aceptable');
        $aromaRating = $brix  >= 24.0 ? 'excelente' : ($brix  >= 22.0 ? 'bueno' : 'aceptable');

        // Health status: mostly sano, occasional daño_leve
        $health  = ($idx % 9 === 0) ? 'daño_leve' : 'sano';

        // Sanitary state (0-100% affected)
        $botrytis = $health === 'sano' ? round(mt_rand(0, 3) / 10, 1) : round(mt_rand(5, 15) / 10, 1);
        $oidium   = round(mt_rand(0, 2) / 10, 1);

        return [
            'baume'          => $baume,
            'brix'           => $brix,
            'ph'             => $ph,
            'acid'           => $acid,
            'alcohol'        => $alcohol,
            'color_rating'   => $colorRating,
            'aroma_rating'   => $aromaRating,
            'health'         => $health,
            'sanitary_grapes'=> $health === 'sano' ? 100.0 : round(mt_rand(82, 95) / 1.0, 1),
            'botrytis'       => $botrytis,
            'oidium'         => $oidium,
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

        // Demo viticulturist infrastructure
        $demoUserIds = DB::table('users')
            ->where('email', 'LIKE', '%' . self::DEMO_EMAIL_SUFFIX)
            ->pluck('id');

        if ($demoUserIds->isNotEmpty()) {
            // Remove winery links
            DB::table('winery_viticulturist')
                ->whereIn('viticulturist_id', $demoUserIds)
                ->where('winery_id', self::WINERY_USER_ID)
                ->delete();

            // Remove campaigns
            DB::table('campaigns')
                ->whereIn('viticulturist_id', $demoUserIds)
                ->delete();

            // Remove plot_plantings and plots
            $plotIds = DB::table('plots')
                ->whereIn('viticulturist_id', $demoUserIds)
                ->pluck('id');

            if ($plotIds->isNotEmpty()) {
                DB::table('plot_plantings')
                    ->whereIn('plot_id', $plotIds)
                    ->delete();
                DB::table('plots')
                    ->whereIn('id', $plotIds)
                    ->delete();
            }

            // Remove demo users
            DB::table('users')
                ->whereIn('id', $demoUserIds)
                ->delete();
        }
    }
}
