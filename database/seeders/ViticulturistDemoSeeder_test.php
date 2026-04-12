<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder demo completo para el rol Viticultor (user_id = 338).
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
class ViticulturistDemoSeeder_test extends Seeder
{
    private const VIT_USER_ID    = 338;
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

        // ── 0. Verificar / crear usuarios demo ───────────────────────────────
        $this->ensureDemoUsers($now);

        $vit    = DB::table('users')->find(self::VIT_USER_ID);
        $winery = DB::table('users')->find(self::WINERY_USER_ID);
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

        // ── 4. Parcelas + SIGPAC + Geometría (JSON completo) ─────────────────
        $plotIds = [];
        $this->step('Parcelas + SIGPAC + geometría (460 recintos Gran Canaria)', function () use ($now, &$plotIds) {
            $plotIds = $this->createPlotsWithSigpac($now);
        });

        // ── 5. Plantaciones ────────────────────────────────────────────────────
        $plantingIds = [];
        $this->step('Plantaciones (10)', function () use ($now, $plotIds, &$plantingIds) {
            $plantingIds = $this->createPlantings($plotIds, $now);
        });

        // ── 6. Campañas ────────────────────────────────────────────────────────
        $campaign2024Id = 0;
        $campaign2025Id = 0;
        $campaign2026Id = 0;
        $this->step('Campañas (2024 cerrada + 2025 cerrada + 2026 activa)', function () use ($now, $wvId, &$campaign2024Id, &$campaign2025Id, &$campaign2026Id) {
            [$campaign2024Id, $campaign2025Id, $campaign2026Id] = $this->createCampaigns($wvId, $now);
        });

