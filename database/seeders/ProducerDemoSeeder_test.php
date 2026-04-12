<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder demo completo para el rol Producer (user_id = 339).
 *
 * El producer es viticultor + bodega a la vez, por lo que este seeder
 * cubre AMBOS lados:
 *
 * LADO VITICULTOR (cuaderno de campo):
 *   · 4 parcelas con 2 plantaciones cada una
 *   · Campañas 2025 (cerrada) y 2026 (activa)
 *   · ~50 actividades agrícolas con sub-tablas
 *   · Plagas, fenología, rendimientos estimados
 *   · Compliance, PAC, almacén insumos
 *
 * LADO BODEGA (elaboración):
 *   · 30 contenedores (depósitos acero + barricas)
 *   · 8 vinos propios
 *   · Controles fermentación, traslados, mermas
 *   · Embotellamientos y lotes de producto
 *   · Facturas mixtas (cosecha + vino)
 *
 * Usuario: demo_test_producer@agro365.es  (user_id = 339)
 *
 * Uso:
 *   php artisan db:seed --class=ProducerDemoSeeder_test
 */
class ProducerDemoSeeder_test extends Seeder
{
    private const PRODUCER_USER_ID = 339;
    private const EMAIL            = 'demo_test_producer@agro365.es';

    // ── Geografía: Valle de La Orotava, Tenerife ──────────────────────────────
    private const AC_ID           = 5;    // Canarias
    private const PROVINCE_ID     = 15;   // Santa Cruz de Tenerife
    private const MUNICIPALITY_ID = 5518; // La Orotava

