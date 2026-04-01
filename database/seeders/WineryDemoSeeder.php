<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder maestro para demo completa del rol Winery (user_id = 1).
 *
 * Autónomo — incluye contenedores y todos los módulos.
 * Requisito único: usuario con id=1 y role=winery debe existir.
 *
 * Uso:
 *   php artisan db:seed --class=WineryDemoSeeder
 *
 * Orden de ejecución (respeta dependencias):
 *   0.  units_of_measurement  (auto-seed si vacío)
 *   1.  Contenedores          (WineryContainersSeeder — limpia y recrea)
 *   2.  Salas de bodega       (WineryRoomsSeeder)
 *   3.  Enólogos
 *   4.  Clientes
 *   5.  Proveedores
 *   6.  Suministros de bodega
 *   7.  Vinos (20 referencias)
 *   8.  Controles de fermentación
 *   9.  Traslados de vino
 *  10.  Mermas de vino
 *  11.  Análisis de vino
 *  12.  Recepciones de uva    (necesita viticultores vinculados)
 *  12b. Actividades de campo  (necesita viticultores + plots + campañas)
 *  13.  Disputas              (depende de recepciones)
 *  14.  Mantenimientos de contenedores
 *  15.  Embotellamientos
 *  16.  Lotes de etiquetas
 *  17.  Etiquetados
 *  18.  Lotes de producto
 *  19.  Operaciones de bodega
 *  20.  Compras uva/mosto externo
 *  21.  Facturas de venta
 *  22.  Liquidaciones de uva  (necesita viticultores vinculados)
 *  23.  Notas de cata
 *  24.  Subproductos
 *  25.  Previsiones de rendimiento (necesita viticultores vinculados)
 *  26.  Cumplimiento regulatorio
 *  27.  Documentos de bodega
 *  28.  Alertas
 *  29.  Recálculo stock contenedores (WineryRecalculateContainerStockSeeder)
 */
class WineryDemoSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🍷 ══════════════════════════════════════════════');
        $this->command->info('🍷  WINERY DEMO SEEDER — user_id = 1');
        $this->command->info('🍷  Bodega Agaete · Gran Canaria');
        $this->command->info('🍷 ══════════════════════════════════════════════');
        $this->command->info('');

        // Verificar que el usuario winery existe
        $user = DB::table('users')->find(self::WINERY_USER_ID);
        if (!$user) {
            $this->command->error('❌ No existe el usuario con ID ' . self::WINERY_USER_ID . '. Créalo primero.');
            return;
        }
        $this->command->info("✅ Usuario: {$user->email} (role: {$user->role})");

        // Paso 0: Asegurar units_of_measurement
        $this->ensureUnitsOfMeasurement();

        // Paso 1: Contenedores (autónomo — limpia y recrea)
        $this->runStep('Contenedores (100 depósitos/barricas)', WineryContainersSeeder::class);

        // Paso 2: Salas de bodega
        $this->runStep('Salas de bodega', WineryRoomsSeeder::class);

        // Paso 3-6: Personas y proveedores
        $this->runStep('Enólogos',           WineryOenologistsSeeder::class);
        $this->runStep('Clientes',           WineryClientsSeeder::class);
        $this->runStep('Proveedores',        WinerySuppliersSeeder::class);
        $this->runStep('Suministros bodega', WinerySuppliesSeeder::class);

        // Paso 7: Core — Vinos
        $this->runStep('Vinos (20 referencias)', WineryWinesSeeder::class);

        // Paso 8-11: Operaciones de vinificación
        $this->runStep('Controles de fermentación',  WineryFermentationControlsSeeder::class);
        $this->runStep('Traslados de vino',           WineryWineTransfersSeeder::class);
        $this->runStep('Mermas de vino',              WineryWineLossesSeeder::class);
        $this->runStep('Análisis fisicoquímicos',     WineryWineAnalysisSeeder::class);

        // Paso 12: Recepciones de uva (depende de viticultores vinculados)
        $this->runStep('Recepciones de uva',         WineryGrapeReceptionsSeeder::class);

        // Paso 12b: Actividades de campo (depende de viticultores + plots + campañas)
        $this->runStep('Actividades de campo',       WineryFieldActivitiesSeeder::class);

        // Paso 13: Disputas (depende de recepciones)
        $this->runStep('Disputas en recepciones',    WineryDisputesSeeder::class);

        // Paso 14: Mantenimiento contenedores
        $this->runStep('Mantenimientos contenedores', WineryContainerMaintenancesSeeder::class);

        // Paso 15-18: Embotellado y comercialización
        $this->runStep('Embotellamientos',            WineryBottlingSeeder::class);
        $this->runStep('Lotes de etiquetas',          WineryLabelBatchesSeeder::class);
        $this->runStep('Etiquetados',                 WineryLabelingSeeder::class);
        $this->runStep('Lotes de producto',           WineryProductLotsSeeder::class);

        // Paso 19-20: Operaciones y compras externas
        $this->runStep('Operaciones de bodega',       WineryCellarOperationsSeeder::class);
        $this->runStep('Compras uva/mosto externo',   WineryExternalPurchasesSeeder::class);

        // Paso 21-22: Facturación
        $this->runStep('Facturas de venta',           WineryInvoicesSeeder::class);
        $this->runStep('Liquidaciones de uva',        WineryGrapeInvoicesSeeder::class);

        // Paso 23-25: Calidad y previsiones
        $this->runStep('Notas de cata',              WineryTastingNotesSeeder::class);
        $this->runStep('Subproductos',               WinerySubproductsSeeder::class);
        $this->runStep('Previsiones de rendimiento', WineryYieldForecastsSeeder::class);

        // Paso 25b: Ciclo de vida completo de parcela demo (plot_id=1521)
        $this->runStep('Ciclo de vida parcela 1521', WineryPlotLifecycleSeeder::class);

        // Paso 26-28: Cumplimiento y documentación
        $this->runStep('Cumplimiento regulatorio',   WineryComplianceSeeder::class);
        $this->runStep('Documentos de bodega',       WineryDocumentsSeeder::class);
        $this->runStep('Alertas',                    WineryAlertsSeeder::class);

        // Paso 29: Recalcular stock de contenedores a partir de operaciones reales
        $this->runStep('Stock contenedores (recálculo)', WineryRecalculateContainerStockSeeder::class);

        $this->command->info('');
        $this->command->info('🍷 ══════════════════════════════════════════════');
        $this->command->info('✅  Demo winery completada. Resumen:');
        $this->printSummary();
        $this->command->info('🍷 ══════════════════════════════════════════════');
        $this->command->info('');
    }

    private function runStep(string $label, string $class): void
    {
        $this->command->info("  ▶ {$label}...");
        $seeder = new $class();
        $seeder->setCommand($this->command);
        $seeder->run();
    }

    private function ensureUnitsOfMeasurement(): void
    {
        $count = DB::table('units_of_measurement')->count();
        if ($count > 0) {
            $this->command->info("  ✅ units_of_measurement: {$count} registros existentes");
            return;
        }

        $this->command->info('  ▶ Seeding units_of_measurement (tabla vacía)...');
        $now = now();
        DB::table('units_of_measurement')->insert([
            ['name' => 'Litros',     'symbol' => 'L',        'category' => 'volume', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mililitros', 'symbol' => 'mL',       'category' => 'volume', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kilogramos', 'symbol' => 'kg',       'category' => 'weight', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gramos',     'symbol' => 'g',        'category' => 'weight', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Unidades',   'symbol' => 'unidades', 'category' => 'count',  'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->command->info('  ✅ units_of_measurement: 5 registros creados');
    }

    private function printSummary(): void
    {
        $userId = self::WINERY_USER_ID;

        $stats = [
            // Infraestructura
            'Contenedores'               => DB::table('containers')->where('user_id', $userId)->count(),
            'Salas de bodega'            => DB::table('container_rooms')->where('user_id', $userId)->count(),
            // Personas
            'Enólogos'                   => DB::table('oenologists')->where('user_id', $userId)->count(),
            'Clientes'                   => DB::table('clients')->where('user_id', $userId)->count(),
            'Proveedores'                => DB::table('suppliers')->where('user_id', $userId)->count(),
            'Suministros de bodega'      => DB::table('winery_supplies')->where('user_id', $userId)->count(),
            // Vinos y operaciones
            'Vinos'                      => DB::table('wines')->where('user_id', $userId)->count(),
            'Controles fermentación'     => DB::table('wine_fermentation_controls')
                                            ->whereIn('wine_id', DB::table('wines')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Traslados'                  => DB::table('wine_transfers')
                                            ->whereIn('wine_id', DB::table('wines')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Mermas'                     => DB::table('wine_losses')
                                            ->whereIn('wine_id', DB::table('wines')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Análisis'                   => DB::table('wine_analyses')->where('user_id', $userId)->count(),
            'Campañas bodega'            => DB::table('campaigns')->where('viticulturist_id', $userId)->count(),
            'Actividades de campo'       => DB::table('agricultural_activities')
                                            ->whereIn('viticulturist_id',
                                                DB::table('winery_viticulturist')->where('winery_id', $userId)->pluck('viticulturist_id')
                                            )->count(),
            'Recepciones de uva'         => DB::table('harvests')->where('winery_id', $userId)->whereNull('activity_id')->count(),
            'Disputas'                   => DB::table('harvest_deliveries')
                                            ->whereIn('harvest_id', DB::table('harvests')->where('winery_id', $userId)->pluck('id'))
                                            ->count(),
            'Mantenimientos contenedor'  => DB::table('container_maintenances')
                                            ->whereIn('container_id', DB::table('containers')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            // Comercialización
            'Embotellamientos'           => DB::table('wine_bottlings')->where('user_id', $userId)->count(),
            'Lotes de etiquetas'         => DB::table('label_batches')->where('user_id', $userId)->count(),
            'Etiquetados'                => DB::table('wine_labelings')->where('user_id', $userId)->count(),
            'Lotes de producto'          => DB::table('wine_lots')->where('user_id', $userId)->count(),
            // Operaciones y compras
            'Operaciones de bodega'      => DB::table('cellar_operations')->where('user_id', $userId)->count(),
            'Compras uva externa'        => DB::table('external_grape_purchases')->where('user_id', $userId)->count(),
            // Facturación
            'Facturas de venta'          => DB::table('invoices')->where('user_id', $userId)->where('invoice_type', 'wine_sale')->count(),
            'Liquidaciones uva'          => DB::table('invoices')->where('user_id', $userId)->where('invoice_type', 'grape_purchase')->count(),
            // Calidad
            'Notas de cata'              => DB::table('wine_tasting_notes')->where('user_id', $userId)->count(),
            'Subproductos'               => DB::table('wine_subproducts')->where('user_id', $userId)->count(),
            'Previsiones rendimiento'    => DB::table('winery_yield_forecasts')->where('winery_id', $userId)->count(),
            'Aforos viticultor'          => DB::table('estimated_yields')->whereIn('estimated_by',
                                            DB::table('winery_viticulturist')->where('winery_id', $userId)->pluck('viticulturist_id')
                                           )->count(),
            // Cumplimiento y docs
            'Ecocertificaciones'         => DB::table('eco_certifications')->where('user_id', $userId)->count(),
            'Registros sanitarios'       => DB::table('sanitary_registrations')->where('user_id', $userId)->count(),
            'Autorizaciones embotellado' => DB::table('bottling_authorizations')->where('user_id', $userId)->count(),
            'Documentos de bodega'       => DB::table('winery_documents')->where('user_id', $userId)->count(),
            'Alertas'                    => DB::table('winery_alerts')->where('user_id', $userId)->count(),
        ];

        foreach ($stats as $label => $count) {
            $icon = $count > 0 ? '  ✅' : '  ⚠️ ';
            $this->command->info("{$icon} {$label}: {$count}");
        }
    }
}
