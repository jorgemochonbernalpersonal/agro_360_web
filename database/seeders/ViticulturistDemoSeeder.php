<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder demo completo para el rol Viticultor (user_id = 2).
 *
 * Crea datos coherentes y realistas para Gran Canaria (Agaete):
 *   · 5 parcelas con 2 plantaciones cada una
 *   · Campañas 2024 (cerrada) y 2025 (activa)
 *   · ~65 actividades agrícolas con sus sub-tablas
 *   · Observaciones fenológicas
 *
 * Requiere que user_id=1 (winery) y user_id=2 (viticultor) existan.
 *
 * Uso:
 *   php artisan db:seed --class=ViticulturistDemoSeeder
 */
class ViticulturistDemoSeeder extends Seeder
{
    private const VIT_USER_ID    = 2;
    private const WINERY_USER_ID = 1;

    // ── Geografía Agaete, Gran Canaria ────────────────────────────────────────
    private const AC_ID          = 5;     // Canarias
    private const PROVINCE_ID    = 14;    // Las Palmas
    private const MUNICIPALITY_ID = 5243; // Agaete

    public function run(): void
    {
        $now = now();

        $this->command->info('');
        $this->command->info('🌿 ═══════════════════════════════════════════════');
        $this->command->info('🌿  VITICULTURIST DEMO SEEDER — user_id = 2');
        $this->command->info('🌿  Viticultor Agaete · Gran Canaria');
        $this->command->info('🌿 ═══════════════════════════════════════════════');
        $this->command->info('');

        // ── 0. Verificar usuarios ─────────────────────────────────────────────
        $vit    = DB::table('users')->find(self::VIT_USER_ID);
        $winery = DB::table('users')->find(self::WINERY_USER_ID);

        if (! $vit || ! $winery) {
            $this->command->error('❌ Faltan los usuarios id=1 (winery) o id=2 (viticultor). Créalos primero.');
            return;
        }
        $this->command->info("✅ Viticultor: {$vit->email}");
        $this->command->info("✅ Bodega:     {$winery->email}");

        // ── 1. Limpiar datos anteriores (idempotente) ─────────────────────────
        $this->step('Limpieza previa', fn() => $this->cleanup());

        // ── 2. Productos fitosanitarios ───────────────────────────────────────
        $productIds = [];
        $this->step('Productos fitosanitarios (5)', function () use ($now, &$productIds) {
            $productIds = $this->createProducts($now);
        });

        // ── 3. Vincular viticultor ↔ bodega ────────────────────────────────────
        $wvId = 0;
        $this->step('Vínculo bodega–viticultor', function () use ($now, &$wvId) {
            $wvId = $this->linkToWinery($now);
        });

        // ── 4. Parcelas ────────────────────────────────────────────────────────
        $plotIds = [];
        $this->step('Parcelas (5)', function () use ($now, &$plotIds) {
            $plotIds = $this->createPlots($now);
        });

        // ── 5. Plantaciones ────────────────────────────────────────────────────
        $plantingIds = [];
        $this->step('Plantaciones (10)', function () use ($now, $plotIds, &$plantingIds) {
            $plantingIds = $this->createPlantings($plotIds, $now);
        });

        // ── 6. Campañas ────────────────────────────────────────────────────────
        $campaign2024Id = 0;
        $campaign2025Id = 0;
        $this->step('Campañas (2024 cerrada + 2025 activa)', function () use ($now, $wvId, &$campaign2024Id, &$campaign2025Id) {
            [$campaign2024Id, $campaign2025Id] = $this->createCampaigns($wvId, $now);
        });

        // ── 7. Actividades de campo ────────────────────────────────────────────
        $this->step('Actividades 2024 (~42)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2024Id) {
            $this->createActivities2024($plotIds, $plantingIds, $productIds, $wvId, $campaign2024Id, $now);
        });