        // ── 7. Actividades de campo ────────────────────────────────────────────
        $this->step('Actividades 2024 (~42)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2024Id) {
            $this->createActivities2024($plotIds, $plantingIds, $productIds, $wvId, $campaign2024Id, $now);
        });

        $this->step('Actividades 2025 (~23)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id) {
            $this->createActivities2025($plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id, $now);
        });

        $this->step('Actividades 2026 (~20)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2026Id) {
            $this->createActivities2026($plotIds, $plantingIds, $productIds, $wvId, $campaign2026Id, $now);
        });

        // ── 8. Fenología ───────────────────────────────────────────────────────
        $this->step('Observaciones fenológicas', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createPhenology($plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 9. Plagas y enfermedades ──────────────────────────────────────────
        $pestIds = [];
        $this->step('Plagas y enfermedades (8)', function () use ($now, $productIds, &$pestIds) {
            $pestIds = $this->createPests($productIds, $now);
        });

        // ── 10. Rendimientos estimados ────────────────────────────────────────
        $this->step('Rendimientos estimados', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createEstimatedYields($plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 11. Contenedores (crítico para vendimias y facturación) ──────────
        $containerIds = [];
        $this->step('Contenedores (50: bins + tinas + depósitos + tanques)', function () use ($now, &$containerIds) {
            $containerIds = $this->createContainers($now);
        });

        // ── 12. Maquinaria ────────────────────────────────────────────────────
        $machineryIds = [];
        $this->step('Maquinaria (12)', function () use ($now, &$machineryIds) {
            $machineryIds = $this->createMachinery($now);
        });

        // ── 13. Explotaciones SIEX/REA ────────────────────────────────────────
        $exploitationIds = [];
        $this->step('Explotaciones SIEX/REA (2)', function () use ($now, &$exploitationIds) {
            $exploitationIds = $this->createExploitations($now);
        });

        // ── 14. Compliance: aplicadores, equipos, asesorías, autorizaciones, seguros
        $this->step('Compliance (5+6+4+6+6 = 27 registros)', function () use ($now, $exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createCompliance($exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 15. Clientes ──────────────────────────────────────────────────────
        $this->step('Clientes (8)', function () use ($now) {
            $this->createClients($now);
        });

        // ── 16. Subcontrataciones ─────────────────────────────────────────────
        $this->step('Subcontrataciones (20)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createSubcontractings($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 17. Registros Oficiales (~120 registros) ──────────────────────────
        $this->step('Registros Oficiales (~120 registros)', function () use ($now, $plantingIds, $plotIds, $productIds, $machineryIds, $exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createRegistrosOficiales($plantingIds, $plotIds, $productIds, $machineryIds, $exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 18. PAC ───────────────────────────────────────────────────────────
        $this->step('PAC (3 declaraciones + 15 items + 12 pagos)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createPAC($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 19. Facturación ───────────────────────────────────────────────────
        $this->step('Facturación (45 costes parcela + 10 cosecha comercializada)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createBilling($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 20. Datos operacionales ───────────────────────────────────────────
        $this->step('Datos operacionales (15 docs campaña + 10 entornos + 2 accesos)', function () use ($now, $plotIds, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createOperationalData($plotIds, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 21. Almacén de Insumos ────────────────────────────────────────────
        $this->step('Almacén de Insumos (1 almacén + 10 suministros + 5 stocks fitosanitarios + movimientos + compras)', function () use ($now, $productIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createAlmacen($productIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ── 22. Facturas ──────────────────────────────────────────────────────
        $this->step('Facturas (6 venta cosecha + 4 liquidaciones bodega + items)', function () use ($now, $campaign2024Id, $campaign2025Id) {
            $this->createInvoices($campaign2024Id, $campaign2025Id, $now);
        });

        // ── 23. Gestión Territorial ───────────────────────────────────────────
        $this->step('Gestión Territorial (parajes + valles + suelos + topografías)', function () use ($now) {
            $this->createTerritorial($now);
        });

        $this->command->info('');
        $this->command->info('✅ ViticulturistDemoSeeder completado.');
        $this->command->info('   Viticultor: demo_viticulturist@agro365.es');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function step(string $label, callable $fn): void
    {
        $this->command->info("  ▸ {$label}...");
        $fn();
        $this->command->info("    ✓");
    }

    // ─── 0b. Ensure demo users exist ─────────────────────────────────────────

    private function ensureDemoUsers($now): void
    {
        if (! DB::table('users')->where('id', self::WINERY_USER_ID)->exists()) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('users')->insert([
                'id'                => self::WINERY_USER_ID,
                'name'              => 'Bodega Agaete Demo',
                'email'             => 'demo_winery@agro365.es',
                'email_verified_at' => $now,
                'password'          => bcrypt('password'),
                'role'              => 'winery',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->command->info('  ✅ Usuario winery creado (id=' . self::WINERY_USER_ID . ')');
        }

        if (! DB::table('users')->where('id', self::VIT_USER_ID)->exists()) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('users')->insert([
                'id'                => self::VIT_USER_ID,
                'name'              => 'Viticultor Agaete Demo',
                'email'             => 'demo_viticulturist@agro365.es',
                'email_verified_at' => $now,
                'password'          => bcrypt('password'),
                'role'              => 'viticulturist',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->command->info('  ✅ Usuario viticulturist creado (id=' . self::VIT_USER_ID . ')');
        }
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
            // Limpiar SIGPAC y geometrías
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

        // Contenedores del viticultor
        DB::table('containers')->where('user_id', $vitId)->delete();

        // Maquinaria
        DB::table('machinery')->where('viticulturist_id', $vitId)->delete();

        // Compliance
        DB::table('field_applicators')->where('viticulturist_id', $vitId)->delete();
        DB::table('field_equipment')->where('viticulturist_id', $vitId)->delete();
        DB::table('advisory_memberships')->where('viticulturist_id', $vitId)->delete();
        DB::table('agri_insurances')->where('viticulturist_id', $vitId)->delete();

        // Explotaciones + dependientes
        $exploitationIds = DB::table('exploitations')->where('viticulturist_id', $vitId)->pluck('id');
        if ($exploitationIds->isNotEmpty()) {
            DB::table('commercial_authorizations')->whereIn('exploitation_id', $exploitationIds)->delete();
            DB::table('cue_exports')->whereIn('exploitation_id', $exploitationIds)->delete();
        }
        DB::table('exploitations')->where('viticulturist_id', $vitId)->delete();

        // Subcontrataciones
        DB::table('subcontractings')->where('viticulturist_id', $vitId)->delete();

        // Registros Oficiales
        DB::table('residue_analyses')->where('viticulturist_id', $vitId)->delete();
        DB::table('residue_managements')->where('viticulturist_id', $vitId)->delete();
        DB::table('energy_usages')->where('viticulturist_id', $vitId)->delete();
        DB::table('water_concessions')->where('viticulturist_id', $vitId)->delete();
        DB::table('fertilization_plans')->where('viticulturist_id', $vitId)->delete();
        DB::table('phytosanitary_container_returns')->where('viticulturist_id', $vitId)->delete();
        DB::table('harvest_declarations')->where('viticulturist_id', $vitId)->delete();
        DB::table('harvest_byproducts')->where('viticulturist_id', $vitId)->delete();
        DB::table('certifications')->where('viticulturist_id', $vitId)->delete();
        DB::table('official_reports')->where('user_id', $vitId)->delete();

        // PAC
        $pacDeclarationIds = DB::table('pac_declarations')->where('viticulturist_id', $vitId)->pluck('id');
        if ($pacDeclarationIds->isNotEmpty()) {
            DB::table('pac_declaration_items')->whereIn('declaration_id', $pacDeclarationIds)->delete();
        }
        DB::table('pac_declarations')->where('viticulturist_id', $vitId)->delete();
        DB::table('pac_payments')->where('viticulturist_id', $vitId)->delete();

        // Facturación
        DB::table('plot_costs')->where('viticulturist_id', $vitId)->delete();
        DB::table('marketed_harvests')->where('viticulturist_id', $vitId)->delete();

        // Datos operacionales
        DB::table('campaign_documents')->where('viticulturist_id', $vitId)->delete();
        DB::table('plot_environments')->where('viticulturist_id', $vitId)->delete();
        DB::table('notebook_access_requests')->where('viticulturist_id', $vitId)->delete();

        // Almacén de Insumos
        $warehouseIds = DB::table('warehouses')->where('user_id', $vitId)->pluck('id');
        if ($warehouseIds->isNotEmpty()) {
            $stockIds = DB::table('product_stocks')->whereIn('warehouse_id', $warehouseIds)->pluck('id');
            if ($stockIds->isNotEmpty()) {
                DB::table('product_stock_movements')->whereIn('stock_id', $stockIds)->delete();
            }
            DB::table('product_stocks')->whereIn('warehouse_id', $warehouseIds)->delete();
        }
        DB::table('product_stocks')->where('user_id', $vitId)->delete();
        $supplyIds = DB::table('supplies')->where('viticulturist_id', $vitId)->pluck('id');
        if ($supplyIds->isNotEmpty()) {
            DB::table('supply_purchases')->whereIn('supply_id', $supplyIds)->delete();
        }
        DB::table('supplies')->where('viticulturist_id', $vitId)->delete();
        DB::table('warehouses')->where('user_id', $vitId)->delete();

        // Facturas (antes que clients por FK)
        $invoiceIds = DB::table('invoices')->where('user_id', $vitId)->pluck('id');
        if ($invoiceIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
        }
        DB::table('invoices')->where('user_id', $vitId)->delete();
        // Liquidaciones de bodega (user_id=winery, viticulturist_id=vit)
        $liqIds = DB::table('invoices')->where('viticulturist_id', $vitId)->pluck('id');
        if ($liqIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $liqIds)->delete();
            DB::table('invoices')->whereIn('id', $liqIds)->delete();
        }

        // Clientes (después de invoices)
        DB::table('clients')->where('user_id', $vitId)->delete();

        // Gestión Territorial (catálogos propios del usuario)
        DB::table('sites')->where('user_id', $vitId)->delete();
        DB::table('valleys')->where('user_id', $vitId)->delete();
        DB::table('soil_types')->where('user_id', $vitId)->delete();
        DB::table('topographies')->where('user_id', $vitId)->delete();
        DB::table('irrigation_types')->where('user_id', $vitId)->delete();
        DB::table('property_types')->where('user_id', $vitId)->delete();
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
            'notebook_access'  => true,
            'notebook_granted_at' => $now,
            'assigned_by'      => self::WINERY_USER_ID,
            'notes'            => 'Viticultor principal. Explotación familiar Agaete.',
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    // ─── 4. Parcelas + SIGPAC + Geometría ────────────────────────────────────
    // Igual que AgaetePlotsSeeder pero todos los 460 recintos asignados al
    // mismo viticulturist_id=338 (sin usuarios ficticios).

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

            // sigpac_code (idempotente)
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

            // plot_geometry con WKT real
            $geomId = DB::table('plot_geometry')->insertGetId([
                'coordinates' => DB::raw("ST_GeomFromText('" . $rec['wkt'] . "', 4326)"),
                'centroid'    => DB::raw("ST_Centroid(ST_GeomFromText('" . $rec['wkt'] . "', 4326))"),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // plot
            $prefijo  = self::PREFIJOS[$index % count(self::PREFIJOS)];
            $munShort = explode(' ', $rec['mun_name'])[0];
            $plotName = "$prefijo $munShort " . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            $plotId = DB::table('plots')->insertGetId([
                'name'                    => $plotName,
                'viticulturist_id'        => self::VIT_USER_ID,
                'area'                    => $rec['area_ha'] > 0 ? $rec['area_ha'] : round(mt_rand(10, 350) / 100, 2),
                'active'                  => true,
                'autonomous_community_id' => self::AC_ID,
                'province_id'             => self::PROVINCE_ID,
                'municipality_id'         => $munDbId,
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);

            // multipart_plot_sigpac
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
            'name'        => 'Campaña 2025',
            'year'        => 2025,
            'start_date'  => '2025-01-01',
            'end_date'    => '2025-12-31',
            'active'      => false,
            'description' => 'Campaña 2025 cerrada. Vendimia exitosa.',
            'final_validation_signed'  => true,
            'final_validation_date'    => '2025-11-28 10:00:00',
            'final_validation_user_id' => self::VIT_USER_ID,
        ]));

        $id2026 = DB::table('campaigns')->insertGetId(array_merge($base, [
            'name'        => 'Campaña 2026',
            'year'        => 2026,
            'start_date'  => '2026-01-01',
            'end_date'    => '2026-12-31',
            'active'      => true,
            'description' => 'Campaña activa. Poda completada. Inicio de brotación.',
        ]));

        return [$id2024, $id2025, $id2026];
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

    // ─── 7c. Actividades 2026 ─────────────────────────────────────────────────

    private function createActivities2026(
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

        // ── Podas (Enero 2026) ─────────────────────────────────────────────────
        $pruningDates = ['2026-01-06', '2026-01-08', '2026-01-13', '2026-01-15', '2026-01-20'];
        $pruningTypes = ['vaso', 'guyot', 'vaso', 'guyot', 'vaso'];

        foreach ($plotIds as $i => $plotId) {
            $aId = $act([
                'plot_id'            => $plotId,
                'activity_type'      => 'pruning',
                'phenological_stage' => 'reposo_invernal',
                'activity_date'      => $pruningDates[$i],
                'weather_conditions' => 'soleado',
                'temperature'        => 13.0 + $i,
                'notes'              => 'Poda 2026. Ajuste de carga según estado sanitario.',
            ]);
            DB::table('cultural_works')->insert([
                'activity_id'                 => $aId,
                'work_type'                   => 'poda',
                'pruning_type'                => $pruningTypes[$i],
                'productive_buds_per_hectare' => 46000 + ($i * 2000),
                'hours_worked'                => 6.0 + ($i * 0.5),
                'workers_count'               => 2,
                'description'                 => 'Poda invernal 2026. Intensidad ajustada al vigor de cada parcela.',
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ]);
        }

        // ── Sellado de heridas post-poda (Feb 2026) ────────────────────────────
        foreach ([$plotIds[0], $plotIds[2], $plotIds[4]] as $i => $plotId) {
            $aId = $act([
                'plot_id'            => $plotId,
                'activity_type'      => 'post_harvest',
                'phenological_stage' => 'reposo_invernal',
                'activity_date'      => '2026-02-0' . ($i + 3),
                'temperature'        => 15.0,
                'notes'              => 'Sellado de heridas de poda con pasta cicatrizante Vintec.',
            ]);
            DB::table('post_harvest_treatments')->insert([
                'activity_id'            => $aId,
                'product_id'             => null,
                'application_type'       => 'wound_sealing',
                'treated_area_ha'        => round(0.65 + $i * 0.2, 2),
                'reentry_interval_hours' => 0,
                'notes'                  => 'Aplicación inmediata tras poda para prevenir Eutipiosis.',
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }

        // ── Tratamientos preventivos inicio temporada (Feb–Mar 2026) ──────────
        $treatments2026 = [
            [0, '2026-02-20', 4, 'mildiu',  'pulverización',    'lloro',   14.0, 2.5],
            [2, '2026-02-22', 4, 'mildiu',  'pulverización',    'lloro',   15.0, 2.5],
            [1, '2026-03-10', 0, 'mildiu',  'pulverización',    'brotacion', 17.0, 3.0],
            [3, '2026-03-12', 0, 'mildiu',  'pulverización',    'brotacion', 18.0, 3.0],
            [0, '2026-03-25', 1, 'oídio',   'aplicación foliar','brotacion', 19.0, 0.4],
            [4, '2026-03-27', 1, 'oídio',   'aplicación foliar','brotacion', 20.0, 0.4],
        ];

        foreach ($treatments2026 as [$pIdx, $date, $prodIdx, $pest, $method, $stage, $temp, $dose]) {
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

        // ── Abonado de fondo primavera (Mar 2026) ──────────────────────────────
        $fertilizations2026 = [
            [0, '2026-03-18', 'orgánico', 'Compost de orujo 2025',     2200.0, 'en cobertura', 'brotacion'],
            [1, '2026-03-20', 'orgánico', 'Compost de orujo 2025',     2000.0, 'en cobertura', 'brotacion'],
            [4, '2026-03-22', 'orgánico', 'Vermicompost ecológico',    2500.0, 'en cobertura', 'brotacion'],
        ];

        foreach ($fertilizations2026 as [$pIdx, $date, $type, $name, $qty, $method, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'fertilization',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 17.0,
                'notes'              => "Abonado de fondo: {$name}.",
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

        // ── Riegos de inicio de temporada (Abr 2026) ───────────────────────────
        $irrigations2026 = [
            [1, '2026-04-05', 2000.0, 80,  false, 'brotacion'],
            [3, '2026-04-07', 1800.0, 72,  false, 'brotacion'],
        ];

        foreach ($irrigations2026 as [$pIdx, $date, $vol, $dur, $fertiIrr, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'irrigation',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 20.0,
                'notes'              => 'Primer riego de la temporada 2026.',
            ]);
            DB::table('irrigations')->insert([
                'activity_id'       => $aId,
                'water_volume'      => $vol,
                'water_volume_unit' => 'L',
                'irrigation_method' => 'goteo',
                'duration_minutes'  => $dur,
                'is_fertirrigation' => $fertiIrr,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Observaciones de brotación (Mar–Abr 2026) ──────────────────────────
        $observations2026 = [
            [0, '2026-03-28', 'fenología', 'Brotación homogénea. 88% de yemas activas.',      null,   'brotacion'],
            [1, '2026-03-30', 'fenología', 'Brotación uniforme. Vigor alto en espaldera.',    null,   'brotacion'],
            [2, '2026-04-02', 'general',   'Estado sanitario óptimo. Sin síntomas de plagas.', null,  'brotacion'],
            [4, '2026-04-05', 'fenología', 'Brotación excelente en parcela ecológica.',        null,   'brotacion'],
        ];

        foreach ($observations2026 as [$pIdx, $date, $type, $desc, $severity, $stage]) {
            $aId = $act([
                'plot_id'            => $plotIds[$pIdx],
                'activity_type'      => 'observation',
                'phenological_stage' => $stage,
                'activity_date'      => $date,
                'temperature'        => 19.0,
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

    private function createEstimatedYields(array $plantingIds, int $campaign2024Id, int $campaign2025Id, int $campaign2026Id, $now): void
    {
        // Usamos los plantingIds principales (uno por parcela: índices 0,2,4,6,8)
        // Campaña 2024: estimación pre-envero (ronda 1) + pre-vendimia (ronda 3) con datos reales
        // Campaña 2025: ronda 1 + ronda 3 con datos reales (campaña cerrada)
        // Campaña 2026: solo ronda 1 (campaña activa, inicio de temporada)

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
                'notes'                       => 'Estimación pre-envero 2025. Campaña cerrada.',
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ]);
        }

        // --- 2026 ronda 1: primera estimación de temporada (Abr 2026) ---
        $estimates2026r1 = [
            // [planting_idx, est_yield_ha, est_total, bunches_plant, bunch_g, plants, pct, health_pct]
            [0, 5000.0, 3000.0, 11.5, 270.0, 20, 20.0, 94.0],
            [2, 5500.0, 4400.0, 12.5, 300.0, 20, 18.0, 92.0],
            [4, 5200.0, 2080.0, 10.5, 275.0, 15, 18.0, 96.0],
            [6, 7000.0, 3220.0, 13.5, 325.0, 20, 20.0, 95.0],
            [8, 6000.0, 3300.0, 12.0, 295.0, 20, 18.0, 93.0],
        ];

        foreach ($estimates2026r1 as [$pIdx, $estHa, $estTotal, $bunchesPlant, $bunchG, $plants, $samplingPct, $healthPct]) {
            DB::table('estimated_yields')->insertOrIgnore([
                'plot_planting_id'            => $plantingIds[$pIdx],
                'campaign_id'                 => $campaign2026Id,
                'estimated_by'                => self::VIT_USER_ID,
                'estimated_yield_per_hectare' => $estHa,
                'estimated_total_yield'       => $estTotal,
                'estimation_date'             => '2026-04-10',
                'estimation_method'           => 'visual',
                'estimation_round'            => 1,
                'status'                      => 'draft',
                'active'                      => true,
                'bunches_per_plant'           => $bunchesPlant,
                'bunch_weight_grams'          => $bunchG,
                'total_plants_sampled'        => $plants,
                'sampling_area_pct'           => $samplingPct,
                'health_percentage'           => $healthPct,
                'health_status'               => 'sano',
                'potential_alcohol'           => round($estHa * 0.0027, 1),
                'vintage'                     => 2026,
                'actual_yield_per_hectare'    => null,
                'actual_total_yield'          => null,
                'variance_percentage'         => null,
                'notes'                       => 'Primera estimación 2026. Brotación reciente, estimación preliminar visual.',
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ]);
        }
    }

    // ─── 8. Fenología ─────────────────────────────────────────────────────────

    private function createPhenology(array $plantingIds, int $campaign2024Id, int $campaign2025Id, int $campaign2026Id, $now): void
    {
        $mainPlantings = [$plantingIds[0], $plantingIds[2], $plantingIds[4], $plantingIds[6], $plantingIds[8]];

        $insert = function (int $plantingId, int $campaignId, string $event, string $date, int $confidence) use ($now) {
            DB::table('phenology_observations')->insertOrIgnore([
                'plot_planting_id' => $plantingId,
                'campaign_id'      => $campaignId,
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
        };

        // ── 2024: ciclo completo ──────────────────────────────────────────────
        $events2024 = [
            ['budbreak',    '2024-03-10', 85],
            ['shoot_growth','2024-04-05', 95],
            ['flowering',   '2024-05-20', 90],
            ['fruit_set',   '2024-06-10', 88],
            ['veraison',    '2024-07-25', 92],
            ['pre_harvest', '2024-09-01', 95],
            ['harvest',     '2024-09-10', 100],
        ];
        foreach ($mainPlantings as $plantingId) {
            foreach ($events2024 as [$event, $date, $confidence]) {
                $insert($plantingId, $campaign2024Id, $event, $date, $confidence);
            }
        }

        // ── 2025: ciclo completo (campaña cerrada) ────────────────────────────
        $events2025 = [
            ['budbreak',    '2025-03-12', 90],
            ['shoot_growth','2025-04-08', 92],
            ['flowering',   '2025-05-22', 90],
            ['fruit_set',   '2025-06-12', 88],
            ['veraison',    '2025-07-28', 93],
            ['pre_harvest', '2025-09-03', 96],
            ['harvest',     '2025-09-12', 100],
        ];
        foreach ($mainPlantings as $plantingId) {
            foreach ($events2025 as [$event, $date, $confidence]) {
                $insert($plantingId, $campaign2025Id, $event, $date, $confidence);
            }
        }

        // ── 2026: inicio de temporada (brotación reciente) ────────────────────
        $events2026 = [
            ['budbreak',    '2026-03-15', 88],
            ['shoot_growth','2026-04-08', 85],
        ];
        foreach (array_slice($mainPlantings, 0, 4) as $plantingId) {
            foreach ($events2026 as [$event, $date, $confidence]) {
                $insert($plantingId, $campaign2026Id, $event, $date, $confidence);
            }
        }
    }

    // ─── 12. Maquinaria ──────────────────────────────────────────────────────

    private function createMachinery($now): array
    {
        $vitId = self::VIT_USER_ID;
        $rows = [
            ['name' => 'Tractor John Deere 5075E',       'type' => 'tractor',        'brand' => 'John Deere',   'model' => '5075E',        'serial_number' => 'JD5075-AGT-001', 'year' => 2019, 'purchase_date' => '2019-03-10', 'purchase_price' => 42000.00, 'current_value' => 30000.00, 'roma_registration' => 'GC-T-4821', 'is_rented' => false, 'capacity' => '75 CV', 'last_revision_date' => '2025-10-15', 'active' => true],
            ['name' => 'Tractor New Holland T4.90',       'type' => 'tractor',        'brand' => 'New Holland',  'model' => 'T4.90',        'serial_number' => 'NH490-AGT-001',  'year' => 2021, 'purchase_date' => '2021-05-20', 'purchase_price' => 52000.00, 'current_value' => 44000.00, 'roma_registration' => 'GC-T-5102', 'is_rented' => false, 'capacity' => '90 CV', 'last_revision_date' => '2025-11-05', 'active' => true],
            ['name' => 'Tractor Kubota M7060',            'type' => 'tractor',        'brand' => 'Kubota',       'model' => 'M7060',        'serial_number' => 'KUB7060-AGT-01', 'year' => 2016, 'purchase_date' => '2016-06-01', 'purchase_price' => 35000.00, 'current_value' => 12000.00, 'roma_registration' => 'GC-T-3244', 'is_rented' => false, 'capacity' => '70 CV', 'last_revision_date' => '2025-08-20', 'active' => true],
            ['name' => 'Atomizador Grupo Ulma 600 L',     'type' => 'sprayer',        'brand' => 'Grupo Ulma',   'model' => 'VF-600',       'serial_number' => 'ATM600-AGT-001', 'year' => 2020, 'purchase_date' => '2020-02-15', 'purchase_price' =>  9500.00, 'current_value' =>  6500.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => '600 L', 'last_revision_date' => '2025-03-12', 'active' => true],
            ['name' => 'Atomizador Empas MP-400',         'type' => 'sprayer',        'brand' => 'Empas',        'model' => 'MP-400',       'serial_number' => 'ATM400-AGT-001', 'year' => 2022, 'purchase_date' => '2022-03-08', 'purchase_price' =>  7200.00, 'current_value' =>  5800.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => '400 L', 'last_revision_date' => '2025-03-12', 'active' => true],
            ['name' => 'Remolque Basculante 5 T',         'type' => 'other',          'brand' => 'Agratic',      'model' => 'RB-5000',      'serial_number' => 'REM5T-AGT-001',  'year' => 2018, 'purchase_date' => '2018-07-20', 'purchase_price' =>  4800.00, 'current_value' =>  2500.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => '5.000 kg', 'last_revision_date' => null,           'active' => true],
            ['name' => 'Remolque Viñero 3 T',             'type' => 'other',          'brand' => 'Castillo',     'model' => 'RV-3000',      'serial_number' => 'REM3T-AGT-001',  'year' => 2021, 'purchase_date' => '2021-09-10', 'purchase_price' =>  3200.00, 'current_value' =>  2600.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => '3.000 kg', 'last_revision_date' => null,           'active' => true],
            ['name' => 'Podadora Neumática Lisam LS-60',  'type' => 'pruner',         'brand' => 'Lisam',        'model' => 'LS-60',        'serial_number' => 'POD60-AGT-001',  'year' => 2021, 'purchase_date' => '2021-11-01', 'purchase_price' =>  1200.00, 'current_value' =>   800.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => null,       'last_revision_date' => null,           'active' => true],
            ['name' => 'Podadora Neumática Felco 801',    'type' => 'pruner',         'brand' => 'Felco',        'model' => '801',          'serial_number' => 'FELCO801-AGT-01','year' => 2023, 'purchase_date' => '2023-10-15', 'purchase_price' =>   950.00, 'current_value' =>   800.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => null,       'last_revision_date' => null,           'active' => true],
            ['name' => 'Desbrozadora Husqvarna 545FX',   'type' => 'mower',          'brand' => 'Husqvarna',    'model' => '545FX',        'serial_number' => 'HUSQ545-AGT-01', 'year' => 2022, 'purchase_date' => '2022-04-20', 'purchase_price' =>  1800.00, 'current_value' =>  1400.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => null,       'last_revision_date' => null,           'active' => true],
            ['name' => 'Microvend. Gregoire G7.200',      'type' => 'harvester',      'brand' => 'Gregoire',     'model' => 'G7.200',       'serial_number' => 'GRE7200-AGT-01', 'year' => 2020, 'purchase_date' => '2020-08-01', 'purchase_price' => 85000.00, 'current_value' => 58000.00, 'roma_registration' => 'GC-T-5088', 'is_rented' => false, 'capacity' => null,       'last_revision_date' => '2025-09-01', 'active' => true],
            ['name' => 'Tractor Fiat 640 (baja)',         'type' => 'tractor',        'brand' => 'Fiat',         'model' => '640',          'serial_number' => 'FIAT640-AGT-01', 'year' => 1998, 'purchase_date' => '2005-01-01', 'purchase_price' =>  6000.00, 'current_value' =>     0.00, 'roma_registration' => null,          'is_rented' => false, 'capacity' => '55 CV', 'last_revision_date' => null,           'active' => false],
        ];
        $ids = [];
        foreach ($rows as $r) {
            $ids[] = DB::table('machinery')->insertGetId(array_merge($r, ['viticulturist_id' => $vitId, 'notes' => null, 'image' => null, 'created_at' => $now, 'updated_at' => $now]));
        }
        return $ids;
    }

    // ─── 13. Explotaciones ───────────────────────────────────────────────────

    private function createExploitations($now): array
    {
        $vitId = self::VIT_USER_ID;
        $rows = [
            [
                'exploitation_name'        => 'Explotación Viticultora Agaete — Principal',
                'rea_code'                 => 'GC-35003-VIT-0021',
                'siex_exploitation_id'     => 'ES35003-0021-A',
                'exploitation_type'        => 'viticultura',
                'holder_name'              => 'Demo Viticulturist',
                'holder_nif'               => '45678901B',
                'representative_name'      => null,
                'representative_nif'       => null,
                'is_ecological'            => true,
                'is_integrated_production' => true,
                'is_quality_scheme'        => true,
                'quality_scheme_desc'      => 'D.O.P. Gran Canaria',
                'notes'                    => 'Explotación principal en el municipio de Agaete',
                'active'                   => true,
            ],
            [
                'exploitation_name'        => 'Parcelas Complementarias La Aldea',
                'rea_code'                 => 'GC-35002-VIT-0087',
                'siex_exploitation_id'     => 'ES35002-0087-B',
                'exploitation_type'        => 'viticultura',
                'holder_name'              => 'Demo Viticulturist',
                'holder_nif'               => '45678901B',
                'representative_name'      => null,
                'representative_nif'       => null,
                'is_ecological'            => false,
                'is_integrated_production' => true,
                'is_quality_scheme'        => false,
                'quality_scheme_desc'      => null,
                'notes'                    => 'Parcelas arrendadas en La Aldea de San Nicolás',
                'active'                   => true,
            ],
        ];
        $ids = [];
        foreach ($rows as $r) {
            $ids[] = DB::table('exploitations')->insertGetId(array_merge($r, ['viticulturist_id' => $vitId, 'created_at' => $now, 'updated_at' => $now]));
        }
        return $ids;
    }

    // ─── 14. Compliance ──────────────────────────────────────────────────────

    private function createCompliance(array $exploitationIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId = self::VIT_USER_ID;
        $expId = $exploitationIds[0] ?? null;

        // ── Aplicadores ROPO (5) ──────────────────────────────────────────────
        $applicators = [
            ['name' => 'Demo Viticulturist',    'ropo_number' => 'ROPO-GC-004521', 'ropo_category' => 'qualified',  'ropo_expiry_date' => '2027-06-30', 'is_advisor' => true,  'advisor_license' => 'ASESOR-GC-0312', 'phone' => '+34 629 111 001', 'email' => 'demo_viticulturist@agro365.es', 'active' => true],
            ['name' => 'Pedro Álvarez Suárez',  'ropo_number' => 'ROPO-GC-003812', 'ropo_category' => 'basic',      'ropo_expiry_date' => '2026-04-15', 'is_advisor' => false, 'advisor_license' => null,              'phone' => '+34 629 222 002', 'email' => null,                            'active' => true],
            ['name' => 'Carmen Trujillo Ramos', 'ropo_number' => 'ROPO-GC-004107', 'ropo_category' => 'basic',      'ropo_expiry_date' => '2026-09-20', 'is_advisor' => false, 'advisor_license' => null,              'phone' => '+34 629 333 003', 'email' => null,                            'active' => true],
            ['name' => 'José Luis Perera',       'ropo_number' => 'ROPO-GC-002991', 'ropo_category' => 'qualified',  'ropo_expiry_date' => '2025-11-30', 'is_advisor' => false, 'advisor_license' => null,              'phone' => '+34 629 444 004', 'email' => null,                            'active' => false],
            ['name' => 'Ana María Déniz Vega',   'ropo_number' => 'ROPO-GC-005201', 'ropo_category' => 'fumigator',  'ropo_expiry_date' => '2028-02-28', 'is_advisor' => false, 'advisor_license' => null,              'phone' => '+34 629 555 005', 'email' => null,                            'active' => true],
        ];
        foreach ($applicators as $r) {
            DB::table('field_applicators')->insert(array_merge($r, ['viticulturist_id' => $vitId, 'campaign_id' => null, 'created_at' => $now, 'updated_at' => $now]));
        }

        // ── Equipos ITB/ITEA (6) ──────────────────────────────────────────────
        $equipment = [
            ['name' => 'Atomizador Ulma VF-600',         'equipment_type' => 'sprayer',    'registration_number' => 'ATM-GC-1021', 'purchase_date' => '2020-02-15', 'last_inspection_date' => '2024-02-10', 'next_inspection_date' => '2027-02-10', 'inspection_entity' => 'CIATCA Gran Canaria', 'active' => true],
            ['name' => 'Atomizador Empas MP-400',         'equipment_type' => 'sprayer',    'registration_number' => 'ATM-GC-1289', 'purchase_date' => '2022-03-08', 'last_inspection_date' => '2024-03-05', 'next_inspection_date' => '2027-03-05', 'inspection_entity' => 'CIATCA Gran Canaria', 'active' => true],
            ['name' => 'Sistema Riego Goteo Parcela 1',   'equipment_type' => 'irrigation', 'registration_number' => null,          'purchase_date' => '2019-05-01', 'last_inspection_date' => '2025-01-15', 'next_inspection_date' => '2028-01-15', 'inspection_entity' => 'Técnico propio',      'active' => true],
            ['name' => 'Sistema Riego Goteo Parcela 2',   'equipment_type' => 'irrigation', 'registration_number' => null,          'purchase_date' => '2020-04-10', 'last_inspection_date' => '2025-01-15', 'next_inspection_date' => '2028-01-15', 'inspection_entity' => 'Técnico propio',      'active' => true],
            ['name' => 'Tractor JD 5075E (tractor)',      'equipment_type' => 'tractor',    'registration_number' => 'GC-T-4821',   'purchase_date' => '2019-03-10', 'last_inspection_date' => '2025-03-10', 'next_inspection_date' => '2026-03-10', 'inspection_entity' => 'ITV Gran Canaria',    'active' => true],
            ['name' => 'Microvendimiadora G7.200',        'equipment_type' => 'harvester',  'registration_number' => 'GC-T-5088',   'purchase_date' => '2020-08-01', 'last_inspection_date' => '2025-08-01', 'next_inspection_date' => '2026-08-01', 'inspection_entity' => 'ITV Gran Canaria',    'active' => true],
        ];
        foreach ($equipment as $r) {
            DB::table('field_equipment')->insert(array_merge($r, ['viticulturist_id' => $vitId, 'notes' => null, 'created_at' => $now, 'updated_at' => $now]));
        }

        // ── Asesorías Técnicas (4) ────────────────────────────────────────────
        $advisors = [
            ['advisor_name' => 'Dra. Isabel Cabrera Monzón',    'license_number' => 'ASESOR-GC-0108', 'specialty' => 'phytosanitary',  'company_name' => 'AgroTécnica Canarias SL',   'phone' => '+34 928 501 001', 'email' => 'icabrera@agrotecnicacanarias.es', 'active' => true],
            ['advisor_name' => 'Ing. Marcos Hernández Falcón',  'license_number' => 'ASESOR-GC-0251', 'specialty' => 'agronomy',       'company_name' => 'Estudio Agronómico Atlántico', 'phone' => '+34 928 502 002', 'email' => 'mhernandez@eaacanarias.es',       'active' => true],
            ['advisor_name' => 'Dr. Antonio Suárez Pérez',      'license_number' => 'ASESOR-GC-0312', 'specialty' => 'oenology',       'company_name' => null,                        'phone' => '+34 629 503 003', 'email' => 'asuarez.enologia@gmail.com',      'active' => true],
            ['advisor_name' => 'Ing. Laura Vega Santana',       'license_number' => 'ASESOR-GC-0441', 'specialty' => 'sustainability', 'company_name' => 'Consultoría Verde GC',     'phone' => '+34 928 504 004', 'email' => 'lvega@consultoriaverdegc.es',     'active' => false],
        ];
        foreach ($advisors as $r) {
            DB::table('advisory_memberships')->insert(array_merge($r, ['viticulturist_id' => $vitId, 'campaign_id' => null, 'created_at' => $now, 'updated_at' => $now]));
        }

        // ── Autorizaciones Comerciales (6) ────────────────────────────────────
        $authorizations = [
            ['authorization_type' => 'do_registration',        'authorization_code' => 'DOP-GC-2021-0034', 'description' => 'Inscripción en el Consejo Regulador D.O.P. Gran Canaria',             'issuing_body' => 'Consejo Regulador DOP Gran Canaria',    'issue_date' => '2021-03-15', 'expiry_date' => null,          'exploitation_id' => $expId],
            ['authorization_type' => 'organic_certification',  'authorization_code' => 'BIO-GC-2022-0112', 'description' => 'Certificación de producción ecológica — parcelas 1, 2 y 3',          'issuing_body' => 'CAAE — Canarias',                        'issue_date' => '2022-05-01', 'expiry_date' => '2025-04-30',  'exploitation_id' => $expId],
            ['authorization_type' => 'organic_certification',  'authorization_code' => 'BIO-GC-2025-0098', 'description' => 'Certificación ecológica renovada 2025-2027',                         'issuing_body' => 'CAAE — Canarias',                        'issue_date' => '2025-05-01', 'expiry_date' => '2027-04-30',  'exploitation_id' => $expId],
            ['authorization_type' => 'integrated_production',  'authorization_code' => 'PI-GC-2020-0055',  'description' => 'Inscripción en Producción Integrada de Canarias',                   'issuing_body' => 'Gobierno de Canarias — SGRA',            'issue_date' => '2020-06-10', 'expiry_date' => null,          'exploitation_id' => $expId],
            ['authorization_type' => 'planting_right',         'authorization_code' => 'DERECHO-VID-0421', 'description' => 'Derecho de plantación — 0,85 ha nuevas variedades',                  'issuing_body' => 'FEGA — Registro Vitícola Nacional',      'issue_date' => '2023-01-20', 'expiry_date' => '2030-12-31',  'exploitation_id' => $expId],
            ['authorization_type' => 'replanting_right',       'authorization_code' => 'REPLANT-GC-0088',  'description' => 'Derecho de replantación parcela 4 — 0,32 ha Negramoll',             'issuing_body' => 'Gobierno de Canarias',                   'issue_date' => '2024-02-15', 'expiry_date' => '2027-02-14',  'exploitation_id' => $expId],
        ];
        foreach ($authorizations as $r) {
            DB::table('commercial_authorizations')->insert(array_merge($r, ['viticulturist_id' => $vitId, 'document_file' => null, 'notes' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now]));
        }

        // ── Seguros Agrarios (6) ──────────────────────────────────────────────
        $insurances = [
            ['coverage_type' => 'comprehensive', 'insurance_company' => 'Agroseguro',           'policy_number' => 'POL-2024-GC-004821', 'start_date' => '2024-04-01', 'end_date' => '2024-09-30', 'insured_amount' => 48000.00, 'premium' => 1250.00, 'subsidy_amount' => 562.50,  'status' => 'expired',  'agent_name' => 'Luis Navarro',    'agent_phone' => '+34 928 601 001'],
            ['coverage_type' => 'comprehensive', 'insurance_company' => 'Agroseguro',           'policy_number' => 'POL-2025-GC-005102', 'start_date' => '2025-04-01', 'end_date' => '2025-09-30', 'insured_amount' => 51000.00, 'premium' => 1380.00, 'subsidy_amount' => 621.00,  'status' => 'expired',  'agent_name' => 'Luis Navarro',    'agent_phone' => '+34 928 601 001'],
            ['coverage_type' => 'comprehensive', 'insurance_company' => 'Agroseguro',           'policy_number' => 'POL-2026-GC-005891', 'start_date' => '2026-04-01', 'end_date' => '2026-09-30', 'insured_amount' => 55000.00, 'premium' => 1450.00, 'subsidy_amount' => 652.50,  'status' => 'active',   'agent_name' => 'Luis Navarro',    'agent_phone' => '+34 928 601 001'],
            ['coverage_type' => 'hail',          'insurance_company' => 'Mapfre Agro',          'policy_number' => 'MAP-2025-004120',    'start_date' => '2025-05-01', 'end_date' => '2025-10-31', 'insured_amount' => 22000.00, 'premium' =>  680.00, 'subsidy_amount' => null,       'status' => 'expired',  'agent_name' => 'Sofía Martel',    'agent_phone' => '+34 928 602 002'],
            ['coverage_type' => 'hail',          'insurance_company' => 'Mapfre Agro',          'policy_number' => 'MAP-2026-004899',    'start_date' => '2026-05-01', 'end_date' => '2026-10-31', 'insured_amount' => 24000.00, 'premium' =>  720.00, 'subsidy_amount' => null,       'status' => 'active',   'agent_name' => 'Sofía Martel',    'agent_phone' => '+34 928 602 002'],
            ['coverage_type' => 'frost',         'insurance_company' => 'Helvetia Agroseguro', 'policy_number' => 'HEL-2026-GC-1021',  'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'insured_amount' => 15000.00, 'premium' =>  420.00, 'subsidy_amount' => 189.00,  'status' => 'active',   'agent_name' => 'Roberto Afonso',  'agent_phone' => '+34 928 603 003'],
        ];
        foreach ($insurances as $r) {
            DB::table('agri_insurances')->insert(array_merge($r, ['viticulturist_id' => $vitId, 'covered_plots' => null, 'notes' => null, 'created_at' => $now, 'updated_at' => $now]));
        }
    }

    // ─── 15. Clientes ────────────────────────────────────────────────────────

    private function createClients($now): void
    {
        $vitId = self::VIT_USER_ID;
        $clients = [
            ['client_type' => 'company',    'company_name' => 'Bodega Agaete Artesana SL',         'company_document' => 'B35012345', 'first_name' => null,      'last_name' => null,         'email' => 'compras@bodegaagaete.com',    'phone' => '+34 928 901 001', 'payment_method' => 'transfer', 'default_discount' => 5.00,  'has_cae' => true,  'cae_number' => 'CAE-GC-0421', 'active' => true,  'balance' => 0.00],
            ['client_type' => 'company',    'company_name' => 'Cooperativa Vitivinícola del Norte', 'company_document' => 'F35098765', 'first_name' => null,      'last_name' => null,         'email' => 'gestion@coopvitivinicola.es', 'phone' => '+34 928 902 002', 'payment_method' => 'transfer', 'default_discount' => 3.00,  'has_cae' => true,  'cae_number' => 'CAE-GC-0118', 'active' => true,  'balance' => 0.00],
            ['client_type' => 'company',    'company_name' => 'Distribuciones Canarias Vino SL',   'company_document' => 'B35054321', 'first_name' => null,      'last_name' => null,         'email' => 'pedidos@dcvino.es',           'phone' => '+34 928 903 003', 'payment_method' => 'transfer', 'default_discount' => 2.00,  'has_cae' => false, 'cae_number' => null,           'active' => true,  'balance' => 0.00],
            ['client_type' => 'company',    'company_name' => 'Restaurante El Faro de Agaete',     'company_document' => 'B35077890', 'first_name' => null,      'last_name' => null,         'email' => 'info@elfarodagaete.com',      'phone' => '+34 928 904 004', 'payment_method' => 'cash',     'default_discount' => 0.00,  'has_cae' => false, 'cae_number' => null,           'active' => true,  'balance' => 0.00],
            ['client_type' => 'individual', 'company_name' => null,                                'company_document' => null,        'first_name' => 'Ramón',   'last_name' => 'Morales Díaz',  'email' => 'ramon.morales@gmail.com',  'phone' => '+34 629 905 005', 'payment_method' => 'cash',     'default_discount' => 0.00,  'has_cae' => false, 'cae_number' => null,           'active' => true,  'balance' => 0.00],
            ['client_type' => 'individual', 'company_name' => null,                                'company_document' => null,        'first_name' => 'Clara',   'last_name' => 'Santana Vega',  'email' => 'clarita.sv@hotmail.com',   'phone' => '+34 629 906 006', 'payment_method' => 'transfer', 'default_discount' => 0.00,  'has_cae' => false, 'cae_number' => null,           'active' => true,  'balance' => 0.00],
            ['client_type' => 'individual', 'company_name' => null,                                'company_document' => null,        'first_name' => 'Miguel',  'last_name' => 'Falcón Torres', 'email' => null,                       'phone' => '+34 629 907 007', 'payment_method' => 'cash',     'default_discount' => 0.00,  'has_cae' => false, 'cae_number' => null,           'active' => true,  'balance' => 0.00],
            ['client_type' => 'individual', 'company_name' => null,                                'company_document' => null,        'first_name' => 'Antonia', 'last_name' => 'Ramos Perdomo', 'email' => 'antonia.rp@outlook.es',    'phone' => '+34 629 908 008', 'payment_method' => 'transfer', 'default_discount' => 2.00,  'has_cae' => false, 'cae_number' => null,           'active' => false, 'balance' => 0.00],
        ];
        foreach ($clients as $r) {
            DB::table('clients')->insert(array_merge($r, ['user_id' => $vitId, 'particular_document' => null, 'account_number' => null, 'avatar' => null, 'notes' => null, 'created_at' => $now, 'updated_at' => $now]));
        }
    }

    // ─── 16. Subcontrataciones ───────────────────────────────────────────────

    private function createSubcontractings(array $plotIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId = self::VIT_USER_ID;
        $p = $plotIds;
        $rows = [
            // 2024
            ['campaign_id' => $c24, 'plot_id' => $p[0], 'service_type' => 'harvesting',   'company_name' => 'Cuadrilla Hernández e Hijos',    'service_date' => '2024-08-20', 'service_end_date' => '2024-08-25', 'amount' => 1800.00, 'invoiced' => true,  'invoice_number' => 'F2024-0041', 'description' => 'Vendimia manual Listán Negro lote 1'],
            ['campaign_id' => $c24, 'plot_id' => $p[1], 'service_type' => 'harvesting',   'company_name' => 'Cuadrilla Hernández e Hijos',    'service_date' => '2024-08-27', 'service_end_date' => '2024-09-01', 'amount' => 2100.00, 'invoiced' => true,  'invoice_number' => 'F2024-0042', 'description' => 'Vendimia manual Listán Blanco lote 2'],
            ['campaign_id' => $c24, 'plot_id' => $p[2], 'service_type' => 'treatment',    'company_name' => 'AgroService Canarias SL',        'service_date' => '2024-05-10', 'service_end_date' => null,         'amount' =>  620.00, 'invoiced' => true,  'invoice_number' => 'AGS-2024-018', 'description' => 'Tratamiento anti-mildiu preventivo'],
            ['campaign_id' => $c24, 'plot_id' => $p[3], 'service_type' => 'pruning',      'company_name' => 'Podas Atlánticas SL',            'service_date' => '2024-01-15', 'service_end_date' => '2024-01-20', 'amount' =>  980.00, 'invoiced' => true,  'invoice_number' => 'PA-2024-005',  'description' => 'Poda en vaso parcela norte'],
            ['campaign_id' => $c24, 'plot_id' => $p[4], 'service_type' => 'fertilization','company_name' => 'Nutrición Vegetal Canarias SL',  'service_date' => '2024-03-05', 'service_end_date' => null,         'amount' =>  450.00, 'invoiced' => true,  'invoice_number' => 'NVC-2024-012', 'description' => 'Aporte orgánico + N-P-K post-poda'],
            ['campaign_id' => $c24, 'plot_id' => $p[0], 'service_type' => 'transport',    'company_name' => 'Transportes Viñedo GC SL',       'service_date' => '2024-09-02', 'service_end_date' => null,         'amount' =>  280.00, 'invoiced' => true,  'invoice_number' => 'TRV-2024-031', 'description' => 'Transporte uva a bodega Agaete'],
            ['campaign_id' => $c24, 'plot_id' => $p[1], 'service_type' => 'analysis',     'company_name' => 'Laboratorio Agrícola Canario SA','service_date' => '2024-07-20', 'service_end_date' => null,         'amount' =>  390.00, 'invoiced' => true,  'invoice_number' => 'LAC-2024-042', 'description' => 'Análisis de madurez + residuos'],
            // 2025
            ['campaign_id' => $c25, 'plot_id' => $p[0], 'service_type' => 'harvesting',   'company_name' => 'Cuadrilla Hernández e Hijos',    'service_date' => '2025-08-18', 'service_end_date' => '2025-08-22', 'amount' => 1950.00, 'invoiced' => true,  'invoice_number' => 'F2025-0038', 'description' => 'Vendimia manual — lote Listán Negro'],
            ['campaign_id' => $c25, 'plot_id' => $p[1], 'service_type' => 'harvesting',   'company_name' => 'Cuadrilla Hernández e Hijos',    'service_date' => '2025-08-25', 'service_end_date' => '2025-08-30', 'amount' => 2200.00, 'invoiced' => true,  'invoice_number' => 'F2025-0039', 'description' => 'Vendimia manual — lote Listán Blanco'],
            ['campaign_id' => $c25, 'plot_id' => $p[2], 'service_type' => 'pruning',      'company_name' => 'Podas Atlánticas SL',            'service_date' => '2025-01-08', 'service_end_date' => '2025-01-13', 'amount' => 1050.00, 'invoiced' => true,  'invoice_number' => 'PA-2025-003',  'description' => 'Poda de formación viñas jóvenes'],
            ['campaign_id' => $c25, 'plot_id' => $p[3], 'service_type' => 'pruning',      'company_name' => 'Podas Atlánticas SL',            'service_date' => '2025-01-20', 'service_end_date' => '2025-01-24', 'amount' =>  920.00, 'invoiced' => true,  'invoice_number' => 'PA-2025-004',  'description' => 'Poda seca parcelas norte'],
            ['campaign_id' => $c25, 'plot_id' => $p[4], 'service_type' => 'irrigation',   'company_name' => 'Riegos Canarios SL',             'service_date' => '2025-06-01', 'service_end_date' => null,         'amount' =>  340.00, 'invoiced' => true,  'invoice_number' => 'RC-2025-021',  'description' => 'Reparación y mantenimiento sistema de riego'],
            ['campaign_id' => $c25, 'plot_id' => $p[0], 'service_type' => 'soil_work',    'company_name' => 'Labores Agrícolas Guía SL',      'service_date' => '2025-02-15', 'service_end_date' => '2025-02-16', 'amount' =>  680.00, 'invoiced' => true,  'invoice_number' => 'LAG-2025-009', 'description' => 'Laboreo entre líneas y subsolado'],
            ['campaign_id' => $c25, 'plot_id' => $p[1], 'service_type' => 'treatment',    'company_name' => 'AgroService Canarias SL',        'service_date' => '2025-04-22', 'service_end_date' => null,         'amount' =>  580.00, 'invoiced' => true,  'invoice_number' => 'AGS-2025-011', 'description' => 'Tratamiento preventivo mildiu + oídio'],
            // 2026
            ['campaign_id' => $c26, 'plot_id' => $p[0], 'service_type' => 'pruning',      'company_name' => 'Podas Atlánticas SL',            'service_date' => '2026-01-10', 'service_end_date' => '2026-01-15', 'amount' => 1100.00, 'invoiced' => false, 'invoice_number' => null,            'description' => 'Poda corta en cordón royat'],
            ['campaign_id' => $c26, 'plot_id' => $p[1], 'service_type' => 'pruning',      'company_name' => 'Podas Atlánticas SL',            'service_date' => '2026-01-18', 'service_end_date' => '2026-01-22', 'amount' => 1000.00, 'invoiced' => false, 'invoice_number' => null,            'description' => 'Poda seca lote sur'],
            ['campaign_id' => $c26, 'plot_id' => $p[2], 'service_type' => 'soil_work',    'company_name' => 'Labores Agrícolas Guía SL',      'service_date' => '2026-02-10', 'service_end_date' => '2026-02-11', 'amount' =>  720.00, 'invoiced' => false, 'invoice_number' => null,            'description' => 'Laboreo primavera y aporte compost'],
            ['campaign_id' => $c26, 'plot_id' => $p[3], 'service_type' => 'fertilization','company_name' => 'Nutrición Vegetal Canarias SL',  'service_date' => '2026-03-12', 'service_end_date' => null,         'amount' =>  510.00, 'invoiced' => false, 'invoice_number' => null,            'description' => 'Fertirrigación brotación'],
            ['campaign_id' => $c26, 'plot_id' => $p[4], 'service_type' => 'irrigation',   'company_name' => 'Riegos Canarios SL',             'service_date' => '2026-03-20', 'service_end_date' => null,         'amount' =>  290.00, 'invoiced' => false, 'invoice_number' => null,            'description' => 'Instalación goteros nuevos'],
            ['campaign_id' => $c26, 'plot_id' => $p[0], 'service_type' => 'treatment',    'company_name' => 'AgroService Canarias SL',        'service_date' => '2026-03-28', 'service_end_date' => null,         'amount' =>  640.00, 'invoiced' => false, 'invoice_number' => null,            'description' => 'Tratamiento preventivo brotación 2026'],
        ];
        foreach ($rows as $r) {
            DB::table('subcontractings')->insert(array_merge($r, [
                'viticulturist_id' => $vitId,
                'contact_person'   => null,
                'contact_phone'    => null,
                'notes'            => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]));
        }
    }

    // ─── 17. Registros Oficiales ─────────────────────────────────────────────

    private function createRegistrosOficiales(array $plantingIds, array $plotIds, array $productIds, array $machineryIds, array $exploitationIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId   = self::VIT_USER_ID;
        $expId   = $exploitationIds[0] ?? null;
        $mach1   = $machineryIds[0]    ?? null;
        $mach4   = $machineryIds[3]    ?? null;

        // ── Análisis de Residuos (24) ─────────────────────────────────────────
        $raData = [
            // 2024 (8)
            [$c24, $plantingIds[0], '2024-07-15', '2024-07-10', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Cumple LMR. Mancozeb < 0,05 mg/kg'],
            [$c24, $plantingIds[1], '2024-07-15', '2024-07-10', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Cumple LMR. Sin residuos detectados'],
            [$c24, $plantingIds[2], '2024-07-20', '2024-07-15', 'SGS España SA',                   'ENAC-1087', 'uva',  true,  'Dentro de límites. Cobre 0,12 mg/kg'],
            [$c24, $plantingIds[3], '2024-07-20', '2024-07-15', 'SGS España SA',                   'ENAC-1087', 'uva',  true,  'Dentro de límites. Sin incidencias'],
            [$c24, $plantingIds[4], '2024-08-01', '2024-07-28', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Conforme. Fosetil-Al < LMR'],
            [$c24, null,            '2024-05-10', '2024-05-05', 'Aqualogic Canarias SL',           null,        'suelo',true,  'Análisis de suelo campaña 2024. pH 6.8, M.O. 2.1%'],
            [$c24, null,            '2024-05-10', '2024-05-05', 'Aqualogic Canarias SL',           null,        'agua', true,  'Agua de riego. Nitratos 12 mg/L. Apto'],
            [$c24, $plantingIds[0], '2024-08-25', '2024-08-22', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  false, 'Mancozeb 0.18 mg/kg. Supera LMR. Incidencia notificada'],
            // 2025 (8)
            [$c25, $plantingIds[0], '2025-07-12', '2025-07-08', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Conforme. Mancozeb nd'],
            [$c25, $plantingIds[1], '2025-07-12', '2025-07-08', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Conforme. Sin residuos detectados'],
            [$c25, $plantingIds[2], '2025-07-18', '2025-07-14', 'Bureau Veritas España SA',        'ENAC-0988', 'uva',  true,  'Cumple LMR. Panel 200 plaguicidas'],
            [$c25, $plantingIds[3], '2025-07-18', '2025-07-14', 'Bureau Veritas España SA',        'ENAC-0988', 'uva',  true,  'Cumple LMR. Cobre 0.08 mg/kg'],
            [$c25, $plantingIds[4], '2025-07-30', '2025-07-26', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Conforme. nd en todos los parámetros'],
            [$c25, null,            '2025-04-20', '2025-04-15', 'Aqualogic Canarias SL',           null,        'suelo',true,  'Análisis suelo 2025. pH 6.7, MO 2.4%'],
            [$c25, null,            '2025-04-20', '2025-04-15', 'Aqualogic Canarias SL',           null,        'agua', true,  'Agua de riego 2025. Nitratos 10 mg/L'],
            [$c25, $plantingIds[2], '2025-08-10', '2025-08-07', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'uva',  true,  'Análisis pre-vendimia. Conforme'],
            // 2026 (8)
            [$c26, $plantingIds[0], '2026-03-15', '2026-03-10', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'suelo',true,  'Análisis suelo brotación 2026. pH 6.9'],
            [$c26, null,            '2026-03-15', '2026-03-10', 'Aqualogic Canarias SL',           null,        'agua', true,  'Agua de riego brotación. Nitratos 9 mg/L'],
            [$c26, $plantingIds[1], '2026-03-20', '2026-03-18', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'suelo',true,  'Análisis foliar. N 3.1%, K 1.8%'],
            [$c26, $plantingIds[2], '2026-03-20', '2026-03-18', 'Laboratorio Agrícola Canario SA', 'ENAC-2104', 'suelo',true,  'Análisis foliar. Niveles normales'],
            [$c26, $plantingIds[3], '2026-04-01', '2026-03-28', 'Bureau Veritas España SA',        'ENAC-0988', 'suelo',true,  'Control residual poscosecha'],
            [$c26, $plantingIds[4], '2026-04-01', '2026-03-28', 'Bureau Veritas España SA',        'ENAC-0988', 'suelo',true,  'Control residual poscosecha'],
            [$c26, null,            '2026-04-08', '2026-04-05', 'Aqualogic Canarias SL',           null,        'agua', true,  'Análisis agua primavera 2026. Apto riego'],
        ];
        foreach ($raData as [$cId, $plantId, $aDate, $sDate, $lab, $accred, $sType, $compliant, $notes]) {
            DB::table('residue_analyses')->insert([
                'campaign_id'                 => $cId,
                'plot_planting_id'            => $plantId,
                'viticulturist_id'            => $vitId,
                'analysis_date'               => $aDate,
                'sample_date'                 => $sDate,
                'laboratory_name'             => $lab,
                'laboratory_accreditation'    => $accred,
                'sample_type'                 => $sType,
                'results'                     => null,
                'overall_compliant'           => $compliant,
                'certificate_file'            => null,
                'notes'                       => $notes,
                'active'                      => true,
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ]);
        }

        // ── Gestión de Residuos (24) ──────────────────────────────────────────
        $rmData = [
            // 2024 (8)
            [$c24, $plotIds[0], null,         '2024-01-25', 'pruning_wood', 'incorporation',  1850.00, 'kg', 'Triturado e incorporado al suelo'],
            [$c24, $plotIds[1], null,         '2024-01-28', 'pruning_wood', 'incorporation',  2100.00, 'kg', 'Triturado e incorporado'],
            [$c24, $plotIds[2], null,         '2024-02-05', 'pruning_wood', 'composting',      980.00, 'kg', 'Compostaje in situ en linde'],
            [$c24, $plotIds[3], null,         '2024-02-10', 'pruning_wood', 'removal',         760.00, 'kg', 'Retirado por contratista'],
            [$c24, $plotIds[0], $plantingIds[0],'2024-09-10','grape_marc',  'sale',           4200.00, 'kg', 'Vendido a destilería Destilados GC SL'],
            [$c24, $plotIds[1], $plantingIds[2],'2024-09-12','grape_marc',  'biogas',         1800.00, 'kg', 'Cesión planta biogás agrario'],
            [$c24, $plotIds[4], null,         '2024-09-15', 'vine_leaves',  'incorporation',  1100.00, 'kg', 'Incorporación post-vendimia'],
            [$c24, null,        null,          '2024-10-20', 'other',        'composting',      420.00, 'kg', 'Restos varios post-vendimia'],
            // 2025 (8)
            [$c25, $plotIds[0], null,         '2025-01-20', 'pruning_wood', 'incorporation',  1920.00, 'kg', 'Triturado e incorporado'],
            [$c25, $plotIds[1], null,         '2025-01-23', 'pruning_wood', 'incorporation',  2200.00, 'kg', 'Triturado e incorporado'],
            [$c25, $plotIds[2], null,         '2025-02-01', 'pruning_wood', 'composting',     1020.00, 'kg', 'Compostaje + aporte al suelo'],
            [$c25, $plotIds[3], null,         '2025-02-08', 'pruning_wood', 'removal',         820.00, 'kg', 'Retirado por Podas Atlánticas'],
            [$c25, $plotIds[0], $plantingIds[0],'2025-09-05','grape_marc',  'sale',           4600.00, 'kg', 'Vendido a destilería'],
            [$c25, $plotIds[1], $plantingIds[2],'2025-09-08','grape_marc',  'biogas',         2100.00, 'kg', 'Planta biogás Agaete'],
            [$c25, $plotIds[4], null,         '2025-09-12', 'vine_leaves',  'incorporation',  1200.00, 'kg', 'Incorporación folios post-vendimia'],
            [$c25, null,        null,          '2025-10-15', 'other',        'composting',      380.00, 'kg', 'Restos generales campaña 2025'],
            // 2026 (8)
            [$c26, $plotIds[0], null,         '2026-01-22', 'pruning_wood', 'incorporation',  1980.00, 'kg', 'Triturado e incorporado 2026'],
            [$c26, $plotIds[1], null,         '2026-01-25', 'pruning_wood', 'incorporation',  2300.00, 'kg', 'Triturado e incorporado 2026'],
            [$c26, $plotIds[2], null,         '2026-02-03', 'pruning_wood', 'composting',     1050.00, 'kg', 'Compostaje 2026'],
            [$c26, $plotIds[3], null,         '2026-02-08', 'pruning_wood', 'removal',         840.00, 'kg', 'Retirado contratista 2026'],
            [$c26, $plotIds[4], null,         '2026-02-12', 'pruning_wood', 'incorporation',   710.00, 'kg', 'Triturado parcela menor 2026'],
            [$c26, $plotIds[0], null,         '2026-03-05', 'grass',        'incorporation',   620.00, 'kg', 'Cubiertas vegetales trituradas'],
            [$c26, $plotIds[1], null,         '2026-03-05', 'grass',        'incorporation',   540.00, 'kg', 'Cubiertas vegetales trituradas'],
            [$c26, null,        null,          '2026-03-20', 'other',        'composting',      210.00, 'kg', 'Restos varios primer trimestre'],
        ];
        foreach ($rmData as [$cId, $pId, $ppId, $date, $matType, $pracType, $qty, $unit, $notes]) {
            DB::table('residue_managements')->insert([
                'campaign_id'        => $cId,
                'plot_id'            => $pId,
                'plot_planting_id'   => $ppId,
                'viticulturist_id'   => $vitId,
                'date'               => $date,
                'practice_type'      => $pracType,
                'material_type'      => $matType,
                'estimated_quantity' => $qty,
                'quantity_unit'      => $unit,
                'justification'      => null,
                'notes'              => $notes,
                'active'             => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Consumo Energético (48) ───────────────────────────────────────────
        $energyTypes = [
            ['diesel', 'liters', 80.0, 1.42, 'diesel', 'Tractor poda + laboreo'],
            ['diesel', 'liters', 45.0, 1.42, 'diesel', 'Atomizador tratamientos'],
            ['electricity', 'kwh', 220.0, 0.18, 'electricity', 'Riego goteo + bomba'],
            ['diesel', 'liters', 35.0, 1.42, 'diesel', 'Transporte vendimia'],
            ['electricity', 'kwh', 150.0, 0.18, 'electricity', 'Instalaciones campo'],
            ['diesel', 'liters', 60.0, 1.42, 'diesel', 'Laboreo suelo primavera'],
            ['electricity', 'kwh', 300.0, 0.18, 'electricity', 'Riego verano'],
            ['diesel', 'liters', 90.0, 1.42, 'diesel', 'Microvendimiadora cosecha'],
        ];
        $energyMonths2024 = ['2024-01-15','2024-02-15','2024-04-15','2024-06-15','2024-07-15','2024-08-15','2024-09-15','2024-10-15'];
        $energyMonths2025 = ['2025-01-15','2025-02-15','2025-04-15','2025-06-15','2025-07-15','2025-08-15','2025-09-15','2025-10-15'];
        $energyMonths2026 = ['2026-01-15','2026-02-15','2026-03-10','2026-03-25','2026-04-05','2026-04-10','2026-04-11','2026-04-11'];
        foreach ([[$c24,$energyMonths2024],[$c25,$energyMonths2025],[$c26,$energyMonths2026]] as [$cId, $months]) {
            foreach ($energyTypes as $i => [$eType, $unit, $qty, $cpu, $co2Type, $desc]) {
                $machineId = ($eType === 'diesel') ? $mach1 : null;
                $co2 = $co2Type === 'diesel' ? round($qty * 2.68, 3) : round($qty * 0.233, 3);
                DB::table('energy_usages')->insert([
                    'campaign_id'        => $cId,
                    'viticulturist_id'   => $vitId,
                    'activity_id'        => null,
                    'machinery_id'       => $machineId,
                    'date'               => $months[$i],
                    'energy_type'        => $eType,
                    'unit'               => $unit,
                    'quantity'           => $qty,
                    'cost_per_unit'      => $cpu,
                    'total_cost'         => round($qty * $cpu, 2),
                    'co2_kg_equivalent'  => $co2,
                    'usage_description'  => $desc,
                    'notes'              => null,
                    'active'             => true,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }

        // ── Concesiones de Agua (6) ───────────────────────────────────────────
        $waterRows = [
            ['subterranea',        'GC-SUB-2019-0088', 'Pozo Barranco del Pino',    'CHC — Confederación Hidrográfica de Canarias', '2019-06-01', '2029-05-31', 15000.0, 11200.0, 5.40],
            ['comunidad_regantes', 'CGR-GC-2021-014',  'Canal Noreste — Agaete',    'Comunidad Regantes Canal Noreste GC',         '2021-03-10', null,          8000.0,  6800.0,  2.90],
            ['superficial',        'GC-SUP-2020-0031', 'Arroyo Barranco Seco',      'CHC — Confederación Hidrográfica de Canarias', '2020-09-15', '2025-09-14', 5000.0,  4100.0,  1.80],
            ['superficial',        'GC-SUP-2025-0102', 'Arroyo Barranco Seco — renovación', 'CHC',                              '2025-10-01', '2030-09-30', 5500.0,  1200.0,  1.80],
            ['subterranea',        'GC-SUB-2022-0145', 'Pozo La Aldea — parcelas arrendadas', 'CHC',                           '2022-04-20', '2032-04-19', 6000.0,  3500.0,  2.10],
            ['otro',               null,               'Agua de lluvia captada en aljibes',  'No requiere concesión',           null,         null,          1200.0,   800.0,  null],
        ];
        foreach ($waterRows as [$type, $num, $body, $auth, $cDate, $expDate, $maxVol, $usedVol, $surface]) {
            DB::table('water_concessions')->insert([
                'viticulturist_id'  => $vitId,
                'campaign_id'       => null,
                'concession_type'   => $type,
                'concession_number' => $num,
                'water_body'        => $body,
                'authority'         => $auth,
                'concession_date'   => $cDate,
                'expiry_date'       => $expDate,
                'max_volume_m3'     => $maxVol,
                'used_volume_m3'    => $usedVol,
                'surface_ha'        => $surface,
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Planes de Fertilización (3) ───────────────────────────────────────
        $fpRows = [
            [$c24, 2024, 'active',   '2024-01-20', 'Dr. Antonio Suárez Pérez', 8.45, 65.2, 20.1, 48.3,
             '[{"plot_id":1,"crop":"viña","n_kg_ha":62.0,"p_kg_ha":18.5,"k_kg_ha":45.0},{"plot_id":2,"crop":"viña","n_kg_ha":68.0,"p_kg_ha":22.0,"k_kg_ha":52.0}]'],
            [$c25, 2025, 'active',   '2025-01-18', 'Dr. Antonio Suárez Pérez', 8.45, 67.8, 21.4, 50.1,
             '[{"plot_id":1,"crop":"viña","n_kg_ha":65.0,"p_kg_ha":20.0,"k_kg_ha":47.0},{"plot_id":2,"crop":"viña","n_kg_ha":70.0,"p_kg_ha":23.0,"k_kg_ha":53.0}]'],
            [$c26, 2026, 'draft',    null,          'Dr. Antonio Suárez Pérez', 8.45, 70.0, 22.0, 51.5,
             '[{"plot_id":1,"crop":"viña","n_kg_ha":68.0,"p_kg_ha":21.0,"k_kg_ha":49.0}]'],
        ];
        foreach ($fpRows as [$cId, $yr, $status, $approvalDate, $preparedBy, $surface, $n, $p, $k, $lines]) {
            DB::table('fertilization_plans')->insert([
                'viticulturist_id'   => $vitId,
                'campaign_id'        => $cId,
                'plan_year'          => $yr,
                'nitrate_zone'       => false,
                'prepared_by'        => $preparedBy,
                'approval_date'      => $approvalDate,
                'total_surface_ha'   => $surface,
                'total_n_kg_ha'      => $n,
                'total_p_kg_ha'      => $p,
                'total_k_kg_ha'      => $k,
                'plan_lines'         => $lines,
                'status'             => $status,
                'notes'              => null,
                'active'             => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Devolución Envases Fitosanitarios (21) ────────────────────────────
        $prodId0 = $productIds[0] ?? null;
        $prodId1 = $productIds[1] ?? null;
        $crData = [
            // 2024 (7)
            [$c24, $prodId0, '2024-03-10', 'Folio Gold WG',  'ES-00421-01', 'plastic', 1.0,  12,  8.4,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c24, $prodId1, '2024-05-15', 'Talendo',        'ES-00589-02', 'plastic', 1.0,   8,  5.6,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c24, null,     '2024-06-20', 'Cuprosan Azul',  'ES-00210-03', 'plastic', 5.0,   4, 12.0,  'sigfito', 'Punto SIGFITO — Teror'],
            [$c24, null,     '2024-07-05', 'Movento 150 OD', 'ES-00834-04', 'glass',   0.25,  6,  1.2,  'field',   'Almacén propio — entrega en campaña'],
            [$c24, null,     '2024-09-18', 'Botryticide WP', null,          'plastic', 0.5,  10,  4.0,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c24, null,     '2024-10-05', 'Cobre 50 WG',    'ES-00105-01', 'plastic', 1.0,   6,  4.2,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c24, null,     '2024-10-20', 'Varios fungicidas', null,        'cardboard',null, 4,  2.8,  'other',   'Recogida campaña por gestor autorizado'],
            // 2025 (7)
            [$c25, $prodId0, '2025-03-08', 'Folio Gold WG',  'ES-00421-01', 'plastic', 1.0,  14,  9.8,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c25, $prodId1, '2025-05-12', 'Talendo',        'ES-00589-02', 'plastic', 1.0,  10,  7.0,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c25, null,     '2025-06-15', 'Cuprosan Azul',  'ES-00210-03', 'plastic', 5.0,   5, 15.0,  'sigfito', 'Punto SIGFITO — Teror'],
            [$c25, null,     '2025-06-30', 'Movento 150 OD', 'ES-00834-04', 'glass',   0.25,  8,  1.6,  'field',   'Almacén propio'],
            [$c25, null,     '2025-09-12', 'Botryticide WP', null,          'plastic', 0.5,  12,  4.8,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c25, null,     '2025-10-08', 'Cobre 50 WG',    'ES-00105-01', 'plastic', 1.0,   8,  5.6,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c25, null,     '2025-10-25', 'Varios fungicidas', null,        'cardboard',null, 5,  3.5,  'other',   'Gestor autorizado recogida anual'],
            // 2026 (7)
            [$c26, $prodId0, '2026-02-20', 'Folio Gold WG',  'ES-00421-01', 'plastic', 1.0,   6,  4.2,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c26, $prodId1, '2026-03-05', 'Talendo',        'ES-00589-02', 'plastic', 1.0,   4,  2.8,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c26, null,     '2026-03-15', 'Cuprosan Azul',  'ES-00210-03', 'plastic', 5.0,   2,  6.0,  'sigfito', 'Punto SIGFITO — Teror'],
            [$c26, null,     '2026-03-22', 'Cobre 50 WG',    'ES-00105-01', 'plastic', 1.0,   4,  2.8,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c26, null,     '2026-04-01', 'Movento 150 OD', 'ES-00834-04', 'glass',   0.25,  4,  0.8,  'field',   'Almacén propio'],
            [$c26, null,     '2026-04-05', 'Varios (primavera)', null,       'plastic', null,  3,  1.5,  'sigfito', 'Punto SIGFITO — Agaete'],
            [$c26, null,     '2026-04-10', 'Residuos miscelánea', null,      'flexible',null,  2,  0.9,  'other',   'Gestor autorizado'],
        ];
        foreach ($crData as [$cId, $pId, $date, $pName, $regNum, $cType, $cSize, $qty, $weight, $system, $point]) {
            DB::table('phytosanitary_container_returns')->insert([
                'viticulturist_id'           => $vitId,
                'campaign_id'                => $cId,
                'phytosanitary_product_id'   => $pId,
                'date'                       => $date,
                'product_name'               => $pName,
                'registration_number'        => $regNum,
                'container_type'             => $cType,
                'container_size_liters'      => $cSize,
                'containers_quantity'        => $qty,
                'total_weight_kg'            => $weight,
                'collection_system'          => $system,
                'collection_point'           => $point,
                'transport_document'         => null,
                'notes'                      => null,
                'active'                     => true,
                'created_at'                 => $now,
                'updated_at'                 => $now,
            ]);
        }

        // ── Declaraciones de Vendimia (2) ─────────────────────────────────────
        foreach ([
            [$c24, 2024, '2024-10-01', '2024-10-15', 'Consejo Regulador DOP Gran Canaria', 'DV-GC-2024-0342', 8.45, 38200.00, 'submitted', null,
             '[{"variedad":"Listán Negro","kg":18500},{"variedad":"Listán Blanco","kg":12400},{"variedad":"Negramoll","kg":4800},{"variedad":"Moscatel","kg":2500}]'],
            [$c25, 2025, '2025-10-05', '2025-10-18', 'Consejo Regulador DOP Gran Canaria', 'DV-GC-2025-0389', 8.45, 41500.00, 'accepted', null,
             '[{"variedad":"Listán Negro","kg":20100},{"variedad":"Listán Blanco","kg":13600},{"variedad":"Negramoll","kg":5200},{"variedad":"Moscatel","kg":2600}]'],
        ] as [$cId, $yr, $dDate, $sDate, $auth, $ref, $surface, $totalKg, $status, $rejection, $lines]) {
            DB::table('harvest_declarations')->insert([
                'viticulturist_id'  => $vitId,
                'campaign_id'       => $cId,
                'declaration_year'  => $yr,
                'declaration_date'  => $dDate,
                'submission_date'   => $sDate,
                'authority'         => $auth,
                'reference_number'  => $ref,
                'total_surface_ha'  => $surface,
                'total_kg'          => $totalKg,
                'declaration_lines' => $lines,
                'status'            => $status,
                'rejection_reason'  => $rejection,
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Subproductos Vendimia (12) ────────────────────────────────────────
        $bpData = [
            [$c24, '2024-09-05', 'pomace', 'distillery',         4200.0, 'Destilados GC SL',      'DES-2024-0041'],
            [$c24, '2024-09-05', 'stem',   'composting',          820.0, 'Compostería Agaete',    null],
            [$c24, '2024-09-10', 'pomace', 'cooperative',        1800.0, 'Coop. Vitivinícola',    'COOP-2024-0088'],
            [$c24, '2024-09-15', 'lees',   'distillery',          380.0, 'Destilados GC SL',      'DES-2024-0052'],
            [$c25, '2025-09-03', 'pomace', 'distillery',         4600.0, 'Destilados GC SL',      'DES-2025-0038'],
            [$c25, '2025-09-03', 'stem',   'composting',          890.0, 'Compostería Agaete',    null],
            [$c25, '2025-09-08', 'pomace', 'cooperative',        2100.0, 'Coop. Vitivinícola',    'COOP-2025-0091'],
            [$c25, '2025-09-12', 'lees',   'distillery',          420.0, 'Destilados GC SL',      'DES-2025-0049'],
            [$c26, '2026-04-01', 'other',  'authorized_landfill', 120.0, 'Vertedero Municipal GC','VER-2026-0012'],
            [$c26, '2026-04-05', 'stem',   'composting',          180.0, 'Compostería Agaete',    null],
            [$c26, '2026-04-05', 'other',  'composting',           95.0, 'Compostería Agaete',    null],
            [$c26, '2026-04-10', 'lees',   'distillery',           50.0, 'Destilados GC SL',      null],
        ];
        foreach ($bpData as [$cId, $date, $type, $dest, $qty, $destName, $docRef]) {
            DB::table('harvest_byproducts')->insert([
                'viticulturist_id'   => $vitId,
                'campaign_id'        => $cId,
                'date'               => $date,
                'byproduct_type'     => $type,
                'quantity_kg'        => $qty,
                'destination_type'   => $dest,
                'destination_name'   => $destName,
                'document_reference' => $docRef,
                'notes'              => null,
                'active'             => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Certificaciones (6) ───────────────────────────────────────────────
        $certs = [
            ['ecologico',             'CAAE — Canarias',                     'CAAE-GC-2022-0401', '2022-05-01', '2025-04-30', 'Parcelas 1, 2 y 3. Viñedo ecológico certificado'],
            ['ecologico',             'CAAE — Canarias',                     'CAAE-GC-2025-0312', '2025-05-01', '2027-04-30', 'Renovación certificado ecológico 2025-2027'],
            ['produccion_integrada',  'Gobierno de Canarias',                'PI-GC-2020-0142',   '2020-07-01', null,          'Todas las parcelas. Producción Integrada Canarias'],
            ['denominacion_origen',   'Consejo Regulador DOP Gran Canaria',  'DOP-GC-2021-0034',  '2021-03-15', null,          'Inscripción en DOP Gran Canaria'],
            ['globalgap',             'Bureau Veritas Certification España',  'GG-ES-2023-88421',  '2023-06-15', '2025-06-14', 'GlobalG.A.P. — vigente hasta 2025'],
            ['globalgap',             'Bureau Veritas Certification España',  'GG-ES-2025-91204',  '2025-07-01', '2027-06-30', 'GlobalG.A.P. renovado 2025-2027'],
        ];
        foreach ($certs as [$type, $body, $num, $issue, $expiry, $scope]) {
            DB::table('certifications')->insert([
                'viticulturist_id'  => $vitId,
                'certification_type'=> $type,
                'certifying_body'   => $body,
                'certificate_number'=> $num,
                'issue_date'        => $issue,
                'expiry_date'       => $expiry,
                'scope'             => $scope,
                'audit_date'        => null,
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Exportaciones CUE (6) ─────────────────────────────────────────────
        if ($expId) {
            $cueRows = [
                [$c24, 2024, 'quarterly', '2024-01-01', '2024-03-31', 'accepted', '2024-04-10'],
                [$c24, 2024, 'quarterly', '2024-04-01', '2024-06-30', 'accepted', '2024-07-08'],
                [$c24, 2024, 'annual',    '2024-01-01', '2024-12-31', 'accepted', '2025-01-20'],
                [$c25, 2025, 'quarterly', '2025-01-01', '2025-03-31', 'accepted', '2025-04-12'],
                [$c25, 2025, 'quarterly', '2025-04-01', '2025-06-30', 'sent',     '2025-07-10'],
                [$c26, 2026, 'quarterly', '2026-01-01', '2026-03-31', 'draft',    null],
            ];
            foreach ($cueRows as [$cId, $yr, $period, $from, $to, $status, $sentAt]) {
                DB::table('cue_exports')->insert([
                    'exploitation_id' => $expId,
                    'viticulturist_id'=> $vitId,
                    'campaign_year'   => $yr,
                    'period_type'     => $period,
                    'from_date'       => $from,
                    'to_date'         => $to,
                    'status'          => $status,
                    'payload_json'    => null,
                    'response_json'   => null,
                    'generated_at'    => $sentAt ? date('Y-m-d H:i:s', strtotime($sentAt . ' -1 day')) : null,
                    'sent_at'         => $sentAt ? $sentAt . ' 09:00:00' : null,
                    'accepted_at'     => $status === 'accepted' ? $sentAt . ' 11:00:00' : null,
                    'error_message'   => null,
                    'file_path'       => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }

        // ── Informes Oficiales (8) ────────────────────────────────────────────
        $reports = [
            [$c24, 'phytosanitary_treatments',  '2024-01-01', '2024-06-30', 'completed'],
            [$c24, 'phytosanitary_treatments',  '2024-07-01', '2024-12-31', 'completed'],
            [$c24, 'full_digital_notebook',     '2024-01-01', '2024-12-31', 'completed'],
            [$c25, 'phytosanitary_treatments',  '2025-01-01', '2025-06-30', 'completed'],
            [$c25, 'phytosanitary_treatments',  '2025-07-01', '2025-12-31', 'completed'],
            [$c25, 'full_digital_notebook',     '2025-01-01', '2025-12-31', 'completed'],
            [$c26, 'phytosanitary_treatments',  '2026-01-01', '2026-03-31', 'completed'],
            [$c26, 'full_digital_notebook',     '2026-01-01', '2026-03-31', 'processing'],
        ];
        foreach ($reports as $i => [$cId, $type, $start, $end, $procStatus]) {
            $hash = md5("report_{$vitId}_{$type}_{$start}_{$end}_{$i}");
            DB::table('official_reports')->insert([
                'user_id'             => $vitId,
                'report_type'         => $type,
                'period_start'        => $start,
                'period_end'          => $end,
                'signature_hash'      => $hash,
                'signed_at'           => $now,
                'signed_ip'           => '192.168.1.1',
                'signature_metadata'  => null,
                'verification_code'   => substr(md5("verify_{$hash}"), 0, 32),
                'verification_count'  => 0,
                'last_verified_at'    => null,
                'report_metadata'     => null,
                'pdf_path'            => $procStatus === 'completed' ? "reports/vit_{$vitId}/{$type}_{$start}_{$end}.pdf" : null,
                'pdf_size'            => $procStatus === 'completed' ? rand(120000, 850000) : null,
                'pdf_filename'        => $procStatus === 'completed' ? "informe_{$type}_{$start}.pdf" : null,
                'is_valid'            => true,
                'invalidation_reason' => null,
                'invalidated_at'      => null,
                'invalidated_by'      => null,
                'processing_status'   => $procStatus,
                'processing_error'    => null,
                'completed_at'        => $procStatus === 'completed' ? $now : null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }

    // ─── 18. PAC ─────────────────────────────────────────────────────────────

    private function createPAC(array $plotIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId = self::VIT_USER_ID;

        foreach ([
            [$c24, 2024, 'GC-PAC-2024-35003-0021', 8.45, 8.20, 'approved', '2024-04-20'],
            [$c25, 2025, 'GC-PAC-2025-35003-0021', 8.45, 8.35, 'approved', '2025-04-18'],
            [$c26, 2026, null,                      8.45, 8.45, 'draft',    null],
        ] as [$cId, $yr, $ref, $declared, $eligible, $status, $submittedAt]) {
            $decId = DB::table('pac_declarations')->insertGetId([
                'viticulturist_id'     => $vitId,
                'year'                 => $yr,
                'reference_number'     => $ref,
                'status'               => $status,
                'submitted_at'         => $submittedAt ? $submittedAt . ' 10:00:00' : null,
                'total_declared_area'  => $declared,
                'total_eligible_area'  => $eligible,
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            // Items (5 parcelas por declaración = 15 total)
            $areas = [1.82, 2.10, 1.95, 1.48, 1.10];
            foreach ($plotIds as $i => $pId) {
                $decArea  = $areas[$i] ?? 1.00;
                $eligArea = round($decArea * 0.97, 3);
                DB::table('pac_declaration_items')->insert([
                    'declaration_id' => $decId,
                    'plot_id'        => $pId,
                    'declared_area'  => $decArea,
                    'eligible_area'  => $eligArea,
                    'eco_schemes'    => json_encode(['rotacion_cultivos' => false, 'cubiertas_vegetales' => true, 'reduccion_fitosanitarios' => true]),
                    'notes'          => null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }

            // Pagos por declaración (3-4 por año = ~12 total)
            if ($status === 'approved') {
                $paymentTypes = [
                    ['basic_payment',    4820.00, date('Y-12-15', mktime(0,0,0,1,1,$yr))],
                    ['eco_scheme',       1240.00, date('Y-12-15', mktime(0,0,0,1,1,$yr))],
                    ['associated_aid',    680.00, date('Y-12-20', mktime(0,0,0,1,1,$yr))],
                    ['transitional',      320.00, date('Y-12-20', mktime(0,0,0,1,1,$yr))],
                ];
                foreach ($paymentTypes as [$pType, $amount, $pDate]) {
                    DB::table('pac_payments')->insert([
                        'viticulturist_id' => $vitId,
                        'declaration_id'   => $decId,
                        'year'             => $yr,
                        'payment_type'     => $pType,
                        'amount'           => $amount,
                        'payment_date'     => $pDate,
                        'reference'        => "PAC-{$yr}-{$pType}-GC",
                        'notes'            => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
            }
        }
    }

    // ─── 19. Facturación ─────────────────────────────────────────────────────

    private function createBilling(array $plotIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId = self::VIT_USER_ID;

        // ── Costes por Parcela (45) ───────────────────────────────────────────
        $categories = [
            'labor', 'machinery', 'materials', 'phytosanitary', 'fertilizer',
            'water', 'insurance', 'transport', 'subcontracting', 'other',
        ];
        $plotCosts = [
            // 2024 (15)
            [$c24, $plotIds[0], 'labor',          'Jornales poda manual — 8 jornadas',        1440.00, '2024-01-20', 'Cuadrilla propia'],
            [$c24, $plotIds[0], 'phytosanitary',  'Folio Gold WG + Talendo — campaña 2024',    580.00, '2024-04-15', 'Cooperativa Agaete'],
            [$c24, $plotIds[1], 'fertilizer',     'Abono orgánico 800 kg + NPK foliar',        420.00, '2024-03-10', 'Agropiensos SA'],
            [$c24, $plotIds[1], 'labor',          'Jornales aclareo racimos',                  360.00, '2024-06-05', 'Cuadrilla propia'],
            [$c24, $plotIds[2], 'water',          'Canon agua comun. regantes Q2 2024',        180.00, '2024-06-30', 'Comunidad Regantes'],
            [$c24, $plotIds[2], 'subcontracting', 'Tratamiento fitosanitario contratado',      620.00, '2024-05-10', 'AgroService Canarias'],
            [$c24, $plotIds[3], 'machinery',      'Combustible tractores campaña',             380.00, '2024-08-01', null],
            [$c24, $plotIds[3], 'transport',      'Transporte uva a bodega',                   280.00, '2024-09-02', 'Transportes Viñedo GC'],
            [$c24, $plotIds[4], 'insurance',      'Prima seguro multiriesgo 2024',            1250.00, '2024-04-01', 'Agroseguro'],
            [$c24, $plotIds[0], 'materials',      'Tutores + alambre reposición',              190.00, '2024-02-15', 'Agropiensos SA'],
            [$c24, $plotIds[1], 'other',          'Análisis suelo y foliar',                   390.00, '2024-05-10', 'Lab. Agrícola Canario'],
            [$c24, $plotIds[2], 'labor',          'Jornales vendimia manual',                 1800.00, '2024-08-20', 'Cuadrilla Hernández'],
            [$c24, $plotIds[3], 'fertilizer',     'Fertirrigación verano 2024',                310.00, '2024-07-01', 'Nutrición Vegetal GC'],
            [$c24, $plotIds[4], 'phytosanitary',  'Cobre 50 WG + anti-Botrytis',              240.00, '2024-07-15', 'Cooperativa Agaete'],
            [$c24, $plotIds[0], 'subcontracting', 'Análisis residuos laboratorio',             390.00, '2024-07-20', 'Lab. Agrícola Canario'],
            // 2025 (15)
            [$c25, $plotIds[0], 'labor',          'Jornales poda seca — 9 jornadas',          1620.00, '2025-01-15', 'Cuadrilla propia'],
            [$c25, $plotIds[1], 'phytosanitary',  'Tratamientos preventivos mildiu + oídio',   610.00, '2025-04-22', 'AgroService Canarias'],
            [$c25, $plotIds[2], 'fertilizer',     'Plan fertilización 2025 — NPK + orgánico',  470.00, '2025-03-08', 'Agropiensos SA'],
            [$c25, $plotIds[3], 'labor',          'Aclareo racimos + deshojado',               400.00, '2025-06-10', 'Cuadrilla propia'],
            [$c25, $plotIds[4], 'water',          'Canon agua comun. regantes 2025',           195.00, '2025-06-30', 'Comunidad Regantes'],
            [$c25, $plotIds[0], 'machinery',      'Combustible campaña completa 2025',         410.00, '2025-09-01', null],
            [$c25, $plotIds[1], 'subcontracting', 'Poda manual subcontratada',                1050.00, '2025-01-08', 'Podas Atlánticas'],
            [$c25, $plotIds[2], 'transport',      'Transporte uva bodega 2025',                310.00, '2025-08-30', 'Transportes Viñedo GC'],
            [$c25, $plotIds[3], 'insurance',      'Prima seguro multiriesgo 2025',            1380.00, '2025-04-01', 'Agroseguro'],
            [$c25, $plotIds[4], 'materials',      'Postes + alambre + grapas',                 240.00, '2025-02-20', 'Agropiensos SA'],
            [$c25, $plotIds[0], 'other',          'Asesoría agronómica anual 2025',            840.00, '2025-01-10', 'Estudio Agronómico Atlántico'],
            [$c25, $plotIds[1], 'labor',          'Jornales vendimia manual 2025',            1950.00, '2025-08-18', 'Cuadrilla Hernández'],
            [$c25, $plotIds[2], 'phytosanitary',  'Botryticide + Movento 2025',               320.00, '2025-07-10', 'Cooperativa Agaete'],
            [$c25, $plotIds[3], 'fertilizer',     'Fertirrigación verano 2025',               330.00, '2025-07-05', 'Nutrición Vegetal GC'],
            [$c25, $plotIds[4], 'subcontracting', 'Mantenimiento sistema de riego',            340.00, '2025-06-01', 'Riegos Canarios'],
            // 2026 (15)
            [$c26, $plotIds[0], 'labor',          'Jornales poda 2026 — 10 jornadas',         1800.00, '2026-01-10', 'Cuadrilla propia'],
            [$c26, $plotIds[1], 'subcontracting', 'Poda subcontratada parcela sur',           1100.00, '2026-01-18', 'Podas Atlánticas'],
            [$c26, $plotIds[2], 'fertilizer',     'Abono post-poda 2026',                      490.00, '2026-02-15', 'Agropiensos SA'],
            [$c26, $plotIds[3], 'subcontracting', 'Laboreo primavera 2026',                    720.00, '2026-02-10', 'Labores Agrícolas Guía'],
            [$c26, $plotIds[4], 'phytosanitary',  'Tratamiento brotación 2026',                640.00, '2026-03-28', 'AgroService Canarias'],
            [$c26, $plotIds[0], 'water',          'Canon agua Q1 2026',                        210.00, '2026-03-31', 'Comunidad Regantes'],
            [$c26, $plotIds[1], 'materials',      'Tutores y malla protectora',                280.00, '2026-02-25', 'Agropiensos SA'],
            [$c26, $plotIds[2], 'labor',          'Jornales sellado heridas poda',             480.00, '2026-02-20', 'Cuadrilla propia'],
            [$c26, $plotIds[3], 'insurance',      'Prima seguros 2026 (parcial)',             1450.00, '2026-04-01', 'Agroseguro'],
            [$c26, $plotIds[4], 'subcontracting', 'Instalación goteros nuevos',                290.00, '2026-03-20', 'Riegos Canarios'],
            [$c26, $plotIds[0], 'other',          'Análisis suelo brotación 2026',             180.00, '2026-03-15', 'Aqualogic Canarias'],
            [$c26, $plotIds[1], 'machinery',      'Combustible primer trimestre 2026',         160.00, '2026-03-31', null],
            [$c26, $plotIds[2], 'fertilizer',     'Fertirrigación brotación',                  510.00, '2026-03-12', 'Nutrición Vegetal GC'],
            [$c26, $plotIds[3], 'phytosanitary',  'Folio Gold 2026 — aplicación 1',            220.00, '2026-03-20', 'Cooperativa Agaete'],
            [$c26, $plotIds[4], 'other',          'Asesoría técnica primer trimestre',         420.00, '2026-01-15', 'Estudio Agronómico Atlántico'],
        ];
        foreach ($plotCosts as [$cId, $pId, $cat, $desc, $amount, $date, $supplier]) {
            DB::table('plot_costs')->insert([
                'viticulturist_id'  => $vitId,
                'plot_id'           => $pId,
                'campaign_id'       => $cId,
                'category'          => $cat,
                'description'       => $desc,
                'amount'            => $amount,
                'cost_date'         => $date,
                'supplier'          => $supplier,
                'invoice_reference' => null,
                'notes'             => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Cosecha Comercializada (10) ───────────────────────────────────────
        // Consultar harvests creados para este viticultor
        $activityIds = DB::table('agricultural_activities')
            ->where('viticulturist_id', $vitId)
            ->pluck('id');

        if ($activityIds->isNotEmpty()) {
            $harvestRows = DB::table('harvests as h')
                ->join('agricultural_activities as a', 'h.activity_id', '=', 'a.id')
                ->whereIn('h.activity_id', $activityIds)
                ->orderBy('h.harvest_start_date')
                ->limit(10)
                ->get(['h.id', 'h.harvest_start_date', 'h.total_weight', 'a.campaign_id']);

            $destinations = [
                ['own_winery', 'Bodega Agaete Artesana SL', '14.00', 'REGA-GC-0421'],
                ['cooperative','Coop. Vitivinícola del Norte', '12.50', 'REGA-GC-0118'],
                ['third_party','Distribuciones Canarias Vino', '13.20', null],
            ];

            foreach ($harvestRows as $i => $harvest) {
                $dest = $destinations[$i % 3];
                $qty  = min((float)$harvest->total_weight, 2000.0);
                DB::table('marketed_harvests')->insert([
                    'harvest_id'          => $harvest->id,
                    'campaign_id'         => $harvest->campaign_id,
                    'viticulturist_id'    => $vitId,
                    'delivery_date'       => $harvest->harvest_start_date,
                    'quantity_kg'         => $qty,
                    'destination_type'    => $dest[0],
                    'buyer_name'          => $dest[1],
                    'buyer_rega_code'     => $dest[3],
                    'transport_document'  => 'TRANS-' . date('Y', strtotime($harvest->harvest_start_date)) . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'vehicle_plate'       => '4521-GCN',
                    'price_per_kg'        => $dest[2],
                    'total_value'         => round($qty * (float)$dest[2], 2),
                    'notes'               => null,
                    'active'              => true,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
    }

    // ─── 20. Datos operacionales ─────────────────────────────────────────────

    private function createOperationalData(array $plotIds, array $plantingIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId   = self::VIT_USER_ID;
        $winId   = self::WINERY_USER_ID;

        // ── Documentos de Campaña (15) ────────────────────────────────────────
        $docs = [
            [$c24, 'Certificado Ecológico CAAE 2024',              'certificate', 'documentos/campana/2024/cert_ecologico_2024.pdf'],
            [$c24, 'Análisis de Residuos Lote 1 — Julio 2024',    'lab_report',  'documentos/campana/2024/analisis_residuos_jul24.pdf'],
            [$c24, 'Análisis de Residuos Lote 2 — Agosto 2024',   'lab_report',  'documentos/campana/2024/analisis_residuos_ago24.pdf'],
            [$c24, 'Declaración de Vendimia 2024',                 'invoice',     'documentos/campana/2024/declaracion_vendimia_2024.pdf'],
            [$c24, 'Plan de Fertilización Campaña 2024',           'other',       'documentos/campana/2024/plan_fertilizacion_2024.pdf'],
            [$c25, 'Certificado GlobalG.A.P. 2025',                'certificate', 'documentos/campana/2025/globalgap_2025.pdf'],
            [$c25, 'Plan de Fertilización Campaña 2025',           'other',       'documentos/campana/2025/plan_fertilizacion_2025.pdf'],
            [$c25, 'Análisis Suelo Parcelas — Abril 2025',         'lab_report',  'documentos/campana/2025/analisis_suelo_abr25.pdf'],
            [$c25, 'Autorización Fitosanitaria — Temporada 2025',  'authorization','documentos/campana/2025/autorizacion_fitosanitaria_2025.pdf'],
            [$c25, 'Declaración de Vendimia 2025',                 'invoice',     'documentos/campana/2025/declaracion_vendimia_2025.pdf'],
            [$c25, 'Mapa Parcelas actualizado 2025',               'map',         'documentos/campana/2025/mapa_parcelas_2025.pdf'],
            [$c26, 'Plan de Fertilización Borrador 2026',          'other',       'documentos/campana/2026/plan_fertilizacion_2026_borrador.pdf'],
            [$c26, 'Análisis Suelo Brotación 2026',                'lab_report',  'documentos/campana/2026/analisis_suelo_mar26.pdf'],
            [$c26, 'Renovación Seguro Agrario 2026',               'authorization','documentos/campana/2026/seguro_agrario_2026.pdf'],
            [$c26, 'Certificado Renovación Ecológico 2025-2027',   'certificate', 'documentos/campana/2026/cert_ecologico_2025_2027.pdf'],
        ];
        foreach ($docs as [$cId, $name, $type, $path]) {
            DB::table('campaign_documents')->insert([
                'campaign_id'       => $cId,
                'viticulturist_id'  => $vitId,
                'name'              => $name,
                'document_type'     => $type,
                'file_path'         => $path,
                'original_filename' => basename($path),
                'mime_type'         => 'application/pdf',
                'file_size_kb'      => rand(80, 2400),
                'notes'             => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ── Entornos de Parcelas (10) — 2 campañas × 5 parcelas ─────────────
        $envData = [
            // 2024
            [$c24, $plotIds[0], $plantingIds[0], false, null,  false, false, false, null, 50.0,  18.5, true],
            [$c24, $plotIds[1], $plantingIds[2], false, null,  false, false, false, null, 30.0,  12.8, false],
            [$c24, $plotIds[2], $plantingIds[4], true,  80.0,  true,  false, 'ZEC', 10.0, 45.0,  22.1, true],
            [$c24, $plotIds[3], null,            false, null,  false, false, false, null, 15.0,  8.4,  false],
            [$c24, $plotIds[4], null,            false, null,  false, false, false, null, 25.0,  11.2, false],
            // 2025
            [$c25, $plotIds[0], $plantingIds[0], false, null,  false, false, false, null, 50.0,  18.5, true],
            [$c25, $plotIds[1], $plantingIds[2], false, null,  false, false, false, null, 30.0,  12.8, false],
            [$c25, $plotIds[2], $plantingIds[4], true,  80.0,  true,  false, 'ZEC', 10.0, 45.0,  22.1, true],
            [$c25, $plotIds[3], null,            false, null,  false, false, false, null, 15.0,  8.4,  false],
            [$c25, $plotIds[4], null,            false, null,  false, false, false, null, 25.0,  11.2, false],
        ];
        foreach ($envData as [$cId, $pId, $ppId, $waterIntake, $waterDist, $protTotal, $protPartial, $protType, $buffer, $slope, $erosionRisk, $hasErosion]) {
            DB::table('plot_environments')->insert([
                'campaign_id'             => $cId,
                'plot_id'                 => $pId,
                'plot_planting_id'        => $ppId,
                'viticulturist_id'        => $vitId,
                'water_intake_nearby'     => $waterIntake,
                'water_intake_distance_m' => $waterDist,
                'protected_zone_total'    => $protTotal,
                'protected_zone_partial'  => $protPartial,
                'protection_zone_type'    => $protType,
                'buffer_zone_m'           => $buffer,
                'slope_pct'               => $slope,
                'erosion_risk'            => $hasErosion,
                'notes'                   => null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);
        }

        // ── Accesos Bodega al Cuaderno (2) ────────────────────────────────────
        DB::table('notebook_access_requests')->insertOrIgnore([
            ['winery_id' => $winId, 'viticulturist_id' => $vitId, 'status' => 'approved', 'requested_at' => '2024-02-01 10:00:00', 'responded_at' => '2024-02-02 09:30:00', 'created_at' => $now, 'updated_at' => $now],
            ['winery_id' => $winId, 'viticulturist_id' => $vitId, 'status' => 'approved', 'requested_at' => '2025-01-15 11:00:00', 'responded_at' => '2025-01-16 08:45:00', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ─── 11. Contenedores ────────────────────────────────────────────────────
    // type_id:     1=Barrica, 2=Depósito, 3=Tanque, 4=Tina, 5=Ánfora
    // material_id: 1=Roble Francés, 4=Acero Inoxidable, 5=Hormigón, 7=Fibra de Vidrio
    // unit_of_measurement_id: 3=Kilogramos (uva/mosto), 1=Litros (vino)

    private function createContainers($now): array
    {
        $vitId = self::VIT_USER_ID;

        $containers = [
            // ── Bins de vendimia — fibra de vidrio, 500 kg (BV-01…20) ──────
            ['name' => 'Bin Vendimia BV-01', 'description' => 'Bin recogida uva — parcela Lomo Grande',   'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-001', 'purchase_date' => '2022-08-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-02', 'description' => 'Bin recogida uva — parcela Lomo Grande',   'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-002', 'purchase_date' => '2022-08-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-03', 'description' => 'Bin recogida uva — parcela Lomo Grande',   'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-003', 'purchase_date' => '2022-08-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-04', 'description' => 'Bin recogida uva — parcela Barranco Seco', 'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-004', 'purchase_date' => '2022-08-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-05', 'description' => 'Bin recogida uva — parcela Barranco Seco', 'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-005', 'purchase_date' => '2022-08-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-06', 'description' => 'Bin recogida uva — parcela El Risco',      'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-006', 'purchase_date' => '2023-07-15', 'archived' => false],
            ['name' => 'Bin Vendimia BV-07', 'description' => 'Bin recogida uva — parcela El Risco',      'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-007', 'purchase_date' => '2023-07-15', 'archived' => false],
            ['name' => 'Bin Vendimia BV-08', 'description' => 'Bin recogida uva — parcela El Risco',      'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-008', 'purchase_date' => '2023-07-15', 'archived' => false],
            ['name' => 'Bin Vendimia BV-09', 'description' => 'Bin recogida uva — parcela Las Nieves',    'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-009', 'purchase_date' => '2023-07-15', 'archived' => false],
            ['name' => 'Bin Vendimia BV-10', 'description' => 'Bin recogida uva — parcela Las Nieves',    'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-010', 'purchase_date' => '2023-07-15', 'archived' => false],
            ['name' => 'Bin Vendimia BV-11', 'description' => 'Bin recogida uva — polivalente',           'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-011', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-12', 'description' => 'Bin recogida uva — polivalente',           'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-012', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-13', 'description' => 'Bin recogida uva — polivalente',           'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-013', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-14', 'description' => 'Bin recogida uva — polivalente',           'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-014', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-15', 'description' => 'Bin recogida uva — polivalente',           'capacity' => 500.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-015', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-16', 'description' => 'Bin recogida uva — reserva campaña',       'capacity' => 300.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-016', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-17', 'description' => 'Bin recogida uva — reserva campaña',       'capacity' => 300.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-017', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-18', 'description' => 'Bin recogida uva — reserva campaña',       'capacity' => 300.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-018', 'purchase_date' => '2024-06-01', 'archived' => false],
            ['name' => 'Bin Vendimia BV-19', 'description' => 'Bin antiguo — aún operativo',              'capacity' => 400.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-019', 'purchase_date' => '2019-08-10', 'archived' => false],
            ['name' => 'Bin Vendimia BV-20', 'description' => 'Bin antiguo — fuera de servicio',          'capacity' => 400.00, 'type_id' => 4, 'material_id' => 7, 'serial_number' => 'BV-AGT-020', 'purchase_date' => '2019-08-10', 'archived' => true],

            // ── Tinas de recepción — hormigón (TR-01…10) ─────────────────────
            ['name' => 'Tina Recepción TR-01', 'description' => 'Tina hormigón — recepción Listán Negro',    'capacity' => 2000.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-001', 'purchase_date' => '2018-04-10', 'next_maintenance_date' => '2026-09-01 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-02', 'description' => 'Tina hormigón — recepción Listán Blanco',   'capacity' => 2000.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-002', 'purchase_date' => '2018-04-10', 'next_maintenance_date' => '2026-09-01 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-03', 'description' => 'Tina hormigón — recepción Negramoll',       'capacity' => 1500.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-003', 'purchase_date' => '2019-03-20', 'next_maintenance_date' => '2026-09-01 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-04', 'description' => 'Tina hormigón — recepción Moscatel',        'capacity' => 1500.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-004', 'purchase_date' => '2019-03-20', 'next_maintenance_date' => '2026-09-01 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-05', 'description' => 'Tina hormigón — recepción Tintilla',        'capacity' => 1000.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-005', 'purchase_date' => '2020-05-05', 'next_maintenance_date' => '2026-09-15 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-06', 'description' => 'Tina hormigón — polivalente mezcla',        'capacity' => 1000.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-006', 'purchase_date' => '2020-05-05', 'next_maintenance_date' => '2026-09-15 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-07', 'description' => 'Tina hormigón — uva ecológica',             'capacity' => 800.00,  'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-007', 'purchase_date' => '2021-04-18', 'next_maintenance_date' => '2026-10-01 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-08', 'description' => 'Tina hormigón — uva ecológica',             'capacity' => 800.00,  'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-008', 'purchase_date' => '2021-04-18', 'next_maintenance_date' => '2026-10-01 00:00:00', 'archived' => false],
            ['name' => 'Tina Recepción TR-09', 'description' => 'Tina antigua — uso esporádico',             'capacity' => 1200.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-009', 'purchase_date' => '2015-06-01', 'archived' => false],
            ['name' => 'Tina Recepción TR-10', 'description' => 'Tina antigua — fuera de servicio',          'capacity' => 1200.00, 'type_id' => 4, 'material_id' => 5, 'serial_number' => 'TR-AGT-010', 'purchase_date' => '2015-06-01', 'archived' => true],

            // ── Depósitos isotérmicos — acero inox (DI-01…10) ────────────────
            ['name' => 'Depósito Isotérmico DI-01', 'description' => 'Depósito inox con camisa frío — mosto Listán Negro',   'capacity' => 5000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-001', 'purchase_date' => '2020-06-10', 'next_maintenance_date' => '2026-12-01 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-02', 'description' => 'Depósito inox con camisa frío — mosto Listán Blanco',  'capacity' => 5000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-002', 'purchase_date' => '2020-06-10', 'next_maintenance_date' => '2026-12-01 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-03', 'description' => 'Depósito inox — almacenamiento temporal mosto',        'capacity' => 3000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-003', 'purchase_date' => '2021-05-20', 'next_maintenance_date' => '2026-12-01 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-04', 'description' => 'Depósito inox — almacenamiento temporal mosto',        'capacity' => 3000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-004', 'purchase_date' => '2021-05-20', 'next_maintenance_date' => '2026-12-01 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-05', 'description' => 'Depósito inox — variedades minoritarias',              'capacity' => 2000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-005', 'purchase_date' => '2022-04-15', 'next_maintenance_date' => '2027-01-15 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-06', 'description' => 'Depósito inox — variedades minoritarias',              'capacity' => 2000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-006', 'purchase_date' => '2022-04-15', 'next_maintenance_date' => '2027-01-15 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-07', 'description' => 'Depósito inox — uva ecológica certificada',            'capacity' => 1500.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-007', 'purchase_date' => '2023-03-10', 'next_maintenance_date' => '2027-03-10 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-08', 'description' => 'Depósito inox — uva ecológica certificada',            'capacity' => 1500.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-008', 'purchase_date' => '2023-03-10', 'next_maintenance_date' => '2027-03-10 00:00:00', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-09', 'description' => 'Depósito inox — uso general campaña tardía',           'capacity' => 1000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-009', 'purchase_date' => '2023-03-10', 'archived' => false],
            ['name' => 'Depósito Isotérmico DI-10', 'description' => 'Depósito antiguo — fuera de servicio',                 'capacity' => 1000.00, 'type_id' => 2, 'material_id' => 4, 'serial_number' => 'DI-AGT-010', 'purchase_date' => '2014-08-01', 'archived' => true],

            // ── Tanques de fermentación — acero inox (TF-01…10) ───────────────
            ['name' => 'Tanque Fermentación TF-01', 'description' => 'Tanque inox fermentación controlada — tinto',       'capacity' => 4000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-001', 'purchase_date' => '2019-07-01', 'next_maintenance_date' => '2026-11-01 00:00:00', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-02', 'description' => 'Tanque inox fermentación controlada — tinto',       'capacity' => 4000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-002', 'purchase_date' => '2019-07-01', 'next_maintenance_date' => '2026-11-01 00:00:00', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-03', 'description' => 'Tanque inox fermentación controlada — blanco',      'capacity' => 3000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-003', 'purchase_date' => '2020-07-15', 'next_maintenance_date' => '2026-11-15 00:00:00', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-04', 'description' => 'Tanque inox fermentación controlada — blanco',      'capacity' => 3000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-004', 'purchase_date' => '2020-07-15', 'next_maintenance_date' => '2026-11-15 00:00:00', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-05', 'description' => 'Tanque inox fermentación — Moscatel',               'capacity' => 2000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-005', 'purchase_date' => '2021-06-20', 'next_maintenance_date' => '2026-12-20 00:00:00', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-06', 'description' => 'Tanque inox fermentación — variedades locales',     'capacity' => 2000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-006', 'purchase_date' => '2021-06-20', 'next_maintenance_date' => '2026-12-20 00:00:00', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-07', 'description' => 'Tanque inox polivalente — campaña extra',           'capacity' => 1500.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-007', 'purchase_date' => '2022-05-10', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-08', 'description' => 'Tanque inox polivalente — campaña extra',           'capacity' => 1500.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-008', 'purchase_date' => '2022-05-10', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-09', 'description' => 'Tanque inox pequeño — ensayos y parcelas nuevas',   'capacity' =>  800.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-009', 'purchase_date' => '2023-04-01', 'archived' => false],
            ['name' => 'Tanque Fermentación TF-10', 'description' => 'Tanque antiguo — fuera de servicio',                'capacity' => 1000.00, 'type_id' => 3, 'material_id' => 4, 'serial_number' => 'TF-AGT-010', 'purchase_date' => '2013-05-01', 'archived' => true],
        ];

        $ids = [];
        foreach ($containers as $container) {
            $ids[] = DB::table('containers')->insertGetId(array_merge($container, [
                'user_id'              => $vitId,
                'supplier_name'        => null,
                'container_room_id'    => null,
                'photos'               => null,
                'thumbnail_img'        => null,
                'oak_type'             => null,
                'toast_type'           => null,
                'x_position'           => null,
                'y_position'           => null,
                'next_maintenance_date' => $container['next_maintenance_date'] ?? null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]));
        }

        return $ids;
    }

    // ─── 21. Almacén de Insumos ──────────────────────────────────────────────

    private function createAlmacen(array $productIds, int $c24, int $c25, int $c26, $now): void
    {
        $vitId = self::VIT_USER_ID;

        // ── Almacén (1) ───────────────────────────────────────────────────────
        $warehouseId = DB::table('warehouses')->insertGetId([
            'user_id'     => $vitId,
            'name'        => 'Almacén Finca Agaete',
            'location'    => 'Camino Lomo Grande s/n, Agaete, Gran Canaria',
            'description' => 'Almacén principal de insumos agrícolas y fitosanitarios',
            'active'      => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // ── Suministros (10) ──────────────────────────────────────────────────
        // columns: name, commercial_name, supply_type, unit, initial_stock, current_stock, min_stock_alert, expiry_date
        $suppliesData = [
            ['Biohumus Sólido',          null, 'fertilizer', 'kg',  500.0, 320.0, 50.0,  null        ],
            ['Nitrato Amónico 33.5%',    null, 'fertilizer', 'kg',  250.0, 180.0, 25.0,  '2026-12-31'],
            ['Sulfato Potásico',          null, 'fertilizer', 'kg',  200.0, 140.0, 20.0,  '2026-12-31'],
            ['Fosfato Monoamónico DAP',  null, 'fertilizer', 'kg',  150.0,  80.0, 15.0,  '2026-09-30'],
            ['Humus de Lombriz Líquido', null, 'fertilizer', 'L',   100.0,  60.0, 10.0,  null        ],
            ['Sulfato de Magnesio',       null, 'fertilizer', 'kg',   80.0,  50.0,  8.0,  '2027-06-30'],
            ['Semilla Cubierta Vegetal',  null, 'seed',       'kg',   30.0,  18.0,  5.0,  '2026-08-31'],
            ['Caldo Bordelés',            null, 'postharvest','kg',   25.0,  15.0,  3.0,  '2026-06-30'],
            ['Cal Viva Desinfección',     null, 'other',      'kg',   50.0,  30.0,  5.0,  null        ],
            ['Bentonita Enológica',       null, 'other',      'kg',   20.0,  12.0,  2.0,  '2027-03-31'],
        ];
        $supplyIds = [];
        foreach ($suppliesData as [$name, $commName, $type, $unit, $initial, $current, $minAlert, $expiry]) {
            $supplyIds[] = DB::table('supplies')->insertGetId([
                'viticulturist_id'   => $vitId,
                'warehouse_id'       => $warehouseId,
                'name'               => $name,
                'commercial_name'    => $commName,
                'registration_number'=> null,
                'supply_type'        => $type,
                'unit_of_measurement'=> $unit,
                'initial_stock'      => $initial,
                'current_stock'      => $current,
                'min_stock_alert'    => $minAlert,
                'expiry_date'        => $expiry,
                'notes'              => null,
                'active'             => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Product Stocks — fitosanitarios (5) ───────────────────────────────
        $productNames = ['Folio Gold WG', 'Talendo', 'Movento 150 OD', 'Cobre 50 WG', 'Botryticide WP'];
        $productUnits = ['kg', 'L', 'L', 'kg', 'kg'];
        $productQtys  = [12.5, 4.0, 3.5, 8.0, 5.0];
        $stockIds = [];
        foreach ($productIds as $i => $prodId) {
            $stockIds[] = DB::table('product_stocks')->insertGetId([
                'product_id'           => $prodId,
                'user_id'              => $vitId,
                'warehouse_id'         => $warehouseId,
                'batch_number'         => 'LOTE-' . (2024 + intdiv($i, 2)) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'expiry_date'          => date('Y-12-31', mktime(0,0,0,1,1, 2026 + $i)),
                'manufacturing_date'   => date('Y-01-15', mktime(0,0,0,1,1, 2024 + intdiv($i, 2))),
                'quantity'             => $productQtys[$i] ?? 5.0,
                'unit'                 => $productUnits[$i] ?? 'L',
                'unit_price'           => [48.50, 62.00, 89.00, 18.50, 35.00][$i] ?? 30.00,
                'supplier'             => 'Cooperativa Agrícola Agaete',
                'invoice_number'       => 'COOP-' . (2024 + intdiv($i, 2)) . '-' . str_pad(100 + $i, 4, '0', STR_PAD_LEFT),
                'notes'                => $productNames[$i] ?? null,
                'active'               => true,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        // ── Movimientos de Stock (15: 3 por producto) ─────────────────────────
        $movTypes = [
            ['purchase',    1.0,  'Compra inicial campaña'],
            ['consumption', -1.0, 'Consumo tratamiento preventivo'],
            ['purchase',    1.0,  'Reposición stock'],
        ];
        $quantities = [[8.0, -3.5, 4.0], [4.0, -1.5, 2.0], [3.5, -1.2, 1.5], [8.0, -2.5, 3.0], [5.0, -2.0, 2.5]];
        foreach ($stockIds as $si => $stockId) {
            $before = $productQtys[$si] ?? 5.0;
            foreach ($movTypes as $mi => [$mType, , $mNotes]) {
                $change = $quantities[$si][$mi] ?? 1.0;
                $after  = round($before + $change, 3);
                DB::table('product_stock_movements')->insert([
                    'stock_id'         => $stockId,
                    'user_id'          => $vitId,
                    'treatment_id'     => null,
                    'movement_type'    => $mType,
                    'quantity_change'  => $change,
                    'quantity_before'  => $before,
                    'quantity_after'   => $after,
                    'unit_price'       => $mType === 'purchase' ? ([48.50, 62.00, 89.00, 18.50, 35.00][$si] ?? 30.00) : null,
                    'reference_number' => $mType === 'purchase' ? ('MOV-' . date('Y') . '-' . str_pad($si * 3 + $mi + 1, 4, '0', STR_PAD_LEFT)) : null,
                    'notes'            => $mNotes,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $before = $after;
            }
        }

        // ── Compras de Suministros (8) ────────────────────────────────────────
        $purchaseData = [
            [$supplyIds[0], $c24, '2024-02-10', 'F2024-COOP-0021', 200.0, 'kg', 0.85, 'Agropiensos SA'],
            [$supplyIds[1], $c24, '2024-03-01', 'F2024-COOP-0038', 150.0, 'kg', 0.42, 'Cooperativa Agaete'],
            [$supplyIds[2], $c24, '2024-03-01', 'F2024-COOP-0039',  80.0, 'kg', 1.20, 'Cooperativa Agaete'],
            [$supplyIds[3], $c24, '2024-02-20', 'F2024-AGR-0055',   80.0, 'kg', 0.78, 'Agropiensos SA'],
            [$supplyIds[0], $c25, '2025-02-08', 'F2025-COOP-0019', 220.0, 'kg', 0.88, 'Agropiensos SA'],
            [$supplyIds[1], $c25, '2025-02-25', 'F2025-COOP-0031', 130.0, 'kg', 0.44, 'Cooperativa Agaete'],
            [$supplyIds[4], $c25, '2025-03-10', 'F2025-AGR-0042',   60.0, 'L',  2.50, 'Agropiensos SA'],
            [$supplyIds[6], $c26, '2026-02-15', 'F2026-COOP-0012',  20.0, 'kg', 3.80, 'Cooperativa Agaete'],
        ];
        foreach ($purchaseData as [$supId, $cId, $date, $invNum, $qty, $unit, $price, $supplier]) {
            DB::table('supply_purchases')->insert([
                'supply_id'           => $supId,
                'viticulturist_id'    => $vitId,
                'campaign_id'         => $cId,
                'invoice_date'        => $date,
                'invoice_number'      => $invNum,
                'quantity'            => $qty,
                'unit_of_measurement' => $unit,
                'price_per_unit'      => $price,
                'total_cost'          => round($qty * $price, 2),
                'supplier_name'       => $supplier,
                'notes'               => null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }

    // ─── 22. Facturas ────────────────────────────────────────────────────────

    private function createInvoices(int $c24, int $c25, $now): void
    {
        $vitId   = self::VIT_USER_ID;
        $winId   = self::WINERY_USER_ID;

        // Obtener clientes del viticultor
        $clients = DB::table('clients')->where('user_id', $vitId)->orderBy('id')->get(['id', 'client_type', 'company_name', 'first_name', 'last_name']);
        if ($clients->isEmpty()) return;

        $cl = $clients->values();

        $invoiceNum = 0;
        $makeNum = function () use ($vitId, &$invoiceNum) {
            $invoiceNum++;
            return 'VIT' . $vitId . '-' . date('Y') . '-' . str_pad($invoiceNum, 4, '0', STR_PAD_LEFT);
        };

        // Obtener harvests del viticultor para vincular items
        $actIds = DB::table('agricultural_activities')->where('viticulturist_id', $vitId)->pluck('id');
        $harvests = DB::table('harvests as h')
            ->join('agricultural_activities as a', 'h.activity_id', '=', 'a.id')
            ->whereIn('h.activity_id', $actIds)
            ->orderBy('h.harvest_start_date')
            ->get(['h.id', 'h.total_weight', 'h.harvest_start_date', 'a.campaign_id']);

        // ── Facturas Venta Cosecha (6) — user_id = viticulturist ──────────────
        $harvestSales = [
            [$c24, 0, '2024-09-10', 'paid',   'paid',   4200.0, 0.28, 'Venta cosecha Listán Negro — Campaña 2024'],
            [$c24, 1, '2024-09-18', 'paid',   'paid',   2800.0, 0.26, 'Venta cosecha Listán Blanco — Campaña 2024'],
            [$c24, 2, '2024-09-25', 'paid',   'paid',   1800.0, 0.30, 'Venta cosecha Negramoll — Campaña 2024'],
            [$c25, 3, '2025-09-08', 'paid',   'paid',   4600.0, 0.30, 'Venta cosecha Listán Negro — Campaña 2025'],
            [$c25, 4, '2025-09-15', 'paid',   'paid',   3100.0, 0.28, 'Venta cosecha Listán Blanco — Campaña 2025'],
            [$c25, 5, '2025-09-22', 'sent',   'unpaid', 2100.0, 0.32, 'Venta cosecha Moscatel + Tintilla — Campaña 2025'],
        ];

        foreach ($harvestSales as $i => [$cId, $clientIdx, $date, $status, $payStatus, $kg, $priceKg, $desc]) {
            $client    = $cl[$clientIdx % $cl->count()] ?? $cl[0];
            $subtotal  = round($kg * $priceKg, 2);
            $taxRate   = 10.00;
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total     = round($subtotal + $taxAmount, 2);
            $clientName = $client->company_name ?? trim("{$client->first_name} {$client->last_name}");

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'                  => $vitId,
                'invoice_type'             => 'harvest_sale',
                'client_id'                => $client->id,
                'viticulturist_id'         => null,
                'invoice_number'           => $makeNum(),
                'invoice_date'             => $date,
                'payment_date'             => date('Y-m-d', strtotime($date . ' +30 days')),
                'subtotal'                 => $subtotal,
                'discount_amount'          => 0,
                'tax_base'                 => $subtotal,
                'tax_rate'                 => $taxRate,
                'tax_amount'               => $taxAmount,
                'total_amount'             => $total,
                'status'                   => $status,
                'payment_status'           => $payStatus,
                'payment_type'             => $status === 'paid' ? 'transfer' : null,
                'billing_company_name'     => $clientName,
                'billing_address'          => 'Gran Canaria, España',
                'current_invoice_code'     => $invoiceNum,
                'current_delivery_note_code' => 1,
                'order_date'               => $now,
                'bank_payment_status'      => $status === 'paid',
                'delivery_status'          => 'delivered',
                'sif_status'               => 'pendiente',
                'is_verified_aet'          => false,
                'sent'                     => $status !== 'draft',
                'viewed'                   => $status !== 'draft',
                'delivery_viewed'          => false,
                'payment_status_viewed'    => false,
                'corrective'               => false,
                'gift'                     => false,
                'observations'             => null,
                'observations_invoice'     => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);

            // Item de la factura
            $harvestId = $harvests->get($i)?->id ?? null;
            DB::table('invoice_items')->insert([
                'invoice_id'          => $invoiceId,
                'harvest_id'          => $harvestId,
                'wine_lot_id'         => null,
                'name'                => $desc,
                'description'         => "{$kg} kg × {$priceKg} €/kg",
                'sku'                 => null,
                'concept_type'        => 'harvest',
                'quantity'            => $kg,
                'unit_price'          => $priceKg,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'tax_id'              => null,
                'tax_name'            => 'IVA 10%',
                'tax_rate'            => $taxRate,
                'tax_base'            => $subtotal,
                'tax_amount'          => $taxAmount,
                'subtotal'            => $subtotal,
                'total'               => $total,
                'status'              => 'active',
                'payment_status'      => $payStatus,
                'delivery_status'     => 'delivered',
                'variations'          => null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        // ── Liquidaciones de Bodega (4) — user_id = winery, viticulturist_id = vit ──
        $liquidaciones = [
            [$c24, '2024-10-15', 'paid', 'paid',   18500.0, 0.29, 'Liquidación vendimia 2024 — Uva Listán Negro'],
            [$c24, '2024-11-01', 'paid', 'paid',   12400.0, 0.27, 'Liquidación vendimia 2024 — Uva Listán Blanco'],
            [$c25, '2025-10-18', 'paid', 'paid',   20100.0, 0.31, 'Liquidación vendimia 2025 — Uva Listán Negro'],
            [$c25, '2025-11-05', 'sent', 'unpaid', 13600.0, 0.29, 'Liquidación vendimia 2025 — Uva Listán Blanco'],
        ];

        foreach ($liquidaciones as $i => [$cId, $date, $status, $payStatus, $kg, $priceKg, $desc]) {
            $subtotal  = round($kg * $priceKg, 2);
            $total     = $subtotal; // sin IVA (operación intracomunitaria agraria)

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'                  => $winId,
                'invoice_type'             => 'grape_purchase',
                'client_id'                => null,
                'viticulturist_id'         => $vitId,
                'invoice_number'           => 'LIQ-' . substr($date, 0, 4) . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date'             => $date,
                'payment_date'             => date('Y-m-d', strtotime($date . ' +15 days')),
                'subtotal'                 => $subtotal,
                'discount_amount'          => 0,
                'tax_base'                 => $subtotal,
                'tax_rate'                 => 0,
                'tax_amount'               => 0,
                'total_amount'             => $total,
                'status'                   => $status,
                'payment_status'           => $payStatus,
                'payment_type'             => $status === 'paid' ? 'transfer' : null,
                'billing_company_name'     => 'Demo Viticulturist',
                'billing_address'          => 'Agaete, Gran Canaria',
                'current_invoice_code'     => $i + 1,
                'current_delivery_note_code' => 1,
                'order_date'               => $now,
                'bank_payment_status'      => $status === 'paid',
                'delivery_status'          => 'delivered',
                'sif_status'               => 'pendiente',
                'is_verified_aet'          => false,
                'sent'                     => true,
                'viewed'                   => true,
                'delivery_viewed'          => false,
                'payment_status_viewed'    => false,
                'corrective'               => false,
                'gift'                     => false,
                'observations'             => null,
                'observations_invoice'     => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);

            $harvestId = $harvests->get(6 + $i)?->id ?? null;
            DB::table('invoice_items')->insert([
                'invoice_id'          => $invoiceId,
                'harvest_id'          => $harvestId,
                'wine_lot_id'         => null,
                'name'                => $desc,
                'description'         => "{$kg} kg × {$priceKg} €/kg",
                'sku'                 => null,
                'concept_type'        => 'harvest',
                'quantity'            => $kg,
                'unit_price'          => $priceKg,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'tax_id'              => null,
                'tax_name'            => 'Exento REAG',
                'tax_rate'            => 0,
                'tax_base'            => $subtotal,
                'tax_amount'          => 0,
                'subtotal'            => $subtotal,
                'total'               => $total,
                'status'              => 'active',
                'payment_status'      => $payStatus,
                'delivery_status'     => 'delivered',
                'variations'          => null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }

    // ─── 23. Gestión Territorial ─────────────────────────────────────────────

    private function createTerritorial($now): void
    {
        $vitId = self::VIT_USER_ID;
        $munId = self::MUNICIPALITY_ID; // 5243 = Agaete

        // ── Parajes / Sites (7) ───────────────────────────────────────────────
        foreach ([
            'Lomo Grande', 'Barranco del Pino', 'La Montañeta',
            'El Risco de Agaete', 'Los Berrazales', 'Pago de Los Llanos', 'Las Pozas de Arriba',
        ] as $siteName) {
            DB::table('sites')->insertOrIgnore([
                'user_id'         => $vitId,
                'name'            => $siteName,
                'municipality_id' => $munId,
                'is_archived'     => false,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ── Valles / Valleys (3) ──────────────────────────────────────────────
        foreach ([
            ['Valle de Agaete',        'AGT', 'Valle costero del noroeste de Gran Canaria'],
            ['Valle de Los Berrazales', 'BRZ', 'Valle interior húmedo con cultivos tradicionales'],
            ['Barranco de La Virgen',   'BVG', 'Barranco con suelo aluvial rico en nutrientes'],
        ] as [$name, $code, $desc]) {
            DB::table('valleys')->insertOrIgnore([
                'user_id' => $vitId, 'name' => $name, 'code' => $code,
                'description_valley' => $desc, 'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── Tipos de Suelo / Soil Types (4) ──────────────────────────────────
        foreach ([
            ['Suelo volcánico basáltico',  'Suelo oscuro de origen volcánico. Rico en minerales. Alta porosidad.'],
            ['Suelo arenoso volcánico',    'Mezcla de arena volcánica y lapilli. Buen drenaje. Bajo en M.O.'],
            ['Suelo arcillo-limoso',       'Textura media con buena retención hídrica. Zonas de vega.'],
            ['Suelo pedregoso de ladera',  'Alta proporción de piedra. Suelo somero. Excelente drenaje.'],
        ] as [$name, $desc]) {
            DB::table('soil_types')->insertOrIgnore([
                'user_id' => $vitId, 'name' => $name, 'description' => $desc,
                'active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── Topografías (3) ───────────────────────────────────────────────────
        foreach ([
            ['Ladera pronunciada',  'Pendiente >25%. Riesgo de erosión. Labor manual.'],
            ['Terraza escalonada',  'Bancales construidos en ladera. Técnica tradicional canaria.'],
            ['Zona de vega',        'Terreno llano o suavemente ondulado. Alta productividad.'],
        ] as [$name, $desc]) {
            DB::table('topographies')->insertOrIgnore([
                'user_id' => $vitId, 'name' => $name, 'description' => $desc,
                'active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── Tipos de Riego (2) ────────────────────────────────────────────────
        foreach ([
            ['Goteo subterráneo', 'Riego localizado enterrado. Ahorro hídrico máximo.'],
            ['Microaspersión',    'Aspersores de bajo caudal. Usado en parcelas en terraza.'],
        ] as [$name, $desc]) {
            DB::table('irrigation_types')->insertOrIgnore([
                'user_id' => $vitId, 'name' => $name, 'description' => $desc,
                'active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── Tipos de Propiedad (2) ────────────────────────────────────────────
        foreach ([
            'Propiedad familiar heredada',
            'Arrendamiento a largo plazo',
        ] as $name) {
            DB::table('property_types')->insertOrIgnore([
                'user_id' => $vitId, 'name' => $name,
                'active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