    public function run(): void
    {
        $now = now();

        $this->command->info('');
        $this->command->info('🌿🍷 ══════════════════════════════════════════════════');
        $this->command->info('🌿🍷  PRODUCER DEMO SEEDER — user_id = 339');
        $this->command->info('🌿🍷  Productor La Orotava · Tenerife');
        $this->command->info('🌿🍷  (viticultor + bodega en uno)');
        $this->command->info('🌿🍷 ══════════════════════════════════════════════════');
        $this->command->info('');

        // ── 0. Crear / verificar usuario ─────────────────────────────────────
        $this->ensureDemoUser($now);

        $user = DB::table('users')->find(self::PRODUCER_USER_ID);
        $this->command->info("✅ Producer: {$user->email} (role: {$user->role})");

        // ── 1. Limpieza previa (idempotente) ──────────────────────────────────
        $this->step('Limpieza previa', fn() => $this->cleanup());

        // ─── LADO VITICULTOR ──────────────────────────────────────────────────

        // 2. Productos fitosanitarios
        $productIds = [];
        $this->step('Productos fitosanitarios (5)', function () use ($now, &$productIds) {
            $productIds = $this->createProducts($now);
        });

        // 3. Auto-vínculo winery_viticulturist (producer es su propia bodega)
        $wvId = 0;
        $this->step('Auto-vínculo bodega↔viticultor', function () use ($now, &$wvId) {
            $wvId = $this->createSelfLink($now);
        });

        // 4. Parcelas + SIGPAC + Geometría (JSON completo)
        $plotIds = [];
        $this->step('Parcelas + SIGPAC + geometría (460 recintos Gran Canaria)', function () use ($now, &$plotIds) {
            $plotIds = $this->createPlotsWithSigpac($now);
        });

        // 5. Plantaciones
        $plantingIds = [];
        $this->step('Plantaciones (8)', function () use ($now, $plotIds, &$plantingIds) {
            $plantingIds = $this->createPlantings($plotIds, $now);
        });

        // 6. Campañas
        $campaign2025Id = 0;
        $campaign2026Id = 0;
        $this->step('Campañas (2025 cerrada + 2026 activa)', function () use ($now, $wvId, &$campaign2025Id, &$campaign2026Id) {
            [$campaign2025Id, $campaign2026Id] = $this->createCampaigns($wvId, $now);
        });

        // 7. Actividades de campo
        $this->step('Actividades 2025 (~28)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id) {
            $this->createActivities2025($plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id, $now);
        });
        $this->step('Actividades 2026 (~15)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2026Id) {
            $this->createActivities2026($plotIds, $plantingIds, $productIds, $wvId, $campaign2026Id, $now);
        });

        // 8. Fenología
        $this->step('Observaciones fenológicas', function () use ($now, $plantingIds, $campaign2025Id, $campaign2026Id) {
            $this->createPhenology($plantingIds, $campaign2025Id, $campaign2026Id, $now);
        });

        // 9. Plagas
        $pestIds = [];
        $this->step('Plagas y enfermedades (6)', function () use ($now, $productIds, &$pestIds) {
            $pestIds = $this->createPests($productIds, $now);
        });

        // 10. Rendimientos estimados
        $this->step('Rendimientos estimados', function () use ($now, $plantingIds, $campaign2025Id, $campaign2026Id) {
            $this->createEstimatedYields($plantingIds, $campaign2025Id, $campaign2026Id, $now);
        });

        // 11. Maquinaria
        $machineryIds = [];
        $this->step('Maquinaria (8)', function () use ($now, &$machineryIds) {
            $machineryIds = $this->createMachinery($now);
        });

        // 12. Explotaciones
        $exploitationIds = [];
        $this->step('Explotaciones SIEX/REA (1)', function () use ($now, &$exploitationIds) {
            $exploitationIds = $this->createExploitations($now);
        });

        // 13. Compliance
        $this->step('Compliance (aplicadores, equipos, seguros)', function () use ($now, $exploitationIds, $campaign2025Id, $campaign2026Id) {
            $this->createCompliance($exploitationIds, $campaign2025Id, $campaign2026Id, $now);
        });

        // 14. PAC
        $this->step('PAC (2 declaraciones + items + pagos)', function () use ($now, $plotIds, $campaign2025Id, $campaign2026Id) {
            $this->createPAC($plotIds, $campaign2025Id, $campaign2026Id, $now);
        });

        // 15. Almacén insumos
        $this->step('Almacén insumos (1 almacén + 8 suministros)', function () use ($now, $productIds, $campaign2025Id, $campaign2026Id) {
            $this->createAlmacen($productIds, $campaign2025Id, $campaign2026Id, $now);
        });

        // ─── LADO BODEGA ──────────────────────────────────────────────────────

        // 16. Contenedores bodega
        $wineryContainerIds = [];
        $this->step('Contenedores bodega (30: depósitos + barricas)', function () use ($now, &$wineryContainerIds) {
            $wineryContainerIds = $this->createWineryContainers($now);
        });

        // 17. Vinos
        $wineIds = [];
        $this->step('Vinos (8 referencias)', function () use ($now, &$wineIds) {
            $wineIds = $this->createWines($now);
        });

        // 18. Controles fermentación
        $this->step('Controles fermentación (20)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createFermentationControls($wineIds, $wineryContainerIds, $now);
        });

        // 19. Traslados y mermas
        $this->step('Traslados de vino (8)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createWineTransfers($wineIds, $wineryContainerIds, $now);
        });
        $this->step('Mermas de vino (5)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createWineLosses($wineIds, $wineryContainerIds, $now);
        });

        // 20. Embotellamientos
        $this->step('Embotellamientos (4)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createBottlings($wineIds, $wineryContainerIds, $now);
        });

        // 21. Clientes
        $this->step('Clientes (5)', function () use ($now) {
            $this->createClients($now);
        });

        // 22. Facturas mixtas (cosecha + vino)
        $this->step('Facturas mixtas (6: cosecha + vino)', function () use ($now, $campaign2025Id) {
            $this->createInvoices($campaign2025Id, $now);
        });

        // 23. Notas de cata
        $this->step('Notas de cata (6)', function () use ($now, $wineIds) {
            $this->createTastingNotes($wineIds, $now);
        });

        $this->command->info('');
        $this->command->info('✅ ProducerDemoSeeder completado.');
        $this->command->info("   Producer: " . self::EMAIL);
        $this->printSummary();
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function step(string $label, callable $fn): void
    {
        $this->command->info("  ▸ {$label}...");
        $fn();
        $this->command->info("    ✓");
    }

    // ─── 0. Crear usuario ─────────────────────────────────────────────────────

    private function ensureDemoUser($now): void
    {
        if (DB::table('users')->where('id', self::PRODUCER_USER_ID)->exists()) {
            return;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->insert([
            'id'                => self::PRODUCER_USER_ID,
            'name'              => 'Productor La Orotava Demo',
            'email'             => self::EMAIL,
            'email_verified_at' => $now,
            'password'          => bcrypt('password'),
            'role'              => 'producer',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('  ✅ Usuario producer creado (id=' . self::PRODUCER_USER_ID . ')');
    }

    // ─── 1. Cleanup ───────────────────────────────────────────────────────────

    private function cleanup(): void
    {
        $uid = self::PRODUCER_USER_ID;

        // ── Lado viticultor ───────────────────────────────────────────────────
        $activityIds = DB::table('agricultural_activities')
            ->where('viticulturist_id', $uid)->pluck('id');
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
        DB::table('phenology_observations')->where('viticulturist_id', $uid)->delete();
        DB::table('campaigns')->where('viticulturist_id', $uid)->delete();
        $plotIds = DB::table('plots')->where('viticulturist_id', $uid)->pluck('id');
        if ($plotIds->isNotEmpty()) {
            $plantingIds = DB::table('plot_plantings')->whereIn('plot_id', $plotIds)->pluck('id');
            if ($plantingIds->isNotEmpty()) {
                DB::table('estimated_yields')->whereIn('plot_planting_id', $plantingIds)->delete();
            }
            $sigpacLinks = DB::table('multipart_plot_sigpac')->whereIn('plot_id', $plotIds)->get();
            $geomIds     = $sigpacLinks->pluck('plot_geometry_id')->filter()->values();
            $sigpacIds   = $sigpacLinks->pluck('sigpac_code_id')->filter()->values();
            DB::table('multipart_plot_sigpac')->whereIn('plot_id', $plotIds)->delete();
            if ($geomIds->isNotEmpty()) {
                DB::table('plot_geometry')->whereIn('id', $geomIds)->delete();
            }
            if ($sigpacIds->isNotEmpty()) {
                DB::table('sigpac_code')->whereIn('id', $sigpacIds)->delete();
            }
            DB::table('plot_plantings')->whereIn('plot_id', $plotIds)->delete();
            DB::table('plots')->whereIn('id', $plotIds)->delete();
        }
        DB::table('winery_viticulturist')
            ->where('viticulturist_id', $uid)
            ->orWhere('winery_id', $uid)
            ->delete();
        $seederPestNames = [
            'Polilla del racimo (Orotava)', 'Mildiu de la vid (Orotava)',
            'Oídio de la vid (Orotava)', 'Araña roja (Orotava)',
            'Botrytis / Podredumbre gris (Orotava)', 'Excoriosis (Orotava)',
        ];
        $pestIds = DB::table('pests')->whereIn('name', $seederPestNames)->pluck('id');
        if ($pestIds->isNotEmpty()) {
            DB::table('pest_product_effectiveness')->whereIn('pest_id', $pestIds)->delete();
            DB::table('pests')->whereIn('id', $pestIds)->delete();
        }
        DB::table('phytosanitary_products')->where('user_id', $uid)->delete();
        DB::table('machinery')->where('viticulturist_id', $uid)->delete();
        DB::table('field_applicators')->where('viticulturist_id', $uid)->delete();
        DB::table('field_equipment')->where('viticulturist_id', $uid)->delete();
        DB::table('advisory_memberships')->where('viticulturist_id', $uid)->delete();
        DB::table('agri_insurances')->where('viticulturist_id', $uid)->delete();
        $exploitationIds = DB::table('exploitations')->where('viticulturist_id', $uid)->pluck('id');
        if ($exploitationIds->isNotEmpty()) {
            DB::table('commercial_authorizations')->whereIn('exploitation_id', $exploitationIds)->delete();
            DB::table('cue_exports')->whereIn('exploitation_id', $exploitationIds)->delete();
        }
        DB::table('exploitations')->where('viticulturist_id', $uid)->delete();
        $pacIds = DB::table('pac_declarations')->where('viticulturist_id', $uid)->pluck('id');
        if ($pacIds->isNotEmpty()) {
            DB::table('pac_declaration_items')->whereIn('declaration_id', $pacIds)->delete();
        }
        DB::table('pac_declarations')->where('viticulturist_id', $uid)->delete();
        DB::table('pac_payments')->where('viticulturist_id', $uid)->delete();
        DB::table('plot_costs')->where('viticulturist_id', $uid)->delete();
        DB::table('marketed_harvests')->where('viticulturist_id', $uid)->delete();
        $warehouseIds = DB::table('warehouses')->where('user_id', $uid)->pluck('id');
        if ($warehouseIds->isNotEmpty()) {
            $stockIds = DB::table('product_stocks')->whereIn('warehouse_id', $warehouseIds)->pluck('id');
            if ($stockIds->isNotEmpty()) {
                DB::table('product_stock_movements')->whereIn('stock_id', $stockIds)->delete();
            }
            DB::table('product_stocks')->whereIn('warehouse_id', $warehouseIds)->delete();
        }
        DB::table('supplies')->where('viticulturist_id', $uid)->delete();
        DB::table('warehouses')->where('user_id', $uid)->delete();
        DB::table('sites')->where('user_id', $uid)->delete();
        DB::table('valleys')->where('user_id', $uid)->delete();
        DB::table('soil_types')->where('user_id', $uid)->delete();
        DB::table('topographies')->where('user_id', $uid)->delete();
        DB::table('irrigation_types')->where('user_id', $uid)->delete();
        DB::table('property_types')->where('user_id', $uid)->delete();

        // ── Lado bodega ───────────────────────────────────────────────────────
        DB::table('containers')->where('user_id', $uid)->delete();
        $wineIds = DB::table('wines')->where('user_id', $uid)->pluck('id');
        if ($wineIds->isNotEmpty()) {
            DB::table('wine_fermentation_controls')->whereIn('wine_id', $wineIds)->delete();
            $transferIds = DB::table('wine_transfers')->whereIn('wine_id', $wineIds)->pluck('id');
            if ($transferIds->isNotEmpty()) {
                DB::table('container_histories')
                    ->whereIn('reference_id', $transferIds)
                    ->whereIn('operation_type', ['wine_transfer_in', 'wine_transfer_out',
                        'wine_transfer_revert_in', 'wine_transfer_revert_out'])
                    ->delete();
            }
            DB::table('wine_transfers')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_losses')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_tasting_notes')->whereIn('wine_id', $wineIds)->delete();
        }
        DB::table('wines')->where('user_id', $uid)->delete();
        $bottlingIds = DB::table('wine_bottlings')->where('user_id', $uid)->pluck('id');
        if ($bottlingIds->isNotEmpty()) {
            DB::table('wine_lots')->whereIn('bottling_id', $bottlingIds)->delete();
        }
        DB::table('wine_bottlings')->where('user_id', $uid)->delete();
        // Facturas (antes que clients)
        $invoiceIds = DB::table('invoices')->where('user_id', $uid)->pluck('id');
        if ($invoiceIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
        }
        DB::table('invoices')->where('user_id', $uid)->delete();
        DB::table('clients')->where('user_id', $uid)->delete();
    }

    // ─── 2. Productos fitosanitarios ──────────────────────────────────────────

    private function createProducts($now): array
    {
        $products = [
            ['name' => 'Ridomil Gold MZ 68 WG', 'active_ingredient' => 'Metalaxil-M 4% + Mancozeb 64% WG', 'registration_number' => 'ES-TF-001-01', 'manufacturer' => 'Syngenta', 'type' => 'fungicida', 'toxicity_class' => 'IV', 'withdrawal_period_days' => 28, 'safety_interval_days' => 28],
            ['name' => 'Domark 10 EC', 'active_ingredient' => 'Tetraconazol 10% EC', 'registration_number' => 'ES-TF-002-01', 'manufacturer' => 'Isagro', 'type' => 'fungicida', 'toxicity_class' => 'III', 'withdrawal_period_days' => 21, 'safety_interval_days' => 21],
            ['name' => 'Pyrinex Supreme', 'active_ingredient' => 'Clorpirifos 25% + Lambda-cihalotrina 2,5% CS', 'registration_number' => 'ES-TF-003-01', 'manufacturer' => 'Adama', 'type' => 'insecticida', 'toxicity_class' => 'II', 'withdrawal_period_days' => 14, 'safety_interval_days' => 14],
            ['name' => 'Cueva WP Zineb', 'active_ingredient' => 'Zineb 65% WP', 'registration_number' => 'ES-TF-004-01', 'manufacturer' => 'Sipcam Inagra', 'type' => 'fungicida', 'toxicity_class' => 'IV', 'withdrawal_period_days' => 14, 'safety_interval_days' => 14],
            ['name' => 'Vertimec 1.9% EC', 'active_ingredient' => 'Abamectina 1,9% EC', 'registration_number' => 'ES-TF-005-01', 'manufacturer' => 'Syngenta', 'type' => 'acaricida', 'toxicity_class' => 'III', 'withdrawal_period_days' => 7, 'safety_interval_days' => 7],
        ];

        $ids = [];
        foreach ($products as $p) {
            $ids[] = DB::table('phytosanitary_products')->insertGetId(array_merge($p, [
                'active'      => true,
                'user_id'     => self::PRODUCER_USER_ID,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }
        return $ids;
    }

    // ─── 3. Auto-vínculo winery↔viticultor ───────────────────────────────────

    private function createSelfLink($now): int
    {
        return DB::table('winery_viticulturist')->insertGetId([
            'winery_id'           => self::PRODUCER_USER_ID,
            'viticulturist_id'    => self::PRODUCER_USER_ID,
            'source'              => 'own',
            'notebook_access'     => true,
            'notebook_granted_at' => $now,
            'assigned_by'         => self::PRODUCER_USER_ID,
            'notes'               => 'Auto-vínculo: productor que es su propia bodega.',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
    }

    // ─── 4. Parcelas + SIGPAC + Geometría ────────────────────────────────────
    // 460 recintos del JSON asignados todos a viticulturist_id=339.

    private const MUN_DB_IDS = [
        'Agaete'   => 5243,
        'Agüimes'  => 5244,
        'Artenara' => 5247,
        'Arucas'   => 5248,
        'Firgas'   => 5250,
        'Gáldar'   => 5251,
        'Ingenio'  => 5253,
    ];

    private const PREFIJOS = ['Finca', 'Parcela', 'Viña', 'Viñedo', 'Pago', 'Suerte', 'Lote'];

    private function createPlotsWithSigpac($now): array
    {
        $jsonPath = database_path('seeders/data/sigpac_gran_canaria.json');
        $recs     = json_decode(file_get_contents($jsonPath), true);

        $ids = [];

        foreach ($recs as $index => $rec) {
            $munDbId   = self::MUN_DB_IDS[$rec['mun_name']] ?? 5243;
            $ineCode   = $rec['ine_code'];
            $polygon   = str_pad($rec['polygon'],  3, '0', STR_PAD_LEFT);
            $parcel    = str_pad($rec['parcel'],   5, '0', STR_PAD_LEFT);
            $enclosure = str_pad($rec['recinto'],  3, '0', STR_PAD_LEFT);

            $code = sprintf(
                '05%02d%03d000000%03d%05d%03d',
                35,
                (int) $ineCode,
                $rec['polygon'],
                $rec['parcel'],
                $rec['recinto']
            );

            $existing = DB::table('sigpac_code')
                ->where('code_province',     '35')
                ->where('code_municipality', str_pad($ineCode, 3, '0', STR_PAD_LEFT))
                ->where('code_polygon',      $polygon)
                ->where('code_plot',         $parcel)
                ->where('code_enclosure',    $enclosure)
                ->first();

            $sigpacId = $existing
                ? $existing->id
                : DB::table('sigpac_code')->insertGetId([
                    'code_autonomous_community' => '05',
                    'code_province'             => '35',
                    'code_municipality'         => str_pad($ineCode, 3, '0', STR_PAD_LEFT),
                    'code_aggregate'            => '000',
                    'code_zone'                 => '000',
                    'code_polygon'              => $polygon,
                    'code_plot'                 => $parcel,
                    'code_enclosure'            => $enclosure,
                    'code'                      => $code,
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ]);

            $geomId = DB::table('plot_geometry')->insertGetId([
                'coordinates' => DB::raw("ST_GeomFromText('" . $rec['wkt'] . "', 4326)"),
                'centroid'    => DB::raw("ST_Centroid(ST_GeomFromText('" . $rec['wkt'] . "', 4326))"),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $prefijo  = self::PREFIJOS[$index % count(self::PREFIJOS)];
            $munShort = explode(' ', $rec['mun_name'])[0];
            $plotName = "$prefijo $munShort " . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            $plotId = DB::table('plots')->insertGetId([
                'name'                    => $plotName,
                'viticulturist_id'        => self::PRODUCER_USER_ID,
                'area'                    => $rec['area_ha'] > 0 ? $rec['area_ha'] : round(mt_rand(10, 350) / 100, 2),
                'active'                  => true,
                'autonomous_community_id' => self::AC_ID,
                'province_id'             => self::PROVINCE_ID,
                'municipality_id'         => $munDbId,
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);

            DB::table('multipart_plot_sigpac')->insert([
                'plot_id'          => $plotId,
                'sigpac_code_id'   => $sigpacId,
                'plot_geometry_id' => $geomId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            $ids[] = $plotId;
        }

        return $ids;
    }

    // ─── 5. Plantaciones ──────────────────────────────────────────────────────

    private function createPlantings(array $plotIds, $now): array
    {
        $varieties = DB::table('grape_varieties')
            ->whereIn('name', ['Listán Negro', 'Listán Blanco', 'Baboso Negro', 'Marmajuelo', 'Vijariego'])
            ->pluck('id', 'name');

        $negro    = $varieties['Listán Negro']  ?? 1;
        $blanco   = $varieties['Listán Blanco'] ?? $negro;
        $baboso   = $varieties['Baboso Negro']  ?? $negro;
        $marmaj   = $varieties['Marmajuelo']    ?? $blanco;
        $vijarieg = $varieties['Vijariego']     ?? $blanco;

        $base = ['status' => 'active', 'active' => true, 'right_type' => 'replantacion', 'created_at' => $now, 'updated_at' => $now];

        $plantings = [
            // Parcela 0 — El Teide
            ['plot_id' => $plotIds[0], 'grape_variety_id' => $negro,    'area_planted' => 0.700, 'planting_year' => 2008, 'vine_count' => 700,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => '110R', 'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Tacoronte-Acentejo', 'notes' => 'Listán Negro para tinto joven y crianza.'],
            ['plot_id' => $plotIds[0], 'grape_variety_id' => $baboso,   'area_planted' => 0.500, 'planting_year' => 2010, 'vine_count' => 500,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => '110R', 'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Tacoronte-Acentejo', 'notes' => 'Baboso Negro para vino de autor.'],
            // Parcela 1 — La Caldera
            ['plot_id' => $plotIds[1], 'grape_variety_id' => $negro,    'area_planted' => 0.550, 'planting_year' => 1995, 'vine_count' => 440,  'row_spacing' => 2.5, 'vine_spacing' => 1.5, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Tacoronte-Acentejo', 'notes' => 'Viña vieja. Producción baja y concentrada.'],
            ['plot_id' => $plotIds[1], 'grape_variety_id' => $blanco,   'area_planted' => 0.300, 'planting_year' => 2000, 'vine_count' => 240,  'row_spacing' => 2.5, 'vine_spacing' => 1.5, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Tacoronte-Acentejo'],
            // Parcela 2 — Las Cañadas (ecológico)
            ['plot_id' => $plotIds[2], 'grape_variety_id' => $baboso,   'area_planted' => 0.400, 'planting_year' => 2003, 'vine_count' => 320,  'row_spacing' => 3.0, 'vine_spacing' => 2.0, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Tacoronte-Acentejo', 'notes' => 'Ecológico. Sin fitosanitarios de síntesis.'],
            ['plot_id' => $plotIds[2], 'grape_variety_id' => $negro,    'area_planted' => 0.300, 'planting_year' => 2005, 'vine_count' => 240,  'row_spacing' => 3.0, 'vine_spacing' => 2.0, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Tacoronte-Acentejo'],
            // Parcela 3 — Aguamansa
            ['plot_id' => $plotIds[3], 'grape_variety_id' => $marmaj,   'area_planted' => 0.600, 'planting_year' => 2012, 'vine_count' => 600,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => 'SO4',        'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Tacoronte-Acentejo', 'notes' => 'Marmajuelo para blanco fresco.'],
            ['plot_id' => $plotIds[3], 'grape_variety_id' => $vijarieg, 'area_planted' => 0.450, 'planting_year' => 2014, 'vine_count' => 450,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => 'SO4',        'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Tacoronte-Acentejo', 'notes' => 'Vijariego blanco. Aromático.'],
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
            'viticulturist_id'        => self::PRODUCER_USER_ID,
            'winery_viticulturist_id' => $wvId,
            'mid_validation_signed'   => false,
            'final_validation_signed' => false,
            'created_at'              => $now,
            'updated_at'              => $now,
        ];

        $id2025 = DB::table('campaigns')->insertGetId(array_merge($base, [
            'name'                    => 'Campaña 2025',
            'year'                    => 2025,
            'start_date'              => '2025-01-01',
            'end_date'                => '2025-12-31',
            'active'                  => false,
            'description'             => 'Campaña 2025 cerrada. Vendimia: 3.820 kg cosechados.',
            'final_validation_signed' => true,
            'final_validation_date'   => '2025-11-20 10:00:00',
            'final_validation_user_id' => self::PRODUCER_USER_ID,
        ]));

        $id2026 = DB::table('campaigns')->insertGetId(array_merge($base, [
            'name'        => 'Campaña 2026',
            'year'        => 2026,
            'start_date'  => '2026-01-01',
            'end_date'    => '2026-12-31',
            'active'      => true,
            'description' => 'Campaña 2026 activa. Poda completada. Inicio de brotación.',
        ]));

        return [$id2025, $id2026];
    }

    // ─── 7a. Actividades 2025 ─────────────────────────────────────────────────

    private function createActivities2025(array $plotIds, array $plantingIds, array $productIds, int $wvId, int $campaignId, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;

        // Poda (solo los primeros 4 plots que tienen plantaciones)
        foreach (array_slice($plotIds, 0, 4) as $i => $pid) {
            $actId = DB::table('agricultural_activities')->insertGetId([
                'viticulturist_id'        => $uid,
                'winery_viticulturist_id' => $wvId,
                'campaign_id'             => $campaignId,
                'plot_id'                 => $pid,
                'plot_planting_id'        => $plantingIds[$i * 2],
                'activity_type'           => 'pruning',
                'activity_date'           => '2025-02-' . str_pad($i * 5 + 5, 2, '0', STR_PAD_LEFT),
                'notes'                   => 'Poda de invierno. Carga ajustada a 8 yemas/cepa.',
                'created_at'              => $now, 'updated_at' => $now,
            ]);
            DB::table('cultural_works')->insert([
                'activity_id'         => $actId,
                'work_type'           => 'pruning',
                'description'         => 'Tijera manual + tijera neumática. Sin incidencias.',
                'hours_worked'        => 6.0,
                'residue_management'  => 'triturado_superficie',
                'created_at'          => $now, 'updated_at' => $now,
            ]);
        }

        // Tratamiento mildiu
        $actId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'plot_id' => $plotIds[0],
            'plot_planting_id' => $plantingIds[0],
            'activity_type' => 'phytosanitary_treatment', 'activity_date' => '2025-04-15',
            'notes' => 'Tratamiento preventivo mildiu. Brotación 4-5 hojas.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('phytosanitary_treatments')->insert([
            'activity_id' => $actId, 'product_id' => $prod0,
            'dose_per_hectare' => 2.5, 'application_method' => 'pulverizador_hidraulico',
            'target_pest' => 'Mildiu', 'humidity' => 65.0,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Tratamiento oídio
        $actId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'plot_id' => $plotIds[1],
            'plot_planting_id' => $plantingIds[2],
            'activity_type' => 'phytosanitary_treatment', 'activity_date' => '2025-05-10',
            'notes' => 'Tratamiento oídio. Racimos visibles.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('phytosanitary_treatments')->insert([
            'activity_id' => $actId, 'product_id' => $prod1,
            'dose_per_hectare' => 0.3, 'application_method' => 'pulverizador_hidraulico',
            'target_pest' => 'Oídio', 'humidity' => 40.0,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Riegos (parcelas con riego)
        foreach ([0, 3] as $i) {
            foreach (['2025-06-01', '2025-07-01', '2025-07-20'] as $date) {
                $actId = DB::table('agricultural_activities')->insertGetId([
                    'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
                    'campaign_id' => $campaignId, 'plot_id' => $plotIds[$i],
                    'plot_planting_id' => $plantingIds[$i * 2],
                    'activity_type' => 'irrigation', 'activity_date' => $date,
                    'notes' => 'Riego de apoyo por goteo.',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('irrigations')->insert([
                    'activity_id' => $actId, 'irrigation_method' => 'goteo',
                    'duration_minutes' => 240, 'water_volume' => 800,
                    'water_volume_unit' => 'L',
                    'water_source' => 'Red de riego agrícola',
                    'is_fertirrigation' => false,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // Fertilización
        $actId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'plot_id' => $plotIds[0],
            'plot_planting_id' => $plantingIds[0],
            'activity_type' => 'fertilization', 'activity_date' => '2025-03-20',
            'notes' => 'Abonado de fondo. Incorporación al suelo.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('fertilizations')->insert([
            'activity_id' => $actId, 'fertilizer_name' => 'NPK 8-15-15',
            'fertilizer_type' => 'mineral', 'npk_ratio' => '8-15-15',
            'quantity' => 300.0, 'application_method' => 'incorporado_suelo',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Vendimia 2025 (solo los primeros 4 plots que tienen plantaciones)
        $containerVendimia = DB::table('containers')->where('user_id', $uid)->value('id');
        foreach (array_slice($plotIds, 0, 4) as $i => $pid) {
            $actId = DB::table('agricultural_activities')->insertGetId([
                'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
                'campaign_id' => $campaignId, 'plot_id' => $pid,
                'plot_planting_id' => $plantingIds[$i * 2],
                'activity_type' => 'harvest', 'activity_date' => '2025-09-' . str_pad(10 + $i * 3, 2, '0', STR_PAD_LEFT),
                'notes' => 'Vendimia manual. Selección de racimos.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $weight = [920, 780, 640, 1000][$i];
            DB::table('harvests')->insert([
                'activity_id'        => $actId,
                'plot_planting_id'   => $plantingIds[$i * 2],
                'harvest_start_date' => '2025-09-' . str_pad(10 + $i * 3, 2, '0', STR_PAD_LEFT),
                'total_weight'       => $weight,
                'brix_degree'        => 22.5 + $i * 0.5,
                'ph_level'           => 3.35 + $i * 0.02,
                'acidity_level'      => 5.8 - $i * 0.1,
                'status'             => 'active',
                'health_status'      => 'sano',
                'notes'              => 'Uva sana. Sin podredumbre.',
                'created_at'         => $now, 'updated_at' => $now,
            ]);
        }

        // Post-vendimia (tratamiento de madera)
        $actId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'plot_id' => $plotIds[0],
            'plot_planting_id' => $plantingIds[0],
            'activity_type' => 'post_harvest_treatment', 'activity_date' => '2025-10-20',
            'notes' => 'Tratamiento post-vendimia. Protección de heridas de poda.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('post_harvest_treatments')->insert([
            'activity_id' => $actId, 'product_id' => $prod3,
            'application_type' => 'trunk_treatment',
            'treated_area_ha' => 1.200,
            'dose_per_hectare' => 1.5, 'dose_unit' => 'kg/ha',
            'notes' => 'Prevención de excoriosis y eutipiosis.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    // ─── 7b. Actividades 2026 ─────────────────────────────────────────────────

    private function createActivities2026(array $plotIds, array $plantingIds, array $productIds, int $wvId, int $campaignId, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;

        // Poda 2026
        foreach ([0, 1, 2, 3] as $i) {
            $actId = DB::table('agricultural_activities')->insertGetId([
                'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
                'campaign_id' => $campaignId, 'plot_id' => $plotIds[$i],
                'plot_planting_id' => $plantingIds[$i * 2],
                'activity_type' => 'pruning', 'activity_date' => '2026-02-' . str_pad($i * 4 + 3, 2, '0', STR_PAD_LEFT),
                'notes' => 'Poda de invierno 2026. Carga 7-8 yemas/cepa.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('cultural_works')->insert([
                'activity_id' => $actId, 'work_type' => 'pruning',
                'description' => 'Tijera neumática', 'hours_worked' => 5.5,
                'residue_management' => 'triturado_incorporado',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // Tratamiento preventivo mildiu 2026
        $actId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'plot_id' => $plotIds[3],
            'plot_planting_id' => $plantingIds[6],
            'activity_type' => 'phytosanitary_treatment', 'activity_date' => '2026-04-05',
            'notes' => 'Tratamiento preventivo mildiu. Inicio brotación.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('phytosanitary_treatments')->insert([
            'activity_id' => $actId, 'product_id' => $prod0,
            'dose_per_hectare' => 2.5, 'application_method' => 'pulverizador_hidraulico',
            'target_pest' => 'Mildiu', 'humidity' => 70.0,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Observación fenológica
        $actId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'plot_id' => $plotIds[0],
            'plot_planting_id' => $plantingIds[0],
            'activity_type' => 'observation', 'activity_date' => '2026-03-28',
            'notes' => 'Observación inicio brotación.',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('observations')->insert([
            'activity_id'               => $actId,
            'observation_type'          => 'phenology',
            'affected_area_percentage'  => 0.00,
            'threshold_exceeded'        => false,
            'description'               => 'Inicio brotación 15-20% yemas. Normal para la altitud.',
            'created_at'                => $now, 'updated_at' => $now,
        ]);
    }

    // ─── 8. Fenología ─────────────────────────────────────────────────────────

    private function createPhenology(array $plantingIds, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $stages2025 = [
            ['stage' => 'budbreak',     'date' => '2025-03-10', 'note' => 'Brotación inicio. Temp acumuladas normales.'],
            ['stage' => 'flowering',    'date' => '2025-04-28', 'note' => 'Floración plena. Sin heladas.'],
            ['stage' => 'fruit_set',    'date' => '2025-05-18', 'note' => 'Cuajado normal. 70% flores.'],
            ['stage' => 'veraison',     'date' => '2025-07-25', 'note' => 'Envero. Cambio de color homogéneo.'],
            ['stage' => 'harvest',      'date' => '2025-09-08', 'note' => 'Madurez óptima. Brix 22-24.'],
        ];
        foreach ($plantingIds as $i => $plantingId) {
            foreach ($stages2025 as $s) {
                DB::table('phenology_observations')->insert([
                    'viticulturist_id' => $uid, 'plot_planting_id' => $plantingId,
                    'campaign_id' => $c2025, 'event' => $s['stage'],
                    'obs_date' => $s['date'], 'notes' => $s['note'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
        // 2026 parcial
        foreach (array_slice($plantingIds, 0, 2) as $plantingId) {
            DB::table('phenology_observations')->insert([
                'viticulturist_id' => $uid, 'plot_planting_id' => $plantingId,
                'campaign_id' => $c2026, 'event' => 'budbreak',
                'obs_date' => '2026-03-15', 'notes' => 'Brotación 2026 en curso.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 9. Plagas ────────────────────────────────────────────────────────────

    private function createPests(array $productIds, $now): array
    {
        $uid = self::PRODUCER_USER_ID;
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;

        $pests = [
            ['name' => 'Polilla del racimo (Orotava)',          'scientific_name' => 'Lobesia botrana',     'type' => 'pest',    'control_methods' => ['biologico','quimico'],  'product_id' => $prod2],
            ['name' => 'Mildiu de la vid (Orotava)',            'scientific_name' => 'Plasmopara viticola', 'type' => 'disease', 'control_methods' => ['quimico','cultural'],   'product_id' => $prod0],
            ['name' => 'Oídio de la vid (Orotava)',             'scientific_name' => 'Erysiphe necator',    'type' => 'disease', 'control_methods' => ['quimico','cultural'],   'product_id' => $prod1],
            ['name' => 'Araña roja (Orotava)',                  'scientific_name' => 'Panonychus ulmi',     'type' => 'pest',    'control_methods' => ['biologico','quimico'],  'product_id' => $prod4],
            ['name' => 'Botrytis / Podredumbre gris (Orotava)', 'scientific_name' => 'Botrytis cinerea',    'type' => 'disease', 'control_methods' => ['cultural','quimico'],   'product_id' => $prod1],
            ['name' => 'Excoriosis (Orotava)',                  'scientific_name' => 'Phomopsis viticola',  'type' => 'disease', 'control_methods' => ['cultural','quimico'],   'product_id' => $prod3],
        ];

        $ids = [];
        foreach ($pests as $p) {
            $productEff = $p['product_id'];
            unset($p['product_id']);
            $pestId = DB::table('pests')->insertGetId(array_merge($p, [
                'control_methods' => json_encode($p['control_methods']),
                'active'          => true,
                'created_at'      => $now, 'updated_at' => $now,
            ]));
            DB::table('pest_product_effectiveness')->insert([
                'pest_id'            => $pestId,
                'product_id'         => $productEff,
                'effectiveness_rating' => 4,
                'notes'              => 'Eficacia contrastada en condiciones locales.',
                'created_at'         => $now, 'updated_at' => $now,
            ]);
            $ids[] = $pestId;
        }
        return $ids;
    }

    // ─── 10. Rendimientos estimados ───────────────────────────────────────────

    private function createEstimatedYields(array $plantingIds, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $yields2025 = [2100, 1800, 1500, 1900, 1400, 1300, 2400, 2200];
        $yields2026 = [2200, 1850, 1550, 2000, 1450, 1350, 2500, 2300];
        foreach ($plantingIds as $i => $pid) {
            DB::table('estimated_yields')->insert([
                'plot_planting_id'           => $pid, 'campaign_id' => $c2025,
                'estimated_by'               => $uid,
                'estimated_total_yield'      => $yields2025[$i] ?? 1600,
                'estimated_yield_per_hectare'=> 1500,
                'estimation_date'            => '2025-07-15',
                'estimation_method'          => 'sampling',
                'notes'                      => 'Aforo verano 2025.',
                'created_at'                 => $now, 'updated_at' => $now,
            ]);
            DB::table('estimated_yields')->insert([
                'plot_planting_id'           => $pid, 'campaign_id' => $c2026,
                'estimated_by'               => $uid,
                'estimated_total_yield'      => $yields2026[$i] ?? 1700,
                'estimated_yield_per_hectare'=> 1500,
                'estimation_date'            => '2026-04-01',
                'estimation_method'          => 'historical',
                'notes'                      => 'Estimación inicial basada en histórico.',
                'created_at'                 => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 11. Maquinaria ───────────────────────────────────────────────────────

    private function createMachinery($now): array
    {
        $uid = self::PRODUCER_USER_ID;
        $machines = [
            ['name' => 'Tractor Kubota M5091',         'brand' => 'Kubota',    'model' => 'M5091',          'year' => 2018, 'type' => 'tractor',           'roma_registration' => 'TF-001-P'],
            ['name' => 'Pulverizador Hardi 600L',      'brand' => 'Hardi',     'model' => 'Ranger 600',     'year' => 2019, 'type' => 'sprayer',           'roma_registration' => null],
            ['name' => 'Tijera Neumática Infaco',      'brand' => 'Infaco',    'model' => 'Electrocoup F3015','year' => 2021,'type' => 'pruning_equipment', 'roma_registration' => null],
            ['name' => 'Remolque agrícola 3t',         'brand' => 'Fautras',   'model' => 'Plateau 3T',     'year' => 2017, 'type' => 'trailer',           'roma_registration' => 'TF-002-P'],
            ['name' => 'Despalilladora-estrujadora',   'brand' => 'Diemme',    'model' => 'GD 20',          'year' => 2020, 'type' => 'harvesting_machine','roma_registration' => null],
            ['name' => 'Prensa neumática 20 hl',       'brand' => 'Della Toffola','model' => 'PA 20',       'year' => 2020, 'type' => 'press',             'roma_registration' => null],
            ['name' => 'Bomba de trasiego centrífuga', 'brand' => 'Velo',      'model' => 'CM 50',          'year' => 2019, 'type' => 'pump',              'roma_registration' => null],
            ['name' => 'Equipo de frío 5000 kcal/h',  'brand' => 'Cofrimell', 'model' => 'CF-5000',        'year' => 2021, 'type' => 'cooling_equipment', 'roma_registration' => null],
        ];
        $ids = [];
        foreach ($machines as $m) {
            $ids[] = DB::table('machinery')->insertGetId(array_merge($m, [
                'viticulturist_id' => $uid, 'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }
        return $ids;
    }

    // ─── 12. Explotaciones ────────────────────────────────────────────────────

    private function createExploitations($now): array
    {
        $uid = self::PRODUCER_USER_ID;
        $id = DB::table('exploitations')->insertGetId([
            'viticulturist_id'          => $uid,
            'exploitation_name'         => 'Explotación Viticultora-Bodeguera La Orotava',
            'rea_code'                  => 'REA-TF-2026-' . $uid,
            'siex_exploitation_id'      => 'SIEX-TF-' . $uid,
            'holder_name'               => 'Productor Demo La Orotava',
            'holder_nif'                => 'B38765432',
            'is_ecological'             => false,
            'is_integrated_production'  => false,
            'is_quality_scheme'         => true,
            'quality_scheme_desc'       => 'DO Valle de La Orotava',
            'active'                    => true,
            'created_at'                => $now, 'updated_at' => $now,
        ]);
        return [$id];
    }

    // ─── 13. Compliance ───────────────────────────────────────────────────────

    private function createCompliance(array $exploitationIds, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        // Aplicadores ROPO
        foreach ([
            ['Juan Carlos Hernández', '2025-05-22', 'ROPO-TF-001'],
            ['María Pérez González',   '2024-09-15', 'ROPO-TF-002'],
        ] as [$name, $expiry, $ropo]) {
            DB::table('field_applicators')->insert([
                'viticulturist_id' => $uid,
                'name'             => $name,
                'ropo_number'      => $ropo,
                'ropo_category'    => 'basic',
                'ropo_expiry_date' => $expiry,
                'active'           => true,
                'created_at'       => $now, 'updated_at' => $now,
            ]);
        }
        // Equipos ITB
        DB::table('field_equipment')->insert([
            'viticulturist_id'    => $uid,
            'name'                => 'Pulverizador Hardi Ranger 600L',
            'equipment_type'      => 'sprayer',
            'registration_number' => 'HD-TF-2019-001',
            'last_inspection_date'=> '2024-11-10',
            'next_inspection_date'=> '2026-11-10',
            'active'              => true,
            'created_at'          => $now, 'updated_at' => $now,
        ]);
        // Seguro agrario
        DB::table('agri_insurances')->insert([
            'viticulturist_id' => $uid,
            'policy_number'    => 'AGS-2025-TF-' . $uid,
            'insurance_company'=> 'AGROSEGURO',
            'coverage_type'    => 'comprehensive',
            'premium'          => 1240.00,
            'start_date'       => '2025-01-01',
            'end_date'         => '2025-12-31',
            'status'           => 'expired',
            'created_at'       => $now, 'updated_at' => $now,
        ]);
    }

    // ─── 14. PAC ─────────────────────────────────────────────────────────────

    private function createPAC(array $plotIds, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach ([['2025', $plotIds], ['2026', array_slice($plotIds, 0, 3)]] as [$year, $pids]) {
            $declId = DB::table('pac_declarations')->insertGetId([
                'viticulturist_id'   => $uid,
                'year'               => (int)$year,
                'status'             => $year === '2025' ? 'approved' : 'draft',
                'total_declared_area'=> 3.80,
                'total_eligible_area'=> 3.70,
                'submitted_at'       => $year === '2025' ? "{$year}-04-20 10:00:00" : null,
                'notes'              => "Solicitud PAC {$year}.",
                'created_at'         => $now, 'updated_at' => $now,
            ]);
            foreach ($pids as $plotId) {
                DB::table('pac_declaration_items')->insert([
                    'declaration_id' => $declId,
                    'plot_id'        => $plotId,
                    'declared_area'  => 0.95,
                    'eligible_area'  => 0.92,
                    'eco_schemes'    => json_encode(['ECO-01']),
                    'created_at'     => $now, 'updated_at' => $now,
                ]);
            }
            if ($year === '2025') {
                DB::table('pac_payments')->insert([
                    'viticulturist_id' => $uid,
                    'declaration_id'   => $declId,
                    'year'             => 2025,
                    'payment_type'     => 'basic_payment',
                    'amount'           => 1850.00,
                    'payment_date'     => '2025-12-10',
                    'reference'        => "PAC-{$year}-TF-{$uid}",
                    'created_at'       => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    // ─── 15. Almacén insumos ──────────────────────────────────────────────────

    private function createAlmacen(array $productIds, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $warehouseId = DB::table('warehouses')->insertGetId([
            'user_id'    => $uid,
            'name'       => 'Almacén La Orotava',
            'location'   => 'Camino Real, 12 — La Orotava',
            'active'     => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ($productIds as $i => $prodId) {
            $qty = 8.0 + $i * 2;
            $stockId = DB::table('product_stocks')->insertGetId([
                'warehouse_id' => $warehouseId,
                'user_id'      => $uid,
                'product_id'   => $prodId,
                'quantity'     => $qty,
                'unit'         => 'kg',
                'active'       => true,
                'created_at'   => $now, 'updated_at' => $now,
            ]);
            DB::table('product_stock_movements')->insert([
                'stock_id'        => $stockId,
                'user_id'         => $uid,
                'movement_type'   => 'purchase',
                'quantity_change'  => $qty,
                'quantity_before'  => 0,
                'quantity_after'   => $qty,
                'notes'            => 'Compra inicio temporada 2025.',
                'created_at'       => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 16. Contenedores bodega (depósitos + barricas) ──────────────────────

    private function createWineryContainers($now): array
    {
        $uid  = self::PRODUCER_USER_ID;
        $typeIds = DB::table('container_types')
            ->whereIn('name', ['Depósito acero inoxidable', 'Barrica francesa', 'Depósito polivalente'])
            ->pluck('id', 'name');
        $deposito   = $typeIds['Depósito acero inoxidable'] ?? $typeIds->first() ?? 1;
        $barrica    = $typeIds['Barrica francesa']           ?? $deposito;
        $polivalent = $typeIds['Depósito polivalente']       ?? $deposito;

        $containers = [];
        // 15 depósitos acero
        for ($i = 1; $i <= 15; $i++) {
            $containers[] = [
                'user_id'     => $uid,
                'name'        => sprintf('Depósito D%02d', $i),
                'type_id'     => $deposito,
                'capacity'    => [5000, 8000, 10000, 12000, 6000][$i % 5],
                'description' => 'Nave elaboración',
                'created_at'  => $now, 'updated_at' => $now,
            ];
        }
        // 10 barricas
        for ($i = 1; $i <= 10; $i++) {
            $containers[] = [
                'user_id'     => $uid,
                'name'        => sprintf('Barrica B%02d', $i),
                'type_id'     => $barrica,
                'capacity'    => 225,
                'description' => 'Sala barricas',
                'created_at'  => $now, 'updated_at' => $now,
            ];
        }
        // 5 depósitos polivalentes (campo + bodega)
        for ($i = 1; $i <= 5; $i++) {
            $containers[] = [
                'user_id'     => $uid,
                'name'        => sprintf('Bins Vendimia BV%02d', $i),
                'type_id'     => $polivalent,
                'capacity'    => 800,
                'description' => 'Patio exterior',
                'created_at'  => $now, 'updated_at' => $now,
            ];
        }

        $ids = [];
        foreach ($containers as $c) {
            $ids[] = DB::table('containers')->insertGetId($c);
        }
        return $ids;
    }

    // ─── 17. Vinos ───────────────────────────────────────────────────────────

    private function createWines($now): array
    {
        $uid = self::PRODUCER_USER_ID;
        $wines = [
            ['name' => 'Orotava Tinto Joven 2025',     'wine_type' => 'red',   'vintage' => 2025, 'variety' => 'Listán Negro 80%, Baboso Negro 20%', 'volume_liters' => 3200.0, 'notes' => 'DO Tacoronte-Acentejo. Tinto joven frutal. Sin crianza.'],
            ['name' => 'Orotava Tinto Crianza 2024',   'wine_type' => 'red',   'vintage' => 2024, 'variety' => 'Listán Negro 70%, Baboso Negro 30%', 'volume_liters' => 1800.0, 'notes' => 'DO Tacoronte-Acentejo. 6 meses barrica francesa.'],
            ['name' => 'Orotava Baboso de Autor 2024', 'wine_type' => 'red',   'vintage' => 2024, 'variety' => 'Baboso Negro 100%',                  'volume_liters' => 800.0,  'notes' => 'DO Tacoronte-Acentejo. Vino de autor. 10 meses barrica nueva.'],
            ['name' => 'Orotava Blanco Marmajuelo 2025','wine_type' => 'white', 'vintage' => 2025, 'variety' => 'Marmajuelo 100%',                   'volume_liters' => 2400.0, 'notes' => 'DO Tacoronte-Acentejo. Blanco fresco y aromático. Sin madera.'],
            ['name' => 'Orotava Vijariego 2025',       'wine_type' => 'white', 'vintage' => 2025, 'variety' => 'Vijariego 100%',                     'volume_liters' => 1600.0, 'notes' => 'DO Tacoronte-Acentejo. Blanco varietal. Elaborado en frío.'],
            ['name' => 'Orotava Rosado 2025',          'wine_type' => 'rose',  'vintage' => 2025, 'variety' => 'Listán Negro 100%',                  'volume_liters' => 1200.0, 'notes' => 'DO Tacoronte-Acentejo. Rosado de sangrado. Salmón intenso.'],
            ['name' => 'Orotava Listán Blanco 2025',   'wine_type' => 'white', 'vintage' => 2025, 'variety' => 'Listán Blanco 100%',                 'volume_liters' => 900.0,  'notes' => 'DO Tacoronte-Acentejo. Blanco tradicional. Suave y equilibrado.'],
            ['name' => 'Orotava Ecológico Tinto 2024', 'wine_type' => 'red',   'vintage' => 2024, 'variety' => 'Baboso Negro 60%, Listán Negro 40%', 'volume_liters' => 600.0,  'notes' => 'DO Tacoronte-Acentejo. Vino ecológico certificado.'],
        ];

        $ids = [];
        foreach ($wines as $w) {
            $ids[] = DB::table('wines')->insertGetId(array_merge($w, [
                'user_id'    => $uid,
                'status'     => 'in_progress',
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }
        return $ids;
    }

    // ─── 18. Controles fermentación ───────────────────────────────────────────

    private function createFermentationControls(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $dates = ['2025-09-12', '2025-09-15', '2025-09-18', '2025-09-21', '2025-09-25'];
        foreach (array_slice($wineIds, 0, 4) as $wi => $wineId) {
            $containerId = $containerIds[$wi] ?? $containerIds[0];
            foreach ($dates as $di => $date) {
                DB::table('wine_fermentation_controls')->insert([
                    'wine_id'          => $wineId,
                    'container_id'     => $containerId,
                    'control_date'     => $date . ' 08:00:00',
                    'temperature'      => round(18.0 + $di * 0.5 + $wi * 0.3, 2),
                    'density'          => round(1.080 - $di * 0.020 - $wi * 0.005, 4),
                    'brix_degree'      => round(22.0 - $di * 3.5, 2),
                    'ph'               => round(3.3 + $di * 0.05, 2),
                    'volatile_acidity' => round(0.15 + $di * 0.03, 2),
                    'notes'            => "Fermentación normal. Día " . ($di + 1) . ".",
                    'created_by'       => $uid,
                    'created_at'       => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    // ─── 19. Traslados de vino ────────────────────────────────────────────────

    private function createWineTransfers(array $wineIds, array $containerIds, $now): void
    {
        $uid    = self::PRODUCER_USER_ID;
        $unitId = DB::table('units_of_measurement')->where('symbol', 'L')->value('id') ?? 1;
        foreach (array_slice($wineIds, 0, 4) as $wi => $wineId) {
            $fromId = $containerIds[$wi]      ?? $containerIds[0];
            $toId   = $containerIds[$wi + 15] ?? $containerIds[1];
            DB::table('wine_transfers')->insert([
                'wine_id'               => $wineId,
                'from_container_id'     => $fromId,
                'to_container_id'       => $toId,
                'transfer_date'         => '2025-10-' . str_pad(5 + $wi * 3, 2, '0', STR_PAD_LEFT),
                'quantity'              => 800.0 + $wi * 200,
                'unit_of_measurement_id'=> $unitId,
                'transfer_type'         => 'racking',
                'notes'                 => 'Traslado post-fermentación a depósito limpio. Sin incidencias.',
                'created_by'            => $uid,
                'created_at'            => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 19b. Mermas de vino ──────────────────────────────────────────────────

    private function createWineLosses(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $lossTypes = ['evaporation', 'other', 'sampling', 'evaporation', 'other'];
        $unitId    = DB::table('units_of_measurement')->where('symbol', 'L')->value('id') ?? 1;
        foreach (array_slice($wineIds, 0, 5) as $wi => $wineId) {
            DB::table('wine_losses')->insert([
                'wine_id'               => $wineId,
                'container_id'          => $containerIds[$wi] ?? $containerIds[0],
                'loss_date'             => '2025-11-' . str_pad(5 + $wi * 4, 2, '0', STR_PAD_LEFT),
                'quantity'              => 20.0 + $wi * 5,
                'unit_of_measurement_id'=> $unitId,
                'loss_type'             => $lossTypes[$wi],
                'notes'                 => 'Merma registrada.',
                'created_by'            => $uid,
                'created_at'            => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 20. Embotellamientos ─────────────────────────────────────────────────

    private function createBottlings(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $bottlings = [
            ['wine_id' => $wineIds[0], 'date' => '2025-11-20', 'bottles' => 4200, 'vol' => 0.75, 'liters' => 3150.0, 'notes' => 'Tinto joven primera tirada.'],
            ['wine_id' => $wineIds[3], 'date' => '2025-11-25', 'bottles' => 3100, 'vol' => 0.75, 'liters' => 2325.0, 'notes' => 'Blanco Marmajuelo.'],
            ['wine_id' => $wineIds[5], 'date' => '2025-12-02', 'bottles' => 1550, 'vol' => 0.75, 'liters' => 1162.5, 'notes' => 'Rosado primera tirada.'],
            ['wine_id' => $wineIds[1], 'date' => '2026-02-15', 'bottles' => 2300, 'vol' => 0.75, 'liters' => 1725.0, 'notes' => 'Crianza 6 meses. Embotellado tras madera.'],
        ];
        foreach ($bottlings as $i => $b) {
            $lotNumber  = 'LOT-' . date('Ymd', strtotime($b['date'])) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $bottlingId = DB::table('wine_bottlings')->insertGetId([
                'user_id'          => $uid,
                'wine_id'          => $b['wine_id'],
                'container_id'     => $containerIds[$i] ?? $containerIds[0],
                'bottling_date'    => $b['date'],
                'bottle_format'    => '750',
                'quantity_bottles' => $b['bottles'],
                'quantity_liters'  => $b['liters'],
                'lot_number'       => $lotNumber,
                'notes'            => $b['notes'],
                'created_by'       => $uid,
                'created_at'       => $now, 'updated_at' => $now,
            ]);
            // Lote de producto
            $lotQty = round($b['liters'], 3);
            DB::table('wine_lots')->insert([
                'user_id'           => $uid,
                'wine_id'           => $b['wine_id'],
                'name'              => $lotNumber,
                'vintage'           => (int)substr($b['date'], 0, 4),
                'wine_type'         => 'tinto',
                'quantity'          => $lotQty,
                'initial_quantity'  => $lotQty,
                'available_quantity'=> $lotQty,
                'unit'              => 'litros',
                'bottling_date'     => $b['date'],
                'archived'          => false,
                'created_at'        => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 21. Clientes ─────────────────────────────────────────────────────────

    private function createClients($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $clients = [
            ['company_name' => 'Restaurantes Canarios SL',     'email' => 'compras@restcanarios.es',  'phone' => '922100001', 'nif' => 'B38100001'],
            ['company_name' => 'Distribuidora Atlántico SLU',  'email' => 'pedidos@disatlantico.es',  'phone' => '922100002', 'nif' => 'B38100002'],
            ['first_name' => 'Pedro', 'last_name' => 'Suárez Martín', 'email' => 'pedro.suarez@gmail.com', 'phone' => '608100001', 'nif' => '45100001T'],
            ['company_name' => 'Wine Export Tenerife SL',      'email' => 'export@wineexporttf.es',   'phone' => '922100003', 'nif' => 'B38100003'],
            ['first_name' => 'Laura', 'last_name' => 'Álvarez Pérez', 'email' => 'laura.alvarez@gmail.com', 'phone' => '608100002', 'nif' => '45100002P'],
        ];
        foreach ($clients as $c) {
            DB::table('clients')->insert(array_merge($c, [
                'user_id'    => $uid,
                'active'     => true,
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }
    }

    // ─── 22. Facturas mixtas ──────────────────────────────────────────────────

    private function createInvoices(int $campaignId, $now): void
    {
        $uid     = self::PRODUCER_USER_ID;
        $clients = DB::table('clients')->where('user_id', $uid)->pluck('id');
        if ($clients->isEmpty()) return;

        $invoiceData = [
            ['type' => 'wine_sale', 'date' => '2025-12-01', 'total' => 4200.00,  'client_idx' => 0, 'desc' => 'Venta tinto joven 2025'],
            ['type' => 'wine_sale', 'date' => '2025-12-10', 'total' => 3100.00,  'client_idx' => 1, 'desc' => 'Venta blanco Marmajuelo 2025'],
            ['type' => 'wine_sale', 'date' => '2026-01-15', 'total' => 1800.00,  'client_idx' => 2, 'desc' => 'Venta rosado 2025'],
            ['type' => 'wine_sale', 'date' => '2026-02-20', 'total' => 5200.00,  'client_idx' => 3, 'desc' => 'Venta crianza 2024 — exportación'],
            ['type' => 'harvest_sale', 'date' => '2025-10-05', 'total' => 1250.00, 'client_idx' => 0, 'desc' => 'Venta cosecha uva tinto'],
            ['type' => 'harvest_sale', 'date' => '2025-10-12', 'total' => 980.00,  'client_idx' => 1, 'desc' => 'Venta cosecha uva blanco'],
        ];

        foreach ($invoiceData as $i => $inv) {
            $clientId = $clients[$inv['client_idx']] ?? $clients->first();
            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'      => $uid,
                'client_id'    => $clientId,
                'campaign_id'  => $campaignId,
                'invoice_type' => $inv['type'],
                'invoice_number' => 'F-' . date('Y', strtotime($inv['date'])) . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date' => $inv['date'],
                'status'       => 'paid',
                'total_amount' => $inv['total'],
                'notes'        => $inv['desc'],
                'created_at'   => $now, 'updated_at' => $now,
            ]);
            DB::table('invoice_items')->insert([
                'invoice_id'  => $invoiceId,
                'name'        => $inv['desc'],
                'description' => $inv['desc'],
                'quantity'    => 1,
                'unit'        => 'partida',
                'unit_price'  => $inv['total'],
                'total_price' => $inv['total'],
                'created_at'  => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 23. Notas de cata ────────────────────────────────────────────────────

    private function createTastingNotes(array $wineIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $notes = [
            ['color' => 'rojo cereza brillante', 'aroma' => 'Frutas rojas frescas, frambuesa, violeta.', 'palate' => 'Entrada amable, fresco, taninos suaves. Final frutal.', 'score' => 88],
            ['color' => 'rojo rubí con ribete granate', 'aroma' => 'Frutas negras maduras, especias, madera integrada.', 'palate' => 'Estructurado, taninos maduros, largo final.', 'score' => 91],
            ['color' => 'rojo granate profundo', 'aroma' => 'Mora, ciruela, tabaco, vainilla.', 'palate' => 'Complejo, untuoso, taninos sedosos. Muy largo.', 'score' => 93],
            ['color' => 'amarillo pajizo con destellos dorados', 'aroma' => 'Flor blanca, cítricos, hierbas aromáticas.', 'palate' => 'Fresco, mineral, acidez viva. Posgusto largo.', 'score' => 90],
            ['color' => 'amarillo pálido', 'aroma' => 'Flores blancas, fruta tropical, manzana verde.', 'palate' => 'Ligero, fresco, delicado.', 'score' => 87],
            ['color' => 'salmón rosáceo intenso', 'aroma' => 'Fresas, frambuesa, pétalos de rosa.', 'palate' => 'Carnoso, frutal, frescura equilibrada.', 'score' => 89],
        ];
        foreach (array_slice($wineIds, 0, 6) as $wi => $wineId) {
            $n = $notes[$wi];
            DB::table('wine_tasting_notes')->insert([
                'user_id'       => $uid,
                'wine_id'       => $wineId,
                'tasting_date'  => '2025-12-' . str_pad(1 + $wi * 3, 2, '0', STR_PAD_LEFT),
                'taster_name'   => 'Productor La Orotava',
                'color_notes'   => $n['color'],
                'aroma_notes'   => $n['aroma'],
                'palate_notes'  => $n['palate'],
                'overall_score' => $n['score'],
                'created_at'    => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── Resumen final ────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $uid = self::PRODUCER_USER_ID;
        $this->command->info('');
        $this->command->info('📊 Resumen datos creados:');
        $stats = [
            'Parcelas'            => DB::table('plots')->where('viticulturist_id', $uid)->count(),
            'Plantaciones'        => DB::table('plot_plantings')->whereIn('plot_id', DB::table('plots')->where('viticulturist_id', $uid)->pluck('id'))->count(),
            'Campañas'            => DB::table('campaigns')->where('viticulturist_id', $uid)->count(),
            'Actividades campo'   => DB::table('agricultural_activities')->where('viticulturist_id', $uid)->count(),
            'Contenedores bodega' => DB::table('containers')->where('user_id', $uid)->count(),
            'Vinos'               => DB::table('wines')->where('user_id', $uid)->count(),
            'Embotellamientos'    => DB::table('wine_bottlings')->where('user_id', $uid)->count(),
            'Clientes'            => DB::table('clients')->where('user_id', $uid)->count(),
            'Facturas'            => DB::table('invoices')->where('user_id', $uid)->count(),
        ];
        foreach ($stats as $label => $count) {
            $icon = $count > 0 ? '  ✅' : '  ⚠️ ';
            $this->command->info("{$icon} {$label}: {$count}");
        }
    }
}
