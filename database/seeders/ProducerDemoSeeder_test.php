<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder demo completo para el rol Producer (user_id = 339).
 *
 * El producer es viticultor + bodega a la vez, por lo que este seeder
 * cubre AMBOS lados.
 *
 * Vínculos winery_viticulturist que se crean:
 *   · Auto-vínculo:  winery_id=339 ↔ viticulturist_id=339  (producer gestiona su propia bodega)
 *   · Vínculo winery: winery_id=1  ↔ viticulturist_id=339  (bodega demo ve las parcelas del producer)
 *
 * LADO VITICULTOR (cuaderno de campo):
 *   · 460 parcelas con datos SIGPAC reales (Gran Canaria)
 *   · 8 plantaciones sobre las primeras 4 parcelas
 *   · 3 campañas: 2024 (cerrada), 2025 (cerrada), 2026 (activa)
 *   · ~70 actividades agrícolas con sub-tablas
 *   · Plagas, fenología, rendimientos estimados
 *   · Compliance, PAC, almacén insumos, registros oficiales
 *   · Subcontrataciones, costes parcelas, cosechas comercializadas
 *   · Documentos campaña, entornos parcela, gestión territorial
 *
 * LADO BODEGA (elaboración):
 *   · 3 salas de bodega + 30 contenedores (depósitos + barricas)
 *   · 2 enólogos, 3 proveedores, 5 suministros bodega
 *   · 8 vinos propios
 *   · Controles fermentación, traslados, mermas, aditivos
 *   · Análisis de vino, embotellamientos, lotes, etiquetado
 *   · Subproductos bodega, operaciones bodega, mantenimientos
 *   · Clientes, facturas mixtas (cosecha + vino), notas de cata
 *   · Compliance bodega, documentos bodega, alertas
 *
 * Usuario: demo_test_producer@agro365.es  (user_id = 339)
 *
 * Uso:
 *   php artisan db:seed --class=ProducerDemoSeeder_test
 */
class ProducerDemoSeeder_test extends Seeder
{
    private const PRODUCER_USER_ID = 339;
    private const WINERY_USER_ID   = 1;    // Bodega demo — misma que usa ViticulturistDemoSeeder
    private const EMAIL            = 'demo_test_producer@agro365.es';

    // ── Geografía: Gran Canaria (mismo JSON que ViticulturistDemoSeeder) ──────
    private const AC_ID           = 5;    // Canarias
    private const PROVINCE_ID     = 14;   // Las Palmas de Gran Canaria
    private const MUNICIPALITY_ID = 5243; // Agaete