        $this->step('Actividades 2025 (~23)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id) {
            $this->createActivities2025($plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id, $now);
        });

        // ── 8. Fenología ───────────────────────────────────────────────────────
        $this->step('Observaciones fenológicas (14)', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id) {
            $this->createPhenology($plantingIds, $campaign2024Id, $campaign2025Id, $now);
        });

        // ── 9. Plagas y enfermedades ──────────────────────────────────────────
        $pestIds = [];
        $this->step('Plagas y enfermedades (8)', function () use ($now, $productIds, &$pestIds) {
            $pestIds = $this->createPests($productIds, $now);
        });

        // ── 10. Rendimientos estimados ────────────────────────────────────────
        $this->step('Rendimientos estimados (14)', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id) {
            $this->createEstimatedYields($plantingIds, $campaign2024Id, $campaign2025Id, $now);
        });

        $this->command->info('');
        $this->command->info('✅ ViticulturistDemoSeeder completado.');
        $this->command->info('   Viticultor: viticultor@agaete.es / Password1234!');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function step(string $label, callable $fn): void
    {
        $this->command->info("  ▸ {$label}...");
        $fn();
        $this->command->info("    ✓");
    }

    // ─── 1. Cleanup ───────────────────────────────────────────────────────────

    private function cleanup(): void
    {
        $vitId = self::VIT_USER_ID;

        // Actividades y sus sub-tablas (cascada por FK)
        $activityIds = DB::table('agricultural_activities')
            ->where('viticulturist_id', $vitId)
            ->pluck('id');

        if ($activityIds->isNotEmpty()) {
            DB::table('phytosanitary_treatments')->whereIn('activity_id', $activityIds)->delete();
            DB::table('fertilizations')->whereIn('activity_id', $activityIds)->delete();
            DB::table('irrigations')->whereIn('activity_id', $activityIds)->delete();
            DB::table('cultural_works')->whereIn('activity_id', $activityIds)->delete();
            DB::table('observations')->whereIn('activity_id', $activityIds)->delete();
            DB::table('post_harvest_treatments')->whereIn('activity_id', $activityIds)->delete();

            $harvestIds = DB::table('harvests')->whereIn('activity_id', $activityIds)->pluck('id');
            if ($harvestIds->isNotEmpty()) {
                DB::table('marketed_harvests')->whereIn('harvest_id', $harvestIds)->delete();
            }
            DB::table('harvests')->whereIn('activity_id', $activityIds)->delete();
            DB::table('agricultural_activities')->whereIn('id', $activityIds)->delete();
        }

        // Fenología
        DB::table('phenology_observations')->where('viticulturist_id', $vitId)->delete();

        // Campañas
        DB::table('campaigns')->where('viticulturist_id', $vitId)->delete();

        // Parcelas y plantaciones (cascada por FK)
        $plotIds = DB::table('plots')->where('viticulturist_id', $vitId)->pluck('id');
        if ($plotIds->isNotEmpty()) {
            DB::table('plot_plantings')->whereIn('plot_id', $plotIds)->delete();
            DB::table('plots')->whereIn('id', $plotIds)->delete();
        }

        // Vínculo bodega–viticultor
        DB::table('winery_viticulturist')
            ->where('viticulturist_id', $vitId)
            ->where('winery_id', self::WINERY_USER_ID)
            ->delete();

        // Rendimientos estimados (vinculados a plantaciones del viticultor)
        $ownPlotIds = DB::table('plots')->where('viticulturist_id', $vitId)->pluck('id');
        if ($ownPlotIds->isNotEmpty()) {
            $ownPlantingIds = DB::table('plot_plantings')->whereIn('plot_id', $ownPlotIds)->pluck('id');
            if ($ownPlantingIds->isNotEmpty()) {
                DB::table('estimated_yields')->whereIn('plot_planting_id', $ownPlantingIds)->delete();
            }
        }

        // Plagas (globales — solo borramos las que no tengan treatments ni observations vinculadas fuera)
        // Las plagas son catálogo global, solo borramos las creadas por este seeder por nombre
        $seederPestNames = [
            'Polilla del racimo', 'Mildiu de la vid', 'Oídio de la vid',
            'Araña roja', 'Trips de la vid', 'Botrytis / Podredumbre gris',
            'Excoriosis', 'Eutipiosis',
        ];
        $pestIds = DB::table('pests')->whereIn('name', $seederPestNames)->pluck('id');
        if ($pestIds->isNotEmpty()) {
            DB::table('pest_product_effectiveness')->whereIn('pest_id', $pestIds)->delete();
            DB::table('pests')->whereIn('id', $pestIds)->delete();
        }

        // Productos fitosanitarios propios
        DB::table('phytosanitary_products')
            ->where('user_id', $vitId)
            ->delete();
    }

    // ─── 2. Productos fitosanitarios ──────────────────────────────────────────

    private function createProducts($now): array
    {
        $products = [
            [
                'name'                => 'Folio Gold WG',
                'active_ingredient'   => 'Mancozeb 37,5% + Fosetil-Al 25%',
                'registration_number' => 'ES-00421-01',
                'manufacturer'        => 'Syngenta',
                'type'                => 'fungicida',
                'toxicity_class'      => 'IV',
                'withdrawal_period_days' => 28,
                'safety_interval_days' =>28,
                'active'              => true,
                'user_id'             => self::VIT_USER_ID,
                'description'         => 'Fungicida sistémico-de contacto para mildiu de la vid',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'name'                => 'Talendo',
                'active_ingredient'   => 'Proquinazid 20% EC',
                'registration_number' => 'ES-00589-02',
                'manufacturer'        => 'Corteva Agriscience',
                'type'                => 'fungicida',
                'toxicity_class'      => 'IV',
                'withdrawal_period_days' => 21,
                'safety_interval_days' =>21,
                'active'              => true,
                'user_id'             => self::VIT_USER_ID,
                'description'         => 'Fungicida específico para el control del oídio',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'name'                => 'Cabrio Top',
                'active_ingredient'   => 'Metiram 55% + Piraclostrobina 5% WG',
                'registration_number' => 'ES-00234-03',
                'manufacturer'        => 'BASF',
                'type'                => 'fungicida',
                'toxicity_class'      => 'III',
                'withdrawal_period_days' => 35,
                'safety_interval_days' =>35,
                'active'              => true,
                'user_id'             => self::VIT_USER_ID,
                'description'         => 'Fungicida combinado para mildiu y botritis',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'name'                => 'Decis Protech',
                'active_ingredient'   => 'Deltametrina 100 g/L EW',
                'registration_number' => 'ES-00778-04',
                'manufacturer'        => 'Bayer CropScience',
                'type'                => 'insecticida',
                'toxicity_class'      => 'III',
                'withdrawal_period_days' => 14,
                'safety_interval_days' =>14,
                'active'              => true,
                'user_id'             => self::VIT_USER_ID,
                'description'         => 'Insecticida piretroide para polilla del racimo y trips',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'name'                => 'Cobre Nordox 75 WG',
                'active_ingredient'   => 'Oxicloruro de cobre 75% WG',
                'registration_number' => 'ES-00112-05',
                'manufacturer'        => 'Nordox AS',
                'type'                => 'fungicida',
                'toxicity_class'      => 'IV',
                'withdrawal_period_days' => 14,
                'safety_interval_days' =>14,
                'active'              => true,
                'user_id'             => self::VIT_USER_ID,
                'description'         => 'Fungicida de cobre para tratamientos preventivos y post-vendimia',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        $ids = [];
        foreach ($products as $p) {
            $ids[] = DB::table('phytosanitary_products')->insertGetId($p);
        }
        return $ids; // [mildiu, oidio, mixto, insecticida, cobre]
    }

    // ─── 3. Vínculo bodega–viticultor ─────────────────────────────────────────

    private function linkToWinery($now): int
    {
        return DB::table('winery_viticulturist')->insertGetId([
            'winery_id'        => self::WINERY_USER_ID,
            'viticulturist_id' => self::VIT_USER_ID,
            'source'           => 'own',
            'cuaderno_access'  => true,
            'cuaderno_granted_at' => $now,
            'assigned_by'      => self::WINERY_USER_ID,
            'notes'            => 'Viticultor principal. Explotación familiar Agaete.',
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    // ─── 4. Parcelas ──────────────────────────────────────────────────────────

    private function createPlots($now): array
    {
        $base = [
            'viticulturist_id'       => self::VIT_USER_ID,
            'autonomous_community_id' => self::AC_ID,
            'province_id'            => self::PROVINCE_ID,
            'municipality_id'        => self::MUNICIPALITY_ID,
            'active'                 => true,
            'is_locked'              => false,
            'alert_email_enabled'    => false,
            'created_at'             => $now,
            'updated_at'             => $now,
        ];

        // training_system_id: 1=Vaso, 2=Espaldera simple
        $plots = [
            [
                'name'               => 'Viña La Montañeta',
                'area'               => 0.850,
                'is_organic'         => false,
                'training_system_id' => 1, // Vaso
                'plantation_year'    => 1998,
                'description'        => 'Parcela en ladera con suelo volcánico. Orientación N-NE.',
            ],
            [
                'name'               => 'Finca Los Llanos',
                'area'               => 1.200,
                'is_organic'         => false,
                'training_system_id' => 2, // Espaldera
                'plantation_year'    => 2005,
                'description'        => 'Finca en zona llana con riego por goteo. Alta productividad.',
            ],
            [
                'name'               => 'Parcela El Risco',
                'area'               => 0.650,
                'is_organic'         => false,
                'training_system_id' => 1, // Vaso
                'plantation_year'    => 1992,
                'description'        => 'Viña de alta montaña en terreno volcánico escarpado.',
            ],
            [
                'name'               => 'Pago La Umbría',
                'area'               => 0.920,
                'is_organic'         => false,
                'training_system_id' => 2, // Espaldera
                'plantation_year'    => 2010,
                'description'        => 'Parcela orientada al sur. Suelo arenoso. Moscatel aromático.',
            ],
            [
                'name'               => 'Viñedo Las Pozas',
                'area'               => 1.100,
                'is_organic'         => true,
                'training_system_id' => 1, // Vaso
                'plantation_year'    => 2001,
                'description'        => 'Producción ecológica certificada. Sin fitosanitarios de síntesis.',
            ],
        ];

        $ids = [];
        foreach ($plots as $plot) {
            $ids[] = DB::table('plots')->insertGetId(array_merge($base, $plot));
        }
        return $ids;
    }

    // ─── 5. Plantaciones ──────────────────────────────────────────────────────

    private function createPlantings(array $plotIds, $now): array
    {
        // grape_variety_id: query por nombre para ser robusto
        $varieties = DB::table('grape_varieties')
            ->whereIn('name', ['Listán Negro', 'Listán Blanco', 'Negramoll', 'Moscatel de Alejandría', 'Tintilla'])
            ->pluck('id', 'name');

        $listanNegro  = $varieties['Listán Negro']  ?? $varieties->first() ?? 1;
        $listanBlanco = $varieties['Listán Blanco'] ?? $listanNegro;
        $negramoll    = $varieties['Negramoll']      ?? $listanNegro;
        $moscatel     = $varieties['Moscatel de Alejandría'] ?? $listanBlanco;
        $tintilla     = $varieties['Tintilla']       ?? $listanNegro;

        $base = [
            'status'     => 'active',
            'active'     => true,
            'irrigated'  => false,
            'right_type' => 'replantacion',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 2 plantaciones por parcela = 10 total
        $plantings = [
            // Parcela 0 — Viña La Montañeta
            [
                'plot_id'              => $plotIds[0],
                'grape_variety_id'     => $listanNegro,
                'area_planted'         => 0.600,
                'planting_year'        => 1998,
                'vine_count'           => 480,
                'row_spacing'          => 2.5,
                'vine_spacing'         => 1.5,
                'rootstock'            => '110R',
                'training_system_id'   => 1,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Listán Negro tradicional. Producción tinto joven.',
            ],
            [
                'plot_id'              => $plotIds[0],
                'grape_variety_id'     => $negramoll,
                'area_planted'         => 0.250,
                'planting_year'        => 2002,
                'vine_count'           => 200,
                'row_spacing'          => 2.5,
                'vine_spacing'         => 1.5,
                'rootstock'            => '110R',
                'training_system_id'   => 1,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Negramoll para mezcla y blending.',
            ],
            // Parcela 1 — Finca Los Llanos
            [
                'plot_id'              => $plotIds[1],
                'grape_variety_id'     => $listanBlanco,
                'area_planted'         => 0.800,
                'planting_year'        => 2005,
                'vine_count'           => 800,
                'row_spacing'          => 2.0,
                'vine_spacing'         => 1.5,
                'rootstock'            => 'SO4',
                'training_system_id'   => 2,
                'irrigated'            => true,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Listán Blanco para vino blanco fresco.',
            ],
            [
                'plot_id'              => $plotIds[1],
                'grape_variety_id'     => $listanNegro,
                'area_planted'         => 0.400,
                'planting_year'        => 2007,
                'vine_count'           => 400,
                'row_spacing'          => 2.0,
                'vine_spacing'         => 1.5,
                'rootstock'            => 'SO4',
                'training_system_id'   => 2,
                'irrigated'            => true,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Listán Negro complementario.',
            ],
            // Parcela 2 — El Risco
            [
                'plot_id'              => $plotIds[2],
                'grape_variety_id'     => $negramoll,
                'area_planted'         => 0.400,
                'planting_year'        => 1992,
                'vine_count'           => 320,
                'row_spacing'          => 3.0,
                'vine_spacing'         => 1.5,
                'rootstock'            => 'Propio (pie franco)',
                'training_system_id'   => 1,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Negramoll centenario. Producción baja y concentrada.',
            ],
            [
                'plot_id'              => $plotIds[2],
                'grape_variety_id'     => $listanNegro,
                'area_planted'         => 0.250,
                'planting_year'        => 1998,
                'vine_count'           => 200,
                'row_spacing'          => 3.0,
                'vine_spacing'         => 1.5,
                'rootstock'            => 'Propio (pie franco)',
                'training_system_id'   => 1,
                'designation_of_origin' => 'DO Gran Canaria',
            ],
            // Parcela 3 — Pago La Umbría
            [
                'plot_id'              => $plotIds[3],
                'grape_variety_id'     => $moscatel,
                'area_planted'         => 0.600,
                'planting_year'        => 2010,
                'vine_count'           => 600,
                'row_spacing'          => 2.0,
                'vine_spacing'         => 1.5,
                'rootstock'            => '41B',
                'training_system_id'   => 2,
                'irrigated'            => true,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Moscatel para vino dulce y naturalmente dulce.',
            ],
            [
                'plot_id'              => $plotIds[3],
                'grape_variety_id'     => $listanBlanco,
                'area_planted'         => 0.320,
                'planting_year'        => 2012,
                'vine_count'           => 320,
                'row_spacing'          => 2.0,
                'vine_spacing'         => 1.5,
                'rootstock'            => '41B',
                'training_system_id'   => 2,
                'irrigated'            => true,
                'designation_of_origin' => 'DO Gran Canaria',
            ],
            // Parcela 4 — Viñedo Las Pozas (ecológico)
            [
                'plot_id'              => $plotIds[4],
                'grape_variety_id'     => $listanNegro,
                'area_planted'         => 0.750,
                'planting_year'        => 2001,
                'vine_count'           => 600,
                'row_spacing'          => 2.5,
                'vine_spacing'         => 2.0,
                'rootstock'            => '110R',
                'training_system_id'   => 1,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Producción ecológica. Sin herbicidas ni síntesis.',
            ],
            [
                'plot_id'              => $plotIds[4],
                'grape_variety_id'     => $tintilla,
                'area_planted'         => 0.350,
                'planting_year'        => 2004,
                'vine_count'           => 280,
                'row_spacing'          => 2.5,
                'vine_spacing'         => 2.0,
                'rootstock'            => '110R',
                'training_system_id'   => 1,
                'designation_of_origin' => 'DO Gran Canaria',
                'notes'                => 'Tintilla para rosado y crianza.',
            ],
        ];

        $ids = [];
        foreach ($plantings as $p) {
            $ids[] = DB::table('plot_plantings')->insertGetId(array_merge($base, $p));
        }
        return $ids;
    }

    // ─── 6. Campañas ──────────────────────────────────────────────────────────

    private function createCampaigns(int $wvId, $now): array
    {
        $base = [
            'viticulturist_id'         => self::VIT_USER_ID,
            'winery_viticulturist_id'  => $wvId,
            'mid_validation_signed'    => false,
            'final_validation_signed'  => false,
            'created_at'               => $now,
            'updated_at'               => $now,
        ];

        $id2024 = DB::table('campaigns')->insertGetId(array_merge($base, [
            'name'       => 'Campaña 2024',
            'year'       => 2024,
            'start_date' => '2024-01-01',
            'end_date'   => '2024-12-31',
            'active'     => false,
            'description' => 'Campaña 2024 cerrada. Vendimia: 4.820 kg cosechados.',
            'final_validation_signed' => true,
            'final_validation_date'   => '2024-11-30 10:00:00',
            'final_validation_user_id' => self::VIT_USER_ID,
        ]));

        $id2025 = DB::table('campaigns')->insertGetId(array_merge($base, [
            'name'       => 'Campaña 2025',
            'year'       => 2025,
            'start_date' => '2025-01-01',
            'end_date'   => '2025-12-31',
            'active'     => true,
            'description' => 'Campaña activa. Evolución favorable del viñedo.',
        ]));

        return [$id2024, $id2025];
    }

    // ─── 7a. Actividades 2024 ─────────────────────────────────────────────────

    private function createActivities2024(
        array $plotIds, array $plantingIds, array $productIds,
        int $wvId, int $campaignId, $now
    ): void {
        [$mildiuId, $oidioId, $mixtoId, $insectId, $cobreId] = $productIds;

        // Helper: insertar actividad y devolver su ID
        $act = fn(array $data) => DB::table('agricultural_activities')->insertGetId(array_merge([
            'viticulturist_id'        => self::VIT_USER_ID,
            'winery_viticulturist_id' => $wvId,
            'campaign_id'             => $campaignId,
            'is_locked'               => false,
            'created_at'              => $now,
            'updated_at'              => $now,
        ], $data));

        // ── Podas (Enero 2024) ─────────────────────────────────────────────────
        $pruningDates = ['2024-01-08', '2024-01-10', '2024-01-15', '2024-01-17', '2024-01-22'];
        $pruningTypes = ['guyot', 'guyot', 'vaso', 'guyot', 'vaso'];

        foreach ($plotIds as $i => $plotId) {
            $aId = $act([
                'plot_id'             => $plotId,
                'activity_type'       => 'pruning',
                'phenological_stage'  => 'reposo_invernal',
                'activity_date'       => $pruningDates[$i],
                'weather_conditions'  => 'soleado',
                'temperature'         => 15.0 + $i,
                'notes'               => 'Poda de formación/producción. Intensidad media.',
            ]);
            DB::table('cultural_works')->insert([
                'activity_id'               => $aId,
                'work_type'                 => 'poda',
                'pruning_type'              => $pruningTypes[$i],
                'productive_buds_per_hectare' => 50000 + ($i * 2000),
                'hours_worked'              => 6.0 + $i,
                'workers_count'             => 2,
                'description'               => 'Poda invernal con criterio productivo y sanitario.',
                'created_at'                => $now,
                'updated_at'                => $now,
            ]);
        }

        // ── Tratamientos fitosanitarios preventivos (Feb-Sep 2024) ────────────
        $treatments = [
            // [plot_idx, date, product_idx(0-based), pest, method, stage, temp, dose]
            [0, '2024-02-20', 4, 'mildiu',             'pulverización',    'lloro',               14.0, 2.5],
            [1, '2024-02-22', 4, 'mildiu',             'pulverización',    'lloro',               15.0, 2.5],
            [0, '2024-03-18', 0, 'mildiu',             'pulverización',    'brotacion',           18.0, 3.0],
            [2, '2024-03-20', 0, 'mildiu',             'pulverización',    'brotacion',           17.5, 3.0],
            [1, '2024-04-10', 1, 'oídio',              'aplicación foliar','desarrollo_vegetativo',20.0, 0.4],
            [3, '2024-04-12', 1, 'oídio',              'aplicación foliar','desarrollo_vegetativo',21.0, 0.4],
            [0, '2024-05-08', 2, 'mildiu y oídio',     'pulverización',    'floracion',           22.0, 2.0],
            [1, '2024-05-10', 2, 'mildiu y oídio',     'pulverización',    'floracion',           23.0, 2.0],
            [4, '2024-06-15', 4, 'mildiu',             'pulverización',    'cuajado',             24.0, 2.0],
            [0, '2024-07-12', 3, 'polilla del racimo', 'pulverización',    'envero',              27.0, 0.2],
        ];

        foreach ($treatments as [$pIdx, $date, $prodIdx, $pest, $method, $stage, $temp, $dose]) {
            $aId = $act([
                'plot_id'             => $plotIds[$pIdx],
                'plot_planting_id'    => $plantingIds[$pIdx * 2],
                'activity_type'       => 'phytosanitary',
                'phenological_stage'  => $stage,
                'activity_date'       => $date,
                'weather_conditions'  => 'soleado',
                'temperature'         => $temp,
                'notes'               => "Tratamiento preventivo contra {$pest}.",
            ]);
            DB::table('phytosanitary_treatments')->insert([
                'activity_id'        => $aId,
                'product_id'         => $productIds[$prodIdx],
                'dose_per_hectare'   => $dose,
                'area_treated'       => round($plotIds[$pIdx] * 0.001 + 0.5, 3),
                'application_method' => $method,
                'target_pest'        => $pest,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Fertilizaciones (Abr, Ago, Nov 2024) ──────────────────────────────
        $fertilizations = [
            [0, '2024-04-05', 'orgánico',  'Compost de orujo', 2000.0, 'en cobertura', 'brotacion'],
            [1, '2024-04-07', 'orgánico',  'Compost de orujo', 2000.0, 'en cobertura', 'brotacion'],
            [2, '2024-04-09', 'orgánico',  'Compost de orujo', 1500.0, 'en cobertura', 'brotacion'],
            [3, '2024-08-10', 'mineral',   'Nitrato potásico 13-0-46', 80.0, 'fertirrigación', 'maduracion'],
            [4, '2024-08-12', 'orgánico',  'Purín de oveja compostado', 1800.0, 'en cobertura', 'maduracion'],
            [0, '2024-11-20', 'orgánico',  'Estiércol bovino compostado', 3000.0, 'enterrado superficial', 'post_vendimia'],
            [1, '2024-11-22', 'mineral',   'Superfosfato triple 0-46-0', 120.0, 'en cobertura', 'post_vendimia'],
            [2, '2024-11-25', 'orgánico',  'Compost de restos vegetales', 2500.0, 'en cobertura', 'post_vendimia'],
        ];

        foreach ($fertilizations as [$pIdx, $date, $type, $name, $qty, $method, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'fertilization',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 20.0,
                'notes'              => "Aportación de {$name}.",
            ]);
            DB::table('fertilizations')->insert([
                'activity_id'        => $aId,
                'fertilizer_type'    => $type,
                'fertilizer_name'    => $name,
                'quantity'           => $qty,
                'application_method' => $method,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Riegos (May–Sep 2024) ──────────────────────────────────────────────
        $irrigations = [
            [1, '2024-05-15', 3000.0, 'goteo',     120, false, 'floracion'],
            [1, '2024-06-01', 3500.0, 'goteo',     140, false, 'cuajado'],
            [1, '2024-06-20', 4000.0, 'goteo',     160, true,  'cuajado'],   // fertirrigation
            [3, '2024-06-05', 2500.0, 'goteo',     100, false, 'cuajado'],
            [3, '2024-06-25', 3000.0, 'goteo',     120, false, 'envero'],
            [1, '2024-07-10', 4500.0, 'goteo',     180, false, 'envero'],
            [3, '2024-07-20', 3200.0, 'goteo',     130, false, 'envero'],
            [1, '2024-08-01', 5000.0, 'goteo',     200, false, 'maduracion'],
            [3, '2024-08-15', 4000.0, 'goteo',     160, false, 'maduracion'],
            [1, '2024-08-28', 4000.0, 'goteo',     160, false, 'maduracion'],
            [3, '2024-09-01', 2000.0, 'goteo',     80,  false, 'vendimia'],
            [1, '2024-09-03', 2000.0, 'goteo',     80,  false, 'vendimia'],
        ];

        foreach ($irrigations as [$pIdx, $date, $vol, $method, $dur, $fertiIrr, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'irrigation',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 26.0,
                'notes'              => $fertiIrr ? 'Fertirrigación con abono potásico.' : 'Riego de apoyo según estado hídrico.',
            ]);
            DB::table('irrigations')->insert([
                'activity_id'      => $aId,
                'water_volume'     => $vol,
                'water_volume_unit' => 'L',
                'irrigation_method' => $method,
                'duration_minutes'  => $dur,
                'is_fertirrigation' => $fertiIrr,
                'fertilizer_product' => $fertiIrr ? 'Nitrato potásico 13-0-46' : null,
                'fertilizer_dose_per_ha' => $fertiIrr ? 5.0 : null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Labores culturales (Abr–Nov 2024) ─────────────────────────────────
        $culturalWorks = [
            [0, '2024-04-28', 'deshojado',  'Deshojado en zona de racimos para aireación.', 5.0, 2, 'floracion'],
            [1, '2024-04-30', 'deshojado',  'Deshojado parcial cara de salida del sol.',     5.0, 2, 'floracion'],
            [0, '2024-07-18', 'despunte',   'Despunte de pámpanos para equilibrar vigor.',   4.0, 2, 'envero'],
            [2, '2024-07-20', 'despunte',   'Despunte en pámpanos largos.',                  3.5, 1, 'envero'],
            [4, '2024-05-10', 'escarda',    'Control de cubierta vegetal espontánea.',       6.0, 2, 'floracion'],
            [4, '2024-09-25', 'vendimia',   'Vendimia manual. Selección de racimos.',        8.0, 4, 'vendimia'],
        ];

        foreach ($culturalWorks as [$pIdx, $date, $workType, $desc, $hours, $workers, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'cultural',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 22.0,
                'notes'              => $desc,
            ]);
            DB::table('cultural_works')->insert([
                'activity_id'    => $aId,
                'work_type'      => $workType,
                'hours_worked'   => $hours,
                'workers_count'  => $workers,
                'description'    => $desc,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // ── Observaciones (Jun–Dic 2024) ───────────────────────────────────────
        $observations = [
            [0, '2024-06-08', 'enfermedad', 'Primeras manchas de aceite por mildiu en hojas basales.', 'leve',    'cuajado'],
            [1, '2024-06-10', 'enfermedad', 'Síntomas leves de oídio en hojas jóvenes.',              'leve',    'cuajado'],
            [2, '2024-07-05', 'plaga',      'Presencia de cochinilla algodonosa en sarmientos.',      'moderada','envero'],
            [3, '2024-08-20', 'general',    'Estado sanitario previo a vendimia: muy bueno. Sin daños apreciables.', null, 'maduracion'],
            [4, '2024-08-22', 'general',    'Estado sanitario ecológico óptimo. Racimos sanos al 98%.', null,    'maduracion'],
            [0, '2024-12-10', 'general',    'Revisión final de la madera. Ligeros síntomas de eutipiosis en 3 cepas.', 'leve', 'post_vendimia'],
        ];

        foreach ($observations as [$pIdx, $date, $type, $desc, $severity, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'observation',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 24.0,
                'notes'              => $desc,
            ]);
            DB::table('observations')->insert([
                'activity_id'      => $aId,
                'observation_type' => $type,
                'description'      => $desc,
                'severity'         => $severity,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── Vendimias (Sep 2024) ───────────────────────────────────────────────
        $harvests = [
            // [plot_idx, planting_idx, date, weight_kg, baume, brix, ph, destination, price]
            [0, 0, '2024-09-10', 1250.0, 13.2, 24.0, 3.45, 'winery', 0.62],
            [0, 1, '2024-09-10',  320.0, 12.8, 23.5, 3.50, 'winery', 0.58],
            [1, 2, '2024-09-12', 1680.0, 12.5, 23.0, 3.35, 'winery', 0.60],
            [2, 4, '2024-09-14',  850.0, 13.5, 24.8, 3.40, 'winery', 0.65],
            [3, 6, '2024-09-18',  720.0, 14.2, 26.0, 3.55, 'winery', 0.75],
        ];

        foreach ($harvests as [$pIdx, $plIdx, $date, $weight, $baume, $brix, $ph, $dest, $price]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'plot_planting_id'   => $plantingIds[$plIdx],
                'activity_type'      => 'harvest',
                'phenological_stage' => 'vendimia',
                'activity_date'      => $date,
                'weather_conditions' => 'soleado',
                'temperature'        => 28.0,
                'notes'              => 'Vendimia manual selectiva en óptimo estado de madurez.',
            ]);
            DB::table('harvests')->insert([
                'activity_id'        => $aId,
                'plot_planting_id'   => $plantingIds[$plIdx],
                'harvest_start_date' => $date,
                'vintage'            => 2024,
                'total_weight'       => $weight,
                'yield_per_hectare'  => round($weight / 0.850, 2),
                'baume_degree'       => $baume,
                'brix_degree'        => $brix,
                'ph_level'           => $ph,
                'acidity_level'      => 5.8,
                'potential_alcohol'  => round($baume * 0.6, 2),
                'color_rating'       => 'bueno',
                'health_status'      => 'sano',
                'sanitary_state_grapes' => 96.0,
                'destination_type'   => $dest,
                'destination'        => 'Bodega Agaete S.L.',
                'price_per_kg'       => $price,
                'total_value'        => round($weight * $price, 2),
                'status'             => 'active',
                'disqualified'       => false,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Tratamientos post-vendimia (Oct–Feb 2024/25) ──────────────────────
        $postHarvests = [
            [0, '2024-10-05', 'copper_treatment',  0.850, 3.0, 'kg/ha', 200.0, 0],
            [1, '2024-10-08', 'copper_treatment',  1.200, 3.0, 'kg/ha', 280.0, 0],
            [2, '2024-10-10', 'sulfur_treatment',  0.650, 4.0, 'kg/ha', 150.0, 0],
            [4, '2024-10-15', 'copper_treatment',  1.100, 3.0, 'kg/ha', 250.0, 24],
            [0, '2024-11-15', 'wound_sealing',     0.850, null, null,   null,  0],
        ];

        foreach ($postHarvests as [$pIdx, $date, $appType, $area, $dose, $unit, $water, $reentry]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'post_harvest',
                'phenological_stage' => 'post_vendimia',
                'activity_date'      => $date,
                'weather_conditions' => 'soleado',
                'temperature'        => 20.0,
                'notes'              => 'Tratamiento post-vendimia para protección madera.',
            ]);
            DB::table('post_harvest_treatments')->insert([
                'activity_id'          => $aId,
                'product_id'           => $appType === 'wound_sealing' ? null : $cobreId,
                'application_type'     => $appType,
                'treated_area_ha'      => $area,
                'dose_per_hectare'     => $dose,
                'dose_unit'            => $unit,
                'water_volume_liters'  => $water,
                'reentry_interval_hours' => $reentry,
                'notes'                => "Aplicación {$appType} sobre parcela " . ($pIdx + 1) . ".",
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }

    // ─── 7b. Actividades 2025 ─────────────────────────────────────────────────

    private function createActivities2025(
        array $plotIds, array $plantingIds, array $productIds,
        int $wvId, int $campaignId, $now
    ): void {
        [$mildiuId, $oidioId, $mixtoId, $insectId, $cobreId] = $productIds;

        $act = fn(array $data) => DB::table('agricultural_activities')->insertGetId(array_merge([
            'viticulturist_id'        => self::VIT_USER_ID,
            'winery_viticulturist_id' => $wvId,
            'campaign_id'             => $campaignId,
            'is_locked'               => false,
            'created_at'              => $now,
            'updated_at'              => $now,
        ], $data));

        // ── Podas (Ene 2025) ───────────────────────────────────────────────────
        $pruningDates2025 = ['2025-01-07', '2025-01-09', '2025-01-14', '2025-01-16', '2025-01-21'];

        foreach ($plotIds as $i => $plotId) {
            $aId = $act([
                'plot_id'            => $plotId,
                'activity_type'      => 'pruning',
                'phenological_stage' => 'reposo_invernal',
                'activity_date'      => $pruningDates2025[$i],
                'weather_conditions' => 'soleado',
                'temperature'        => 14.0 + $i,
                'notes'              => 'Poda 2025. Mayor intensidad en parcelas de alto vigor.',
            ]);
            DB::table('cultural_works')->insert([
                'activity_id'               => $aId,
                'work_type'                 => 'poda',
                'pruning_type'              => $i % 2 === 0 ? 'vaso' : 'guyot',
                'productive_buds_per_hectare' => 48000 + ($i * 1500),
                'hours_worked'              => 5.5 + ($i * 0.5),
                'workers_count'             => 2,
                'description'               => 'Poda de producción. Ajuste de carga según estado de la vid.',
                'created_at'                => $now,
                'updated_at'                => $now,
            ]);
        }

        // ── Tratamiento post-vendimia sellado heridas (Feb 2025) ──────────────
        $aId = $act([
            'plot_id'            => $plotIds[0],
            'activity_type'      => 'post_harvest',
            'phenological_stage' => 'reposo_invernal',
            'activity_date'      => '2025-02-10',
            'temperature'        => 16.0,
            'notes'              => 'Sellado de heridas de poda con pasta cicatrizante.',
        ]);
        DB::table('post_harvest_treatments')->insert([
            'activity_id'          => $aId,
            'product_id'           => null,
            'application_type'     => 'wound_sealing',
            'treated_area_ha'      => 0.850,
            'dose_per_hectare'     => null,
            'reentry_interval_hours' => 0,
            'notes'                => 'Sellado de heridas de poda con producto autorizado.',
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // ── Tratamientos fitosanitarios preventivos 2025 (Feb–Jun) ────────────
        $treatments2025 = [
            [0, '2025-02-25', 4, 'mildiu',         'pulverización',    'lloro',               15.0, 2.5],
            [2, '2025-02-27', 4, 'mildiu',         'pulverización',    'lloro',               15.5, 2.5],
            [0, '2025-03-20', 0, 'mildiu',         'pulverización',    'brotacion',           19.0, 3.0],
            [1, '2025-04-05', 1, 'oídio',          'aplicación foliar','desarrollo_vegetativo',21.0, 0.4],
            [3, '2025-04-08', 1, 'oídio',          'aplicación foliar','desarrollo_vegetativo',22.0, 0.4],
            [0, '2025-05-15', 2, 'mildiu y oídio', 'pulverización',    'floracion',           23.0, 2.0],
            [4, '2025-06-10', 4, 'mildiu',         'pulverización',    'cuajado',             25.0, 2.0],
        ];

        foreach ($treatments2025 as [$pIdx, $date, $prodIdx, $pest, $method, $stage, $temp, $dose]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'plot_planting_id'   => $plantingIds[$pIdx * 2],
                'activity_type'      => 'phytosanitary',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'weather_conditions' => 'soleado',
                'temperature'        => $temp,
                'notes'              => "Tratamiento preventivo {$pest}.",
            ]);
            DB::table('phytosanitary_treatments')->insert([
                'activity_id'        => $aId,
                'product_id'         => $productIds[$prodIdx],
                'dose_per_hectare'   => $dose,
                'area_treated'       => 0.85,
                'application_method' => $method,
                'target_pest'        => $pest,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Fertilizaciones 2025 ──────────────────────────────────────────────
        $fertilizations2025 = [
            [0, '2025-03-15', 'orgánico', 'Compost de orujo 2024', 2200.0, 'en cobertura', 'brotacion'],
            [2, '2025-03-17', 'orgánico', 'Compost de orujo 2024', 1800.0, 'en cobertura', 'brotacion'],
            [4, '2025-03-20', 'orgánico', 'Vermicompost ecológico', 2000.0, 'en cobertura', 'brotacion'],
        ];

        foreach ($fertilizations2025 as [$pIdx, $date, $type, $name, $qty, $method, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'fertilization',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 18.0,
                'notes'              => "Abonado de fondo 2025: {$name}.",
            ]);
            DB::table('fertilizations')->insert([
                'activity_id'        => $aId,
                'fertilizer_type'    => $type,
                'fertilizer_name'    => $name,
                'quantity'           => $qty,
                'application_method' => $method,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Riegos 2025 ────────────────────────────────────────────────────────
        $irrigations2025 = [
            [1, '2025-04-20', 2500.0, 100, false, 'desarrollo_vegetativo'],
            [3, '2025-04-22', 2000.0, 80,  false, 'desarrollo_vegetativo'],
            [1, '2025-05-20', 3500.0, 140, false, 'floracion'],
            [3, '2025-05-22', 3000.0, 120, false, 'floracion'],
            [1, '2025-06-25', 4000.0, 160, true,  'cuajado'],
            [3, '2025-06-27', 3500.0, 140, false, 'cuajado'],
        ];

        foreach ($irrigations2025 as [$pIdx, $date, $vol, $dur, $fertiIrr, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'irrigation',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 25.0,
                'notes'              => $fertiIrr ? 'Fertirrigación potásica.' : 'Riego de mantenimiento.',
            ]);
            DB::table('irrigations')->insert([
                'activity_id'        => $aId,
                'water_volume'       => $vol,
                'water_volume_unit'  => 'L',
                'irrigation_method'  => 'goteo',
                'duration_minutes'   => $dur,
                'is_fertirrigation'  => $fertiIrr,
                'fertilizer_product' => $fertiIrr ? 'Nitrato potásico 13-0-46' : null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Labores culturales 2025 ────────────────────────────────────────────
        $culturalWorks2025 = [
            [0, '2025-04-25', 'deshojado', 'Deshojado temprano en zona de fructificación.', 4.5, 2, 'desarrollo_vegetativo'],
            [1, '2025-04-28', 'deshojado', 'Deshojado selectivo cara de salida del sol.',   4.5, 2, 'desarrollo_vegetativo'],
            [4, '2025-05-05', 'escarda',   'Control manual de cubierta vegetal.',            5.0, 1, 'floracion'],
        ];

        foreach ($culturalWorks2025 as [$pIdx, $date, $workType, $desc, $hours, $workers, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'cultural',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 21.0,
                'notes'              => $desc,
            ]);
            DB::table('cultural_works')->insert([
                'activity_id'   => $aId,
                'work_type'     => $workType,
                'hours_worked'  => $hours,
                'workers_count' => $workers,
                'description'   => $desc,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // ── Observaciones 2025 ────────────────────────────────────────────────
        $observations2025 = [
            [0, '2025-03-28', 'fenología', 'Brotación uniforme. 85% de yemas brotadas.',      null,     'brotacion'],
            [1, '2025-03-30', 'fenología', 'Brotación algo retrasada respecto al año anterior.', null,  'brotacion'],
            [2, '2025-04-15', 'general',   'Estado vegetativo óptimo. Sin plagas observadas.', null,    'desarrollo_vegetativo'],
            [0, '2025-05-20', 'enfermedad','Síntomas iniciales de mildiu tras lluvias.',       'leve',  'floracion'],
            [4, '2025-06-15', 'general',   'Viña ecológica en excelente estado. Cuajado muy bueno.', null, 'cuajado'],
        ];

        foreach ($observations2025 as [$pIdx, $date, $type, $desc, $severity, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'observation',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 22.0,
                'notes'              => $desc,
            ]);
            DB::table('observations')->insert([
                'activity_id'      => $aId,
                'observation_type' => $type,
                'description'      => $desc,
                'severity'         => $severity,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    // ─── 9. Plagas y enfermedades ─────────────────────────────────────────────

    private function createPests(array $productIds, $now): array
    {
        [$mildiuId, $oidioId, $mixtoId, $insectId, $cobreId] = $productIds;

        $pests = [
            [
                'type'               => 'pest',
                'name'               => 'Polilla del racimo',
                'scientific_name'    => 'Lobesia botrana',
                'description'        => 'Lepidóptero cuyas larvas atacan los racimos en tres generaciones anuales. Es la plaga más importante de la vid en España.',
                'symptoms'           => 'Primera generación: daños en flores. Segunda y tercera: larvas barrenan bayas causando pudriciones secundarias. Seda característica envolviendo los racimos.',
                'lifecycle'          => 'Tres generaciones al año. Invernada como crisálida bajo la corteza. Primera vuelo en brotación, segunda en floración, tercera en envero.',
                'risk_months'        => json_encode([4, 5, 6, 7, 8, 9]),
                'threshold'          => '1 captura/trampa/semana en 1ª generación; 5 capturas en 2ª y 3ª generación',
                'prevention_methods' => 'Trampas de feromonas para monitoreo. Confusión sexual. Eliminación de cortezas donde invernan las crisálidas.',
                'control_methods'    => json_encode(['biologico', 'quimico']),
                'active'             => true,
                'product_link'       => $insectId,
                'effectiveness'      => 5,
            ],
            [
                'type'               => 'disease',
                'name'               => 'Mildiu de la vid',
                'scientific_name'    => 'Plasmopara viticola',
                'description'        => 'Oomiceto que provoca una de las enfermedades más devastadoras de la vid. Necesita humedad y temperatura suave para desarrollarse.',
                'symptoms'           => 'Manchas aceitosas en haz de hojas (manchas de aceite), eflorescencia blanca algodonosa en envés. En racimos: podredumbre parda y desecación (racimo cuero).',
                'lifecycle'          => 'Invernada como oosporas en hojas caídas. Infección primaria con lluvias > 10mm y temperaturas > 10°C. Ciclo asexual muy rápido en condiciones favorables.',
                'risk_months'        => json_encode([4, 5, 6, 7, 8]),
                'threshold'          => 'Tratamiento preventivo desde brotación. Regla de los "3 × 10" (10 cm de brote, 10°C, 10 mm lluvia)',
                'prevention_methods' => 'Variedades resistentes. Poda que favorezca aireación. Evitar encharcamientos. Control de cubierta vegetal.',
                'control_methods'    => json_encode(['cultural', 'quimico']),
                'active'             => true,
                'product_link'       => $mildiuId,
                'effectiveness'      => 5,
            ],
            [
                'type'               => 'disease',
                'name'               => 'Oídio de la vid',
                'scientific_name'    => 'Uncinula necator',
                'description'        => 'Hongo ascomiceto que forma micelio superficial sobre órganos verdes. No necesita lluvia para infectar, se favorece con tiempo seco y cálido.',
                'symptoms'           => 'Polvillo grisáceo-blanquecino en hojas, sarmientos y racimos. En bayas: craqueo de la piel, pardeamiento interno y sabor amargo.',
                'lifecycle'          => 'Invernada en cleistotecios sobre corteza o en yemas infectadas. Conidios se dispersan por viento. Temperatura óptima 20-27°C.',
                'risk_months'        => json_encode([4, 5, 6, 7, 8]),
                'threshold'          => 'Tratamiento desde brotación en parcelas con historial de oídio. Especialmente crítico en floración.',
                'prevention_methods' => 'Poda adecuada para airear el dosel. Deshojado temprano. Variedades menos sensibles.',
                'control_methods'    => json_encode(['cultural', 'quimico']),
                'active'             => true,
                'product_link'       => $oidioId,
                'effectiveness'      => 5,
            ],
            [
                'type'               => 'pest',
                'name'               => 'Araña roja',
                'scientific_name'    => 'Panonychus ulmi',
                'description'        => 'Ácaro fitófago que se alimenta del contenido celular de las hojas. Favorecido por tratamientos excesivos con insecticidas que eliminan sus depredadores naturales.',
                'symptoms'           => 'Hojas con aspecto bronceado o plateado, comenzando por el nervio central. Defoliación prematura en ataques severos. Telaraña fina en envés.',
                'lifecycle'          => 'Varias generaciones al año (6-8). Invernada como huevo de invierno rojo en corteza de madera. Ciclos muy cortos en verano (10-12 días a 25°C).',
                'risk_months'        => json_encode([5, 6, 7, 8, 9]),
                'threshold'          => 'Tratamiento cuando > 30% de hojas con formas móviles y sin depredadores visibles',
                'prevention_methods' => 'Conservar fauna auxiliar (fitoseidos). Evitar exceso de nitrógeno. Uso moderado de insecticidas de amplio espectro.',
                'control_methods'    => json_encode(['biologico', 'quimico']),
                'active'             => true,
                'product_link'       => null,
                'effectiveness'      => null,
            ],
            [
                'type'               => 'pest',
                'name'               => 'Trips de la vid',
                'scientific_name'    => 'Frankliniella occidentalis / Drepanothrips reuteri',
                'description'        => 'Insecto minúsculo que raspa los tejidos vegetales. Especialmente dañino durante la floración, donde puede impedir el cuajado.',
                'symptoms'           => 'Deformaciones en hojas jóvenes (hojas en cuchara). Cicatrices plateadas en bayas tras el cuajado. Daños en flores que limitan la producción.',
                'lifecycle'          => 'Varias generaciones. Adultos invernan bajo corteza y en el suelo. Actividad máxima en floración.',
                'risk_months'        => json_encode([4, 5, 6]),
                'threshold'          => 'Monitoreo con trampas azules. Tratamiento si > 10 adultos/flor durante plena floración',
                'prevention_methods' => 'Eliminación de malas hierbas huésped. Trampas cromáticas azules para monitoreo.',
                'control_methods'    => json_encode(['fisico', 'quimico']),
                'active'             => true,
                'product_link'       => $insectId,
                'effectiveness'      => 3,
            ],
            [
                'type'               => 'disease',
                'name'               => 'Botrytis / Podredumbre gris',
                'scientific_name'    => 'Botrytis cinerea',
                'description'        => 'Hongo necrotrófico que afecta principalmente a los racimos en condiciones de alta humedad. Especialmente grave en variedades de hollejo fino.',
                'symptoms'           => 'Micelio grisáceo algodonoso sobre bayas. Podredumbre blanda y húmeda. Baya se arruga y deseca. Olor característico a moho.',
                'lifecycle'          => 'Esporas (conidios) omnipresentes. Infección a través de heridas, residuos florales o daños mecánicos. Favorecida por humedad > 90% y temperatura 15-25°C.',
                'risk_months'        => json_encode([7, 8, 9, 10]),
                'threshold'          => 'Tratamiento preventivo en floración y pre-cierre de racimos en variedades sensibles',
                'prevention_methods' => 'Deshojado en zona de fructificación. Aclareo de racimos. Control de vigor. Evitar heridas.',
                'control_methods'    => json_encode(['cultural', 'biologico', 'quimico']),
                'active'             => true,
                'product_link'       => $cobreId,
                'effectiveness'      => 3,
            ],
            [
                'type'               => 'disease',
                'name'               => 'Excoriosis',
                'scientific_name'    => 'Phomopsis viticola',
                'description'        => 'Hongo que ataca principalmente la madera joven y los órganos herbáceos en primavera. Provoca muerte de yemas y cancros en sarmientos.',
                'symptoms'           => 'Manchas negras en la base de los pámpanos con necrosis que asciende. Cancros alargados en sarmientos. Acortamiento de entrenudos. Decoloración en madera.',
                'lifecycle'          => 'Invernada en cancros y madera infectada. Picnidios liberan esporas con lluvia en primavera. Infección óptima a 10-20°C.',
                'risk_months'        => json_encode([3, 4, 5]),
                'threshold'          => 'Tratamiento en desborre (escama hinchada) y en 2-3 hojas en parcelas con historial',
                'prevention_methods' => 'Poda elimando madera infectada. Desinfección de herramientas. Aplicación de pasta cicatrizante en cortes grandes.',
                'control_methods'    => json_encode(['cultural', 'quimico']),
                'active'             => true,
                'product_link'       => $cobreId,
                'effectiveness'      => 4,
            ],
            [
                'type'               => 'disease',
                'name'               => 'Eutipiosis',
                'scientific_name'    => 'Eutypa lata',
                'description'        => 'Enfermedad de la madera de la vid que provoca decaimiento progresivo de los brazos afectados. De difícil control una vez establecida.',
                'symptoms'           => 'Pámpanos con entrenudos cortos y hojas pequeñas cloróticas en primavera. Racimos atrofiados o sin cuajado. Necrosis sectorial en sección de brazos afectados.',
                'lifecycle'          => 'El hongo penetra a través de heridas de poda. Esporas liberadas con lluvia en otoño-invierno. Micelio avanza lentamente por la madera.',
                'risk_months'        => json_encode([11, 12, 1, 2]),
                'threshold'          => 'Eliminación de brazos afectados en cuanto se detecte. No hay umbral económico — acción inmediata',
                'prevention_methods' => 'Realizar podas con tiempo seco. Proteger heridas con pasta fungicida inmediatamente. Desinfectar herramientas entre cepas.',
                'control_methods'    => json_encode(['cultural']),
                'active'             => true,
                'product_link'       => null,
                'effectiveness'      => null,
            ],
        ];

        $ids = [];
        foreach ($pests as $pest) {
            $productLink  = $pest['product_link'];
            $effectiveness = $pest['effectiveness'];
            unset($pest['product_link'], $pest['effectiveness']);

            $pestId = DB::table('pests')->insertGetId(array_merge($pest, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
            $ids[] = $pestId;

            if ($productLink !== null) {
                DB::table('pest_product_effectiveness')->insertOrIgnore([
                    'pest_id'              => $pestId,
                    'product_id'           => $productLink,
                    'effectiveness_rating' => $effectiveness,
                    'notes'                => 'Eficacia probada en condiciones de Gran Canaria.',
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
        }

        return $ids;
    }

    // ─── 10. Rendimientos estimados ───────────────────────────────────────────

    private function createEstimatedYields(array $plantingIds, int $campaign2024Id, int $campaign2025Id, $now): void
    {
        // Usamos los plantingIds principales (uno por parcela: índices 0,2,4,6,8)
        // Campaña 2024: estimación pre-envero (ronda 1) + pre-vendimia (ronda 3) con datos reales
        // Campaña 2025: solo pre-envero (ronda 1, campaña en curso)

        // --- 2024 ronda 1: estimación pre-envero (Julio 2024) ---
        $estimates2024r1 = [
            // [planting_idx, est_yield_ha, est_total, bunches_plant, bunch_g, plants, pct, health_pct, health_status, actual_ha, actual_total, variance_pct]
            [0, 4800.0, 2880.0, 12.5, 280.0, 20, 25.0, 92.0, 'sano',        5882.0, 1250.0, 22.5],
            [2, 5500.0, 4400.0, 13.0, 310.0, 25, 20.0, 90.0, 'sano',        5250.0, 1680.0,  4.5],
            [4, 6000.0, 2400.0, 11.0, 290.0, 15, 20.0, 88.0, 'sano',        5000.0,  850.0, 16.7],
            [6, 7500.0, 3450.0, 14.5, 340.0, 20, 22.0, 95.0, 'sano',        6957.0,  720.0,  7.2],
            [8, 6200.0, 3410.0, 12.0, 310.0, 20, 18.0, 91.0, 'sano',        null,    null,   null],
        ];

        foreach ($estimates2024r1 as [$pIdx, $estHa, $estTotal, $bunchesPlant, $bunchG, $plants, $samplingPct, $healthPct, $healthStatus, $actualHa, $actualTotal, $variancePct]) {
            DB::table('estimated_yields')->insertOrIgnore([
                'plot_planting_id'           => $plantingIds[$pIdx],
                'campaign_id'                => $campaign2024Id,
                'estimated_by'               => self::VIT_USER_ID,
                'estimated_yield_per_hectare'=> $estHa,
                'estimated_total_yield'      => $estTotal,
                'estimation_date'            => '2024-07-20',
                'estimation_method'          => 'sampling',
                'estimation_round'           => 1,
                'status'                     => 'confirmed',
                'active'                     => true,
                'bunches_per_plant'          => $bunchesPlant,
                'bunch_weight_grams'         => $bunchG,
                'total_plants_sampled'       => $plants,
                'sampling_area_pct'          => $samplingPct,
                'health_percentage'          => $healthPct,
                'health_status'              => $healthStatus,
                'potential_alcohol'          => round($estHa * 0.0027, 1), // aprox
                'vintage'                    => 2024,
                'actual_yield_per_hectare'   => $actualHa,
                'actual_total_yield'         => $actualTotal,
                'variance_percentage'        => $variancePct,
                'notes'                      => 'Muestreo pre-envero. Condiciones meteorológicas favorables.',
                'created_at'                 => $now,
                'updated_at'                 => $now,
            ]);
        }

        // --- 2024 ronda 3: estimación pre-vendimia (Sep 2024) ---
        $estimates2024r3 = [
            [0, 5500.0, 3300.0, 13.0, 295.0, 30, 35.0, 96.0, 'sano', 5882.0, 1250.0,  6.9],
            [2, 5400.0, 4320.0, 13.5, 300.0, 30, 25.0, 95.0, 'sano', 5250.0, 1680.0,  2.8],
            [4, 5800.0, 2320.0, 11.5, 285.0, 20, 25.0, 94.0, 'sano', 5000.0,  850.0, 13.8],
        ];

        foreach ($estimates2024r3 as [$pIdx, $estHa, $estTotal, $bunchesPlant, $bunchG, $plants, $samplingPct, $healthPct, $healthStatus, $actualHa, $actualTotal, $variancePct]) {
            DB::table('estimated_yields')->insertOrIgnore([
                'plot_planting_id'            => $plantingIds[$pIdx],
                'campaign_id'                 => $campaign2024Id,
                'estimated_by'                => self::VIT_USER_ID,
                'estimated_yield_per_hectare' => $estHa,
                'estimated_total_yield'       => $estTotal,
                'estimation_date'             => '2024-09-01',
                'estimation_method'           => 'sampling',
                'estimation_round'            => 3,
                'status'                      => 'confirmed',
                'active'                      => true,
                'bunches_per_plant'           => $bunchesPlant,
                'bunch_weight_grams'          => $bunchG,
                'total_plants_sampled'        => $plants,
                'sampling_area_pct'           => $samplingPct,
                'health_percentage'           => $healthPct,
                'health_status'               => $healthStatus,
                'potential_alcohol'           => round($estHa * 0.0027, 1),
                'vintage'                     => 2024,
                'actual_yield_per_hectare'    => $actualHa,
                'actual_total_yield'          => $actualTotal,
                'variance_percentage'         => $variancePct,
                'notes'                       => 'Estimación pre-vendimia con muestreo intensivo. Uva en óptimo estado.',
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ]);
        }

        // --- 2025 ronda 1: estimación pre-envero (Jun 2025, campaña en curso) ---
        $estimates2025r1 = [
            [0, 5200.0, 3120.0, 12.0, 275.0, 20, 22.0, 93.0, 'sano'],
            [2, 5800.0, 4640.0, 13.5, 315.0, 20, 20.0, 91.0, 'sano'],
            [4, 5000.0, 2000.0, 10.5, 280.0, 15, 18.0, 95.0, 'sano'],
            [6, 7200.0, 3312.0, 14.0, 330.0, 20, 20.0, 96.0, 'sano'],
            [8, 6500.0, 3575.0, 12.5, 305.0, 20, 18.0, 97.0, 'sano'],
            [1, 5100.0, 1530.0, 11.5, 260.0, 15, 15.0, 90.0, 'sano'],
        ];

        foreach ($estimates2025r1 as [$pIdx, $estHa, $estTotal, $bunchesPlant, $bunchG, $plants, $samplingPct, $healthPct, $healthStatus]) {
            DB::table('estimated_yields')->insertOrIgnore([
                'plot_planting_id'            => $plantingIds[$pIdx],
                'campaign_id'                 => $campaign2025Id,
                'estimated_by'                => self::VIT_USER_ID,
                'estimated_yield_per_hectare' => $estHa,
                'estimated_total_yield'       => $estTotal,
                'estimation_date'             => '2025-06-20',
                'estimation_method'           => 'sampling',
                'estimation_round'            => 1,
                'status'                      => 'confirmed',
                'active'                      => true,
                'bunches_per_plant'           => $bunchesPlant,
                'bunch_weight_grams'          => $bunchG,
                'total_plants_sampled'        => $plants,
                'sampling_area_pct'           => $samplingPct,
                'health_percentage'           => $healthPct,
                'health_status'               => $healthStatus,
                'potential_alcohol'           => round($estHa * 0.0027, 1),
                'vintage'                     => 2025,
                'actual_yield_per_hectare'    => null,
                'actual_total_yield'          => null,
                'variance_percentage'         => null,
                'notes'                       => 'Estimación pre-envero 2025. Campaña en curso con perspectivas favorables.',
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ]);
        }
    }

    // ─── 8. Fenología ─────────────────────────────────────────────────────────

    private function createPhenology(array $plantingIds, int $campaign2024Id, int $campaign2025Id, $now): void
    {
        // Usamos los 5 primeros plantingIds (uno por parcela, plantación principal)
        $mainPlantings = [$plantingIds[0], $plantingIds[2], $plantingIds[4], $plantingIds[6], $plantingIds[8]];

        // Campaña 2024 — eventos completos (vendimia realizada)
        $events2024 = [
            ['budbreak',    '2024-03-10', 85],
            ['shoot_growth','2024-04-05', 95],
            ['flowering',   '2024-05-20', 90],
            ['fruit_set',   '2024-06-10', 88],
            ['veraison',    '2024-07-25', 92],
            ['pre_harvest', '2024-09-01', 95],
            ['harvest',     '2024-09-10', 100],
        ];

        foreach ($mainPlantings as $i => $plantingId) {
            foreach ($events2024 as [$event, $date, $confidence]) {
                DB::table('phenology_observations')->insertOrIgnore([
                    'plot_planting_id'    => $plantingId,
                    'campaign_id'         => $campaign2024Id,
                    'viticulturist_id'    => self::VIT_USER_ID,
                    'event'               => $event,
                    'obs_date'            => $date,
                    'source'              => 'manual',
                    'confidence'          => $confidence,
                    'active'              => true,
                    'notes'               => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }

        // Campaña 2025 — solo primeros eventos (campaña en curso)
        $events2025 = [
            ['budbreak',    '2025-03-12', 90],
            ['shoot_growth','2025-04-08', 92],
            ['flowering',   '2025-05-25', 88],
        ];

        foreach (array_slice($mainPlantings, 0, 3) as $plantingId) {
            foreach ($events2025 as [$event, $date, $confidence]) {
                DB::table('phenology_observations')->insertOrIgnore([
                    'plot_planting_id' => $plantingId,
                    'campaign_id'      => $campaign2025Id,
                    'viticulturist_id' => self::VIT_USER_ID,
                    'event'            => $event,
                    'obs_date'         => $date,
                    'source'           => 'manual',
                    'confidence'       => $confidence,
                    'active'           => true,
                    'notes'            => null,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }
    }
}