    public function run(): void
    {
        $now = now();

        $this->command->info('');
        $this->command->info('🌿🍷 ══════════════════════════════════════════════════');
        $this->command->info('🌿🍷  PRODUCER DEMO SEEDER — user_id = 339');
        $this->command->info('🌿🍷  Productor Gran Canaria · Las Palmas');
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

        // 3a. Auto-vínculo winery_viticulturist (producer es su propia bodega)
        $wvId = 0;
        $this->step('Auto-vínculo bodega↔viticultor (339↔339)', function () use ($now, &$wvId) {
            $wvId = $this->createSelfLink($now);
        });

        // 3b. Vínculo con bodega demo (igual que ViticulturistDemoSeeder)
        $this->step('Vínculo con bodega demo (winery_id=1 ↔ viticulturist_id=339)', function () use ($now) {
            $this->linkToWinery($now);
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

        // 6. Campañas (3)
        $campaign2024Id = 0;
        $campaign2025Id = 0;
        $campaign2026Id = 0;
        $this->step('Campañas (2024 cerrada + 2025 cerrada + 2026 activa)', function () use ($now, $wvId, &$campaign2024Id, &$campaign2025Id, &$campaign2026Id) {
            [$campaign2024Id, $campaign2025Id, $campaign2026Id] = $this->createCampaigns($wvId, $now);
        });

        // 7. Territorio (sites, valleys, soil_types, etc.)
        $this->step('Gestión Territorial (parajes + valles + suelos + topografías)', function () use ($now) {
            $this->createTerritorial($now);
        });

        // 8. Actividades 2024
        $this->step('Actividades 2024 (~28)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2024Id) {
            $this->createActivities2024($plotIds, $plantingIds, $productIds, $wvId, $campaign2024Id, $now);
        });

        // 9. Actividades 2025
        $this->step('Actividades 2025 (~28)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id) {
            $this->createActivities2025($plotIds, $plantingIds, $productIds, $wvId, $campaign2025Id, $now);
        });

        // 10. Actividades 2026
        $this->step('Actividades 2026 (~640)', function () use ($now, $plotIds, $plantingIds, $productIds, $wvId, $campaign2026Id) {
            $this->createActivities2026($plotIds, $plantingIds, $productIds, $wvId, $campaign2026Id, $now);
        });

        // 11. Fenología
        $this->step('Observaciones fenológicas', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createPhenology($plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 12. Plagas
        $pestIds = [];
        $this->step('Plagas y enfermedades (6)', function () use ($now, $productIds, &$pestIds) {
            $pestIds = $this->createPests($productIds, $now);
        });

        // 13. Rendimientos estimados
        $this->step('Rendimientos estimados', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createEstimatedYields($plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 14. Maquinaria
        $machineryIds = [];
        $this->step('Maquinaria (8)', function () use ($now, &$machineryIds) {
            $machineryIds = $this->createMachinery($now);
        });

        // 15. Explotaciones
        $exploitationIds = [];
        $this->step('Explotaciones SIEX/REA (1)', function () use ($now, &$exploitationIds) {
            $exploitationIds = $this->createExploitations($now);
        });

        // 16. Compliance viticultor
        $this->step('Compliance (aplicadores, equipos, seguros, asesorías, autorizaciones)', function () use ($now, $exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createCompliance($exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 17. Subcontrataciones
        $this->step('Subcontrataciones (450: 150×campaña)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createSubcontractings($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 18. Registros Oficiales (medioambiente, declaraciones, CUE)
        $this->step('Registros Oficiales (~80 registros)', function () use ($now, $plantingIds, $plotIds, $productIds, $machineryIds, $exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createRegistrosOficiales($plantingIds, $plotIds, $productIds, $machineryIds, $exploitationIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 19. PAC
        $this->step('PAC (3 declaraciones + items + pagos)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createPAC($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 20. Almacén insumos + supplies + stocks
        $this->step('Almacén insumos (1 almacén + 8 suministros + stocks)', function () use ($now, $productIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createAlmacen($productIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 21. Costes parcelas + cosechas comercializadas
        $this->step('Facturación viticultor (costes parcela + cosecha comercializada)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createBilling($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 22. Documentos campaña + entornos parcela
        $this->step('Datos operacionales (docs campaña + entornos parcela)', function () use ($now, $plotIds, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createOperationalData($plotIds, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // ─── LADO BODEGA ──────────────────────────────────────────────────────

        // 23. Salas de bodega
        $this->step('Salas de bodega (3)', function () use ($now) {
            $this->createContainerRooms($now);
        });

        // 24. Contenedores bodega
        $wineryContainerIds = [];
        $this->step('Contenedores bodega (30: depósitos + barricas)', function () use ($now, &$wineryContainerIds) {
            $wineryContainerIds = $this->createWineryContainers($now);
        });

        // 25. Enólogos + proveedores + suministros bodega
        $this->step('Enólogos (2)', function () use ($now) {
            $this->createOenologists($now);
        });
        $this->step('Proveedores bodega (3)', function () use ($now) {
            $this->createSuppliers($now);
        });
        $this->step('Suministros bodega (5)', function () use ($now) {
            $this->createWinerySupplies($now);
        });

        // 26. Vinos
        $wineIds = [];
        $this->step('Vinos (8 referencias)', function () use ($now, &$wineIds) {
            $wineIds = $this->createWines($now);
        });

        // 27. Controles fermentación
        $this->step('Controles fermentación (20)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createFermentationControls($wineIds, $wineryContainerIds, $now);
        });

        // 28. Traslados y mermas
        $this->step('Traslados de vino (6)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createWineTransfers($wineIds, $wineryContainerIds, $now);
        });
        $this->step('Mermas de vino (5)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createWineLosses($wineIds, $wineryContainerIds, $now);
        });

        // 29. Aditivos bodega
        $this->step('Aditivos enológicos (15)', function () use ($now, $wineIds) {
            $this->createWineAdditives($wineIds, $now);
        });

        // 30. Análisis de vino
        $this->step('Análisis de vino (4)', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createWineAnalyses($wineIds, $wineryContainerIds, $now);
        });

        // 31. Embotellamientos + lotes + etiquetado
        $this->step('Embotellamientos (50) + Lotes etiquetas (4) + Etiquetado', function () use ($now, $wineIds, $wineryContainerIds) {
            $this->createBottlings($wineIds, $wineryContainerIds, $now);
            $this->createLabelBatches($wineIds, $now);
            $this->createLabelings($now);
        });

        // 32. Subproductos bodega + operaciones bodega
        $this->step('Subproductos bodega (4)', function () use ($now, $wineIds) {
            $this->createWineSubproducts($wineIds, $now);
        });
        $this->step('Operaciones de bodega (5)', function () use ($now, $wineryContainerIds) {
            $this->createCellarOperations($wineryContainerIds, $now);
        });

        // 33. Mantenimiento contenedores
        $this->step('Mantenimiento contenedores (5)', function () use ($now, $wineryContainerIds) {
            $this->createContainerMaintenances($wineryContainerIds, $now);
        });

        // 34. Clientes
        $this->step('Clientes (450: 250 empresas + 200 particulares)', function () use ($now) {
            $this->createClients($now);
        });

        // 35. Facturas
        $this->step('Facturas mixtas (150: 75×2024 + 75×2025)', function () use ($now, $campaign2024Id, $campaign2025Id) {
            $this->createInvoices($campaign2024Id, $campaign2025Id, $now);
        });

        // 36. Notas de cata
        $this->step('Notas de cata (450)', function () use ($now, $wineIds) {
            $this->createTastingNotes($wineIds, $now);
        });

        // 37. Compliance bodega
        $this->step('Compliance bodega (eco_certifications + sanitary + bottling_auth)', function () use ($now, $wineIds) {
            $this->createWineryCompliance($wineIds, $now);
        });

        // 38. Documentos bodega + alertas
        $this->step('Documentos bodega (450)', function () use ($now) {
            $this->createWineryDocuments($now);
        });
        $this->step('Alertas bodega (450)', function () use ($now) {
            $this->createWineryAlerts($now);
        });

        // 39. Plan de trabajos 2026
        $this->step('Plan de trabajos 2026 (~450)', function () use ($now, $plotIds, $campaign2026Id) {
            $this->createPlannedWorks($plotIds, $campaign2026Id, $now);
        });

        // 40. Recepciones bodega 2026 (batches + cosechas winery_id)
        $this->step('Recepciones bodega 2026 (450 batches + 450 recepciones)', function () use ($now, $plantingIds, $campaign2026Id) {
            $this->createWineryReceptions2026($plantingIds, $campaign2026Id, $now);
        });

        // 41. Vinos cosecha 2026
        $wineIds2026 = [];
        $this->step('Vinos cosecha 2026 (8 referencias)', function () use ($now, &$wineIds2026) {
            $wineIds2026 = $this->createWines2026($now);
        });

        // 42. Lotes producto vintage 2026 (40)
        $this->step('Lotes producto vintage 2026 (~456)', function () use ($now, $wineIds2026) {
            $this->createProductLots2026($wineIds2026, $now);
        });

        // 43. Análisis de suelo (24)
        $this->step('Análisis de suelo (450: 150 parcelas × 3 campañas)', function () use ($now, $plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createSoilAnalyses($plotIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 44. Registros biodiversidad (24)
        $this->step('Registros biodiversidad (450: 150 parcelas × 3 tipos)', function () use ($now, $plotIds, $campaign2026Id) {
            $this->createBiodiversityRecords($plotIds, $campaign2026Id, $now);
        });

        // 45. Alertas fitosanitarias (12)
        $this->step('Alertas fitosanitarias (450)', function () use ($now) {
            $this->createPhytosanitaryAlerts($now);
        });

        // 46. Previsiones cosecha (24)
        $this->step('Previsiones cosecha (456: 8 plantaciones × 3 campañas × 19 estimaciones)', function () use ($now, $plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id) {
            $this->createYieldForecasts($plantingIds, $campaign2024Id, $campaign2025Id, $campaign2026Id, $now);
        });

        // 47. Entregas + cosechas comercializadas 2026
        $this->step('Entregas cosecha (450: 2024+2025+2026)', function () use ($now, $plantingIds, $campaign2026Id) {
            $this->createHarvestDeliveries($plantingIds, $campaign2026Id, $now);
        });

        // 48. Actividades extra — parcelas 8-32 (250)
        $this->step('Actividades extra parcelas 8-32 (~250)', function () use ($now, $plotIds, $wvId, $campaign2026Id) {
            $this->createExtraActivities($plotIds, $wvId, $campaign2026Id, $now);
        });

        // 49. Facturas producto vino 2026 (13)
        $this->step('Facturas producto vino 2026 (300)', function () use ($now, $wineIds2026) {
            $this->createProductInvoices($wineIds2026, $now);
        });

        // 50. Mantenimientos contenedores adicionales (10)
        $this->step('Mantenimientos contenedores adicionales (10)', function () use ($now, $wineryContainerIds) {
            $this->createExtraContainerMaintenances($wineryContainerIds, $now);
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
            'name'              => 'Productor Gran Canaria Demo',
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

        // Fenología
        DB::table('phenology_observations')->where('viticulturist_id', $uid)->delete();

        // Campañas
        DB::table('campaigns')->where('viticulturist_id', $uid)->delete();

        // Parcelas y plantaciones
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

        // Vínculo bodega–viticultor
        DB::table('winery_viticulturist')
            ->where('viticulturist_id', $uid)
            ->orWhere('winery_id', $uid)
            ->delete();

        // Plagas
        $seederPestNames = [
            'Polilla del racimo (Producer)', 'Mildiu de la vid (Producer)',
            'Oídio de la vid (Producer)', 'Araña roja (Producer)',
            'Botrytis / Podredumbre gris (Producer)', 'Excoriosis (Producer)',
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

        // Explotaciones + dependientes
        $exploitationIds = DB::table('exploitations')->where('viticulturist_id', $uid)->pluck('id');
        if ($exploitationIds->isNotEmpty()) {
            DB::table('commercial_authorizations')->whereIn('exploitation_id', $exploitationIds)->delete();
            DB::table('cue_exports')->whereIn('exploitation_id', $exploitationIds)->delete();
        }
        DB::table('exploitations')->where('viticulturist_id', $uid)->delete();

        // Subcontrataciones
        DB::table('subcontractings')->where('viticulturist_id', $uid)->delete();

        // Registros Oficiales
        DB::table('residue_analyses')->where('viticulturist_id', $uid)->delete();
        DB::table('residue_managements')->where('viticulturist_id', $uid)->delete();
        DB::table('energy_usages')->where('viticulturist_id', $uid)->delete();
        DB::table('water_concessions')->where('viticulturist_id', $uid)->delete();
        DB::table('fertilization_plans')->where('viticulturist_id', $uid)->delete();
        DB::table('phytosanitary_container_returns')->where('viticulturist_id', $uid)->delete();
        DB::table('harvest_declarations')->where('viticulturist_id', $uid)->delete();
        DB::table('harvest_byproducts')->where('viticulturist_id', $uid)->delete();
        DB::table('certifications')->where('viticulturist_id', $uid)->delete();
        DB::table('official_reports')->where('user_id', $uid)->delete();

        // PAC
        $pacIds = DB::table('pac_declarations')->where('viticulturist_id', $uid)->pluck('id');
        if ($pacIds->isNotEmpty()) {
            DB::table('pac_declaration_items')->whereIn('declaration_id', $pacIds)->delete();
        }
        DB::table('pac_declarations')->where('viticulturist_id', $uid)->delete();
        DB::table('pac_payments')->where('viticulturist_id', $uid)->delete();

        // Facturación viticultor
        DB::table('plot_costs')->where('viticulturist_id', $uid)->delete();
        DB::table('marketed_harvests')->where('viticulturist_id', $uid)->delete();

        // Datos operacionales
        DB::table('campaign_documents')->where('viticulturist_id', $uid)->delete();
        DB::table('plot_environments')->where('viticulturist_id', $uid)->delete();

        // Almacén de Insumos
        $warehouseIds = DB::table('warehouses')->where('user_id', $uid)->pluck('id');
        if ($warehouseIds->isNotEmpty()) {
            $stockIds = DB::table('product_stocks')->whereIn('warehouse_id', $warehouseIds)->pluck('id');
            if ($stockIds->isNotEmpty()) {
                DB::table('product_stock_movements')->whereIn('stock_id', $stockIds)->delete();
            }
            DB::table('product_stocks')->whereIn('warehouse_id', $warehouseIds)->delete();
        }
        DB::table('product_stocks')->where('user_id', $uid)->delete();
        $supplyIds = DB::table('supplies')->where('viticulturist_id', $uid)->pluck('id');
        if ($supplyIds->isNotEmpty()) {
            DB::table('supply_purchases')->whereIn('supply_id', $supplyIds)->delete();
        }
        DB::table('supplies')->where('viticulturist_id', $uid)->delete();
        DB::table('warehouses')->where('user_id', $uid)->delete();

        // Gestión Territorial
        DB::table('sites')->where('user_id', $uid)->delete();
        DB::table('valleys')->where('user_id', $uid)->delete();
        DB::table('soil_types')->where('user_id', $uid)->delete();
        DB::table('topographies')->where('user_id', $uid)->delete();
        DB::table('irrigation_types')->where('user_id', $uid)->delete();
        DB::table('property_types')->where('user_id', $uid)->delete();

        // ── Lado bodega ───────────────────────────────────────────────────────
        // Alertas y documentos bodega
        DB::table('winery_alerts')->where('user_id', $uid)->delete();
        DB::table('winery_documents')->where('user_id', $uid)->delete();

        // Compliance bodega
        DB::table('eco_certifications')->where('user_id', $uid)->delete();
        DB::table('sanitary_registrations')->where('user_id', $uid)->delete();
        DB::table('bottling_authorizations')->where('user_id', $uid)->delete();

        // Subproductos y operaciones
        $wineIds = DB::table('wines')->where('user_id', $uid)->pluck('id');
        if ($wineIds->isNotEmpty()) {
            DB::table('wine_subproducts')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_additives')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_analyses')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_fermentation_controls')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_transfers')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_losses')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_tasting_notes')->whereIn('wine_id', $wineIds)->delete();
            DB::table('wine_labelings')->where('user_id', $uid)->delete();
            DB::table('label_batches')->where('user_id', $uid)->delete();
        }
        DB::table('wines')->where('user_id', $uid)->delete();

        // Embotellamientos y lotes
        $bottlingIds = DB::table('wine_bottlings')->where('user_id', $uid)->pluck('id');
        if ($bottlingIds->isNotEmpty()) {
            DB::table('wine_lots')->whereIn('bottling_id', $bottlingIds)->delete();
        }
        DB::table('wine_bottlings')->where('user_id', $uid)->delete();
        DB::table('wine_lots')->where('user_id', $uid)->delete();

        // Operaciones y mantenimientos
        DB::table('cellar_operations')->where('user_id', $uid)->delete();
        $containerIds = DB::table('containers')->where('user_id', $uid)->pluck('id');
        if ($containerIds->isNotEmpty()) {
            DB::table('container_maintenances')->whereIn('container_id', $containerIds)->delete();
        }
        DB::table('containers')->where('user_id', $uid)->delete();
        DB::table('container_rooms')->where('user_id', $uid)->delete();

        // Enólogos, proveedores, suministros bodega
        DB::table('oenologists')->where('user_id', $uid)->delete();
        DB::table('suppliers')->where('user_id', $uid)->delete();
        DB::table('winery_supplies')->where('user_id', $uid)->delete();

        // Facturas (antes que clients por FK)
        $invoiceIds = DB::table('invoices')->where('user_id', $uid)->pluck('id');
        if ($invoiceIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
        }
        DB::table('invoices')->where('user_id', $uid)->delete();

        // Clientes (después de invoices)
        DB::table('clients')->where('user_id', $uid)->delete();

        // Plan de trabajos
        DB::table('planned_works')->where('viticulturist_id', $uid)->delete();

        // Recepciones bodega (harvests con winery_id)
        DB::table('harvests')->where('winery_id', $uid)->delete();
        // Batches recepción
        DB::table('grape_reception_batches')->where('winery_id', $uid)->orWhere('viticulturist_id', $uid)->delete();
        // Análisis de suelo
        DB::table('soil_analyses')->where('viticulturist_id', $uid)->delete();
        // Biodiversidad
        DB::table('biodiversity_records')->where('viticulturist_id', $uid)->delete();
        // Alertas fitosanitarias
        DB::table('phytosanitary_alerts')->where('viticulturist_id', $uid)->delete();
        // Previsiones cosecha
        DB::table('winery_yield_forecasts')->where('winery_id', $uid)->orWhere('viticulturist_id', $uid)->delete();
        // Entregas cosecha
        DB::table('harvest_deliveries')->where('viticulturist_id', $uid)->delete();
    }

    // ─── 2. Productos fitosanitarios ──────────────────────────────────────────

    private function createProducts($now): array
    {
        $products = [
            ['name' => 'Ridomil Gold MZ 68 WG', 'active_ingredient' => 'Metalaxil-M 4% + Mancozeb 64% WG', 'registration_number' => 'ES-PR-001-01', 'manufacturer' => 'Syngenta', 'type' => 'fungicida', 'toxicity_class' => 'IV', 'withdrawal_period_days' => 28, 'safety_interval_days' => 28],
            ['name' => 'Domark 10 EC',           'active_ingredient' => 'Tetraconazol 10% EC',               'registration_number' => 'ES-PR-002-01', 'manufacturer' => 'Isagro',   'type' => 'fungicida', 'toxicity_class' => 'III','withdrawal_period_days' => 21, 'safety_interval_days' => 21],
            ['name' => 'Pyrinex Supreme',        'active_ingredient' => 'Clorpirifos 25% + Lambda-cihalotrina 2,5% CS', 'registration_number' => 'ES-PR-003-01', 'manufacturer' => 'Adama',  'type' => 'insecticida', 'toxicity_class' => 'II', 'withdrawal_period_days' => 14, 'safety_interval_days' => 14],
            ['name' => 'Cueva WP Zineb',         'active_ingredient' => 'Zineb 65% WP',                     'registration_number' => 'ES-PR-004-01', 'manufacturer' => 'Sipcam Inagra','type' => 'fungicida', 'toxicity_class' => 'IV', 'withdrawal_period_days' => 14, 'safety_interval_days' => 14],
            ['name' => 'Vertimec 1.9% EC',       'active_ingredient' => 'Abamectina 1,9% EC',               'registration_number' => 'ES-PR-005-01', 'manufacturer' => 'Syngenta', 'type' => 'acaricida', 'toxicity_class' => 'III','withdrawal_period_days' => 7, 'safety_interval_days' => 7],
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

    // ─── 3. Vínculos winery↔viticultor ───────────────────────────────────────

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

    private function linkToWinery($now): int
    {
        return DB::table('winery_viticulturist')->insertGetId([
            'winery_id'           => self::WINERY_USER_ID,
            'viticulturist_id'    => self::PRODUCER_USER_ID,
            'source'              => 'own',
            'notebook_access'     => true,
            'notebook_granted_at' => $now,
            'assigned_by'         => self::WINERY_USER_ID,
            'notes'               => 'Productor Gran Canaria vinculado a bodega demo.',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
    }

    // ─── 4. Parcelas + SIGPAC + Geometría ────────────────────────────────────

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
            $plotName = "$prefijo $munShort Producer " . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

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
            ['plot_id' => $plotIds[0], 'grape_variety_id' => $negro,    'area_planted' => 0.700, 'planting_year' => 2008, 'vine_count' => 700,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => '110R', 'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Gran Canaria', 'notes' => 'Listán Negro para tinto joven y crianza.'],
            ['plot_id' => $plotIds[0], 'grape_variety_id' => $baboso,   'area_planted' => 0.500, 'planting_year' => 2010, 'vine_count' => 500,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => '110R', 'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Gran Canaria', 'notes' => 'Baboso Negro para vino de autor.'],
            ['plot_id' => $plotIds[1], 'grape_variety_id' => $negro,    'area_planted' => 0.550, 'planting_year' => 1995, 'vine_count' => 440,  'row_spacing' => 2.5, 'vine_spacing' => 1.5, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Gran Canaria', 'notes' => 'Viña vieja. Producción baja y concentrada.'],
            ['plot_id' => $plotIds[1], 'grape_variety_id' => $blanco,   'area_planted' => 0.300, 'planting_year' => 2000, 'vine_count' => 240,  'row_spacing' => 2.5, 'vine_spacing' => 1.5, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Gran Canaria'],
            ['plot_id' => $plotIds[2], 'grape_variety_id' => $baboso,   'area_planted' => 0.400, 'planting_year' => 2003, 'vine_count' => 320,  'row_spacing' => 3.0, 'vine_spacing' => 2.0, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Gran Canaria', 'notes' => 'Ecológico. Sin fitosanitarios de síntesis.'],
            ['plot_id' => $plotIds[2], 'grape_variety_id' => $negro,    'area_planted' => 0.300, 'planting_year' => 2005, 'vine_count' => 240,  'row_spacing' => 3.0, 'vine_spacing' => 2.0, 'rootstock' => 'Pie franco', 'training_system_id' => 1, 'irrigated' => false, 'designation_of_origin' => 'DO Gran Canaria'],
            ['plot_id' => $plotIds[3], 'grape_variety_id' => $marmaj,   'area_planted' => 0.600, 'planting_year' => 2012, 'vine_count' => 600,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => 'SO4',        'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Gran Canaria', 'notes' => 'Marmajuelo para blanco fresco.'],
            ['plot_id' => $plotIds[3], 'grape_variety_id' => $vijarieg, 'area_planted' => 0.450, 'planting_year' => 2014, 'vine_count' => 450,  'row_spacing' => 2.0, 'vine_spacing' => 1.5, 'rootstock' => 'SO4',        'training_system_id' => 2, 'irrigated' => true,  'designation_of_origin' => 'DO Gran Canaria', 'notes' => 'Vijariego blanco. Aromático.'],
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

        $id2024 = DB::table('campaigns')->insertGetId(array_merge($base, [
            'name'                    => 'Campaña 2024',
            'year'                    => 2024,
            'start_date'              => '2024-01-01',
            'end_date'                => '2024-12-31',
            'active'                  => false,
            'description'             => 'Campaña 2024 cerrada. Vendimia: 3.200 kg cosechados.',
            'final_validation_signed' => true,
            'final_validation_date'   => '2024-11-30 10:00:00',
            'final_validation_user_id' => self::PRODUCER_USER_ID,
        ]));

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

        return [$id2024, $id2025, $id2026];
    }

    // ─── 7. Gestión Territorial ──────────────────────────────────────────────

    private function createTerritorial($now): void
    {
        $uid   = self::PRODUCER_USER_ID;
        $munId = self::MUNICIPALITY_ID;

        foreach (['Lomo Productor', 'Barranco Los Viñedos', 'La Montañeta Alta', 'El Risco Norte', 'Los Berrazales Sur'] as $name) {
            DB::table('sites')->insertOrIgnore(['user_id' => $uid, 'name' => $name, 'municipality_id' => $munId, 'is_archived' => false, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([['Valle de Agaete Producer', 'APR', 'Valle costero del noroeste'], ['Valle Los Berrazales Producer', 'BPR', 'Valle interior húmedo']] as [$n, $c, $d]) {
            DB::table('valleys')->insertOrIgnore(['user_id' => $uid, 'name' => $n, 'code' => $c, 'description_valley' => $d, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([['Suelo volcánico basáltico (Producer)', 'Rico en minerales.'], ['Suelo arenoso volcánico (Producer)', 'Buen drenaje.']] as [$n, $d]) {
            DB::table('soil_types')->insertOrIgnore(['user_id' => $uid, 'name' => $n, 'description' => $d, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([['Ladera pronunciada (Producer)', 'Pendiente >25%.'], ['Terraza escalonada (Producer)', 'Bancales tradicionales.']] as [$n, $d]) {
            DB::table('topographies')->insertOrIgnore(['user_id' => $uid, 'name' => $n, 'description' => $d, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([['Goteo subterráneo Producer', 'Riego localizado enterrado.']] as [$n, $d]) {
            DB::table('irrigation_types')->insertOrIgnore(['user_id' => $uid, 'name' => $n, 'description' => $d, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach (['Propiedad familiar Producer', 'Arrendamiento Producer'] as $name) {
            DB::table('property_types')->insertOrIgnore(['user_id' => $uid, 'name' => $name, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 8. Actividades 2024 ─────────────────────────────────────────────────

    private function createActivities2024(array $plotIds, array $plantingIds, array $productIds, int $wvId, int $campaignId, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;

        $act = fn(array $data) => DB::table('agricultural_activities')->insertGetId(array_merge([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'is_locked' => false, 'created_at' => $now, 'updated_at' => $now,
        ], $data));

        // Poda
        foreach (array_slice($plotIds, 0, 4) as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantingIds[$i * 2], 'activity_type' => 'pruning', 'activity_date' => '2024-01-' . str_pad(8 + $i * 3, 2, '0', STR_PAD_LEFT), 'notes' => 'Poda de invierno 2024.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'poda', 'hours_worked' => 6.0, 'workers_count' => 2, 'description' => 'Poda invernal.', 'created_at' => $now, 'updated_at' => $now]);
        }

        // Tratamientos
        foreach ([
            [0, '2024-02-20', $prod0, 'Mildiu'],  [1, '2024-04-10', $prod1, 'Oídio'],
            [2, '2024-05-08', $prod2, 'Mildiu + oídio'], [3, '2024-06-15', $prod4, 'Ácaros'],
        ] as [$pi, $date, $prodId, $pest]) {
            $aId = $act(['plot_id' => $plotIds[$pi], 'plot_planting_id' => $plantingIds[$pi * 2], 'activity_type' => 'phytosanitary_treatment', 'activity_date' => $date, 'notes' => "Tratamiento preventivo {$pest}."]);
            DB::table('phytosanitary_treatments')->insert(['activity_id' => $aId, 'product_id' => $prodId, 'dose_per_hectare' => 2.5, 'application_method' => 'pulverizador_hidraulico', 'target_pest' => $pest, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Fertilización
        $aId = $act(['plot_id' => $plotIds[0], 'plot_planting_id' => $plantingIds[0], 'activity_type' => 'fertilization', 'activity_date' => '2024-03-20', 'notes' => 'Abonado de fondo 2024.']);
        DB::table('fertilizations')->insert(['activity_id' => $aId, 'fertilizer_name' => 'NPK 8-15-15', 'fertilizer_type' => 'mineral', 'npk_ratio' => '8-15-15', 'quantity' => 300.0, 'application_method' => 'incorporado_suelo', 'created_at' => $now, 'updated_at' => $now]);

        // Riegos
        foreach ([0, 3] as $i) {
            foreach (['2024-06-01', '2024-07-01'] as $date) {
                $aId = $act(['plot_id' => $plotIds[$i], 'plot_planting_id' => $plantingIds[$i * 2], 'activity_type' => 'irrigation', 'activity_date' => $date, 'notes' => 'Riego de apoyo por goteo.']);
                DB::table('irrigations')->insert(['activity_id' => $aId, 'irrigation_method' => 'goteo', 'duration_minutes' => 240, 'water_volume' => 800, 'water_volume_unit' => 'L', 'water_source' => 'Red de riego agrícola', 'is_fertirrigation' => false, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Vendimia 2024
        foreach (array_slice($plotIds, 0, 4) as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantingIds[$i * 2], 'activity_type' => 'harvest', 'activity_date' => '2024-09-' . str_pad(8 + $i * 3, 2, '0', STR_PAD_LEFT), 'notes' => 'Vendimia manual.']);
            $weight = [800, 650, 550, 900][$i];
            $brix2024 = 22.0 + $i * 0.5;
            DB::table('harvests')->insert(['activity_id' => $aId, 'plot_planting_id' => $plantingIds[$i * 2], 'harvest_start_date' => '2024-09-' . str_pad(8 + $i * 3, 2, '0', STR_PAD_LEFT), 'total_weight' => $weight, 'brix_degree' => $brix2024, 'baume_degree' => round($brix2024 * 0.55, 1), 'ph_level' => 3.35, 'acidity_level' => 5.8, 'price_per_kg' => 0.62, 'yield_per_hectare' => round($weight / 0.5, 1), 'total_value' => round($weight * 0.62, 2), 'status' => 'active', 'health_status' => 'sano', 'created_at' => $now, 'updated_at' => $now]);
        }

        // Post-vendimia
        $aId = $act(['plot_id' => $plotIds[0], 'plot_planting_id' => $plantingIds[0], 'activity_type' => 'post_harvest_treatment', 'activity_date' => '2024-10-15', 'notes' => 'Tratamiento post-vendimia.']);
        DB::table('post_harvest_treatments')->insert(['activity_id' => $aId, 'product_id' => $prod3, 'application_type' => 'trunk_treatment', 'treated_area_ha' => 1.200, 'dose_per_hectare' => 1.5, 'dose_unit' => 'kg/ha', 'notes' => 'Prevención excoriosis.', 'created_at' => $now, 'updated_at' => $now]);
    }

    // ─── 9. Actividades 2025 ─────────────────────────────────────────────────

    private function createActivities2025(array $plotIds, array $plantingIds, array $productIds, int $wvId, int $campaignId, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;

        $act = fn(array $data) => DB::table('agricultural_activities')->insertGetId(array_merge([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'is_locked' => false, 'created_at' => $now, 'updated_at' => $now,
        ], $data));

        // Poda
        foreach (array_slice($plotIds, 0, 4) as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantingIds[$i * 2], 'activity_type' => 'pruning', 'activity_date' => '2025-02-' . str_pad($i * 5 + 5, 2, '0', STR_PAD_LEFT), 'notes' => 'Poda de invierno. Carga ajustada a 8 yemas/cepa.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'pruning', 'description' => 'Tijera manual + tijera neumática.', 'hours_worked' => 6.0, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Tratamientos
        $aId = $act(['plot_id' => $plotIds[0], 'plot_planting_id' => $plantingIds[0], 'activity_type' => 'phytosanitary_treatment', 'activity_date' => '2025-04-15', 'notes' => 'Preventivo mildiu.']);
        DB::table('phytosanitary_treatments')->insert(['activity_id' => $aId, 'product_id' => $prod0, 'dose_per_hectare' => 2.5, 'application_method' => 'pulverizador_hidraulico', 'target_pest' => 'Mildiu', 'humidity' => 65.0, 'created_at' => $now, 'updated_at' => $now]);

        $aId = $act(['plot_id' => $plotIds[1], 'plot_planting_id' => $plantingIds[2], 'activity_type' => 'phytosanitary_treatment', 'activity_date' => '2025-05-10', 'notes' => 'Tratamiento oídio.']);
        DB::table('phytosanitary_treatments')->insert(['activity_id' => $aId, 'product_id' => $prod1, 'dose_per_hectare' => 0.3, 'application_method' => 'pulverizador_hidraulico', 'target_pest' => 'Oídio', 'humidity' => 40.0, 'created_at' => $now, 'updated_at' => $now]);

        // Riegos
        foreach ([0, 3] as $i) {
            foreach (['2025-06-01', '2025-07-01', '2025-07-20'] as $date) {
                $aId = $act(['plot_id' => $plotIds[$i], 'plot_planting_id' => $plantingIds[$i * 2], 'activity_type' => 'irrigation', 'activity_date' => $date, 'notes' => 'Riego de apoyo por goteo.']);
                DB::table('irrigations')->insert(['activity_id' => $aId, 'irrigation_method' => 'goteo', 'duration_minutes' => 240, 'water_volume' => 800, 'water_volume_unit' => 'L', 'water_source' => 'Red de riego agrícola', 'is_fertirrigation' => false, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Fertilización
        $aId = $act(['plot_id' => $plotIds[0], 'plot_planting_id' => $plantingIds[0], 'activity_type' => 'fertilization', 'activity_date' => '2025-03-20', 'notes' => 'Abonado de fondo.']);
        DB::table('fertilizations')->insert(['activity_id' => $aId, 'fertilizer_name' => 'NPK 8-15-15', 'fertilizer_type' => 'mineral', 'npk_ratio' => '8-15-15', 'quantity' => 300.0, 'application_method' => 'incorporado_suelo', 'created_at' => $now, 'updated_at' => $now]);

        // Vendimia 2025
        foreach (array_slice($plotIds, 0, 4) as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantingIds[$i * 2], 'activity_type' => 'harvest', 'activity_date' => '2025-09-' . str_pad(10 + $i * 3, 2, '0', STR_PAD_LEFT), 'notes' => 'Vendimia manual. Selección de racimos.']);
            $weight = [920, 780, 640, 1000][$i];
            $brix2025 = 22.5 + $i * 0.5;
            DB::table('harvests')->insert(['activity_id' => $aId, 'plot_planting_id' => $plantingIds[$i * 2], 'harvest_start_date' => '2025-09-' . str_pad(10 + $i * 3, 2, '0', STR_PAD_LEFT), 'total_weight' => $weight, 'brix_degree' => $brix2025, 'baume_degree' => round($brix2025 * 0.55, 1), 'ph_level' => 3.35 + $i * 0.02, 'acidity_level' => 5.8 - $i * 0.1, 'price_per_kg' => 0.65, 'yield_per_hectare' => round($weight / 0.5, 1), 'total_value' => round($weight * 0.65, 2), 'status' => 'active', 'health_status' => 'sano', 'notes' => 'Uva sana.', 'created_at' => $now, 'updated_at' => $now]);
        }

        // Post-vendimia
        $aId = $act(['plot_id' => $plotIds[0], 'plot_planting_id' => $plantingIds[0], 'activity_type' => 'post_harvest_treatment', 'activity_date' => '2025-10-20', 'notes' => 'Protección heridas de poda.']);
        DB::table('post_harvest_treatments')->insert(['activity_id' => $aId, 'product_id' => $prod3, 'application_type' => 'trunk_treatment', 'treated_area_ha' => 1.200, 'dose_per_hectare' => 1.5, 'dose_unit' => 'kg/ha', 'notes' => 'Prevención excoriosis y eutipiosis.', 'created_at' => $now, 'updated_at' => $now]);
    }

    // ─── 10. Actividades 2026 ─────────────────────────────────────────────────
    // ~640 actividades: poda(8) + laboreo(40) + trabajos verde(48) + tratamientos(128)
    // + fertilizaciones(64) + riegos(200) + observaciones(128) + vendimia(8) + post-vendimia(16)

    private function createActivities2026(array $plotIds, array $plantingIds, array $productIds, int $wvId, int $campaignId, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;

        $act = fn(array $data) => DB::table('agricultural_activities')->insertGetId(array_merge([
            'viticulturist_id' => $uid, 'winery_viticulturist_id' => $wvId,
            'campaign_id' => $campaignId, 'is_locked' => false, 'created_at' => $now, 'updated_at' => $now,
        ], $data));

        $plots    = array_slice($plotIds,     0, 8);
        $plantings = array_slice($plantingIds, 0, 8);
        $d = fn(string $base, int $offset) => date('Y-m-d', strtotime("{$base} +{$offset} days"));

        // ── 1. Poda de invierno (Ene-Feb) — 8 ──────────────────────────────
        $podaDates = ['2026-01-12','2026-01-16','2026-01-22','2026-01-28','2026-02-03','2026-02-09','2026-02-15','2026-02-20'];
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'pruning',
                'activity_date' => $podaDates[$i], 'notes' => 'Poda de invierno 2026. Carga 8-10 yemas/cepa.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'pruning',
                'description' => 'Tijera neumática Infaco + tijera manual. Residuos triturados e incorporados.',
                'hours_worked' => 6.5, 'residue_management' => 'triturado_incorporado',
                'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 2. Laboreo suelo (3 rondas × 8 = 24) ───────────────────────────
        foreach ([
            ['2026-01-25', 'Laboreo superficial post-poda. Volteo de restos vegetales.', 'soil_tillage', null],
            ['2026-02-28', 'Pase de cultivador. Eliminación de adventicias.',             'weeding',      null],
            ['2026-03-18', 'Laboreo preparatorio pre-brotación.',                         'soil_tillage', null],
        ] as [$base, $desc, $wt, $res]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                    'activity_date' => $d($base, $i), 'notes' => $desc]);
                $row = ['activity_id' => $aId, 'work_type' => $wt, 'description' => $desc, 'hours_worked' => 3.5, 'created_at' => $now, 'updated_at' => $now];
                if ($res) $row['residue_management'] = $res;
                DB::table('cultural_works')->insert($row);
            }
        }

        // ── 3. Desniete / eliminación brotes (Mar) — 8 ─────────────────────
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                'activity_date' => $d('2026-03-25', $i), 'notes' => 'Desniete. Eliminación de brotes no deseados.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'desbudding',
                'description' => 'Eliminación manual brotes basales y de tronco. 10-15 min/cepa.',
                'hours_worked' => 4.0, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 4. Atado de pámpanos (Abr) — 8 ─────────────────────────────────
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                'activity_date' => $d('2026-04-14', $i * 2), 'notes' => 'Atado y orientación de pámpanos en alambres.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'shoot_positioning',
                'description' => 'Atado con grapas y rafia en segundo y tercer alambre.',
                'hours_worked' => 5.0, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 5. Deshojado zona racimos (May) — 8 ────────────────────────────
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                'activity_date' => $d('2026-05-12', $i * 2), 'notes' => 'Deshojado zona de racimos. Mejora aireación y luminosidad.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'leaf_removal',
                'description' => 'Deshojado manual lado mañana. 6-8 hojas eliminadas por sarmiento.',
                'hours_worked' => 5.5, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 6. Despunte de pámpanos (Jun) — 8 ──────────────────────────────
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                'activity_date' => $d('2026-06-08', $i * 2), 'notes' => 'Despunte de pámpanos. Control del vigor vegetativo.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'shoot_topping',
                'description' => 'Despunte mecánico + repaso manual. Altura canopia 70 cm.',
                'hours_worked' => 3.0, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 7. Aclareo de racimos (Jun-Jul) — 8 ────────────────────────────
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                'activity_date' => $d('2026-06-22', $i * 3), 'notes' => 'Aclareo de racimos. Objetivo 1-1.5 kg/cepa.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'bunch_thinning',
                'description' => 'Eliminación segundo racimo en cepas con exceso de carga.',
                'hours_worked' => 6.0, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 8. Desbroce verano (Jul) — 8 ───────────────────────────────────
        foreach ($plots as $i => $pid) {
            $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                'activity_date' => $d('2026-07-06', $i * 2), 'notes' => 'Desbroce entre filas. Mantenimiento cubierta vegetal.']);
            DB::table('cultural_works')->insert(['activity_id' => $aId, 'work_type' => 'weeding',
                'description' => 'Pase de desbrozadora en entrecalles.',
                'hours_worked' => 2.5, 'residue_management' => 'triturado_superficie',
                'created_at' => $now, 'updated_at' => $now]);
        }

        // ── 9. Laboreo otoñal (2 rondas × 8 = 16) ──────────────────────────
        foreach ([
            ['2026-10-15', 'Laboreo post-vendimia. Incorporación de restos vegetales.', 'soil_tillage', 'triturado_incorporado'],
            ['2026-11-10', 'Arado otoñal. Apertura de surcos para recogida de lluvias.',  'soil_tillage', null],
        ] as [$base, $desc, $wt, $res]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'cultural',
                    'activity_date' => $d($base, $i), 'notes' => $desc]);
                $row = ['activity_id' => $aId, 'work_type' => $wt, 'description' => $desc, 'hours_worked' => 3.0, 'created_at' => $now, 'updated_at' => $now];
                if ($res) $row['residue_management'] = $res;
                DB::table('cultural_works')->insert($row);
            }
        }
        // Trabajos culturales: 8+24+8+8+8+8+8+8+16 = 96

        // ── 10. Tratamientos fitosanitarios (16 rondas × 8 = 128) ──────────
        foreach ([
            ['2026-02-20', $prod0, 2.0,  'pulverizador_hidraulico', 'Mildiu',         60, 'Preventivo invernal. Caldo bordelés.'],
            ['2026-03-15', $prod0, 2.5,  'pulverizador_hidraulico', 'Mildiu',         65, 'Primer preventivo mildiu inicio brotación.'],
            ['2026-04-05', $prod0, 2.5,  'pulverizador_hidraulico', 'Mildiu',         70, 'Preventivo mildiu pre-floración.'],
            ['2026-04-22', $prod1, 0.30, 'pulverizador_hidraulico', 'Oídio',          45, 'Preventivo oídio. Humedad moderada.'],
            ['2026-05-08', $prod0, 3.0,  'pulverizador_hidraulico', 'Mildiu',         75, 'Preventivo mildiu cuajado.'],
            ['2026-05-22', $prod1, 0.35, 'pulverizador_hidraulico', 'Oídio',          42, 'Tratamiento oídio pre-verano.'],
            ['2026-06-05', $prod0, 3.0,  'pulverizador_hidraulico', 'Mildiu',         80, 'Preventivo mildiu engorde del grano.'],
            ['2026-06-18', $prod4, 1.5,  'pulverizador_hidraulico', 'Ácaros',         38, 'Acaricida. Araña roja detectada en focos.'],
            ['2026-07-02', $prod0, 2.5,  'pulverizador_hidraulico', 'Mildiu',         72, 'Tratamiento mildiu verano.'],
            ['2026-07-16', $prod1, 0.30, 'pulverizador_hidraulico', 'Oídio',          35, 'Mantenimiento preventivo oídio verano.'],
            ['2026-07-30', $prod2, 1.0,  'pulverizador_hidraulico', 'Polilla racimo', 55, 'Tratamiento polilla 2ª generación.'],
            ['2026-08-10', $prod0, 2.0,  'pulverizador_hidraulico', 'Mildiu',         68, 'Preventivo mildiu pre-veraison.'],
            ['2026-08-25', $prod2, 1.0,  'pulverizador_hidraulico', 'Botrytis',       70, 'Preventivo botrytis inicio veraison.'],
            ['2026-09-02', $prod1, 0.25, 'pulverizador_hidraulico', 'Oídio',          40, 'Último tratamiento pre-vendimia.'],
            ['2026-10-10', $prod3, 1.5,  'pulverizador_hidraulico', 'Excoriosis',     55, 'Post-vendimia. Protección heridas poda verde.'],
            ['2026-11-05', $prod3, 1.2,  'mochila',                  'Eutipiosis',     50, 'Preventivo eutipiosis y yesca en troncos.'],
        ] as [$base, $prodId, $dose, $method, $pest, $humidity, $notes]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'phytosanitary_treatment',
                    'activity_date' => $d($base, $i), 'notes' => $notes]);
                DB::table('phytosanitary_treatments')->insert(['activity_id' => $aId, 'product_id' => $prodId,
                    'dose_per_hectare' => $dose, 'application_method' => $method, 'target_pest' => $pest,
                    'humidity' => $humidity, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        // 128 actividades

        // ── 11. Fertilizaciones (8 rondas × 8 = 64) ────────────────────────
        foreach ([
            ['2026-03-05', 'NPK 8-15-15',          'mineral', '8-15-15',  300.0, 'incorporado_suelo', 'Abonado de fondo pre-brotación.'],
            ['2026-04-10', 'Nitrato amónico 27%',   'mineral', '27-0-0',   150.0, 'incorporado_suelo', 'Cobertera nitrogenada brotación.'],
            ['2026-05-15', 'Fosfato monoamónico',   'mineral', '12-61-0',   80.0, 'fertirrigation',    'Aporte P vía fertirriego cuajado.'],
            ['2026-06-01', 'Sulfato potásico',       'mineral', '0-0-50',   120.0, 'fertirrigation',    'Aporte K engorde. Fertirriego.'],
            ['2026-06-25', 'Calcio-Boro foliar',    'mineral',  null,        10.0, 'foliar',            'Corrector foliar Ca+B cuajado.'],
            ['2026-07-15', 'Sulfato de magnesio',    'mineral',  null,        60.0, 'fertirrigation',    'Corrección deficiencia Mg verano.'],
            ['2026-08-05', 'Cloruro potásico',       'mineral', '0-0-60',    90.0, 'fertirrigation',    'Pre-vendimia. Maduración con K.'],
            ['2026-10-20', 'Compost orgánico',       'organic',  null,      1500.0, 'incorporated',      'Enmienda orgánica otoñal.'],
        ] as [$base, $name, $type, $npk, $qty, $method, $notes]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'fertilization',
                    'activity_date' => $d($base, $i), 'notes' => $notes]);
                $row = ['activity_id' => $aId, 'fertilizer_name' => $name, 'fertilizer_type' => $type,
                    'quantity' => $qty, 'application_method' => $method, 'created_at' => $now, 'updated_at' => $now];
                if ($npk) $row['npk_ratio'] = $npk;
                DB::table('fertilizations')->insert($row);
            }
        }
        // 64 actividades

        // ── 12. Riegos (25 sesiones × 8 = 200) ─────────────────────────────
        foreach ([
            // Mayo — 6 sesiones
            ['2026-05-04', 180, 600,  false, null,                    null],
            ['2026-05-09', 210, 700,  false, null,                    null],
            ['2026-05-14', 180, 600,  true,  'Aminoácidos 1 L/ha',    0.5],
            ['2026-05-20', 240, 800,  false, null,                    null],
            ['2026-05-25', 210, 700,  false, null,                    null],
            ['2026-05-30', 180, 600,  true,  'NPK líquido 2 L/ha',    1.0],
            // Junio — 6 sesiones
            ['2026-06-04', 240, 850,  false, null,                    null],
            ['2026-06-09', 240, 850,  true,  'Sulfato K 1.5 kg/ha',   0.8],
            ['2026-06-14', 210, 750,  false, null,                    null],
            ['2026-06-20', 240, 850,  true,  'Calcio foliar 1 L/ha',  0.5],
            ['2026-06-25', 210, 750,  false, null,                    null],
            ['2026-06-30', 240, 850,  false, null,                    null],
            // Julio — 5 sesiones
            ['2026-07-05', 270, 950,  false, null,                    null],
            ['2026-07-11', 270, 950,  true,  'Mg foliar 1 kg/ha',     0.6],
            ['2026-07-17', 270, 950,  false, null,                    null],
            ['2026-07-23', 240, 850,  false, null,                    null],
            ['2026-07-29', 240, 850,  true,  'K líquido 2 L/ha',      1.0],
            // Agosto — 5 sesiones
            ['2026-08-04', 210, 750,  false, null,                    null],
            ['2026-08-10', 210, 750,  true,  'Calcio 1.5 L/ha',       0.7],
            ['2026-08-16', 180, 600,  false, null,                    null],
            ['2026-08-22', 180, 600,  false, null,                    null],
            ['2026-08-28', 150, 500,  false, null,                    null],
            // Septiembre — 3 sesiones
            ['2026-09-03', 120, 400,  false, null,                    null],
            ['2026-09-08', 120, 400,  false, null,                    null],
            ['2026-09-13',  90, 300,  false, null,                    null],
        ] as [$base, $duration, $volume, $isFertirrigation, $fertProduct, $fertDose]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'irrigation',
                    'activity_date' => $d($base, $i), 'notes' => 'Riego localizado por goteo.']);
                $row = ['activity_id' => $aId, 'irrigation_method' => 'goteo', 'duration_minutes' => $duration,
                    'water_volume' => $volume, 'water_volume_unit' => 'L', 'water_source' => 'Red de riego agrícola',
                    'is_fertirrigation' => $isFertirrigation, 'created_at' => $now, 'updated_at' => $now];
                if ($isFertirrigation && $fertProduct) {
                    $row['fertilizer_product'] = $fertProduct;
                    $row['fertilizer_dose_per_ha']    = $fertDose;
                }
                DB::table('irrigations')->insert($row);
            }
        }
        // 200 actividades

        // ── 13. Observaciones (16 tipos × 8 = 128) ──────────────────────────
        foreach ([
            ['2026-02-10', 'phenology', 'Recuento yemas activas post-poda. 95% viabilidad.',                    0.0,  false, null],
            ['2026-03-12', 'phenology', 'Inicio lloro. Actividad vegetativa confirmada.',                        0.0,  false, null],
            ['2026-03-28', 'phenology', 'Inicio brotación 15-20% yemas. Estado B-C BBCH.',                       0.0,  false, null],
            ['2026-04-08', 'phenology', 'Brotación 80%. Estado D-E BBCH. Sin daños por heladas.',                0.0,  false, null],
            ['2026-04-20', 'pest',      'Inspección mildiu. Sin síntomas visibles. Riesgo bajo.',                0.0,  false, null],
            ['2026-05-02', 'phenology', 'Inicio floración. 15% flores abiertas. BBCH 61.',                       0.0,  false, null],
            ['2026-05-18', 'pest',      'Oídio detectado 5% superficie. Umbral no superado. Seguimiento.',       5.0,  false, '2026-05-25'],
            ['2026-05-28', 'phenology', 'Cuajado confirmado. 40% flores cuajadas. BBCH 71.',                     0.0,  false, null],
            ['2026-06-10', 'pest',      'Polilla 1ª generación. Daños menores <3%. Monitoreo trampas.',          3.0,  false, '2026-06-20'],
            ['2026-06-28', 'pest',      'Araña roja. Focos localizados. Umbral de acción superado.',             8.0,  true,  '2026-07-05'],
            ['2026-07-08', 'phenology', 'Inicio veraison. 20% bayas cambiando color. BBCH 81.',                  0.0,  false, null],
            ['2026-07-20', 'pest',      'Revisión post-tratamiento ácaros. Control eficaz >90%.',                1.0,  false, null],
            ['2026-08-01', 'phenology', 'Veraison 90% completado. Inicio maduración tecnológica.',               0.0,  false, null],
            ['2026-08-15', 'pest',      'Botrytis riesgo medio. Humedad elevada. Tratamiento preventivo urgente.',7.0, false, '2026-08-20'],
            ['2026-09-05', 'general',   'Pre-vendimia. Brix 21.5-23.0°. pH 3.30-3.42. Estado óptimo.',          0.0,  false, null],
            ['2026-09-18', 'general',   'Parcela vendimia completada. Estado sanitario: sano.',                  0.0,  false, null],
        ] as [$base, $type, $desc, $area, $exceeded, $followUp]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'observation',
                    'activity_date' => $d($base, $i), 'notes' => $desc]);
                $row = ['activity_id' => $aId, 'observation_type' => $type, 'description' => $desc,
                    'affected_area_percentage' => $area, 'threshold_exceeded' => $exceeded,
                    'created_at' => $now, 'updated_at' => $now];
                if ($followUp) $row['follow_up_date'] = $followUp;
                DB::table('observations')->insert($row);
            }
        }
        // 128 actividades

        // ── 14. Vendimia 2026 (8) ───────────────────────────────────────────
        $vendimiaData = [
            ['2026-09-15', 0, 1050, 23.5, 3.38, 5.6, 'Tinto joven. Excelente sanidad. Vendimia manual.'],
            ['2026-09-17', 1,  870, 22.8, 3.35, 5.9, 'Blanco Marmajuelo. Aromático. Recolección temprana.'],
            ['2026-09-18', 2,  720, 22.0, 3.32, 6.1, 'Moscatel. Cosecha pequeña. Alta concentración.'],
            ['2026-09-20', 3, 1100, 23.8, 3.40, 5.5, 'Listán negro. Buena carga. Madurez perfecta.'],
            ['2026-09-22', 4,  680, 22.5, 3.33, 5.8, 'Vijariego blanco. Maduro. Baja producción.'],
            ['2026-09-23', 5,  610, 21.8, 3.30, 6.2, 'Tintilla. Parcela joven. Brix más bajo.'],
            ['2026-09-25', 6, 1200, 24.0, 3.42, 5.4, 'Listán blanco. Excelente cosecha. Brix alto.'],
            ['2026-09-26', 7,  980, 23.2, 3.36, 5.7, 'Malvasía aromática. Óptimo equilibrio.'],
        ];
        foreach ($vendimiaData as [$date, $idx, $weight, $brix, $ph, $acidity, $notes]) {
            $aId = $act(['plot_id' => $plots[$idx], 'plot_planting_id' => $plantings[$idx], 'activity_type' => 'harvest',
                'activity_date' => $date, 'notes' => $notes]);
            DB::table('harvests')->insert(['activity_id' => $aId, 'plot_planting_id' => $plantings[$idx],
                'harvest_start_date' => $date, 'total_weight' => $weight, 'brix_degree' => $brix,
                'baume_degree' => round($brix * 0.55, 1), 'ph_level' => $ph, 'acidity_level' => $acidity,
                'price_per_kg' => 0.68, 'yield_per_hectare' => round($weight / 0.5, 1),
                'total_value' => round($weight * 0.68, 2), 'status' => 'active',
                'health_status' => 'sano', 'notes' => $notes, 'created_at' => $now, 'updated_at' => $now]);
        }
        // 8 actividades

        // ── 15. Post-vendimia (2 rondas × 8 = 16) ──────────────────────────
        foreach ([
            ['2026-10-05', 'trunk_treatment', 1.5, 'kg/ha', 'Protección heridas poda verde. Prevención excoriosis.',   0],
            ['2026-11-03', 'trunk_treatment', 1.2, 'kg/ha', 'Tratamiento preventivo eutipiosis y yesca en troncos.',  48],
        ] as [$base, $appType, $dose, $doseUnit, $notes, $reentry]) {
            foreach ($plots as $i => $pid) {
                $aId = $act(['plot_id' => $pid, 'plot_planting_id' => $plantings[$i], 'activity_type' => 'post_harvest_treatment',
                    'activity_date' => $d($base, $i), 'notes' => $notes]);
                DB::table('post_harvest_treatments')->insert(['activity_id' => $aId, 'product_id' => $prod3,
                    'application_type' => $appType, 'treated_area_ha' => 1.2, 'dose_per_hectare' => $dose,
                    'dose_unit' => $doseUnit, 'reentry_interval_hours' => $reentry,
                    'notes' => $notes, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        // 16 actividades
        // ─────────────────────────────────────────────────────────────────────
        // TOTAL 2026: 96+128+64+200+128+8+16 = 640 actividades
    }

    // ─── 11. Fenología ─────────────────────────────────────────────────────────

    private function createPhenology(array $plantingIds, int $c2024, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $insert = function (int $plantingId, int $campaignId, string $event, string $date) use ($uid, $now) {
            DB::table('phenology_observations')->insertOrIgnore([
                'plot_planting_id' => $plantingId, 'campaign_id' => $campaignId,
                'viticulturist_id' => $uid, 'event' => $event, 'obs_date' => $date,
                'source' => 'manual', 'confidence' => 90, 'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        };

        $events2024 = [['budbreak','2024-03-10'],['flowering','2024-04-28'],['fruit_set','2024-05-18'],['veraison','2024-07-25'],['harvest','2024-09-08']];
        $events2025 = [['budbreak','2025-03-10'],['flowering','2025-04-28'],['fruit_set','2025-05-18'],['veraison','2025-07-25'],['harvest','2025-09-08']];

        foreach ($plantingIds as $pid) {
            foreach ($events2024 as [$e, $d]) { $insert($pid, $c2024, $e, $d); }
            foreach ($events2025 as [$e, $d]) { $insert($pid, $c2025, $e, $d); }
        }
        // 2026 — 10 eventos × 8 plantaciones = 80 registros
        $events2026 = [
            ['dormancy',          '2026-01-10'],
            ['bud_swell',         '2026-02-18'],
            ['budbreak',          '2026-03-14'],
            ['shoot_growth',      '2026-04-02'],
            ['inflorescence',     '2026-04-22'],
            ['flowering',         '2026-05-08'],
            ['fruit_set',         '2026-05-28'],
            ['berry_development', '2026-06-28'],
            ['veraison',          '2026-08-05'],
            ['harvest',           '2026-09-18'],
        ];
        foreach ($plantingIds as $pid) {
            foreach ($events2026 as [$e, $dt]) { $insert($pid, $c2026, $e, $dt); }
        }
    }

    // ─── 12. Plagas ────────────────────────────────────────────────────────────

    private function createPests(array $productIds, $now): array
    {
        [$prod0, $prod1, $prod2, $prod3, $prod4] = $productIds;
        $pests = [
            ['name' => 'Polilla del racimo (Producer)',           'scientific_name' => 'Lobesia botrana',     'type' => 'pest',    'control_methods' => ['biologico','quimico'],  'product_id' => $prod2],
            ['name' => 'Mildiu de la vid (Producer)',             'scientific_name' => 'Plasmopara viticola', 'type' => 'disease', 'control_methods' => ['quimico','cultural'],   'product_id' => $prod0],
            ['name' => 'Oídio de la vid (Producer)',              'scientific_name' => 'Erysiphe necator',    'type' => 'disease', 'control_methods' => ['quimico','cultural'],   'product_id' => $prod1],
            ['name' => 'Araña roja (Producer)',                   'scientific_name' => 'Panonychus ulmi',     'type' => 'pest',    'control_methods' => ['biologico','quimico'],  'product_id' => $prod4],
            ['name' => 'Botrytis / Podredumbre gris (Producer)', 'scientific_name' => 'Botrytis cinerea',    'type' => 'disease', 'control_methods' => ['cultural','quimico'],   'product_id' => $prod1],
            ['name' => 'Excoriosis (Producer)',                   'scientific_name' => 'Phomopsis viticola',  'type' => 'disease', 'control_methods' => ['cultural','quimico'],   'product_id' => $prod3],
        ];

        $ids = [];
        foreach ($pests as $p) {
            $productEff = $p['product_id'];
            unset($p['product_id']);
            $pestId = DB::table('pests')->insertGetId(array_merge($p, [
                'control_methods' => json_encode($p['control_methods']),
                'active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]));
            DB::table('pest_product_effectiveness')->insert(['pest_id' => $pestId, 'product_id' => $productEff, 'effectiveness_rating' => 4, 'notes' => 'Eficacia contrastada.', 'created_at' => $now, 'updated_at' => $now]);
            $ids[] = $pestId;
        }
        return $ids;
    }

    // ─── 13. Rendimientos estimados ───────────────────────────────────────────

    private function createEstimatedYields(array $plantingIds, int $c2024, int $c2025, int $c2026, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $yields2024 = [2000, 1700, 1400, 1800, 1300, 1200, 2300, 2100];
        $yields2025 = [2100, 1800, 1500, 1900, 1400, 1300, 2400, 2200];
        $yields2026 = [2200, 1850, 1550, 2000, 1450, 1350, 2500, 2300];
        foreach ($plantingIds as $i => $pid) {
            DB::table('estimated_yields')->insert(['plot_planting_id' => $pid, 'campaign_id' => $c2024, 'estimated_by' => $uid, 'estimated_total_yield' => $yields2024[$i] ?? 1600, 'estimated_yield_per_hectare' => 1500, 'estimation_date' => '2024-07-15', 'estimation_method' => 'sampling', 'notes' => 'Aforo verano 2024.', 'created_at' => $now, 'updated_at' => $now]);
            DB::table('estimated_yields')->insert(['plot_planting_id' => $pid, 'campaign_id' => $c2025, 'estimated_by' => $uid, 'estimated_total_yield' => $yields2025[$i] ?? 1600, 'estimated_yield_per_hectare' => 1500, 'estimation_date' => '2025-07-15', 'estimation_method' => 'sampling', 'notes' => 'Aforo verano 2025.', 'created_at' => $now, 'updated_at' => $now]);
            DB::table('estimated_yields')->insert(['plot_planting_id' => $pid, 'campaign_id' => $c2026, 'estimated_by' => $uid, 'estimated_total_yield' => $yields2026[$i] ?? 1700, 'estimated_yield_per_hectare' => 1500, 'estimation_date' => '2026-04-01', 'estimation_method' => 'historical', 'notes' => 'Estimación inicial.', 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 14. Maquinaria ───────────────────────────────────────────────────────

    private function createMachinery($now): array
    {
        $uid = self::PRODUCER_USER_ID;
        $machines = [
            ['name' => 'Tractor Kubota M5091',         'brand' => 'Kubota',    'model' => 'M5091',           'year' => 2018, 'type' => 'tractor',           'roma_registration' => 'GC-001-P'],
            ['name' => 'Pulverizador Hardi 600L',      'brand' => 'Hardi',     'model' => 'Ranger 600',      'year' => 2019, 'type' => 'sprayer',           'roma_registration' => null],
            ['name' => 'Tijera Neumática Infaco',      'brand' => 'Infaco',    'model' => 'F3015',           'year' => 2021, 'type' => 'pruning_equipment', 'roma_registration' => null],
            ['name' => 'Remolque agrícola 3t',         'brand' => 'Fautras',   'model' => 'Plateau 3T',      'year' => 2017, 'type' => 'trailer',           'roma_registration' => 'GC-002-P'],
            ['name' => 'Despalilladora-estrujadora',   'brand' => 'Diemme',    'model' => 'GD 20',           'year' => 2020, 'type' => 'harvesting_machine','roma_registration' => null],
            ['name' => 'Prensa neumática 20 hl',       'brand' => 'Della Toffola','model' => 'PA 20',        'year' => 2020, 'type' => 'press',             'roma_registration' => null],
            ['name' => 'Bomba de trasiego centrífuga', 'brand' => 'Velo',      'model' => 'CM 50',           'year' => 2019, 'type' => 'pump',              'roma_registration' => null],
            ['name' => 'Equipo de frío 5000 kcal/h',  'brand' => 'Cofrimell', 'model' => 'CF-5000',         'year' => 2021, 'type' => 'cooling_equipment', 'roma_registration' => null],
        ];
        $ids = [];
        foreach ($machines as $m) {
            $ids[] = DB::table('machinery')->insertGetId(array_merge($m, ['viticulturist_id' => $uid, 'active' => true, 'created_at' => $now, 'updated_at' => $now]));
        }
        return $ids;
    }

    // ─── 15. Explotaciones ────────────────────────────────────────────────────

    private function createExploitations($now): array
    {
        $uid = self::PRODUCER_USER_ID;
        $id = DB::table('exploitations')->insertGetId([
            'viticulturist_id' => $uid, 'exploitation_name' => 'Explotación Productor Agaete',
            'rea_code' => 'REA-GC-2026-' . $uid, 'siex_exploitation_id' => 'SIEX-GC-' . $uid,
            'holder_name' => 'Productor Demo Agaete', 'holder_nif' => 'B35765432',
            'is_ecological' => false, 'is_integrated_production' => false, 'is_quality_scheme' => true,
            'quality_scheme_desc' => 'DO Gran Canaria', 'active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        return [$id];
    }

    // ─── 16. Compliance ───────────────────────────────────────────────────────

    private function createCompliance(array $exploitationIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $expId = $exploitationIds[0] ?? null;

        // Aplicadores ROPO (3)
        foreach ([
            ['Juan Carlos Hernández', '2026-05-22', 'ROPO-GC-P001', 'qualified'],
            ['María Pérez González',   '2025-09-15', 'ROPO-GC-P002', 'basic'],
            ['Demo Productor',         '2027-06-30', 'ROPO-GC-P003', 'qualified'],
        ] as [$name, $expiry, $ropo, $cat]) {
            DB::table('field_applicators')->insert(['viticulturist_id' => $uid, 'name' => $name, 'ropo_number' => $ropo, 'ropo_category' => $cat, 'ropo_expiry_date' => $expiry, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Equipos ITB (2)
        DB::table('field_equipment')->insert(['viticulturist_id' => $uid, 'name' => 'Pulverizador Hardi Ranger 600L', 'equipment_type' => 'sprayer', 'registration_number' => 'ATM-GC-P001', 'purchase_date' => '2019-02-15', 'last_inspection_date' => '2024-11-10', 'next_inspection_date' => '2027-11-10', 'inspection_entity' => 'CIATCA Gran Canaria', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('field_equipment')->insert(['viticulturist_id' => $uid, 'name' => 'Tractor Kubota M5091', 'equipment_type' => 'tractor', 'registration_number' => 'GC-001-P', 'purchase_date' => '2018-03-10', 'last_inspection_date' => '2025-03-10', 'next_inspection_date' => '2026-03-10', 'inspection_entity' => 'ITV Gran Canaria', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);

        // Asesorías (2)
        foreach ([
            ['Dra. Isabel Cabrera', 'ASESOR-GC-P108', 'phytosanitary', 'AgroTécnica Canarias SL'],
            ['Ing. Marcos Hernández', 'ASESOR-GC-P251', 'agronomy', 'Estudio Agronómico Atlántico'],
        ] as [$name, $lic, $spec, $company]) {
            DB::table('advisory_memberships')->insert(['viticulturist_id' => $uid, 'advisor_name' => $name, 'license_number' => $lic, 'specialty' => $spec, 'company_name' => $company, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Autorizaciones comerciales (3)
        if ($expId) {
            foreach ([
                ['do_registration', 'DOP-GC-PROD-0034', 'Inscripción DOP Gran Canaria', 'Consejo Regulador DOP Gran Canaria', '2021-03-15', null],
                ['planting_right', 'DERECHO-VID-P421', 'Derecho de plantación — 0,5 ha', 'FEGA', '2023-01-20', '2030-12-31'],
                ['organic_certification', 'BIO-GC-PROD-0098', 'Certificación ecológica 2025-2027', 'CAAE', '2025-05-01', '2027-04-30'],
            ] as [$type, $code, $desc, $body, $issue, $expiry]) {
                DB::table('commercial_authorizations')->insert(['exploitation_id' => $expId, 'viticulturist_id' => $uid, 'authorization_type' => $type, 'authorization_code' => $code, 'description' => $desc, 'issuing_body' => $body, 'issue_date' => $issue, 'expiry_date' => $expiry, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Seguros agrarios (3)
        foreach ([
            ['comprehensive', 'Agroseguro', 'POL-2024-GC-P001', '2024-04-01', '2024-09-30', 48000.00, 1250.00, 'expired'],
            ['comprehensive', 'Agroseguro', 'POL-2025-GC-P001', '2025-04-01', '2025-09-30', 51000.00, 1380.00, 'expired'],
            ['comprehensive', 'Agroseguro', 'POL-2026-GC-P001', '2026-04-01', '2026-09-30', 55000.00, 1450.00, 'active'],
        ] as [$coverage, $company, $policy, $start, $end, $insured, $premium, $status]) {
            DB::table('agri_insurances')->insert(['viticulturist_id' => $uid, 'coverage_type' => $coverage, 'insurance_company' => $company, 'policy_number' => $policy, 'start_date' => $start, 'end_date' => $end, 'insured_amount' => $insured, 'premium' => $premium, 'status' => $status, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 17. Subcontrataciones ───────────────────────────────────────────────

    private function createSubcontractings(array $plotIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $plotCount    = count($plotIds);
        $campaigns    = [
            $c24 => ['year' => 2024, 'invoiced' => true],
            $c25 => ['year' => 2025, 'invoiced' => true],
            $c26 => ['year' => 2026, 'invoiced' => false],
        ];
        $serviceTypes = ['harvesting', 'treatment', 'pruning', 'analysis', 'soil_work', 'fertilization', 'irrigation', 'transport', 'other', 'other'];
        $companies    = [
            'Cuadrilla Pérez e Hijos', 'AgroService Canarias SL', 'Podas Atlánticas SL',
            'Laboratorio Agrícola Canario', 'Labores Agrícolas Guía SL', 'Nutrición Vegetal Canarias SL',
            'Riegos Insulares SA', 'Transporte Agrícola GC', 'TechAgro Canarias', 'AgroConsult GC SL',
        ];
        $descriptions = [
            'Vendimia manual parcela #{p}', 'Tratamiento preventivo mildiu #{p}', 'Poda invierno parcela #{p}',
            'Análisis madurez + residuos #{p}', 'Laboreo entre líneas #{p}', 'Fertirrigación nitrogenada #{p}',
            'Instalación/mantenimiento goteo #{p}', 'Transporte materiales parcela #{p}', 'Trabajos varios parcela #{p}', 'Servicios técnicos parcela #{p}',
        ];

        $i = 1;
        foreach ($campaigns as $cId => $cam) {
            $year = $cam['year'];
            // 150 subcontrataciones por campaña = 450 total
            for ($j = 1; $j <= 150; $j++, $i++) {
                $sTypeIdx  = ($i - 1) % count($serviceTypes);
                $pIdx      = ($i - 1) % $plotCount;
                $month     = mt_rand(1, 12);
                $day       = mt_rand(1, 28);
                $amount    = round(mt_rand(300, 2500) + mt_rand(0, 99) / 100, 2);
                $invoiced  = $cam['invoiced'];
                $invNum    = $invoiced ? strtoupper(substr($serviceTypes[$sTypeIdx], 0, 3)) . "-{$year}-P" . str_pad($i, 4, '0', STR_PAD_LEFT) : null;
                $desc      = str_replace('#{p}', $pIdx + 1, $descriptions[$sTypeIdx]);
                DB::table('subcontractings')->insert([
                    'viticulturist_id' => $uid,
                    'campaign_id'      => $cId,
                    'plot_id'          => $plotIds[$pIdx],
                    'service_type'     => $serviceTypes[$sTypeIdx],
                    'company_name'     => $companies[$sTypeIdx % count($companies)],
                    'service_date'     => sprintf('%d-%02d-%02d', $year, $month, $day),
                    'amount'           => $amount,
                    'invoiced'         => $invoiced,
                    'invoice_number'   => $invNum,
                    'description'      => $desc,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }
    }

    // ─── 18. Registros Oficiales ─────────────────────────────────────────────

    private function createRegistrosOficiales(array $plantingIds, array $plotIds, array $productIds, array $machineryIds, array $exploitationIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid   = self::PRODUCER_USER_ID;
        $expId = $exploitationIds[0] ?? null;
        $mach1 = $machineryIds[0] ?? null;

        // ── Análisis de Residuos (6) ──────────────────────────────────────────
        foreach ([
            [$c24, $plantingIds[0], '2024-07-15', '2024-07-10', 'Laboratorio Agrícola Canario SA', 'uva',  true, 'Cumple LMR. Mancozeb < 0,05 mg/kg'],
            [$c24, null,            '2024-05-10', '2024-05-05', 'Aqualogic Canarias SL',           'suelo',true, 'Análisis suelo 2024. pH 6.8'],
            [$c25, $plantingIds[0], '2025-07-12', '2025-07-08', 'Laboratorio Agrícola Canario SA', 'uva',  true, 'Conforme. Mancozeb nd'],
            [$c25, null,            '2025-04-20', '2025-04-15', 'Aqualogic Canarias SL',           'agua', true, 'Agua riego 2025. Nitratos 10 mg/L'],
            [$c26, $plantingIds[0], '2026-03-15', '2026-03-10', 'Laboratorio Agrícola Canario SA', 'suelo',true, 'Análisis suelo brotación 2026'],
            [$c26, null,            '2026-03-15', '2026-03-10', 'Aqualogic Canarias SL',           'agua', true, 'Agua riego brotación 2026'],
        ] as [$cId, $ppId, $aDate, $sDate, $lab, $sType, $compliant, $notes]) {
            DB::table('residue_analyses')->insert(['campaign_id' => $cId, 'plot_planting_id' => $ppId, 'viticulturist_id' => $uid, 'analysis_date' => $aDate, 'sample_date' => $sDate, 'laboratory_name' => $lab, 'sample_type' => $sType, 'overall_compliant' => $compliant, 'notes' => $notes, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Gestión de Residuos (6) ───────────────────────────────────────────
        foreach ([
            [$c24, $plotIds[0], '2024-01-25', 'pruning_wood', 'incorporation', 1850.0, 'Triturado e incorporado'],
            [$c24, $plotIds[0], '2024-09-10', 'grape_marc',   'sale',          4200.0, 'Vendido a destilería'],
            [$c25, $plotIds[0], '2025-01-20', 'pruning_wood', 'incorporation', 1920.0, 'Triturado e incorporado'],
            [$c25, $plotIds[0], '2025-09-05', 'grape_marc',   'sale',          4600.0, 'Vendido a destilería'],
            [$c26, $plotIds[0], '2026-01-22', 'pruning_wood', 'incorporation', 1980.0, 'Triturado 2026'],
            [$c26, $plotIds[1], '2026-02-03', 'pruning_wood', 'composting',    1050.0, 'Compostaje 2026'],
        ] as [$cId, $pId, $date, $matType, $pracType, $qty, $notes]) {
            DB::table('residue_managements')->insert(['campaign_id' => $cId, 'plot_id' => $pId, 'viticulturist_id' => $uid, 'date' => $date, 'practice_type' => $pracType, 'material_type' => $matType, 'estimated_quantity' => $qty, 'quantity_unit' => 'kg', 'notes' => $notes, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Consumo Energético (6) ────────────────────────────────────────────
        foreach ([
            [$c24, '2024-01-15', 'diesel', 'liters', 80.0, 1.42, 'Tractor poda + laboreo'],
            [$c24, '2024-04-15', 'electricity', 'kwh', 220.0, 0.18, 'Riego goteo'],
            [$c25, '2025-01-15', 'diesel', 'liters', 85.0, 1.42, 'Tractor poda 2025'],
            [$c25, '2025-06-15', 'electricity', 'kwh', 300.0, 0.18, 'Riego verano'],
            [$c26, '2026-01-15', 'diesel', 'liters', 75.0, 1.42, 'Tractor poda 2026'],
            [$c26, '2026-03-10', 'electricity', 'kwh', 150.0, 0.18, 'Instalaciones campo'],
        ] as [$cId, $date, $eType, $unit, $qty, $cpu, $desc]) {
            $co2 = $eType === 'diesel' ? round($qty * 2.68, 3) : round($qty * 0.233, 3);
            DB::table('energy_usages')->insert(['campaign_id' => $cId, 'viticulturist_id' => $uid, 'machinery_id' => $eType === 'diesel' ? $mach1 : null, 'date' => $date, 'energy_type' => $eType, 'unit' => $unit, 'quantity' => $qty, 'cost_per_unit' => $cpu, 'total_cost' => round($qty * $cpu, 2), 'co2_kg_equivalent' => $co2, 'usage_description' => $desc, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Concesiones de Agua (2) ───────────────────────────────────────────
        foreach ([
            ['subterranea', 'GC-SUB-P-0088', 'Pozo Barranco Productor', 'CHC', '2019-06-01', '2029-05-31', 15000.0, 11200.0, 5.40],
            ['comunidad_regantes', 'CGR-GC-P-014', 'Canal Noreste', 'Comunidad Regantes Canal Noreste', '2021-03-10', null, 8000.0, 6800.0, 2.90],
        ] as [$type, $num, $body, $auth, $cDate, $exp, $maxVol, $usedVol, $surface]) {
            DB::table('water_concessions')->insert(['viticulturist_id' => $uid, 'concession_type' => $type, 'concession_number' => $num, 'water_body' => $body, 'authority' => $auth, 'concession_date' => $cDate, 'expiry_date' => $exp, 'max_volume_m3' => $maxVol, 'used_volume_m3' => $usedVol, 'surface_ha' => $surface, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Planes de Fertilización (3) ───────────────────────────────────────
        foreach ([
            [$c24, 2024, 'active', '2024-01-20'], [$c25, 2025, 'active', '2025-01-18'], [$c26, 2026, 'draft', null],
        ] as [$cId, $yr, $status, $approvalDate]) {
            DB::table('fertilization_plans')->insert(['viticulturist_id' => $uid, 'campaign_id' => $cId, 'plan_year' => $yr, 'nitrate_zone' => false, 'prepared_by' => 'Ing. Marcos Hernández', 'approval_date' => $approvalDate, 'total_surface_ha' => 3.80, 'total_n_kg_ha' => 65.0, 'total_p_kg_ha' => 20.0, 'total_k_kg_ha' => 48.0, 'status' => $status, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Devolución Envases (6) ────────────────────────────────────────────
        foreach ([
            [$c24, $productIds[0], '2024-03-10', 'Ridomil Gold', 'plastic', 12, 8.4, 'sigfito'],
            [$c24, $productIds[1], '2024-05-15', 'Domark 10 EC', 'plastic', 8,  5.6, 'sigfito'],
            [$c25, $productIds[0], '2025-03-08', 'Ridomil Gold', 'plastic', 14, 9.8, 'sigfito'],
            [$c25, $productIds[1], '2025-05-12', 'Domark 10 EC', 'plastic', 10, 7.0, 'sigfito'],
            [$c26, $productIds[0], '2026-02-20', 'Ridomil Gold', 'plastic', 6,  4.2, 'sigfito'],
            [$c26, $productIds[1], '2026-03-05', 'Domark 10 EC', 'plastic', 4,  2.8, 'sigfito'],
        ] as [$cId, $pId, $date, $pName, $cType, $qty, $weight, $system]) {
            DB::table('phytosanitary_container_returns')->insert(['viticulturist_id' => $uid, 'campaign_id' => $cId, 'phytosanitary_product_id' => $pId, 'date' => $date, 'product_name' => $pName, 'container_type' => $cType, 'containers_quantity' => $qty, 'total_weight_kg' => $weight, 'collection_system' => $system, 'collection_point' => 'Punto SIGFITO — Agaete', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Declaraciones de Vendimia (2) ─────────────────────────────────────
        foreach ([
            [$c24, 2024, '2024-10-01', 'Consejo Regulador DOP Gran Canaria', 'DV-GC-P-2024-0342', 3.80, 2900.0, 'submitted'],
            [$c25, 2025, '2025-10-05', 'Consejo Regulador DOP Gran Canaria', 'DV-GC-P-2025-0389', 3.80, 3340.0, 'accepted'],
        ] as [$cId, $yr, $dDate, $auth, $ref, $surface, $totalKg, $status]) {
            DB::table('harvest_declarations')->insert(['viticulturist_id' => $uid, 'campaign_id' => $cId, 'declaration_year' => $yr, 'declaration_date' => $dDate, 'authority' => $auth, 'reference_number' => $ref, 'total_surface_ha' => $surface, 'total_kg' => $totalKg, 'status' => $status, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Subproductos Vendimia (4) ─────────────────────────────────────────
        foreach ([
            [$c24, '2024-09-05', 'pomace', 'distillery', 2800.0, 'Destilados GC SL'],
            [$c24, '2024-09-05', 'stem',   'composting',  620.0, 'Compostería Agaete'],
            [$c25, '2025-09-03', 'pomace', 'distillery', 3200.0, 'Destilados GC SL'],
            [$c25, '2025-09-03', 'stem',   'composting',  720.0, 'Compostería Agaete'],
        ] as [$cId, $date, $type, $dest, $qty, $destName]) {
            DB::table('harvest_byproducts')->insert(['viticulturist_id' => $uid, 'campaign_id' => $cId, 'date' => $date, 'byproduct_type' => $type, 'quantity_kg' => $qty, 'destination_type' => $dest, 'destination_name' => $destName, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Certificaciones (3) ───────────────────────────────────────────────
        foreach ([
            ['denominacion_origen', 'Consejo Regulador DOP Gran Canaria', 'DOP-GC-PROD-0034', '2021-03-15', null],
            ['produccion_integrada', 'Gobierno de Canarias', 'PI-GC-PROD-0142', '2020-07-01', null],
            ['globalgap', 'Bureau Veritas', 'GG-ES-PROD-88421', '2025-07-01', '2027-06-30'],
        ] as [$type, $body, $num, $issue, $expiry]) {
            DB::table('certifications')->insert(['viticulturist_id' => $uid, 'certification_type' => $type, 'certifying_body' => $body, 'certificate_number' => $num, 'issue_date' => $issue, 'expiry_date' => $expiry, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Exportaciones CUE (3) ─────────────────────────────────────────────
        if ($expId) {
            foreach ([
                [$c24, 2024, 'annual', '2024-01-01', '2024-12-31', 'accepted', '2025-01-20'],
                [$c25, 2025, 'quarterly', '2025-01-01', '2025-03-31', 'accepted', '2025-04-12'],
                [$c26, 2026, 'quarterly', '2026-01-01', '2026-03-31', 'draft', null],
            ] as [$cId, $yr, $period, $from, $to, $status, $sentAt]) {
                DB::table('cue_exports')->insert(['exploitation_id' => $expId, 'viticulturist_id' => $uid, 'campaign_year' => $yr, 'period_type' => $period, 'from_date' => $from, 'to_date' => $to, 'status' => $status, 'sent_at' => $sentAt ? $sentAt . ' 09:00:00' : null, 'accepted_at' => $status === 'accepted' ? ($sentAt . ' 11:00:00') : null, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // ── Informes Oficiales (3 base) ───────────────────────────────────────
        foreach ([
            [$c24, 'full_digital_notebook',    '2024-01-01', '2024-12-31', 'completed'],
            [$c25, 'full_digital_notebook',    '2025-01-01', '2025-12-31', 'completed'],
            [$c26, 'phytosanitary_treatments', '2026-01-01', '2026-03-31', 'processing'],
        ] as $i => [$cId, $type, $start, $end, $procStatus]) {
            $hash = md5("report_prod_{$uid}_{$type}_{$start}_{$end}_{$i}");
            DB::table('official_reports')->insert(['user_id' => $uid, 'report_type' => $type, 'period_start' => $start, 'period_end' => $end, 'signature_hash' => $hash, 'signed_at' => $now, 'signed_ip' => '192.168.1.1', 'verification_code' => substr(md5("verify_{$hash}"), 0, 32), 'verification_count' => 0, 'pdf_path' => $procStatus === 'completed' ? "reports/prod_{$uid}/{$type}_{$start}_{$end}.pdf" : null, 'pdf_size' => $procStatus === 'completed' ? rand(120000, 850000) : null, 'is_valid' => true, 'processing_status' => $procStatus, 'completed_at' => $procStatus === 'completed' ? $now : null, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ══════════════════════════════════════════════════════════════════════
        // AMPLIACIÓN 2026 — ~120 registros adicionales
        // ══════════════════════════════════════════════════════════════════════

        // ── Energy usages 2026 mensual (30 records) ───────────────────────────
        // diesel: Feb-Nov (10 meses) + electricity: Ene, Feb, Abr-Nov (10 meses)
        foreach ([
            ['2026-02-18', 'diesel',      'liters',  90.0, 1.45, 'Tractor poda y laboreo febrero.'],
            ['2026-03-20', 'diesel',      'liters',  70.0, 1.45, 'Tractor laboreo pre-brotación.'],
            ['2026-04-12', 'diesel',      'liters',  65.0, 1.47, 'Tractor desniete y atado.'],
            ['2026-05-08', 'diesel',      'liters',  80.0, 1.47, 'Tractor deshojado y tratamientos.'],
            ['2026-06-10', 'diesel',      'liters',  75.0, 1.48, 'Tractor pase verano.'],
            ['2026-07-05', 'diesel',      'liters',  60.0, 1.48, 'Tractor aclareo y despunte.'],
            ['2026-08-02', 'diesel',      'liters',  55.0, 1.50, 'Tractor preparación vendimia.'],
            ['2026-09-15', 'diesel',      'liters', 110.0, 1.50, 'Remolque vendimia + tractor.'],
            ['2026-10-08', 'diesel',      'liters',  80.0, 1.48, 'Tractor laboreo post-vendimia.'],
            ['2026-11-12', 'diesel',      'liters',  70.0, 1.47, 'Tractor arado otoñal.'],
            ['2026-01-10', 'electricity', 'kwh',    120.0, 0.19, 'Instalaciones campo enero.'],
            ['2026-02-10', 'electricity', 'kwh',    130.0, 0.19, 'Instalaciones campo febrero.'],
            ['2026-04-15', 'electricity', 'kwh',    180.0, 0.19, 'Bomba riego + instalaciones.'],
            ['2026-05-15', 'electricity', 'kwh',    320.0, 0.19, 'Riego goteo mayo.'],
            ['2026-06-15', 'electricity', 'kwh',    480.0, 0.19, 'Riego goteo junio.'],
            ['2026-07-15', 'electricity', 'kwh',    520.0, 0.19, 'Riego goteo julio.'],
            ['2026-08-15', 'electricity', 'kwh',    440.0, 0.19, 'Riego goteo agosto.'],
            ['2026-09-10', 'electricity', 'kwh',    180.0, 0.19, 'Riego pre-vendimia.'],
            ['2026-10-15', 'electricity', 'kwh',    140.0, 0.19, 'Instalaciones post-vendimia.'],
            ['2026-11-15', 'electricity', 'kwh',    120.0, 0.19, 'Instalaciones noviembre.'],
        ] as [$date, $eType, $unit, $qty, $cpu, $desc]) {
            $co2 = $eType === 'diesel' ? round($qty * 2.68, 3) : round($qty * 0.233, 3);
            DB::table('energy_usages')->insert(['campaign_id' => $c26, 'viticulturist_id' => $uid,
                'machinery_id' => $eType === 'diesel' ? $mach1 : null, 'date' => $date,
                'energy_type' => $eType, 'unit' => $unit, 'quantity' => $qty,
                'cost_per_unit' => $cpu, 'total_cost' => round($qty * $cpu, 2),
                'co2_kg_equivalent' => $co2, 'usage_description' => $desc,
                'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Gestión de residuos 2026 (parcelas 2-8, múltiples tipos) — 20 ────
        $plots8 = array_slice($plotIds, 0, 8);
        $residueRows = [];
        foreach ($plots8 as $i => $pId) {
            $residueRows[] = [$pId, '2026-02-' . str_pad(5 + $i, 2, '0', STR_PAD_LEFT), 'pruning_wood', 'incorporation', round(1800 + $i * 80, 0), 'Restos poda triturados e incorporados.'];
            $residueRows[] = [$pId, '2026-10-' . str_pad(8 + $i, 2, '0', STR_PAD_LEFT), 'grape_marc',   'sale',          round(3200 + $i * 200, 0), 'Orujo vendido a destilería.'];
        }
        // Skip plots already seeded (index 0 pruning_wood and 1 composting exist)
        foreach (array_slice($residueRows, 2) as [$pId, $date, $matType, $pracType, $qty, $notes]) {
            DB::table('residue_managements')->insert(['campaign_id' => $c26, 'plot_id' => $pId,
                'viticulturist_id' => $uid, 'date' => $date, 'practice_type' => $pracType,
                'material_type' => $matType, 'estimated_quantity' => $qty, 'quantity_unit' => 'kg',
                'notes' => $notes, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Análisis de residuos 2026 adicionales (8 records) ─────────────────
        foreach (array_slice($plantingIds, 1, 7) as $i => $ppId) {
            $types = ['uva', 'suelo', 'agua', 'hoja', 'uva', 'suelo', 'agua'];
            $labs  = ['Laboratorio Agrícola Canario SA', 'Aqualogic Canarias SL', 'Laboratorio Agrícola Canario SA',
                      'Aqualogic Canarias SL', 'Laboratorio Agrícola Canario SA', 'Aqualogic Canarias SL', 'Laboratorio Agrícola Canario SA'];
            $sType = $types[$i];
            DB::table('residue_analyses')->insert(['campaign_id' => $c26, 'plot_planting_id' => $ppId,
                'viticulturist_id' => $uid,
                'analysis_date' => '2026-04-' . str_pad(5 + $i * 2, 2, '0', STR_PAD_LEFT),
                'sample_date'    => '2026-04-' . str_pad(2 + $i * 2, 2, '0', STR_PAD_LEFT),
                'laboratory_name' => $labs[$i], 'sample_type' => $sType,
                'overall_compliant' => true, 'notes' => "Análisis {$sType} parcela " . ($i + 2) . " campaña 2026.",
                'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Devolución envases 2026 adicionales (15 records) ──────────────────
        $products5 = array_slice($productIds, 0, 5);
        $returnRounds = [
            ['2026-04-28', 'Tratamientos abril', 5, 3.5],
            ['2026-06-10', 'Tratamientos mayo-junio', 8, 5.6],
            ['2026-09-05', 'Tratamientos verano', 6, 4.2],
        ];
        foreach ($returnRounds as [$date, $label, $qty, $weight]) {
            foreach (array_slice($products5, 0, 5) as $j => $pId) {
                if ($j >= 3) continue; // 3 productos × 3 rondas = 9 + los 2 originales = 11
                DB::table('phytosanitary_container_returns')->insert(['viticulturist_id' => $uid,
                    'campaign_id' => $c26, 'phytosanitary_product_id' => $pId,
                    'date' => $date, 'product_name' => "Producto fitosanitario " . ($j + 1),
                    'container_type' => 'plastic', 'containers_quantity' => $qty + $j,
                    'total_weight_kg' => round($weight + $j * 0.4, 1),
                    'collection_system' => 'sigfito',
                    'collection_point' => 'Punto SIGFITO — Agaete',
                    'active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // ── Declaración vendimia 2026 provisional (1) ─────────────────────────
        DB::table('harvest_declarations')->insert(['viticulturist_id' => $uid, 'campaign_id' => $c26,
            'declaration_year' => 2026, 'declaration_date' => '2026-09-28',
            'authority' => 'Consejo Regulador DOP Gran Canaria',
            'reference_number' => 'DV-GC-P-2026-0412', 'total_surface_ha' => 3.80,
            'total_kg' => 8210.0, 'status' => 'draft',
            'active' => true, 'created_at' => $now, 'updated_at' => $now]);

        // ── Subproductos vendimia 2026 estimados (4) ──────────────────────────
        foreach ([
            ['2026-09-25', 'pomace', 'distillery', 3500.0, 'Destilados GC SL'],
            ['2026-09-25', 'stem',   'composting',  780.0, 'Compostería Agaete'],
            ['2026-10-02', 'lees',   'distillery',  420.0, 'Destilados GC SL'],
            ['2026-10-02', 'pomace', 'composting',  650.0, 'Compostería Agaete'],
        ] as [$date, $type, $dest, $qty, $destName]) {
            DB::table('harvest_byproducts')->insert(['viticulturist_id' => $uid, 'campaign_id' => $c26,
                'date' => $date, 'byproduct_type' => $type, 'quantity_kg' => $qty,
                'destination_type' => $dest, 'destination_name' => $destName,
                'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ── Exportaciones CUE Q2–Q4 2026 (3) ─────────────────────────────────
        if ($expId) {
            foreach ([
                [2026, 'quarterly', '2026-04-01', '2026-06-30', 'draft', null],
                [2026, 'quarterly', '2026-07-01', '2026-09-30', 'draft', null],
                [2026, 'quarterly', '2026-10-01', '2026-12-31', 'draft', null],
            ] as [$yr, $period, $from, $to, $status, $sentAt]) {
                DB::table('cue_exports')->insert(['exploitation_id' => $expId, 'viticulturist_id' => $uid,
                    'campaign_year' => $yr, 'period_type' => $period, 'from_date' => $from, 'to_date' => $to,
                    'status' => $status, 'sent_at' => null, 'accepted_at' => null,
                    'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // ── Informes oficiales 2026 adicionales (4) ───────────────────────────
        foreach ([
            ['full_digital_notebook',    '2026-01-01', '2026-03-31', 'completed'],
            ['phytosanitary_treatments', '2026-01-01', '2026-03-31', 'completed'],
            ['phytosanitary_treatments', '2026-04-01', '2026-06-30', 'processing'],
            ['full_digital_notebook',    '2026-04-01', '2026-06-30', 'processing'],
        ] as $i => [$type, $start, $end, $procStatus]) {
            $hash = md5("report_prod26_{$uid}_{$type}_{$start}_{$end}_{$i}");
            DB::table('official_reports')->insert(['user_id' => $uid, 'report_type' => $type,
                'period_start' => $start, 'period_end' => $end,
                'signature_hash' => $hash, 'signed_at' => $now, 'signed_ip' => '192.168.1.1',
                'verification_code' => substr(md5("verify26_{$hash}"), 0, 32), 'verification_count' => 0,
                'pdf_path' => $procStatus === 'completed' ? "reports/prod_{$uid}/{$type}_{$start}_{$end}.pdf" : null,
                'pdf_size' => $procStatus === 'completed' ? rand(120000, 850000) : null,
                'is_valid' => true, 'processing_status' => $procStatus,
                'completed_at' => $procStatus === 'completed' ? $now : null,
                'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 19. PAC ─────────────────────────────────────────────────────────────

    private function createPAC(array $plotIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach ([
            [$c24, '2024', 'approved', $plotIds], [$c25, '2025', 'approved', $plotIds], [$c26, '2026', 'draft', array_slice($plotIds, 0, 3)],
        ] as [$cId, $year, $status, $pids]) {
            $declId = DB::table('pac_declarations')->insertGetId(['viticulturist_id' => $uid, 'year' => (int)$year, 'status' => $status, 'total_declared_area' => 3.80, 'total_eligible_area' => 3.70, 'submitted_at' => $status === 'approved' ? "{$year}-04-20 10:00:00" : null, 'notes' => "PAC {$year}.", 'created_at' => $now, 'updated_at' => $now]);
            foreach (array_slice($pids, 0, 4) as $plotId) {
                DB::table('pac_declaration_items')->insert(['declaration_id' => $declId, 'plot_id' => $plotId, 'declared_area' => 0.95, 'eligible_area' => 0.92, 'eco_schemes' => json_encode(['ECO-01']), 'created_at' => $now, 'updated_at' => $now]);
            }
            if ($status === 'approved') {
                DB::table('pac_payments')->insert(['viticulturist_id' => $uid, 'declaration_id' => $declId, 'year' => (int)$year, 'payment_type' => 'basic_payment', 'amount' => 1850.00, 'payment_date' => "{$year}-12-10", 'reference' => "PAC-{$year}-PROD-{$uid}", 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    // ─── 20. Almacén insumos ──────────────────────────────────────────────────

    private function createAlmacen(array $productIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $warehouseId = DB::table('warehouses')->insertGetId(['user_id' => $uid, 'name' => 'Almacén Productor Agaete', 'location' => 'Calle Real, 8 — Agaete', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);

        // Suministros (5)
        $suppliesData = [
            ['Biohumus Sólido Producer', 'fertilizer', 'kg', 500.0, 320.0, 50.0],
            ['Nitrato Amónico 33.5% Producer', 'fertilizer', 'kg', 250.0, 180.0, 25.0],
            ['Sulfato Potásico Producer', 'fertilizer', 'kg', 200.0, 140.0, 20.0],
            ['Semilla Cubierta Vegetal Producer', 'seed', 'kg', 30.0, 18.0, 5.0],
            ['Cal Viva Producer', 'other', 'kg', 50.0, 30.0, 5.0],
        ];
        $supplyIds = [];
        foreach ($suppliesData as [$name, $type, $unit, $initial, $current, $minAlert]) {
            $supplyIds[] = DB::table('supplies')->insertGetId(['viticulturist_id' => $uid, 'warehouse_id' => $warehouseId, 'name' => $name, 'supply_type' => $type, 'unit_of_measurement' => $unit, 'initial_stock' => $initial, 'current_stock' => $current, 'min_stock_alert' => $minAlert, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Product stocks (5 fitosanitarios)
        foreach ($productIds as $i => $prodId) {
            $qty = 8.0 + $i * 2;
            $stockId = DB::table('product_stocks')->insertGetId(['warehouse_id' => $warehouseId, 'user_id' => $uid, 'product_id' => $prodId, 'quantity' => $qty, 'unit' => 'kg', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
            DB::table('product_stock_movements')->insert(['stock_id' => $stockId, 'user_id' => $uid, 'movement_type' => 'purchase', 'quantity_change' => $qty, 'quantity_before' => 0, 'quantity_after' => $qty, 'notes' => 'Compra inicio temporada.', 'created_at' => $now, 'updated_at' => $now]);
        }

        // Compras suministros (3)
        foreach ([
            [$supplyIds[0], $c24, '2024-02-10', 'FP2024-COOP-0021', 200.0, 'kg', 0.85, 'Agropiensos SA'],
            [$supplyIds[1], $c25, '2025-02-08', 'FP2025-COOP-0019', 130.0, 'kg', 0.44, 'Cooperativa Agaete'],
            [$supplyIds[3], $c26, '2026-02-15', 'FP2026-COOP-0012', 20.0,  'kg', 3.80, 'Cooperativa Agaete'],
        ] as [$supId, $cId, $date, $invNum, $qty, $unit, $price, $supplier]) {
            DB::table('supply_purchases')->insert(['supply_id' => $supId, 'viticulturist_id' => $uid, 'campaign_id' => $cId, 'invoice_date' => $date, 'invoice_number' => $invNum, 'quantity' => $qty, 'unit_of_measurement' => $unit, 'price_per_unit' => $price, 'total_cost' => round($qty * $price, 2), 'supplier_name' => $supplier, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 21. Facturación viticultor ──────────────────────────────────────────

    private function createBilling(array $plotIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid = self::PRODUCER_USER_ID;

        // Costes por Parcela (15)
        $plotCosts = [
            [$c24, $plotIds[0], 'labor', 'Jornales poda 2024', 1440.00, '2024-01-20'],
            [$c24, $plotIds[0], 'phytosanitary', 'Tratamientos campaña 2024', 580.00, '2024-04-15'],
            [$c24, $plotIds[1], 'fertilizer', 'Abono orgánico 2024', 420.00, '2024-03-10'],
            [$c24, $plotIds[2], 'water', 'Canon agua Q2 2024', 180.00, '2024-06-30'],
            [$c24, $plotIds[3], 'insurance', 'Prima seguro 2024', 1250.00, '2024-04-01'],
            [$c25, $plotIds[0], 'labor', 'Jornales poda 2025', 1620.00, '2025-01-15'],
            [$c25, $plotIds[1], 'phytosanitary', 'Tratamientos 2025', 610.00, '2025-04-22'],
            [$c25, $plotIds[2], 'fertilizer', 'Plan fertilización 2025', 470.00, '2025-03-08'],
            [$c25, $plotIds[3], 'machinery', 'Combustible campaña 2025', 410.00, '2025-09-01'],
            [$c25, $plotIds[0], 'subcontracting', 'Poda subcontratada', 1050.00, '2025-01-08'],
            [$c26, $plotIds[0], 'labor', 'Jornales poda 2026', 1800.00, '2026-01-10'],
            [$c26, $plotIds[1], 'subcontracting', 'Poda subcontratada 2026', 1100.00, '2026-01-18'],
            [$c26, $plotIds[2], 'fertilizer', 'Abono post-poda 2026', 490.00, '2026-02-15'],
            [$c26, $plotIds[3], 'insurance', 'Prima seguros 2026', 1450.00, '2026-04-01'],
            [$c26, $plotIds[0], 'other', 'Análisis suelo 2026', 180.00, '2026-03-15'],
        ];
        foreach ($plotCosts as [$cId, $pId, $cat, $desc, $amount, $date]) {
            DB::table('plot_costs')->insert(['viticulturist_id' => $uid, 'plot_id' => $pId, 'campaign_id' => $cId, 'category' => $cat, 'description' => $desc, 'amount' => $amount, 'cost_date' => $date, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Cosecha Comercializada (5)
        $actIds = DB::table('agricultural_activities')->where('viticulturist_id', $uid)->pluck('id');
        if ($actIds->isNotEmpty()) {
            $harvestRows = DB::table('harvests as h')
                ->join('agricultural_activities as a', 'h.activity_id', '=', 'a.id')
                ->whereIn('h.activity_id', $actIds)->orderBy('h.harvest_start_date')->limit(5)
                ->get(['h.id', 'h.harvest_start_date', 'h.total_weight', 'a.campaign_id']);
            foreach ($harvestRows as $i => $harvest) {
                $qty = min((float)$harvest->total_weight, 2000.0);
                DB::table('marketed_harvests')->insert(['harvest_id' => $harvest->id, 'campaign_id' => $harvest->campaign_id, 'viticulturist_id' => $uid, 'delivery_date' => $harvest->harvest_start_date, 'quantity_kg' => $qty, 'destination_type' => 'own_winery', 'buyer_name' => 'Bodega Propia Producer', 'price_per_kg' => '14.00', 'total_value' => round($qty * 14.00, 2), 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    // ─── 22. Datos operacionales ─────────────────────────────────────────────

    private function createOperationalData(array $plotIds, array $plantingIds, int $c24, int $c25, int $c26, $now): void
    {
        $uid = self::PRODUCER_USER_ID;

        // Documentos de Campaña (6)
        foreach ([
            [$c24, 'Certificado Ecológico 2024', 'certificate', 'docs/prod/2024/cert_eco_2024.pdf'],
            [$c24, 'Análisis Residuos 2024',     'lab_report',  'docs/prod/2024/analisis_residuos.pdf'],
            [$c25, 'Plan Fertilización 2025',    'other',       'docs/prod/2025/plan_fert_2025.pdf'],
            [$c25, 'Declaración Vendimia 2025',  'invoice',     'docs/prod/2025/declaracion_vendimia.pdf'],
            [$c26, 'Análisis Suelo 2026',        'lab_report',  'docs/prod/2026/analisis_suelo.pdf'],
            [$c26, 'Seguro Agrario 2026',        'authorization','docs/prod/2026/seguro_2026.pdf'],
        ] as [$cId, $name, $type, $path]) {
            DB::table('campaign_documents')->insert(['campaign_id' => $cId, 'viticulturist_id' => $uid, 'name' => $name, 'document_type' => $type, 'file_path' => $path, 'original_filename' => basename($path), 'mime_type' => 'application/pdf', 'file_size_kb' => rand(80, 2400), 'created_at' => $now, 'updated_at' => $now]);
        }

        // Entornos de Parcelas (4)
        foreach ([
            [$c25, $plotIds[0], $plantingIds[0], false, null, false, 50.0, 18.5, true],
            [$c25, $plotIds[1], $plantingIds[2], false, null, false, 30.0, 12.8, false],
            [$c25, $plotIds[2], $plantingIds[4], true,  80.0, true,  45.0, 22.1, true],
            [$c25, $plotIds[3], null,            false, null, false, 15.0, 8.4,  false],
        ] as [$cId, $pId, $ppId, $waterIntake, $waterDist, $protTotal, $slope, $erosionRisk, $hasErosion]) {
            DB::table('plot_environments')->insert(['campaign_id' => $cId, 'plot_id' => $pId, 'plot_planting_id' => $ppId, 'viticulturist_id' => $uid, 'water_intake_nearby' => $waterIntake, 'water_intake_distance_m' => $waterDist, 'protected_zone_total' => $protTotal, 'slope_pct' => $slope, 'erosion_risk' => $hasErosion, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 23. Salas de bodega ─────────────────────────────────────────────────

    private function createContainerRooms($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach ([
            ['Nave Elaboración Producer', 'Sala central de fermentación.', 20, 16.5, 72.0],
            ['Bodega Barricas Producer', 'Sala de crianza en barrica.', 15, 14.0, 80.0],
            ['Sala Embotellado Producer', 'Embotellado y almacén.', 10, 18.0, 65.0],
        ] as [$name, $desc, $cap, $temp, $hum]) {
            DB::table('container_rooms')->insert(['user_id' => $uid, 'name' => $name, 'description' => $desc, 'capacity' => $cap, 'temperature' => $temp, 'humidity' => $hum, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 24. Contenedores bodega ─────────────────────────────────────────────

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
        for ($i = 1; $i <= 15; $i++) {
            $containers[] = ['user_id' => $uid, 'name' => sprintf('Depósito PD%02d', $i), 'type_id' => $deposito, 'capacity' => [5000, 8000, 10000, 12000, 6000][$i % 5], 'description' => 'Nave elaboración', 'created_at' => $now, 'updated_at' => $now];
        }
        for ($i = 1; $i <= 10; $i++) {
            $containers[] = ['user_id' => $uid, 'name' => sprintf('Barrica PB%02d', $i), 'type_id' => $barrica, 'capacity' => 225, 'description' => 'Sala barricas', 'created_at' => $now, 'updated_at' => $now];
        }
        for ($i = 1; $i <= 5; $i++) {
            $containers[] = ['user_id' => $uid, 'name' => sprintf('Bins Vendimia PBV%02d', $i), 'type_id' => $polivalent, 'capacity' => 800, 'description' => 'Patio exterior', 'created_at' => $now, 'updated_at' => $now];
        }

        $ids = [];
        foreach ($containers as $c) {
            $ids[] = DB::table('containers')->insertGetId($c);
        }
        return $ids;
    }

    // ─── 25. Enólogos, Proveedores, Suministros bodega ──────────────────────

    private function createOenologists($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach ([
            ['Marcos', 'Rodríguez Santana', 'OEN-GC-P421', 'marcos.rod.producer@enologia.es', true, 'Enólogo jefe del productor.'],
            ['Elena', 'Castro Medina', 'OEN-GC-P389', 'elena.cast.producer@enologia.es', true, 'Responsable de calidad.'],
        ] as [$name, $surname, $lic, $email, $active, $notes]) {
            DB::table('oenologists')->insert(['user_id' => $uid, 'name' => $name, 'surname' => $surname, 'license_number' => $lic, 'email' => $email, 'active' => $active, 'notes' => $notes, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function createSuppliers($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach ([
            ['Botella & Corcho Producer SL', 'Pedro Jiménez', 'ventas@botellaycorcho-prod.es', 'B76543P11', 'packaging', true],
            ['Suministros Enológicos Producer', 'Laura Montes', 'pedidos@enologicos-prod.com', 'B22876P43', 'chemicals', true],
            ['Agrochem Producer', 'Rodrigo Acosta', 'info@agrochem-prod.es', 'B11234P67', 'chemicals', true],
        ] as [$name, $contact, $email, $vat, $cat, $active]) {
            DB::table('suppliers')->insert(['user_id' => $uid, 'name' => $name, 'contact_person' => $contact, 'email' => $email, 'vat_number' => $vat, 'category' => $cat, 'active' => $active, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function createWinerySupplies($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $kgId = DB::table('units_of_measurement')->where('symbol', 'kg')->value('id');
        $gId  = DB::table('units_of_measurement')->where('symbol', 'g')->value('id');
        $lId  = DB::table('units_of_measurement')->where('symbol', 'L')->value('id');

        foreach ([
            ['Metabisulfito de Potasio', 'Vinifloc K50', 'sulfiting', $kgId, 45.50, 10.0],
            ['Bentonita Sódica', 'Bentofine', 'fining', $kgId, 22.00, 5.0],
            ['Levadura Seca Activa', 'Lalvin EC-1118', 'yeast', $gId, 8500.00, 500.0],
            ['Ácido Tartárico', 'Acidex', 'acid', $kgId, 30.00, 5.0],
            ['Soda Cáustica', 'NaOH Bodega 30%', 'cleaning', $lId, 80.00, 20.0],
        ] as [$name, $cName, $type, $unitId, $stock, $minAlert]) {
            DB::table('winery_supplies')->insert(['user_id' => $uid, 'name' => $name, 'commercial_name' => $cName, 'supply_type' => $type, 'unit_of_measurement_id' => $unitId, 'current_stock' => $stock, 'min_stock_alert' => $minAlert, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 26. Vinos ───────────────────────────────────────────────────────────

    private function createWines($now): array
    {
        $uid = self::PRODUCER_USER_ID;
        $wines = [
            ['name' => 'Agaete Tinto Joven 2025',      'wine_type' => 'red',   'vintage' => 2025, 'variety' => 'Listán Negro 80%, Baboso Negro 20%', 'volume_liters' => 3200.0, 'notes' => 'DO Gran Canaria. Tinto joven frutal.'],
            ['name' => 'Agaete Tinto Crianza 2024',     'wine_type' => 'red',   'vintage' => 2024, 'variety' => 'Listán Negro 70%, Baboso Negro 30%', 'volume_liters' => 1800.0, 'notes' => 'DO Gran Canaria. 6 meses barrica.'],
            ['name' => 'Agaete Baboso de Autor 2024',   'wine_type' => 'red',   'vintage' => 2024, 'variety' => 'Baboso Negro 100%',                  'volume_liters' => 800.0,  'notes' => 'DO Gran Canaria. Vino de autor.'],
            ['name' => 'Agaete Blanco Marmajuelo 2025', 'wine_type' => 'white', 'vintage' => 2025, 'variety' => 'Marmajuelo 100%',                    'volume_liters' => 2400.0, 'notes' => 'DO Gran Canaria. Blanco fresco.'],
            ['name' => 'Agaete Vijariego 2025',         'wine_type' => 'white', 'vintage' => 2025, 'variety' => 'Vijariego 100%',                     'volume_liters' => 1600.0, 'notes' => 'DO Gran Canaria. Blanco varietal.'],
            ['name' => 'Agaete Rosado 2025',            'wine_type' => 'rose',  'vintage' => 2025, 'variety' => 'Listán Negro 100%',                  'volume_liters' => 1200.0, 'notes' => 'DO Gran Canaria. Rosado de sangrado.'],
            ['name' => 'Agaete Listán Blanco 2025',     'wine_type' => 'white', 'vintage' => 2025, 'variety' => 'Listán Blanco 100%',                 'volume_liters' => 900.0,  'notes' => 'DO Gran Canaria. Blanco tradicional.'],
            ['name' => 'Agaete Ecológico Tinto 2024',   'wine_type' => 'red',   'vintage' => 2024, 'variety' => 'Baboso Negro 60%, Listán Negro 40%', 'volume_liters' => 600.0,  'notes' => 'DO Gran Canaria. Vino ecológico.'],
        ];

        $ids = [];
        foreach ($wines as $w) {
            $ids[] = DB::table('wines')->insertGetId(array_merge($w, ['user_id' => $uid, 'status' => 'in_progress', 'created_at' => $now, 'updated_at' => $now]));
        }
        return $ids;
    }

    // ─── 27. Controles fermentación ───────────────────────────────────────────

    private function createFermentationControls(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $dates = ['2025-09-12', '2025-09-15', '2025-09-18', '2025-09-21', '2025-09-25'];
        foreach (array_slice($wineIds, 0, 4) as $wi => $wineId) {
            $containerId = $containerIds[$wi] ?? $containerIds[0];
            foreach ($dates as $di => $date) {
                DB::table('wine_fermentation_controls')->insert(['wine_id' => $wineId, 'container_id' => $containerId, 'control_date' => $date . ' 08:00:00', 'temperature' => round(18.0 + $di * 0.5 + $wi * 0.3, 2), 'density' => round(1.080 - $di * 0.020 - $wi * 0.005, 4), 'brix_degree' => round(22.0 - $di * 3.5, 2), 'ph' => round(3.3 + $di * 0.05, 2), 'volatile_acidity' => round(0.15 + $di * 0.03, 2), 'notes' => "Fermentación normal. Día " . ($di + 1) . ".", 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    // ─── 28. Traslados ───────────────────────────────────────────────────────

    private function createWineTransfers(array $wineIds, array $containerIds, $now): void
    {
        $uid    = self::PRODUCER_USER_ID;
        $unitId = DB::table('units_of_measurement')->where('symbol', 'L')->value('id') ?? 1;
        foreach (array_slice($wineIds, 0, 4) as $wi => $wineId) {
            $fromId = $containerIds[$wi] ?? $containerIds[0];
            $toId   = $containerIds[$wi + 15] ?? $containerIds[1];
            DB::table('wine_transfers')->insert(['wine_id' => $wineId, 'from_container_id' => $fromId, 'to_container_id' => $toId, 'transfer_date' => '2025-10-' . str_pad(5 + $wi * 3, 2, '0', STR_PAD_LEFT), 'quantity' => 800.0 + $wi * 200, 'unit_of_measurement_id' => $unitId, 'transfer_type' => 'racking', 'notes' => 'Traslado post-fermentación.', 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 28b. Mermas ──────────────────────────────────────────────────────────

    private function createWineLosses(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $lossTypes = ['evaporation', 'other', 'sampling', 'evaporation', 'other'];
        $unitId    = DB::table('units_of_measurement')->where('symbol', 'L')->value('id') ?? 1;
        foreach (array_slice($wineIds, 0, 5) as $wi => $wineId) {
            DB::table('wine_losses')->insert(['wine_id' => $wineId, 'container_id' => $containerIds[$wi] ?? $containerIds[0], 'loss_date' => '2025-11-' . str_pad(5 + $wi * 4, 2, '0', STR_PAD_LEFT), 'quantity' => 20.0 + $wi * 5, 'unit_of_measurement_id' => $unitId, 'loss_type' => $lossTypes[$wi], 'notes' => 'Merma registrada.', 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 29. Aditivos enológicos ──────────────────────────────────────────────

    private function createWineAdditives(array $wineIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $oenologistId = DB::table('oenologists')->where('user_id', $uid)->value('id');
        $supplyId     = DB::table('winery_supplies')->where('user_id', $uid)->value('id');
        $unitId       = DB::table('units_of_measurement')->where('symbol', 'g')->value('id');

        $catalog = [
            ['Metabisulfito de potasio (SO2)', 5.0], ['Bentonita', 80.0], ['Levaduras EC1118', 20.0],
            ['Nutrientes (DAP)', 15.0], ['Ácido tartárico', 50.0],
        ];

        foreach (array_slice($wineIds, 0, 5) as $i => $wineId) {
            $count = 2 + ($i % 3);
            for ($j = 0; $j < $count; $j++) {
                $a = $catalog[($i + $j) % count($catalog)];
                DB::table('wine_additives')->insert(['wine_id' => $wineId, 'additive_name' => $a[0], 'quantity' => $a[1], 'unit_of_measurement_id' => $unitId, 'application_date' => '2025-09-' . str_pad(15 + $j * 3, 2, '0', STR_PAD_LEFT), 'oenologist_id' => $oenologistId, 'winery_supply_id' => $supplyId, 'notes' => 'Dosis estándar.', 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    // ─── 30. Análisis de vino ─────────────────────────────────────────────────

    private function createWineAnalyses(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach (array_slice($wineIds, 0, 4) as $i => $wineId) {
            DB::table('wine_analyses')->insert([
                'wine_id' => $wineId, 'container_id' => $containerIds[$i] ?? $containerIds[0],
                'analysis_date' => '2025-12-' . str_pad(5 + $i * 5, 2, '0', STR_PAD_LEFT),
                'alcoholic_strength' => round(13.0 + $i * 0.3, 2), 'ph' => round(3.30 + $i * 0.05, 2),
                'total_acidity' => round(5.5 + $i * 0.2, 2), 'volatile_acidity' => round(0.25 + $i * 0.05, 2),
                'residual_sugar' => round(1.5 + $i * 0.3, 2), 'free_so2' => round(25.0 + $i * 2, 1),
                'total_so2' => round(60.0 + $i * 5, 1), 'density' => round(0.992 + $i * 0.001, 4),
                'laboratory' => 'Laboratorio Enológico Atlántico', 'result' => 'passed',
                'notes' => 'Análisis completo pre-embotellado.', 'created_by' => $uid,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 31. Embotellamientos + lotes + etiquetado ───────────────────────────

    private function createBottlings(array $wineIds, array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $containerCount = count($containerIds);
        $wineCount      = count($wineIds);

        $notesByType = [
            'tinto'    => ['Tinto joven primera tirada.', 'Tinto selección — embotellado manual.', 'Tinto barrica 6 meses.', 'Tinto crianza cosecha especial.'],
            'blanco'   => ['Blanco Marmajuelo fresco.', 'Blanco afrutado vendimia temprana.', 'Blanco fermentado en barrica.'],
            'rosado'   => ['Rosado primera tirada.', 'Rosado sangrado directo.', 'Rosado monovarietal.'],
            'espumoso' => ['Brut nature segunda fermentación.', 'Espumoso reserva 18 meses.'],
        ];
        $wineTypes  = ['tinto', 'tinto', 'tinto', 'blanco', 'blanco', 'rosado', 'espumoso', 'tinto'];
        $formats    = ['750', '750', '750', '500', '375', '1500'];

        // ── 2024: 12 embotellamientos ─────────────────────────────────────
        for ($i = 1; $i <= 12; $i++) {
            $wIdx    = ($i - 1) % $wineCount;
            $wType   = $wineTypes[$wIdx % count($wineTypes)];
            $notes   = $notesByType[$wType][($i - 1) % count($notesByType[$wType])];
            $month   = mt_rand(9, 12);
            $day     = mt_rand(1, 28);
            $date    = sprintf('2024-%02d-%02d', $month, $day);
            $bottles = mt_rand(800, 5000);
            $liters  = round($bottles * 0.75, 3);
            $lot     = 'PLOT-' . date('Ymd', strtotime($date)) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            DB::table('wine_bottlings')->insertGetId([
                'user_id' => $uid, 'wine_id' => $wineIds[$wIdx],
                'container_id' => $containerIds[$i % $containerCount],
                'bottling_date' => $date, 'bottle_format' => $formats[$i % count($formats)],
                'quantity_bottles' => $bottles, 'quantity_liters' => $liters,
                'lot_number' => $lot, 'notes' => $notes,
                'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('wine_lots')->insert([
                'user_id' => $uid, 'wine_id' => $wineIds[$wIdx],
                'name' => $lot, 'vintage' => 2024, 'wine_type' => $wType,
                'quantity' => $liters, 'initial_quantity' => $liters, 'available_quantity' => $liters,
                'unit' => 'litros', 'bottling_date' => $date, 'archived' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── 2025: 16 embotellamientos ─────────────────────────────────────
        for ($i = 1; $i <= 16; $i++) {
            $wIdx    = $i % $wineCount;
            $wType   = $wineTypes[$wIdx % count($wineTypes)];
            $notes   = $notesByType[$wType][($i - 1) % count($notesByType[$wType])];
            $month   = mt_rand(9, 12);
            $day     = mt_rand(1, 28);
            $date    = sprintf('2025-%02d-%02d', $month, $day);
            $bottles = mt_rand(1000, 6000);
            $liters  = round($bottles * 0.75, 3);
            $lot     = 'PLOT-' . date('Ymd', strtotime($date)) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            DB::table('wine_bottlings')->insertGetId([
                'user_id' => $uid, 'wine_id' => $wineIds[$wIdx],
                'container_id' => $containerIds[$i % $containerCount],
                'bottling_date' => $date, 'bottle_format' => $formats[$i % count($formats)],
                'quantity_bottles' => $bottles, 'quantity_liters' => $liters,
                'lot_number' => $lot, 'notes' => $notes,
                'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('wine_lots')->insert([
                'user_id' => $uid, 'wine_id' => $wineIds[$wIdx],
                'name' => $lot, 'vintage' => 2025, 'wine_type' => $wType,
                'quantity' => $liters, 'initial_quantity' => $liters, 'available_quantity' => $liters,
                'unit' => 'litros', 'bottling_date' => $date, 'archived' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── 2026: 22 embotellamientos ─────────────────────────────────────
        for ($i = 1; $i <= 22; $i++) {
            $wIdx    = ($i + 2) % $wineCount;
            $wType   = $wineTypes[$wIdx % count($wineTypes)];
            $notes   = $notesByType[$wType][($i - 1) % count($notesByType[$wType])];
            $month   = mt_rand(1, 4);
            $day     = mt_rand(1, 28);
            $date    = sprintf('2026-%02d-%02d', $month, $day);
            $bottles = mt_rand(1200, 7000);
            $liters  = round($bottles * 0.75, 3);
            $lot     = 'PLOT-' . date('Ymd', strtotime($date)) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            DB::table('wine_bottlings')->insertGetId([
                'user_id' => $uid, 'wine_id' => $wineIds[$wIdx],
                'container_id' => $containerIds[$i % $containerCount],
                'bottling_date' => $date, 'bottle_format' => $formats[$i % count($formats)],
                'quantity_bottles' => $bottles, 'quantity_liters' => $liters,
                'lot_number' => $lot, 'notes' => $notes,
                'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('wine_lots')->insert([
                'user_id' => $uid, 'wine_id' => $wineIds[$wIdx],
                'name' => $lot, 'vintage' => 2026, 'wine_type' => $wType,
                'quantity' => $liters, 'initial_quantity' => $liters, 'available_quantity' => $liters,
                'unit' => 'litros', 'bottling_date' => $date, 'archived' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function createLabelBatches(array $wineIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach (array_slice($wineIds, 0, 4) as $i => $wineId) {
            $total = mt_rand(3000, 6000);
            $used  = mt_rand((int)($total * 0.4), $total);
            $start = 2000000 + $i * 10000;
            DB::table('label_batches')->insert(['user_id' => $uid, 'wine_id' => $wineId, 'name' => "Etiqueta principal — Vino Producer {$i}", 'source' => 'own', 'start_number' => $start, 'end_number' => $start + $total - 1, 'total_quantity' => $total, 'used_quantity' => $used, 'wasted_quantity' => mt_rand(5, 30), 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function createLabelings($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $bottlings = DB::table('wine_bottlings')->where('user_id', $uid)->get(['id', 'wine_id', 'bottling_date', 'quantity_bottles']);
        $batches   = DB::table('label_batches')->where('user_id', $uid)->whereNotNull('wine_id')->get(['id', 'wine_id', 'start_number', 'used_quantity', 'total_quantity'])->keyBy('wine_id');

        foreach ($bottlings as $bottling) {
            if (!isset($batches[$bottling->wine_id])) continue;
            $batch = $batches[$bottling->wine_id];
            $qty   = min($bottling->quantity_bottles, $batch->total_quantity - $batch->used_quantity);
            if ($qty <= 0) continue;
            $fromNum = $batch->start_number + $batch->used_quantity;
            DB::table('wine_labelings')->insert(['user_id' => $uid, 'wine_id' => $bottling->wine_id, 'wine_bottling_id' => $bottling->id, 'label_batch_id' => $batch->id, 'labeling_date' => now()->parse($bottling->bottling_date)->addDays(mt_rand(3, 14))->format('Y-m-d'), 'quantity_labeled' => $qty, 'from_number' => $fromNum, 'to_number' => $fromNum + $qty - 1, 'notes' => 'Etiquetado semiautomático.', 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 32. Subproductos bodega + operaciones ──────────────────────────────

    private function createWineSubproducts(array $wineIds, $now): void
    {
        $uid    = self::PRODUCER_USER_ID;
        $unitId = DB::table('units_of_measurement')->where('symbol', 'kg')->value('id') ?? 1;
        foreach ([
            [$wineIds[0], 'pomace', 'distillery',       380.0, 'Destilados GC SL', 'Orujo escurrido.'],
            [$wineIds[1], 'lias',   'authorized_plant', 120.0, 'AGRO-COMPOST',     'Lías gruesas post-trasiego.'],
            [$wineIds[2], 'pomace', 'own_use',           80.0, null,                'Compostaje propio.'],
            [$wineIds[3], 'vinasse','authorized_plant', 200.0, 'Planta Residuos LP','Aguas de lavado.'],
        ] as [$wId, $type, $dest, $qty, $destName, $notes]) {
            DB::table('wine_subproducts')->insert(['user_id' => $uid, 'wine_id' => $wId, 'type' => $type, 'subproduct_date' => now()->subDays(mt_rand(10, 90))->format('Y-m-d'), 'quantity' => $qty, 'unit_of_measurement_id' => $unitId, 'destination' => $dest, 'destination_name' => $destName, 'lot_number' => 'SUB-PROD-' . str_pad(array_search($wId, $wineIds) + 1, 3, '0', STR_PAD_LEFT), 'notes' => $notes, 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function createCellarOperations(array $containerIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        foreach ([
            ['racking',       'Trasiego fermentación tinto joven', 120, 'completed'],
            ['clarification', 'Clarificación bentonita blanco',    90,  'completed'],
            ['filtration',    'Filtración pre-embotellado',         45,  'completed'],
            ['sulfiting',     'Sulfitado depósitos vacíos',         30,  'completed'],
            ['stabilization', 'Estabilización tartárica rosado',    15,  'planned'],
        ] as $i => [$type, $label, $days, $status]) {
            $src = $containerIds[$i % count($containerIds)];
            $dst = $containerIds[($i + 1) % count($containerIds)];
            DB::table('cellar_operations')->insert(['user_id' => $uid, 'operation_type' => $type, 'operation_date' => now()->subDays($days)->toDateString(), 'source_container_id' => $src !== $dst ? $src : null, 'target_container_id' => $src !== $dst ? $dst : null, 'volume_liters' => rand(500, 5000), 'responsible_person' => 'Enólogo Producer', 'status' => $status, 'notes' => $label, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 33. Mantenimiento contenedores ──────────────────────────────────────

    private function createContainerMaintenances(array $containerIds, $now): void
    {
        foreach (array_slice($containerIds, 0, 5) as $i => $containerId) {
            $types = [['cleaning', 'Limpieza con sosa cáustica', 120.0], ['sulfuring', 'Sulfitado preventivo', 50.0], ['inspection', 'Revisión juntas y válvulas', 80.0], ['tartrate_removal', 'Destartraje alcalino', 250.0], ['repair', 'Reparación válvula', 400.0]];
            $t = $types[$i % count($types)];
            DB::table('container_maintenances')->insert(['container_id' => $containerId, 'maintenance_type' => $t[0], 'maintenance_name' => $t[1], 'scheduled_date' => now()->subDays(30 + $i * 10)->format('Y-m-d'), 'performed_date' => now()->subDays(28 + $i * 10)->format('Y-m-d'), 'next_maintenance_date' => now()->addDays(150)->format('Y-m-d'), 'status' => 'completed', 'cost' => $t[2], 'performed_by' => 'Equipo bodega Producer', 'notes' => 'Mantenimiento programado.', 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 34. Clientes ─────────────────────────────────────────────────────────

    private function createClients($now): void
    {
        $uid = self::PRODUCER_USER_ID;

        // ── Empresas (250) ────────────────────────────────────────────────────
        $companyPrefixes = [
            'Restaurantes', 'Distribuidora', 'Exportadora', 'Bodegas', 'Viñedos',
            'Cooperativa', 'Comercial', 'Grupo', 'Inversiones', 'Importadora',
            'Alimentación', 'Hostelería', 'Enoteca', 'Gourmet', 'Agrícola',
            'Vinicultora', 'Denominación', 'Productos', 'Servicios', 'Logística',
        ];
        $companyNouns = [
            'Canarios', 'Atlántico', 'Gran Canaria', 'Isleña', 'del Sur',
            'Palmera', 'Norteña', 'Insular', 'del Vino', 'Vinícola',
            'de Agaete', 'del Norte', 'Macaronesia', 'Tropical', 'Lanzaroteña',
            'Gomera', 'Hierro', 'Tenerife', 'Fuerteventura', 'La Palma',
        ];
        $companySuffixes = ['SL', 'SLU', 'SA', 'CB', 'SC', 'SLU', 'SAU', 'SLL', 'SL', 'SA'];
        $companyActivities = [
            'restauracion', 'distribucion', 'exportacion', 'importacion', 'horeca',
            'enoteca', 'logistica', 'comercial', 'agricola', 'hosteleria',
        ];

        $rows = [];
        for ($i = 1; $i <= 250; $i++) {
            $prefix   = $companyPrefixes[($i - 1) % count($companyPrefixes)];
            $noun     = $companyNouns[(int)(($i - 1) / count($companyPrefixes)) % count($companyNouns)];
            $suffix   = $companySuffixes[$i % count($companySuffixes)];
            $activity = $companyActivities[$i % count($companyActivities)];
            $name     = "{$prefix} {$noun} {$suffix}";
            $docNum   = 'B35' . str_pad(200000 + $i, 6, '0', STR_PAD_LEFT);
            $domain   = strtolower(str_replace([' ', 'á','é','í','ó','ú','ñ'], ['-','a','e','i','o','u','n'], "{$prefix}-{$noun}"));
            $rows[] = [
                'user_id'          => $uid,
                'client_type'      => 'company',
                'company_name'     => $name,
                'company_document' => $docNum,
                'email'            => "pedidos{$i}@{$domain}-gc.es",
                'phone'            => '928' . str_pad(200000 + $i, 6, '0', STR_PAD_LEFT),
                'active'           => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        // ── Particulares (200) ────────────────────────────────────────────────
        $firstNames = [
            'Pedro','Laura','Miguel','Carmen','Antonio','Isabel','José','María',
            'Francisco','Ana','Manuel','Rosa','Juan','Lucía','Carlos','Elena',
            'Rafael','Sofía','Fernando','Marta','Diego','Valentina','Pablo','Nuria',
            'Álvaro','Patricia','Sergio','Cristina','David','Raquel',
        ];
        $lastNames = [
            'Suárez','Álvarez','Falcón','González','Hernández','Rodríguez','Martín',
            'Pérez','Torres','Cabrera','Domínguez','Reyes','Santana','Vega','Cruz',
            'Medina','Delgado','Ortega','Mora','Navarro','Gutiérrez','Jiménez','Ruiz',
            'Díaz','Vargas','Ramos','Romero','Flores','León','Castro',
        ];
        $nifLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        for ($i = 1; $i <= 200; $i++) {
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName1 = $lastNames[($i - 1) % count($lastNames)];
            $lastName2 = $lastNames[$i % count($lastNames)];
            $nifNum    = 45200000 + $i;
            $nifLetter = $nifLetters[$nifNum % 23];
            $emailSlug = strtolower(str_replace(['á','é','í','ó','ú','ñ','ü'], ['a','e','i','o','u','n','u'], "{$firstName}.{$lastName1}{$i}"));
            $rows[] = [
                'user_id'             => $uid,
                'client_type'         => 'individual',
                'first_name'          => $firstName,
                'last_name'           => "{$lastName1} {$lastName2}",
                'particular_document' => $nifNum . $nifLetter,
                'email'               => $i % 5 === 0 ? null : "{$emailSlug}@gmail.com",
                'phone'               => '629' . str_pad(200000 + $i, 6, '0', STR_PAD_LEFT),
                'active'              => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        // Insertar en bloques de 50
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('clients')->insert($chunk);
        }
    }

    // ─── 35. Facturas mixtas ──────────────────────────────────────────────────

    private function createInvoices(int $c24, int $c25, $now): void
    {
        $uid     = self::PRODUCER_USER_ID;
        $clients = DB::table('clients')->where('user_id', $uid)->pluck('id');
        if ($clients->isEmpty()) return;
        $clientCount = $clients->count();

        $wineDescs = [
            'Venta tinto joven', 'Venta blanco Marmajuelo', 'Venta rosado selección',
            'Venta crianza barrica', 'Venta listán blanco', 'Venta malvasía seco',
            'Venta espumoso brut', 'Venta tinto selección', 'Venta blanco afrutado',
            'Venta tinto reserva',
        ];
        $harvestDescs = [
            'Venta cosecha uva tinto', 'Venta cosecha uva blanco', 'Venta cosecha uva rosado',
            'Venta uva listán negro', 'Venta uva moscatel', 'Venta uva malvasía',
        ];
        $statuses        = ['paid', 'paid', 'paid', 'sent', 'draft'];
        $paymentStatuses = ['paid', 'paid', 'paid', 'unpaid', 'overdue'];

        // ── 2024: 75 facturas ──────────────────────────────────────────────
        $counter2024 = 1;
        for ($i = 1; $i <= 75; $i++) {
            $isHarvest = $i > 60;
            $month     = $isHarvest ? mt_rand(10, 11) : mt_rand(1, 12);
            $day       = mt_rand(1, 28);
            $date      = sprintf('2024-%02d-%02d', $month, $day);
            $total     = round(mt_rand(800, 8500) + mt_rand(0, 99) / 100, 2);
            $desc      = $isHarvest
                ? ($harvestDescs[($i - 1) % count($harvestDescs)] . ' 2024')
                : ($wineDescs[($i - 1) % count($wineDescs)] . ' 2024');
            $type      = $isHarvest ? 'harvest_sale' : 'wine_sale';
            $clientId  = $clients[($i - 1) % $clientCount];
            $status    = $statuses[$i % count($statuses)];
            $invNum    = 'FP-2024-' . str_pad($counter2024++, 4, '0', STR_PAD_LEFT);
            $invId = DB::table('invoices')->insertGetId([
                'user_id' => $uid, 'client_id' => $clientId,
                'invoice_number' => $invNum, 'invoice_date' => $date,
                'status' => $status, 'payment_status' => $paymentStatuses[$i % count($paymentStatuses)],
                'subtotal' => $total, 'tax_base' => $total, 'total_amount' => $total,
                'observations' => $desc, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('invoice_items')->insert([
                'invoice_id' => $invId, 'name' => $desc, 'description' => $desc,
                'quantity' => 1, 'unit' => 'partida',
                'unit_price' => $total, 'subtotal' => $total, 'total' => $total,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── 2025: 75 facturas ──────────────────────────────────────────────
        $counter2025 = 1;
        for ($i = 1; $i <= 75; $i++) {
            $isHarvest = $i > 60;
            $month     = $isHarvest ? mt_rand(10, 11) : mt_rand(1, 12);
            $day       = mt_rand(1, 28);
            $date      = sprintf('2025-%02d-%02d', $month, $day);
            $total     = round(mt_rand(900, 9500) + mt_rand(0, 99) / 100, 2);
            $desc      = $isHarvest
                ? ($harvestDescs[($i - 1) % count($harvestDescs)] . ' 2025')
                : ($wineDescs[($i - 1) % count($wineDescs)] . ' 2025');
            $type      = $isHarvest ? 'harvest_sale' : 'wine_sale';
            $clientId  = $clients[$i % $clientCount];
            $status    = $statuses[$i % count($statuses)];
            $invNum    = 'FP-2025-' . str_pad($counter2025++, 4, '0', STR_PAD_LEFT);
            $invId = DB::table('invoices')->insertGetId([
                'user_id' => $uid, 'client_id' => $clientId,
                'invoice_number' => $invNum, 'invoice_date' => $date,
                'status' => $status, 'payment_status' => $paymentStatuses[$i % count($paymentStatuses)],
                'subtotal' => $total, 'tax_base' => $total, 'total_amount' => $total,
                'observations' => $desc, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('invoice_items')->insert([
                'invoice_id' => $invId, 'name' => $desc, 'description' => $desc,
                'quantity' => 1, 'unit' => 'partida',
                'unit_price' => $total, 'subtotal' => $total, 'total' => $total,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    // ─── 36. Notas de cata ────────────────────────────────────────────────────

    private function createTastingNotes(array $wineIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $colors = [
            'rojo cereza brillante', 'rojo rubí con ribete granate', 'rojo granate profundo',
            'amarillo pajizo con reflejos verdosos', 'amarillo pálido brillante', 'salmón rosáceo intenso',
            'rojo violáceo intenso', 'dorado luminoso', 'rosa frambuesa vivo',
            'granate oscuro con borde teja', 'amarillo oro pálido', 'rojo cereza picota',
        ];
        $aromas = [
            'Frutas rojas frescas, frambuesa, violeta.', 'Frutas negras maduras, especias, clavo.',
            'Mora, ciruela, tabaco, vainilla tostada.', 'Flor blanca, cítricos, hierbas frescas.',
            'Flores blancas, fruta tropical, coco.', 'Fresas, frambuesa, pétalos de rosa.',
            'Fruta roja madura, pimienta, laurel.', 'Miel, fruta confitada, azafrán.',
            'Pomelo, lima, hierbabuena.', 'Cuero, tabaco, trufa, frutos secos.',
            'Melocotón, albaricoque, vainilla suave.', 'Pera, manzana golden, brioche.',
        ];
        $palates = [
            'Fresco, taninos suaves. Final frutal.', 'Estructurado, taninos maduros, largo.',
            'Complejo, untuoso, postgusto muy largo.', 'Fresco, mineral, acidez viva y elegante.',
            'Ligero, fresco, delicado y equilibrado.', 'Carnoso, frutal, final cremoso.',
            'Potente, denso, tánico y persistente.', 'Dulce, untuoso, equilibrado por acidez.',
            'Sabroso, vibrante, final cítrico.', 'Voluminoso, especiado, retronasal largo.',
            'Suave, goloso, fácil de beber.', 'Elegante, fino, final mineral.',
        ];
        $evaluators = [
            'Productor Agaete', 'Enólogo Jefe', 'Panel de Cata Interno',
            'Sumiller Invitado', 'Equipo Técnico Bodega',
        ];
        $wineCount = count($wineIds);

        // ~28 notas por vino × 16 vinos = ~450 notas en total
        for ($i = 1; $i <= 450; $i++) {
            $wineId   = $wineIds[($i - 1) % $wineCount];
            $year     = [2024, 2025, 2026][($i - 1) % 3];
            $month    = mt_rand(1, 12);
            $day      = mt_rand(1, 28);
            $score    = mt_rand(82, 98);
            DB::table('wine_tasting_notes')->insert([
                'user_id'            => $uid,
                'wine_id'            => $wineId,
                'evaluation_date'    => sprintf('%d-%02d-%02d', $year, $month, $day),
                'evaluator_name'     => $evaluators[($i - 1) % count($evaluators)],
                'visual_color'       => $colors[($i - 1) % count($colors)],
                'aroma_descriptors'  => $aromas[($i - 1) % count($aromas)],
                'overall_conclusion' => $palates[($i - 1) % count($palates)],
                'overall_score'      => $score,
                'created_by'         => $uid,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }

    // ─── 37. Compliance bodega ────────────────────────────────────────────────

    private function createWineryCompliance(array $wineIds, $now): void
    {
        $uid = self::PRODUCER_USER_ID;

        // Eco-certificaciones (3)
        foreach ([
            ['Certificación Ecológica Viñas Producer', 'organic', 'CAAE', 'CAAE-PROD-GC-2023-004821', '2023-01-01', '2026-12-31', 'active'],
            ['Sello Calidad DO Gran Canaria Producer', 'denomination', 'Consejo Regulador DOP GC', 'DO-PROD-GC-2022-00089', '2022-09-01', '2027-08-31', 'active'],
            ['Certificación Vegan-Friendly Producer', 'vegan', 'The Vegan Society', 'TVS-PROD-2024-8821', '2024-01-01', '2024-12-31', 'expired'],
        ] as [$name, $type, $body, $num, $from, $until, $status]) {
            DB::table('eco_certifications')->insert(['user_id' => $uid, 'name' => $name, 'certification_type' => $type, 'certifying_body' => $body, 'certificate_number' => $num, 'valid_from' => $from, 'valid_until' => $until, 'status' => $status, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Registros sanitarios (2)
        foreach ([
            ['RGSA 35.P02318/C', 'elaborador', 'Elaboración y embotellado de vinos', '2018-03-15', '2028-03-15', 'Consejería de Sanidad Canarias', 'active'],
            ['RAE-GC-PROD-00156', 'almacenista', 'Almacenamiento y comercialización vinos', '2019-06-01', '2025-06-01', 'ACCA', 'active'],
        ] as [$regNum, $regType, $desc, $regDate, $renewal, $auth, $status]) {
            DB::table('sanitary_registrations')->insert(['user_id' => $uid, 'registration_number' => $regNum, 'registration_type' => $regType, 'activity_description' => $desc, 'registration_date' => $regDate, 'renewal_date' => $renewal, 'issuing_authority' => $auth, 'status' => $status, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Autorizaciones embotellado (2)
        $wines = DB::table('wines')->where('user_id', $uid)->whereIn('status', ['in_progress'])->limit(2)->get(['id', 'vintage', 'volume_liters']);
        foreach ($wines as $idx => $wine) {
            $authVol = round(($wine->volume_liters ?? 3000) * 1.05, 3);
            DB::table('bottling_authorizations')->insert(['user_id' => $uid, 'authorization_number' => 'AE-PROD-GC-' . $wine->vintage . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT), 'authorization_type' => 'embotellado', 'wine_id' => $wine->id, 'authorized_volume_liters' => $authVol, 'valid_from' => now()->subMonths(6)->format('Y-m-d'), 'valid_until' => now()->addMonths(6)->format('Y-m-d'), 'issuing_authority' => 'Consejo Regulador DO Gran Canaria', 'status' => 'active', 'conditions' => 'Formato autorizado: 750ml.', 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    // ─── 38. Documentos bodega + alertas ─────────────────────────────────────

    private function createWineryDocuments($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $docTypes   = ['license', 'sanitary', 'plan', 'declaration', 'contract', 'certificate', 'permit', 'report', 'audit', 'registration'];
        $docTitles  = [
            'Licencia de Actividad', 'Registro Sanitario', 'Plan APPCC', 'Declaración Cosecha',
            'Contrato Suministro', 'Certificado Ecológico', 'Permiso Vertidos', 'Informe Inspección',
            'Auditoría Interna', 'Registro DO Gran Canaria',
        ];
        $authorities = [
            'Ayuntamiento de Agaete', 'AESAN', null, 'Consejería Agricultura Canarias',
            'Consejo Regulador DO GC', 'CAAE', 'ACCA', 'Cabildo de Gran Canaria', null, 'Ministerio Agricultura',
        ];
        $years = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];

        for ($i = 1; $i <= 450; $i++) {
            $typeIdx = ($i - 1) % count($docTypes);
            $year    = $years[($i - 1) % count($years)];
            $expYear = $year + mt_rand(1, 10);
            $month   = mt_rand(1, 12);
            $day     = mt_rand(1, 28);
            DB::table('winery_documents')->insert([
                'user_id'          => $uid,
                'title'            => $docTitles[$typeIdx] . " {$year} — Ref. {$i}",
                'document_type'    => $docTypes[$typeIdx],
                'reference_number' => strtoupper(substr($docTypes[$typeIdx], 0, 4)) . "-PROD-GC-{$year}-" . str_pad($i, 5, '0', STR_PAD_LEFT),
                'issue_date'       => sprintf('%d-%02d-%02d', $year, $month, $day),
                'expiry_date'      => $i % 4 === 0 ? null : sprintf('%d-%02d-%02d', $expYear, $month, $day),
                'issuing_authority'=> $authorities[$typeIdx],
                'active'           => $i % 10 !== 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    private function createWineryAlerts($now): void
    {
        $uid = self::PRODUCER_USER_ID;
        $alertTypes = ['maintenance', 'expiry', 'fermentation', 'stock', 'certification', 'temperature', 'quality', 'compliance', 'delivery', 'analysis'];
        $severities = ['info', 'warning', 'critical', 'warning'];
        $titleTemplates = [
            'Mantenimiento pendiente — Contenedor {n}',
            'Certificación expira en {n} días',
            'Temperatura fermentación elevada — Depósito D{n}',
            'Stock bajo — Producto #{n}',
            'Renovación autorización #{n} requerida',
            'Temperatura bodega fuera de rango — Sala {n}',
            'Control calidad pendiente — Lote #{n}',
            'Cumplimiento normativo — Acción #{n}',
            'Recepción pendiente — Batch #{n}',
            'Resultado análisis disponible — Muestra #{n}',
        ];
        $messages = [
            'Revisar y programar intervención.',
            'Iniciar trámite de renovación inmediatamente.',
            'Activar sistema de frío. Control urgente.',
            'Solicitar pedido al proveedor.',
            'Documentar y actualizar registro oficial.',
            'Ajustar climatización de la sala.',
            'Programar cata de control interno.',
            'Revisar documentación y actualizar.',
            'Confirmar fecha y cantidad con proveedor.',
            'Revisar valores y comparar con histórico.',
        ];

        for ($i = 1; $i <= 450; $i++) {
            $tIdx    = ($i - 1) % count($alertTypes);
            $title   = str_replace('{n}', $i, $titleTemplates[$tIdx]);
            $isRead  = $i % 3 === 0;
            DB::table('winery_alerts')->insert([
                'user_id'      => $uid,
                'alert_type'   => $alertTypes[$tIdx],
                'severity'     => $severities[$i % count($severities)],
                'title'        => $title,
                'message'      => $messages[$tIdx],
                'is_read'      => $isRead,
                'read_at'      => $isRead ? now()->subDays(mt_rand(1, 30))->toDateTimeString() : null,
                'auto_generated' => true,
                'triggered_at' => now()->subDays(mt_rand(1, 365))->toDateTimeString(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    // ─── 39. Plan de trabajos 2026 ────────────────────────────────────────────

    private function createPlannedWorks(array $plotIds, int $campaignId, $now): void
    {
        $uid = self::PRODUCER_USER_ID;

        // 8 plots: $plotIds[0..7]
        $p = $plotIds;

        $works = [
            // ── Podas ──────────────────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'poda',         'pri' => 'alta',    'sta' => 'completada', 'title' => 'Poda de invierno — Parcela El Lomo',           'desc' => 'Poda en vaso. Carga ajustada 8-10 yemas/cepa.', 'start' => '2026-01-12', 'end' => '2026-01-13', 'comp' => '2026-01-13', 'notes' => 'Completada según plan.'],
            ['plot' => 1, 'cat' => 'poda',         'pri' => 'alta',    'sta' => 'completada', 'title' => 'Poda de invierno — Parcela Las Tinajas',        'desc' => 'Tijera neumática. 6h trabajo.', 'start' => '2026-01-20', 'end' => '2026-01-21', 'comp' => '2026-01-21', 'notes' => null],
            ['plot' => 2, 'cat' => 'poda',         'pri' => 'alta',    'sta' => 'completada', 'title' => 'Poda de invierno — Parcela Caidero Norte',      'desc' => 'Poda mixta Guyot.', 'start' => '2026-01-26', 'end' => '2026-01-27', 'comp' => '2026-01-27', 'notes' => null],
            // ── Labores culturales ─────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'labor_cultural', 'pri' => 'media', 'sta' => 'completada', 'title' => 'Laboreo suelo — 1ª pasada primavera',           'desc' => 'Control de cubierta vegetal y aireación.', 'start' => '2026-03-10', 'end' => '2026-03-10', 'comp' => '2026-03-10', 'notes' => null],
            ['plot' => 3, 'cat' => 'labor_cultural', 'pri' => 'media', 'sta' => 'completada', 'title' => 'Despunte y deshojado — Parcela Bandama',        'desc' => 'Mejora ventilación racimos. Reducir presión fúngica.', 'start' => '2026-06-15', 'end' => '2026-06-16', 'comp' => '2026-06-16', 'notes' => null],
            ['plot' => 4, 'cat' => 'labor_cultural', 'pri' => 'baja',  'sta' => 'completada', 'title' => 'Aclareo de racimos — Parcela Tenteniguada',     'desc' => 'Reducción carga. Mejorar calidad uva.', 'start' => '2026-07-08', 'end' => '2026-07-08', 'comp' => '2026-07-08', 'notes' => null],
            ['plot' => 5, 'cat' => 'labor_cultural', 'pri' => 'media', 'sta' => 'completada', 'title' => 'Laboreo otoñal — Parcela Montaña Alta',         'desc' => 'Aireación y preparación para abono.', 'start' => '2026-10-20', 'end' => '2026-10-20', 'comp' => '2026-10-20', 'notes' => null],
            // ── Tratamientos ──────────────────────────────────────────────────
            ['plot' => null, 'cat' => 'tratamiento', 'pri' => 'alta',   'sta' => 'completada', 'title' => 'Preventivo mildiu — 1ª aplicación',            'desc' => 'Cobre + azufre WG. Todas las parcelas.', 'start' => '2026-04-05', 'end' => '2026-04-05', 'comp' => '2026-04-05', 'notes' => 'Condiciones climáticas óptimas.'],
            ['plot' => null, 'cat' => 'tratamiento', 'pri' => 'alta',   'sta' => 'completada', 'title' => 'Tratamiento oídio — 2ª aplicación',             'desc' => 'Azufre mojable 80%. Temperatura < 28°C.', 'start' => '2026-05-18', 'end' => '2026-05-18', 'comp' => '2026-05-19', 'notes' => null],
            ['plot' => null, 'cat' => 'tratamiento', 'pri' => 'urgente','sta' => 'completada', 'title' => 'Tratamiento Botrytis preventivo',               'desc' => 'Cierre de racimos. Fenhexamid.', 'start' => '2026-07-25', 'end' => '2026-07-25', 'comp' => '2026-07-26', 'notes' => 'Aplicado un día después por lluvia.'],
            ['plot' => null, 'cat' => 'tratamiento', 'pri' => 'alta',   'sta' => 'completada', 'title' => 'Tratamiento post-vendimia heridas poda verde',  'desc' => 'Pasta fungicida en cortes. Prevención excoriosis.', 'start' => '2026-10-05', 'end' => '2026-10-06', 'comp' => '2026-10-06', 'notes' => null],
            // ── Fertilizaciones ──────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'fertilizacion', 'pri' => 'alta',   'sta' => 'completada', 'title' => 'Abonado de fondo NPK — Parcela El Lomo',        'desc' => 'NPK 8-15-15, 300 kg/ha. Incorporado suelo.', 'start' => '2026-03-18', 'end' => '2026-03-18', 'comp' => '2026-03-18', 'notes' => null],
            ['plot' => 1, 'cat' => 'fertilizacion', 'pri' => 'media',  'sta' => 'completada', 'title' => 'Fertirrigación nitrogenada — verano',           'desc' => 'Nitrato amónico diluido en riego goteo.', 'start' => '2026-06-28', 'end' => '2026-06-28', 'comp' => '2026-06-28', 'notes' => null],
            ['plot' => 2, 'cat' => 'fertilizacion', 'pri' => 'media',  'sta' => 'completada', 'title' => 'Corrección potásica — premaduración',           'desc' => 'Sulfato potásico, 150 kg/ha. Envero.', 'start' => '2026-08-05', 'end' => '2026-08-05', 'comp' => '2026-08-05', 'notes' => null],
            // ── Riegos ────────────────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'riego',        'pri' => 'media',   'sta' => 'completada', 'title' => 'Riego post-brotación — parcelas goteo',         'desc' => 'Dotación 800L. Reposición hídrica.', 'start' => '2026-05-02', 'end' => '2026-05-02', 'comp' => '2026-05-02', 'notes' => null],
            ['plot' => 3, 'cat' => 'riego',        'pri' => 'alta',    'sta' => 'completada', 'title' => 'Riego de apoyo verano — Bandama',               'desc' => 'Periodo seco. 2×800L semana.', 'start' => '2026-07-10', 'end' => '2026-07-10', 'comp' => '2026-07-10', 'notes' => null],
            ['plot' => 4, 'cat' => 'riego',        'pri' => 'alta',    'sta' => 'completada', 'title' => 'Riego premaduración — Tenteniguada',            'desc' => 'Cese riego 3 semanas antes vendimia.', 'start' => '2026-08-25', 'end' => '2026-08-25', 'comp' => '2026-08-25', 'notes' => null],
            // ── Vendimia ──────────────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'vendimia',     'pri' => 'urgente', 'sta' => 'completada', 'title' => 'Vendimia — El Lomo (Listán Negro)',             'desc' => 'Recolección manual. Control brix > 23°.', 'start' => '2026-09-15', 'end' => '2026-09-15', 'comp' => '2026-09-15', 'notes' => '1.050 kg recogidos.'],
            ['plot' => 1, 'cat' => 'vendimia',     'pri' => 'urgente', 'sta' => 'completada', 'title' => 'Vendimia — Las Tinajas (Marmajuelo)',           'desc' => 'Maduración tecnológica 22-23°Brix.', 'start' => '2026-09-17', 'end' => '2026-09-17', 'comp' => '2026-09-17', 'notes' => null],
            ['plot' => 6, 'cat' => 'vendimia',     'pri' => 'urgente', 'sta' => 'completada', 'title' => 'Vendimia — Parcela Sur (Listán Blanco)',        'desc' => 'Selección racimos. Brix objetivo 24°.', 'start' => '2026-09-25', 'end' => '2026-09-25', 'comp' => '2026-09-25', 'notes' => '1.200 kg. Excelente cosecha.'],
            // ── Observaciones ─────────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'observacion',  'pri' => 'media',   'sta' => 'completada', 'title' => 'Control estado sanitario — junio',              'desc' => 'Revisión síntomas mildiu/oídio pre-tratamiento.', 'start' => '2026-06-01', 'end' => '2026-06-01', 'comp' => '2026-06-01', 'notes' => 'Sin incidencias.'],
            ['plot' => 2, 'cat' => 'observacion',  'pri' => 'media',   'sta' => 'completada', 'title' => 'Control madurez — control brix agosto',         'desc' => 'Seguimiento semanal grados brix hasta vendimia.', 'start' => '2026-08-12', 'end' => '2026-08-12', 'comp' => '2026-08-12', 'notes' => '22.0°Brix. Previsión vendimia 18 sep.'],
            // ── Post-vendimia ─────────────────────────────────────────────────
            ['plot' => 0, 'cat' => 'post_vendimia','pri' => 'media',   'sta' => 'completada', 'title' => 'Tratamiento post-vendimia — madera',            'desc' => 'Aplicación fungicida heridas poda verde.', 'start' => '2026-10-08', 'end' => '2026-10-08', 'comp' => '2026-10-08', 'notes' => null],
            // ── Pendientes ────────────────────────────────────────────────────
            ['plot' => null,'cat' => 'labor_cultural','pri' => 'media', 'sta' => 'pendiente', 'title' => 'Laboreo otoñal y enmienda cálcica',              'desc' => 'Ajuste pH suelo. Cal agrícola 200 kg/ha.', 'start' => '2026-11-15', 'end' => '2026-11-16', 'comp' => null, 'notes' => null],
            ['plot' => 0, 'cat' => 'poda',         'pri' => 'alta',    'sta' => 'pendiente', 'title' => 'Poda invierno 2026/27 — El Lomo',                'desc' => 'Poda en vaso. Carga adaptada campaña anterior.', 'start' => '2027-01-10', 'end' => '2027-01-12', 'comp' => null, 'notes' => null],
            ['plot' => 1, 'cat' => 'poda',         'pri' => 'alta',    'sta' => 'pendiente', 'title' => 'Poda invierno 2026/27 — Las Tinajas',            'desc' => 'Revisar estado sarmientos. Reducir carga si necesario.', 'start' => '2027-01-18', 'end' => '2027-01-20', 'comp' => null, 'notes' => null],
            ['plot' => 2, 'cat' => 'fertilizacion','pri' => 'media',   'sta' => 'pendiente', 'title' => 'Abonado de fondo 2027 — Caidero Norte',          'desc' => 'NPK 8-15-15 según análisis suelo.', 'start' => '2027-03-10', 'end' => '2027-03-10', 'comp' => null, 'notes' => 'Pendiente análisis foliares.'],
        ];

        foreach ($works as $w) {
            DB::table('planned_works')->insert([
                'viticulturist_id' => $uid,
                'campaign_id'      => $campaignId,
                'plot_id'          => $w['plot'] !== null ? ($plotIds[$w['plot']] ?? null) : null,
                'category'         => $w['cat'],
                'title'            => $w['title'],
                'description'      => $w['desc'],
                'planned_date'     => $w['start'],
                'planned_end_date' => $w['end'],
                'priority'         => $w['pri'],
                'status'           => $w['sta'],
                'notes'            => $w['notes'],
                'completed_at'     => $w['comp'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── Trabajos generados (hasta ~450 total) ────────────────────────────
        $categories  = ['poda', 'labor_cultural', 'tratamiento', 'fertilizacion', 'riego', 'observacion', 'vendimia', 'post_vendimia'];
        $priorities  = ['baja', 'media', 'alta', 'urgente'];
        $statuses    = ['completada', 'completada', 'completada', 'pendiente', 'en_curso'];
        $catTitles   = [
            'poda'           => ['Poda de invierno', 'Poda de formación', 'Poda en verde', 'Poda Guyot'],
            'labor_cultural' => ['Laboreo primavera', 'Deshojado', 'Aclareo racimos', 'Laboreo otoñal', 'Despunte'],
            'tratamiento'    => ['Preventivo mildiu', 'Tratamiento oídio', 'Tratamiento botrytis', 'Aplicación insecticida'],
            'fertilizacion'  => ['Abonado NPK fondo', 'Fertirrigación nitrogenada', 'Corrección potásica', 'Enmienda orgánica'],
            'riego'          => ['Riego post-brotación', 'Riego apoyo verano', 'Riego premaduración', 'Riego establecimiento'],
            'observacion'    => ['Control sanitario', 'Control madurez', 'Inspección general', 'Toma muestras'],
            'vendimia'       => ['Vendimia manual', 'Vendimia mecánica', 'Selección racimos'],
            'post_vendimia'  => ['Tratamiento heridas', 'Aplicación fungicida', 'Control post-cosecha'],
        ];
        $plotCount = count($plotIds);
        $generated = count($works);

        for ($i = 1; $generated < 450; $i++, $generated++) {
            $catIdx  = ($i - 1) % count($categories);
            $cat     = $categories[$catIdx];
            $titles  = $catTitles[$cat];
            $title   = $titles[($i - 1) % count($titles)] . " — Parcela #{$i}";
            $month   = mt_rand(1, 12);
            $day     = mt_rand(1, 28);
            $sta     = $statuses[$i % count($statuses)];
            $date    = sprintf('2026-%02d-%02d', $month, $day);
            DB::table('planned_works')->insert([
                'viticulturist_id' => $uid,
                'campaign_id'      => $campaignId,
                'plot_id'          => $plotIds[$i % $plotCount],
                'category'         => $cat,
                'title'            => $title,
                'description'      => "Trabajo programado campaña 2026. {$title}.",
                'planned_date'     => $date,
                'planned_end_date' => $date,
                'priority'         => $priorities[$i % count($priorities)],
                'status'           => $sta,
                'notes'            => null,
                'completed_at'     => $sta === 'completada' ? $date : null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    // ─── 40. Recepciones bodega 2026 ─────────────────────────────────────────

    private function createWineryReceptions2026(array $plantingIds, int $campaignId, $now): void
    {
        $uid  = self::PRODUCER_USER_ID;
        $data = [
            // [date, planting_idx, weight, brix, baume, ph, acidity, notes]
            ['2026-09-15', 0, 1050.0, 23.5, 12.9, 3.38, 5.6, 'Recepción Listán Negro. Excelente estado.'],
            ['2026-09-17', 1,  870.0, 22.8, 12.5, 3.35, 5.9, 'Recepción Marmajuelo. Aromático.'],
            ['2026-09-18', 2,  720.0, 22.0, 12.1, 3.32, 6.1, 'Recepción Moscatel. Concentrado.'],
            ['2026-09-20', 3, 1100.0, 23.8, 13.1, 3.40, 5.5, 'Recepción Listán Negro parcela Bandama.'],
            ['2026-09-22', 4,  680.0, 22.5, 12.4, 3.33, 5.8, 'Recepción Vijariego. Bajo rend., calidad alta.'],
            ['2026-09-23', 5,  610.0, 21.8, 12.0, 3.30, 6.2, 'Recepción Tintilla. Parcela joven.'],
            ['2026-09-25', 6, 1200.0, 24.0, 13.2, 3.42, 5.4, 'Recepción Listán Blanco. Gran cosecha.'],
            ['2026-09-26', 7,  980.0, 23.2, 12.8, 3.36, 5.7, 'Recepción Malvasía Aromática.'],
        ];

        $plantingCount = count($plantingIds);
        $varietyNames  = ['Listán Negro', 'Marmajuelo', 'Moscatel', 'Listán Negro Bandama', 'Vijariego', 'Tintilla', 'Listán Blanco', 'Malvasía Aromática'];
        $healthStates  = ['sano', 'sano', 'sano', 'sano', 'optimo', 'bueno', 'sano'];
        $prices        = [0.68, 0.70, 0.65, 0.72, 0.62, 0.68, 0.70, 0.68];

        // ── 8 recepciones base + 442 generadas = 450 total ─────────────────
        $allData = $data;
        for ($i = count($data) + 1; $i <= 450; $i++) {
            $pIdx    = ($i - 1) % $plantingCount;
            $dayOff  = ($i % 45) + 1;
            $month   = $dayOff <= 30 ? 9 : 10;
            $day     = $dayOff <= 30 ? $dayOff : ($dayOff - 30);
            $weight  = round(mt_rand(300, 1400) + mt_rand(0, 9) / 10, 1);
            $brix    = round(21.5 + ($pIdx * 0.3) + mt_rand(-5, 5) / 10, 1);
            $baume   = round($brix * 0.55, 1);
            $ph      = round(3.28 + mt_rand(0, 15) / 100, 2);
            $acidity = round(5.3 + mt_rand(0, 12) / 10, 1);
            $variety = $varietyNames[$pIdx % count($varietyNames)];
            $allData[] = [sprintf('2026-%02d-%02d', $month, min($day, 28)), $pIdx, $weight, $brix, $baume, $ph, $acidity, "Recepción {$variety}. Batch #{$i}."];
        }

        foreach ($allData as [$date, $idx, $weight, $brix, $baume, $ph, $acidity, $notes]) {
            $batchId = DB::table('grape_reception_batches')->insertGetId([
                'winery_id'             => $uid,
                'viticulturist_id'      => $uid,
                'plot_planting_id'      => $plantingIds[$idx % $plantingCount],
                'campaign_id'           => $campaignId,
                'vintage_year'          => 2026,
                'total_weight_kg'       => $weight,
                'designation_of_origin' => 'DO Gran Canaria',
                'status'                => 'closed',
                'notes'                 => $notes,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);

            $price = $prices[$idx % count($prices)];
            DB::table('harvests')->insert([
                'winery_id'          => $uid,
                'batch_id'           => $batchId,
                'plot_planting_id'   => $plantingIds[$idx % $plantingCount],
                'harvest_start_date' => $date,
                'total_weight'       => $weight,
                'brix_degree'        => $brix,
                'baume_degree'       => $baume,
                'ph_level'           => $ph,
                'acidity_level'      => $acidity,
                'price_per_kg'       => $price,
                'yield_per_hectare'  => round($weight / 0.5, 1),
                'total_value'        => round($weight * $price, 2),
                'status'             => 'active',
                'health_status'      => $healthStates[$idx % count($healthStates)],
                'notes'              => $notes,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }

    // ─── 41. Vinos cosecha 2026 ───────────────────────────────────────────────

    private function createWines2026($now): array
    {
        $uid   = self::PRODUCER_USER_ID;
        $wines = [
            ['name' => 'Agaete Tinto Joven 2026',     'wine_type' => 'red',   'vintage' => 2026, 'variety' => 'Listán Negro 80%, Tintilla 20%',    'volume_liters' => 680.0,  'notes' => 'DO GC 2026. Tinto frutal joven.'],
            ['name' => 'Agaete Tinto Selección 2026',  'wine_type' => 'red',   'vintage' => 2026, 'variety' => 'Listán Negro 70%, Baboso 30%',      'volume_liters' => 715.0,  'notes' => 'DO GC 2026. Selección parcela Bandama.'],
            ['name' => 'Agaete Marmajuelo 2026',       'wine_type' => 'white', 'vintage' => 2026, 'variety' => 'Marmajuelo 100%',                   'volume_liters' => 565.0,  'notes' => 'DO GC 2026. Blanco varietal floral.'],
            ['name' => 'Agaete Listán Blanco 2026',    'wine_type' => 'white', 'vintage' => 2026, 'variety' => 'Listán Blanco 100%',                'volume_liters' => 780.0,  'notes' => 'DO GC 2026. Gran cosecha 2026.'],
            ['name' => 'Agaete Moscatel Natural 2026', 'wine_type' => 'white', 'vintage' => 2026, 'variety' => 'Moscatel 100%',                     'volume_liters' => 468.0,  'notes' => 'DO GC 2026. Dulce natural. Alta concentración.'],
            ['name' => 'Agaete Malvasía 2026',         'wine_type' => 'white', 'vintage' => 2026, 'variety' => 'Malvasía Aromática 100%',           'volume_liters' => 637.0,  'notes' => 'DO GC 2026. Perfume floral atlántico.'],
            ['name' => 'Agaete Vijariego 2026',        'wine_type' => 'white', 'vintage' => 2026, 'variety' => 'Vijariego Blanco 100%',             'volume_liters' => 442.0,  'notes' => 'DO GC 2026. Varietal atlántico seco.'],
            ['name' => 'Agaete Rosado Tintilla 2026',  'wine_type' => 'rose',  'vintage' => 2026, 'variety' => 'Tintilla 100%',                     'volume_liters' => 396.0,  'notes' => 'DO GC 2026. Rosado de lágrima. Sangrado corto.'],
        ];
        $ids = [];
        foreach ($wines as $w) {
            $ids[] = DB::table('wines')->insertGetId(array_merge($w, ['user_id' => $uid, 'status' => 'in_progress', 'created_at' => $now, 'updated_at' => $now]));
        }
        return $ids;
    }

    // ─── 42. Lotes producto vintage 2026 ─────────────────────────────────────

    private function createProductLots2026(array $wineIds, $now): void
    {
        $uid      = self::PRODUCER_USER_ID;
        $wineData = [
            // [wine_type, price_litro, price_75, price_375, price_caja]
            ['tinto',  2.80, 8.50, 5.00, 48.45],
            ['tinto',  3.20, 9.80, 5.90, 55.86],
            ['blanco', 3.00, 9.00, 5.50, 51.30],
            ['blanco', 2.90, 8.80, 5.20, 50.16],
            ['blanco', 4.50, 14.0, 8.00, 79.80],
            ['blanco', 3.80, 12.0, 6.50, 68.40],
            ['blanco', 3.50, 11.0, 6.20, 62.70],
            ['rosado', 3.20, 10.0, 5.80, 57.00],
        ];

        foreach ($wineIds as $i => $wineId) {
            [$wtype, $pL, $p75, $p375, $pCaja] = $wineData[$i] ?? $wineData[0];

            // Lot 1 — Granel (litros)
            $q = 500.0; $s = 160.0;
            DB::table('wine_lots')->insert(['user_id' => $uid, 'wine_id' => $wineId, 'name' => "Granel 2026 — Lot " . ($i + 1), 'vintage' => 2026, 'wine_type' => $wtype, 'quantity' => $q, 'initial_quantity' => $q, 'sold_quantity' => $s, 'available_quantity' => $q - $s, 'reserved_quantity' => 0, 'price_per_unit' => $pL, 'unit' => 'litros', 'alcohol' => 13.0, 'residual_sugar' => 2.5, 'total_acidity' => 5.8, 'archived' => false, 'created_at' => $now, 'updated_at' => $now]);

            // Lot 2 — Botella 75cl
            $q = 600.0; $s = 210.0; $r = 30.0;
            DB::table('wine_lots')->insert(['user_id' => $uid, 'wine_id' => $wineId, 'name' => "Botella 75cl 2026 — Ref " . ($i + 1), 'vintage' => 2026, 'wine_type' => $wtype, 'quantity' => $q, 'initial_quantity' => $q, 'sold_quantity' => $s, 'available_quantity' => $q - $s - $r, 'reserved_quantity' => $r, 'price_per_unit' => $p75, 'unit' => 'botellas', 'bottle_format' => '75cl', 'units_per_case' => 6, 'alcohol' => 13.0, 'archived' => false, 'created_at' => $now, 'updated_at' => $now]);

            // Lot 3 — Botella 37.5cl
            $q = 300.0; $s = 90.0;
            DB::table('wine_lots')->insert(['user_id' => $uid, 'wine_id' => $wineId, 'name' => "Botella 37.5cl 2026 — Ref " . ($i + 1), 'vintage' => 2026, 'wine_type' => $wtype, 'quantity' => $q, 'initial_quantity' => $q, 'sold_quantity' => $s, 'available_quantity' => $q - $s, 'reserved_quantity' => 0, 'price_per_unit' => $p375, 'unit' => 'botellas', 'bottle_format' => '37.5cl', 'units_per_case' => 12, 'alcohol' => 13.0, 'archived' => false, 'created_at' => $now, 'updated_at' => $now]);

            // Lot 4 — Caja 6 botellas 75cl
            $q = 100.0; $s = 38.0;
            DB::table('wine_lots')->insert(['user_id' => $uid, 'wine_id' => $wineId, 'name' => "Caja 6×75cl 2026 — Ref " . ($i + 1), 'vintage' => 2026, 'wine_type' => $wtype, 'quantity' => $q, 'initial_quantity' => $q, 'sold_quantity' => $s, 'available_quantity' => $q - $s, 'reserved_quantity' => 0, 'price_per_unit' => $pCaja, 'unit' => 'cajas', 'units_per_case' => 6, 'alcohol' => 13.0, 'archived' => false, 'created_at' => $now, 'updated_at' => $now]);

            // Lot 5 — Magnum 1.5L
            $q = 120.0; $s = 32.0;
            DB::table('wine_lots')->insert(['user_id' => $uid, 'wine_id' => $wineId, 'name' => "Magnum 1.5L 2026 — Ref " . ($i + 1), 'vintage' => 2026, 'wine_type' => $wtype, 'quantity' => $q, 'initial_quantity' => $q, 'sold_quantity' => $s, 'available_quantity' => $q - $s, 'reserved_quantity' => 0, 'price_per_unit' => round($p75 * 2 * 1.12, 2), 'unit' => 'botellas', 'bottle_format' => '1.5L', 'units_per_case' => 3, 'alcohol' => 13.0, 'archived' => false, 'created_at' => $now, 'updated_at' => $now]);

            // Lotes adicionales — tiradas mensuales (enero–diciembre × variedades formato)
            $formatVariants = [
                ['botellas', '75cl',  6,  $p75,       mt_rand(400, 900)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(200, 600)],
                ['litros',   null,    null, $pL,       mt_rand(200, 800)],
                ['cajas',    null,    6,  $pCaja,      mt_rand(50, 200)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(100, 350)],
                ['botellas', '1.5L',  3,  round($p75 * 2.2, 2), mt_rand(50, 150)],
                ['botellas', '75cl',  6,  round($p75 * 1.05, 2), mt_rand(150, 500)],
                ['cajas',    null,    12, round($pCaja * 0.9, 2), mt_rand(40, 180)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(300, 700)],
                ['litros',   null,    null, $pL,       mt_rand(100, 400)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(80, 250)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(200, 600)],
                ['cajas',    null,    6,  $pCaja,      mt_rand(30, 120)],
                ['botellas', '1.5L',  3,  round($p75 * 2.1, 2), mt_rand(40, 130)],
                ['litros',   null,    null, $pL,       mt_rand(150, 500)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(250, 650)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(90, 280)],
                ['cajas',    null,    6,  $pCaja,      mt_rand(50, 160)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(180, 550)],
                ['litros',   null,    null, $pL,       mt_rand(120, 380)],
                ['botellas', '75cl',  6,  round($p75 * 1.08, 2), mt_rand(200, 600)],
                ['botellas', '1.5L',  3,  round($p75 * 2.15, 2), mt_rand(35, 100)],
                ['cajas',    null,    12, $pCaja,      mt_rand(60, 200)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(70, 220)],
                ['litros',   null,    null, $pL,       mt_rand(200, 600)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(300, 800)],
                ['cajas',    null,    6,  $pCaja,      mt_rand(40, 150)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(150, 450)],
                ['litros',   null,    null, $pL,       mt_rand(80, 300)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(60, 200)],
                ['botellas', '1.5L',  3,  round($p75 * 2.18, 2), mt_rand(30, 90)],
                ['cajas',    null,    6,  round($pCaja * 1.05, 2), mt_rand(50, 180)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(200, 700)],
                ['litros',   null,    null, $pL,       mt_rand(100, 350)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(80, 250)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(250, 650)],
                ['cajas',    null,    6,  $pCaja,      mt_rand(35, 130)],
                ['botellas', '1.5L',  3,  round($p75 * 2.12, 2), mt_rand(40, 110)],
                ['litros',   null,    null, $pL,       mt_rand(150, 450)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(180, 520)],
                ['cajas',    null,    12, round($pCaja * 0.95, 2), mt_rand(50, 180)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(75, 230)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(280, 750)],
                ['litros',   null,    null, $pL,       mt_rand(120, 400)],
                ['botellas', '1.5L',  3,  round($p75 * 2.2, 2),  mt_rand(30, 95)],
                ['cajas',    null,    6,  $pCaja,      mt_rand(45, 160)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(200, 600)],
                ['botellas', '37.5cl',12, $p375,      mt_rand(65, 200)],
                ['litros',   null,    null, $pL,       mt_rand(100, 300)],
                ['botellas', '75cl',  6,  $p75,       mt_rand(250, 700)],
                ['cajas',    null,    6,  round($pCaja * 1.02, 2), mt_rand(40, 140)],
                ['botellas', '1.5L',  3,  round($p75 * 2.08, 2), mt_rand(35, 105)],
            ];

            foreach ($formatVariants as $fv => [$unit, $format, $upc, $price, $qty]) {
                $sold = (int)($qty * mt_rand(30, 75) / 100);
                $rsv  = (int)(($qty - $sold) * mt_rand(0, 20) / 100);
                $lotName = "Ref {$wtype} 2026 — Vino " . ($i + 1) . " Lote " . ($fv + 6);
                $row = [
                    'user_id'           => $uid,
                    'wine_id'           => $wineId,
                    'name'              => $lotName,
                    'vintage'           => 2026,
                    'wine_type'         => $wtype,
                    'quantity'          => (float)$qty,
                    'initial_quantity'  => (float)$qty,
                    'sold_quantity'     => (float)$sold,
                    'available_quantity'=> (float)max(0, $qty - $sold - $rsv),
                    'reserved_quantity' => (float)$rsv,
                    'price_per_unit'    => $price,
                    'unit'              => $unit,
                    'alcohol'           => round(12.5 + ($fv % 4) * 0.25, 1),
                    'archived'          => false,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
                if ($format)  $row['bottle_format']  = $format;
                if ($upc)     $row['units_per_case'] = $upc;
                DB::table('wine_lots')->insert($row);
            }
        }
    }

    // ─── 43. Análisis de suelo ────────────────────────────────────────────────

    private function createSoilAnalyses(array $plotIds, int $c2024, int $c2025, int $c2026, $now): void
    {
        $uid      = self::PRODUCER_USER_ID;
        $labs     = ['SGS Servicios Analíticos', 'Laboratorio Agroalimentario Las Palmas', 'CIFA Canarias'];
        $textures = ['franco-arcilloso', 'franco', 'franco-limoso', 'arcilloso-limoso', 'franco-arenoso'];
        $campaigns = [$c2024 => 2024, $c2025 => 2025, $c2026 => 2026];

        // 150 parcelas × 3 campañas = 450 análisis
        foreach (array_slice($plotIds, 0, 150) as $pi => $plotId) {
            foreach ($campaigns as $cId => $year) {
                $day = str_pad(($pi % 20) + 1, 2, '0', STR_PAD_LEFT);
                DB::table('soil_analyses')->insert([
                    'viticulturist_id'        => $uid,
                    'plot_id'                 => $plotId,
                    'campaign_id'             => $cId,
                    'analysis_date'           => "{$year}-02-{$day}",
                    'laboratory'              => $labs[$pi % 3],
                    'sample_depth_cm'         => [30, 40, 50][$pi % 3],
                    'ph'                      => round(5.8 + ($pi % 15) * 0.08, 1),
                    'organic_matter'          => round(1.8 + ($pi % 20) * 0.12, 2),
                    'nitrogen_total'          => round(0.10 + ($pi % 25) * 0.008, 3),
                    'phosphorus'              => round(12 + ($pi % 30) * 2.0, 1),
                    'potassium'               => round(160 + ($pi % 40) * 15, 1),
                    'calcium'                 => round(1100 + ($pi % 50) * 60, 1),
                    'magnesium'               => round(75 + ($pi % 30) * 6, 1),
                    'texture_class'           => $textures[$pi % 5],
                    'electrical_conductivity' => round(0.15 + ($pi % 25) * 0.015, 2),
                    'limestone'               => round(6 + ($pi % 20) * 1.2, 1),
                    'notes'                   => "Análisis campaña {$year}. Parcela #{$pi}.",
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);
            }
        }
    }

    // ─── 44. Registros biodiversidad ─────────────────────────────────────────

    private function createBiodiversityRecords(array $plotIds, int $campaignId, $now): void
    {
        $uid   = self::PRODUCER_USER_ID;
        $types = [
            'flora'           => ['Presencia de plantas aromáticas nativas: romero, tomillo, lavanda silvestre.', 'Rosmarinus officinalis, Thymus vulgaris, Lavandula canariensis'],
            'fauna'           => ['Avistamiento cernícalo vulgar. Especie indicadora de ecosistema saludable.', 'Falco tinnunculus, Columba livia, Sylvia melanocephala'],
            'cubierta_vegetal' => ['Cubierta vegetal espontánea con gramíneas y leguminosas. BCAM 9 PAC.', 'Lolium perenne, Trifolium repens, Medicago sativa'],
        ];
        $months = [4, 6, 8];

        // 150 parcelas × 3 tipos = 450 registros
        foreach (array_slice($plotIds, 0, 150) as $pi => $plotId) {
            foreach (array_values($types) as $ti => [$desc, $species]) {
                $rtype = array_keys($types)[$ti];
                $day   = str_pad(($pi % 18) + 1, 2, '0', STR_PAD_LEFT);
                DB::table('biodiversity_records')->insert([
                    'viticulturist_id' => $uid,
                    'plot_id'          => $plotId,
                    'campaign_id'      => $campaignId,
                    'record_type'      => $rtype,
                    'description'      => $desc,
                    'area_m2'          => round(80 + ($pi % 50) * 12, 1),
                    'species'          => $species,
                    'record_date'      => '2026-0' . $months[$ti] . '-' . $day,
                    'notes'            => "Parcela #{$pi}. Biodiversidad favorable.",
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }
    }

    // ─── 45. Alertas fitosanitarias ──────────────────────────────────────────

    private function createPhytosanitaryAlerts($now): void
    {
        $uid    = self::PRODUCER_USER_ID;
        $alerts = [
            ['Alerta Mildiu — condiciones favorables infección', 'ESTACION_FITOPATOLOGICA', 'mildiu', 'alta', 'Gran Canaria Norte', 'Temperatura 18-22°C con HR >90%. Favorable para Plasmopara viticola.', 'Aplicar fungicida cúprico preventivo en parcelas de ladera.', '2026-04-02', '2026-05-15', true],
            ['Alerta Oídio — presión moderada-alta', 'SERVICIO_SANIDAD_VEGETAL', 'oidio', 'media', 'Valle de Agaete', 'Condiciones cálidas y secas propicias para Erysiphe necator.', 'Azufre micronizado en días sin lluvia.', '2026-05-10', '2026-06-20', true],
            ['Polilla del Racimo — vuelo activo', 'ESTACION_FITOPATOLOGICA', 'polilla', 'media', 'Gran Canaria', 'Capturas superan umbral 200 adultos/trampa/semana.', 'Bacillus thuringiensis en ecológico.', '2026-05-25', '2026-06-30', true],
            ['Alerta Botrytis — humedad post-lluvia', 'SERVICIO_SANIDAD_VEGETAL', 'botrytis', 'alta', 'Gran Canaria Noreste', 'Período húmedo 3+ días. Riesgo botritis en cierre racimos.', 'Fenhexamid antes del cierre de racimos.', '2026-07-20', '2026-08-15', true],
            ['Araña Roja — calor extremo verano', 'ESTACION_FITOPATOLOGICA', 'arana_roja', 'media', 'Medianías Sur GC', 'Temp. >35°C sostenidas favorecen Panonychus ulmi.', 'Revisar envés hojas semanalmente. Acaricida si >5/hoja.', '2026-07-01', '2026-08-31', false],
            ['Excoriosis activa tras pedrisco', 'ESTACION_FITOPATOLOGICA', 'excoriosis', 'alta', 'Zona Interior GC', 'Daños mecánicos granizo. Alto riesgo Phomopsis viticola.', 'Pasta fungicida en heridas. Revisar sarmientos.', '2026-04-18', '2026-05-20', false],
            ['Aviso helada tardía — abril', 'AEMET_CANARIAS', 'helada', 'urgente', 'Altitudes >700m GC', 'Descenso térmico puntual. Brotación avanzada en riesgo.', 'Velas antihelada o riego aspersión nocturno.', '2026-04-12', '2026-04-14', false],
            ['Flavescencia Dorada — vigilancia preventiva', 'DGPIF_CANARIAS', 'flavescencia', 'baja', 'Gran Canaria', 'Sin detección oficial. Vigilancia obligatoria.', 'Inspeccionar síntomas: hojas en cucurucho, racimos secos.', '2026-03-01', '2026-11-30', true],
            ['Cicadela — vector FD activo', 'SERVICIO_SANIDAD_VEGETAL', 'cicadela', 'media', 'Viñedos costeros', 'Capturas Scaphoideus titanus crecientes en red de trampas.', 'Insecticida autorizado en período vegetativo.', '2026-06-01', '2026-07-31', true],
            ['Mildiu tardío — lluvias otoñales', 'ESTACION_FITOPATOLOGICA', 'mildiu', 'media', 'Gran Canaria Norte', 'Lluvias sept. Riesgo proteger cosecha tardía.', 'Preventivo en parcelas vendimia prevista >20 sep.', '2026-09-01', '2026-09-25', false],
            ['Podredumbre ácida — ataque avispa', 'ESTACION_FITOPATOLOGICA', 'podredumbre_acida', 'alta', 'Valle de Agaete', 'Daños avispa aumentan infección levaduras ácidas.', 'Control plagas + inspección diaria cosecha tardía.', '2026-08-10', '2026-09-20', false],
            ['Sequía severa — estrés hídrico crítico', 'CLAVERIE_CANARIAS', 'sequia', 'media', 'Medianías GC', 'Déficit hídrico >60% sobre media. Riesgo fitosanitario secundario.', 'Aumentar riego disponible. Priorizar parcelas jóvenes.', '2026-07-15', '2026-08-31', true],
        ];

        foreach ($alerts as [$title, $source, $type, $severity, $area, $desc, $recs, $date, $expiry, $active]) {
            DB::table('phytosanitary_alerts')->insert([
                'viticulturist_id' => $uid,
                'title'            => $title,
                'source'           => $source,
                'alert_type'       => $type,
                'severity'         => $severity,
                'affected_area'    => $area,
                'description'      => $desc,
                'recommendations'  => $recs,
                'alert_date'       => $date,
                'expiry_date'      => $expiry,
                'active'           => $active,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── Alertas generadas (hasta 450 total) ──────────────────────────────
        $genTypes      = ['mildiu', 'oidio', 'botrytis', 'polilla', 'arana_roja', 'excoriosis', 'helada', 'sequia', 'cicadela', 'podredumbre_acida'];
        $genSeverities = ['baja', 'media', 'alta', 'urgente'];
        $genSources    = ['ESTACION_FITOPATOLOGICA', 'SERVICIO_SANIDAD_VEGETAL', 'AEMET_CANARIAS', 'CLAVERIE_CANARIAS', 'DGPIF_CANARIAS'];
        $genAreas      = ['Gran Canaria Norte', 'Valle de Agaete', 'Medianías GC', 'Zona Interior GC', 'Gran Canaria'];
        $generated     = count($alerts);

        for ($i = $generated + 1; $generated < 450; $i++, $generated++) {
            $tIdx     = ($i - 1) % count($genTypes);
            $type     = $genTypes[$tIdx];
            $year     = [2024, 2025, 2026][($i - 1) % 3];
            $month    = mt_rand(3, 11);
            $day      = mt_rand(1, 28);
            $date     = sprintf('%d-%02d-%02d', $year, $month, $day);
            $expMonth = $month + mt_rand(1, 2);
            $expYear  = $expMonth > 12 ? $year + 1 : $year;
            $expMonth = $expMonth > 12 ? $expMonth - 12 : $expMonth;
            DB::table('phytosanitary_alerts')->insert([
                'viticulturist_id' => $uid,
                'title'            => "Alerta {$type} #{$i} — condiciones favorables campaña {$year}",
                'source'           => $genSources[$i % count($genSources)],
                'alert_type'       => $type,
                'severity'         => $genSeverities[$i % count($genSeverities)],
                'affected_area'    => $genAreas[$i % count($genAreas)],
                'description'      => "Condiciones propicias para {$type}. Campaña {$year}. Monitorizar parcelas.",
                'recommendations'  => "Aplicar tratamiento preventivo. Revisar umbrales en parcelas afectadas.",
                'alert_date'       => $date,
                'expiry_date'      => sprintf('%d-%02d-%02d', $expYear, $expMonth, min($day + 10, 28)),
                'active'           => $i % 4 !== 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    // ─── 46. Previsiones de cosecha ───────────────────────────────────────────

    private function createYieldForecasts(array $plantingIds, int $c2024, int $c2025, int $c2026, $now): void
    {
        $uid         = self::PRODUCER_USER_ID;
        $baseKg      = [850, 900, 780, 1050, 720, 650, 1100, 950];
        $campaigns   = [
            $c2024 => ['year' => 2024, 'mult' => 0.88, 'date' => '2024-07-15', 'status' => 'confirmed'],
            $c2025 => ['year' => 2025, 'mult' => 0.95, 'date' => '2025-07-20', 'status' => 'confirmed'],
            $c2026 => ['year' => 2026, 'mult' => 1.00, 'date' => '2026-07-18', 'status' => 'confirmed'],
        ];

        // 8 plantaciones × 3 campañas × 19 estimaciones mensuales = 456 previsiones
        $estimationMonths = [3, 4, 5, 6, 7, 8, 9];   // meses de estimación en campaña
        $estimationTypes  = ['early', 'mid', 'final']; // tipo de estimación

        foreach ($campaigns as $cId => $c) {
            foreach (array_slice($plantingIds, 0, 8) as $pi => $plantingId) {
                $base = $baseKg[$pi] * $c['mult'];
                // ~19 estimaciones por combinación planting×campaign distribuidas en meses + tipos
                for ($em = 0; $em < 19; $em++) {
                    $month    = $estimationMonths[$em % count($estimationMonths)];
                    $day      = mt_rand(1, 28);
                    $variance = 0.85 + ($em % 7) * 0.05; // de 0.85 a 1.15 conforme avanza temporada
                    $estKg    = round($base * $variance + mt_rand(-50, 50), 1);
                    DB::table('winery_yield_forecasts')->insert([
                        'winery_id'        => $uid,
                        'viticulturist_id' => $uid,
                        'plot_planting_id' => $plantingId,
                        'campaign_id'      => $cId,
                        'vintage_year'     => $c['year'],
                        'estimated_kg'     => max(100, $estKg),
                        'estimation_date'  => sprintf('%d-%02d-%02d', $c['year'], $month, $day),
                        'status'           => $c['status'],
                        'notes'            => "Previsión {$estimationTypes[$em % 3]} — mes {$month} campaña {$c['year']}.",
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
            }
        }
    }

    // ─── 47. Entregas + cosechas comercializadas 2026 ────────────────────────

    private function createHarvestDeliveries(array $plantingIds, int $campaignId, $now): void
    {
        $uid  = self::PRODUCER_USER_ID;
        $data = [
            ['2026-09-15', 0, 1050.0, 0.68, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-001'],
            ['2026-09-17', 1,  870.0, 0.68, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-002'],
            ['2026-09-18', 2,  720.0, 0.65, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-003'],
            ['2026-09-20', 3, 1100.0, 0.70, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-004'],
            ['2026-09-22', 4,  680.0, 0.65, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-005'],
            ['2026-09-23', 5,  610.0, 0.62, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-006'],
            ['2026-09-25', 6, 1200.0, 0.70, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-007'],
            ['2026-09-26', 7,  980.0, 0.68, 'Bodega Propia Agaete', '35.P02318/C', 'PROD-2026-008'],
        ];
        $brixData  = [23.5, 22.8, 22.0, 23.8, 22.5, 21.8, 24.0, 23.2];
        $baumeData = [12.9, 12.5, 12.1, 13.1, 12.4, 12.0, 13.2, 12.8];

        // Lookup field harvest IDs for 2026 (activity-based)
        $harvestIds = DB::table('harvests as h')
            ->join('agricultural_activities as aa', 'h.activity_id', '=', 'aa.id')
            ->where('aa.viticulturist_id', $uid)
            ->where('aa.campaign_id', $campaignId)
            ->where('aa.activity_type', 'harvest')
            ->orderBy('h.id')
            ->pluck('h.id')
            ->values();

        $plantingCount = count($plantingIds);
        $buyers = [
            'Bodega Propia Agaete', 'Cooperativa Viticultores GC', 'Bodegas Tafuriaste SL',
            'Bodega Los Berrazales SA', 'Compra Directa — Bodega Atlántico', 'Cooperativa Norteña GC',
        ];
        $regas   = ['35.P02318/C', '35.P04521/C', '35.P01834/C'];
        $statuses = ['matched', 'matched', 'matched', 'pending', 'confirmed'];

        // ── 8 entregas base + 442 generadas = 450 total ────────────────────
        $allData = [];
        foreach ($data as $i => [$date, $idx, $weight, $price, $buyer, $rega, $ticket]) {
            $allData[] = [$date, $idx, $weight, $price, $buyer, $rega, $ticket,
                $brixData[$i], $baumeData[$i], 2026];
        }
        for ($i = count($data) + 1; $i <= 450; $i++) {
            $pIdx    = ($i - 1) % $plantingCount;
            $year    = [2024, 2025, 2026][($i - 1) % 3];
            $month   = $year === 2026 ? mt_rand(9, 10) : mt_rand(8, 11);
            $day     = mt_rand(1, 28);
            $weight  = round(mt_rand(300, 1500) + mt_rand(0, 9) / 10, 1);
            $price   = round(0.60 + ($pIdx % 5) * 0.025, 3);
            $brix    = round(21.0 + mt_rand(0, 30) / 10, 1);
            $baume   = round($brix * 0.55, 1);
            $allData[] = [
                sprintf('%d-%02d-%02d', $year, $month, $day),
                $pIdx, $weight, $price,
                $buyers[$i % count($buyers)],
                $regas[$i % count($regas)],
                'PROD-' . $year . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                $brix, $baume, $year,
            ];
        }

        foreach ($allData as $i => [$date, $idx, $weight, $price, $buyer, $rega, $ticket, $brix, $baume, $year]) {
            $total = round($weight * $price, 2);
            $status = $statuses[$i % count($statuses)];

            DB::table('harvest_deliveries')->insert([
                'viticulturist_id'      => $uid,
                'plot_planting_id'      => $plantingIds[$idx % $plantingCount] ?? null,
                'harvest_id'            => $harvestIds[$i] ?? null,
                'vintage_year'          => $year,
                'buyer_name'            => $buyer,
                'delivery_date'         => $date,
                'delivered_kg'          => $weight,
                'price_per_kg'          => $price,
                'total_price'           => $total,
                'ticket_number'         => $ticket,
                'destination_rega_code' => $rega,
                'status'                => $status,
                'baume_degree'          => $baume,
                'brix_degree'           => $brix,
                'notes'                 => 'Uva propia. Entregada en bodega.',
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);

            if ($harvestIds->has($i)) {
                DB::table('marketed_harvests')->insert([
                    'harvest_id'         => $harvestIds[$i],
                    'campaign_id'        => $campaignId,
                    'viticulturist_id'   => $uid,
                    'delivery_date'      => $date,
                    'quantity_kg'        => $weight,
                    'destination_type'   => 'own_winery',
                    'buyer_name'         => $buyer,
                    'buyer_rega_code'    => $rega,
                    'transport_document' => $ticket,
                    'price_per_kg'       => $price,
                    'total_value'        => $total,
                    'active'             => true,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }
    }

    // ─── 48. Actividades extra — parcelas 8-32 ───────────────────────────────

    private function createExtraActivities(array $plotIds, int $wvId, int $campaignId, $now): void
    {
        $uid      = self::PRODUCER_USER_ID;
        $schedule = [
            ['phytosanitary_treatment', '2026-04-08',  'Preventivo mildiu 1ª aplicación.'],
            ['cultural',                '2026-04-22',  'Laboreo primavera. Control cubierta vegetal.'],
            ['irrigation',             '2026-05-18',  'Riego post-brotación. Reposición hídrica.'],
            ['observation',            '2026-06-03',  'Seguimiento estado fitosanitario.'],
            ['phytosanitary_treatment', '2026-06-20',  'Tratamiento oídio. Azufre mojable.'],
            ['irrigation',             '2026-07-08',  'Riego apoyo verano. Sequía activa.'],
            ['observation',            '2026-07-25',  'Control madurez y estado sanitario.'],
            ['irrigation',             '2026-08-08',  'Riego pre-maduración.'],
            ['observation',            '2026-08-30',  'Toma de muestras para análisis brix.'],
            ['cultural',                '2026-10-28',  'Laboreo otoñal. Aireación suelo.'],
        ];

        foreach (array_slice($plotIds, 8, 25) as $pi => $plotId) {
            foreach ($schedule as [$type, $date, $notes]) {
                DB::table('agricultural_activities')->insert([
                    'viticulturist_id'         => $uid,
                    'winery_viticulturist_id'  => $wvId,
                    'campaign_id'              => $campaignId,
                    'plot_id'                  => $plotId,
                    'plot_planting_id'         => null,
                    'activity_type'            => $type,
                    'activity_date'            => $date,
                    'is_locked'                => false,
                    'notes'                    => $notes . " Parcela idx {$pi}.",
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ]);
            }
        }
    }

    // ─── 49. Facturas producto vino 2026 ─────────────────────────────────────

    private function createProductInvoices(array $wineIds2026, $now): void
    {
        $uid     = self::PRODUCER_USER_ID;
        $clients = DB::table('clients')->where('user_id', $uid)->pluck('id');
        if ($clients->isEmpty()) return;
        $clientCount = $clients->count();

        $wineDescs = [
            'Venta tinto joven 2026 — mercado canario',
            'Venta Marmajuelo 2026 — tiendas especializadas',
            'Venta tinto selección 2026',
            'Exportación 2026 — rosado y blanco',
            'Venta Moscatel 2026 — restaurantes GC',
            'Venta Malvasía 2026 — enotecas',
            'Venta Vijariego 2026 — canal HORECA',
            'Venta blancos 2026 — canal HORECA',
            'Pedido gran formato exportación Península',
            'Fin temporada — turismo enológico',
            'Venta tinto barrica 2026 — distribuidora',
            'Venta espumoso 2026 — eventos',
            'Venta crianza 2026 — exportación Europa',
            'Venta rosado 2026 — hostelería GC',
            'Venta listán negro 2026 — restauración',
        ];
        $harvestDescs = [
            'Venta uva cosecha 2026 — parcelas extra',
            'Cosecha uva blanca 2026 — cooperativa',
            'Uva tinto 2026 — venta directa bodega',
            'Uva moscatel 2026 — productor local',
            'Cosecha uva rosado 2026 — venta directa',
        ];
        $statuses        = ['paid', 'paid', 'paid', 'paid', 'sent', 'draft'];
        $paymentStatuses = ['paid', 'paid', 'paid', 'paid', 'unpaid', 'overdue'];

        // ── 300 facturas 2026 ────────────────────────────────────────────────
        for ($i = 1; $i <= 300; $i++) {
            $isHarvest = $i > 260;
            $month     = $isHarvest ? mt_rand(10, 12) : mt_rand(1, 12);
            $day       = mt_rand(1, 28);
            $date      = sprintf('2026-%02d-%02d', $month, $day);
            $total     = round(mt_rand(1000, 12000) + mt_rand(0, 99) / 100, 2);
            $desc      = $isHarvest
                ? $harvestDescs[($i - 1) % count($harvestDescs)]
                : $wineDescs[($i - 1) % count($wineDescs)];
            $clientId  = $clients[($i - 1) % $clientCount];
            $status    = $statuses[$i % count($statuses)];
            $invNum    = 'FP-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'        => $uid,
                'client_id'      => $clientId,
                'invoice_number' => $invNum,
                'invoice_date'   => $date,
                'status'         => $status,
                'payment_status' => $paymentStatuses[$i % count($paymentStatuses)],
                'subtotal'       => $total,
                'tax_base'       => $total,
                'total_amount'   => $total,
                'observations'   => $desc,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            DB::table('invoice_items')->insert([
                'invoice_id'  => $invoiceId,
                'name'        => $desc,
                'description' => $desc,
                'quantity'    => 1,
                'unit'        => 'partida',
                'unit_price'  => $total,
                'subtotal'    => $total,
                'total'       => $total,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    // ─── 50. Mantenimientos contenedores adicionales ──────────────────────────

    private function createExtraContainerMaintenances(array $containerIds, $now): void
    {
        $maintenances = [
            ['Limpieza profunda DT-01 pre-campaña 2026',       'cleaning',        '2026-01-10', '2026-01-10', 120.0],
            ['Reválvulas y juntas DT-02',                       'repair',          '2026-01-15', '2026-01-16', 280.0],
            ['Revisión temperatura fermentación DT-03',         'inspection',      '2026-02-05', '2026-02-05',  85.0],
            ['Sulfitado preventivo barricas — lote primavera',  'sulfuring',       '2026-03-01', '2026-03-01', 150.0],
            ['Revisión técnica anual contenedores bodega',      'inspection',      '2026-03-20', '2026-03-21', 380.0],
            ['Recubrimiento epoxi DT-05',                       'repair',          '2026-04-12', '2026-04-14', 620.0],
            ['Limpieza y desinfección pre-vendimia',            'cleaning',        '2026-08-20', '2026-08-20', 240.0],
            ['Revisión manómetros y válvulas seguridad',        'inspection',      '2026-09-01', '2026-09-01',  95.0],
            ['Tratamiento madera barrica B-03',                 'tartrate_removal','2026-10-05', '2026-10-06', 185.0],
            ['Mantenimiento preventivo anual — auditoría',      'inspection',      '2026-11-10', null,          450.0],
        ];

        foreach ($maintenances as $mi => [$desc, $type, $sched, $performed, $cost]) {
            $containerId = $containerIds[$mi % count($containerIds)];
            DB::table('container_maintenances')->insert([
                'container_id'          => $containerId,
                'maintenance_type'      => $type,
                'maintenance_name'      => $desc,
                'scheduled_date'        => $sched,
                'performed_date'        => $performed,
                'next_maintenance_date' => '2027-01-15',
                'status'                => $performed ? 'completed' : 'scheduled',
                'cost'                  => $cost,
                'performed_by'          => 'Equipo bodega Producer 2026',
                'notes'                 => 'Mantenimiento planificado 2026.',
                'created_at'            => $now,
                'updated_at'            => $now,
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
            'Parcelas'               => DB::table('plots')->where('viticulturist_id', $uid)->count(),
            'Plantaciones'           => DB::table('plot_plantings')->whereIn('plot_id', DB::table('plots')->where('viticulturist_id', $uid)->pluck('id'))->count(),
            'Campañas'               => DB::table('campaigns')->where('viticulturist_id', $uid)->count(),
            'Actividades campo'      => DB::table('agricultural_activities')->where('viticulturist_id', $uid)->count(),
            'Fenología'              => DB::table('phenology_observations')->where('viticulturist_id', $uid)->count(),
            'Rendimientos estimados' => DB::table('estimated_yields')->whereIn('plot_planting_id', DB::table('plot_plantings')->whereIn('plot_id', DB::table('plots')->where('viticulturist_id', $uid)->pluck('id'))->pluck('id'))->count(),
            'Maquinaria'             => DB::table('machinery')->where('viticulturist_id', $uid)->count(),
            'Subcontrataciones'      => DB::table('subcontractings')->where('viticulturist_id', $uid)->count(),
            'Costes parcela'         => DB::table('plot_costs')->where('viticulturist_id', $uid)->count(),
            'Salas bodega'           => DB::table('container_rooms')->where('user_id', $uid)->count(),
            'Contenedores bodega'    => DB::table('containers')->where('user_id', $uid)->count(),
            'Enólogos'               => DB::table('oenologists')->where('user_id', $uid)->count(),
            'Proveedores'            => DB::table('suppliers')->where('user_id', $uid)->count(),
            'Vinos'                  => DB::table('wines')->where('user_id', $uid)->count(),
            'Embotellamientos'       => DB::table('wine_bottlings')->where('user_id', $uid)->count(),
            'Clientes'               => DB::table('clients')->where('user_id', $uid)->count(),
            'Facturas'               => DB::table('invoices')->where('user_id', $uid)->count(),
            'Notas de cata'          => DB::table('wine_tasting_notes')->whereIn('wine_id', DB::table('wines')->where('user_id', $uid)->pluck('id'))->count(),
            'Eco-certificaciones'    => DB::table('eco_certifications')->where('user_id', $uid)->count(),
            'Documentos bodega'      => DB::table('winery_documents')->where('user_id', $uid)->count(),
            'Alertas'                => DB::table('winery_alerts')->where('user_id', $uid)->count(),
            'Lotes producto 2026'    => DB::table('wine_lots')->where('user_id', $uid)->where('vintage', 2026)->count(),
            'Recepciones 2026'       => DB::table('harvests')->where('winery_id', $uid)->count(),
            'Batches recepción'      => DB::table('grape_reception_batches')->where('winery_id', $uid)->count(),
            'Análisis suelo'         => DB::table('soil_analyses')->where('viticulturist_id', $uid)->count(),
            'Registros biodiversidad'=> DB::table('biodiversity_records')->where('viticulturist_id', $uid)->count(),
            'Alertas fitosanitarias' => DB::table('phytosanitary_alerts')->where('viticulturist_id', $uid)->count(),
            'Previsiones cosecha'    => DB::table('winery_yield_forecasts')->where('winery_id', $uid)->count(),
            'Entregas cosecha'       => DB::table('harvest_deliveries')->where('viticulturist_id', $uid)->count(),
            'Plan trabajos'          => DB::table('planned_works')->where('viticulturist_id', $uid)->count(),
            'Total actividades'      => DB::table('agricultural_activities')->where('viticulturist_id', $uid)->count(),
        ];
        foreach ($stats as $label => $count) {
            $icon = $count > 0 ? '  ✅' : '  ⚠️ ';
            $this->command->info("{$icon} {$label}: {$count}");
        }
    }
}
