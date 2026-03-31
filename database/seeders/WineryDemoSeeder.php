<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder maestro para demo completa del rol Winery (user_id = 1).
 *
 * Prerequisitos (ejecutar una vez con DatabaseSeeder si no están):
 *   - UnitSeeder (units table)
 *   - WineryContainersSeeder (100 depósitos/barricas)
 *   - AgaetePlotsSeeder → AgaeteHarvestsSeeder → AgaeteReceptionSeeder
 *
 * Uso:
 *   php artisan db:seed --class=WineryDemoSeeder
 *
 * O para producción desde cero:
 *   php artisan db:seed --class=WineryDemoSeeder
 *
 * Orden de ejecución (respeta dependencias):
 *   1. units_of_measurement (auto-seed si vacío)
 *   2. Enólogos
 *   3. Clientes
 *   4. Proveedores
 *   5. Suministros de bodega
 *   6. Vinos (core: 20 vinos de añadas 2022-2025)
 *   7. Controles de fermentación
 *   8. Traslados de vino
 *   9. Mermas de vino
 *  10. Análisis de vino
 *  11. Mantenimientos de contenedores
 *  12. Embotellamientos
 *  13. Lotes de etiquetas
 *  14. Etiquetados
 *  15. Lotes de producto (wine_lots)
 *  16. Notas de cata
 *  17. Subproductos
 *  18. Cumplimiento regulatorio (eco-certs, registros sanitarios, autorizaciones)
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

        // Paso 0: Asegurar que units_of_measurement tiene datos
        $this->ensureUnitsOfMeasurement();

        // Paso 1-4: Personas y proveedores
        $this->runStep('Enólogos',           WineryOenologistsSeeder::class);
        $this->runStep('Clientes',           WineryClientsSeeder::class);
        $this->runStep('Proveedores',        WinerySuppliersSeeder::class);
        $this->runStep('Suministros bodega', WinerySuppliesSeeder::class);

        // Paso 5: Core — Vinos
        $this->runStep('Vinos (20 referencias)', WineryWinesSeeder::class);

        // Paso 6-10: Operaciones de bodega (dependen de vinos + contenedores)
        $this->ensureContainersExist();
        $this->runStep('Controles de fermentación', WineryFermentationControlsSeeder::class);
        $this->runStep('Traslados de vino',          WineryWineTransfersSeeder::class);
        $this->runStep('Mermas de vino',             WineryWineLossesSeeder::class);
        $this->runStep('Análisis fisicoquímicos',    WineryWineAnalysisSeeder::class);
        $this->runStep('Mantenimientos contenedores',WineryContainerMaintenancesSeeder::class);

        // Paso 11-15: Embotellado y comercialización
        $this->runStep('Embotellamientos',           WineryBottlingSeeder::class);
        $this->runStep('Lotes de etiquetas',         WineryLabelBatchesSeeder::class);
        $this->runStep('Etiquetados',                WineryLabelingSeeder::class);
        $this->runStep('Lotes de producto',          WineryProductLotsSeeder::class);

        // Paso 16-18: Calidad y cumplimiento
        $this->runStep('Notas de cata',              WineryTastingNotesSeeder::class);
        $this->runStep('Subproductos',               WinerySubproductsSeeder::class);
        $this->runStep('Cumplimiento regulatorio',   WineryComplianceSeeder::class);

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

    private function ensureContainersExist(): void
    {
        $count = DB::table('containers')->where('user_id', self::WINERY_USER_ID)->count();
        if ($count === 0) {
            $this->command->warn('  ⚠️  No hay contenedores para user_id=1. Ejecuta WineryContainersSeeder primero.');
            $this->command->warn('     Los seeders de operaciones de bodega pueden quedar incompletos.');
        } else {
            $this->command->info("  ✅ Contenedores disponibles: {$count}");
        }
    }

    private function printSummary(): void
    {
        $userId = self::WINERY_USER_ID;

        $stats = [
            'Enólogos'                  => DB::table('oenologists')->where('user_id', $userId)->count(),
            'Clientes'                  => DB::table('clients')->where('user_id', $userId)->count(),
            'Proveedores'               => DB::table('suppliers')->where('user_id', $userId)->count(),
            'Suministros de bodega'     => DB::table('winery_supplies')->where('user_id', $userId)->count(),
            'Vinos'                     => DB::table('wines')->where('user_id', $userId)->count(),
            'Controles fermentación'    => DB::table('wine_fermentation_controls')
                                            ->whereIn('wine_id', DB::table('wines')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Traslados'                 => DB::table('wine_transfers')
                                            ->whereIn('wine_id', DB::table('wines')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Mermas'                    => DB::table('wine_losses')
                                            ->whereIn('wine_id', DB::table('wines')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Análisis'                  => DB::table('wine_analyses')->where('user_id', $userId)->count(),
            'Mantenimientos contenedor' => DB::table('container_maintenances')
                                            ->whereIn('container_id', DB::table('containers')->where('user_id', $userId)->pluck('id'))
                                            ->count(),
            'Embotellamientos'          => DB::table('wine_bottlings')->where('user_id', $userId)->count(),
            'Lotes de etiquetas'        => DB::table('label_batches')->where('user_id', $userId)->count(),
            'Etiquetados'               => DB::table('wine_labelings')->where('user_id', $userId)->count(),
            'Lotes de producto'         => DB::table('wine_lots')->where('user_id', $userId)->count(),
            'Notas de cata'             => DB::table('wine_tasting_notes')->where('user_id', $userId)->count(),
            'Subproductos'              => DB::table('wine_subproducts')->where('user_id', $userId)->count(),
            'Ecocertificaciones'        => DB::table('eco_certifications')->where('user_id', $userId)->count(),
            'Registros sanitarios'      => DB::table('sanitary_registrations')->where('user_id', $userId)->count(),
            'Autorizaciones embotellado'=> DB::table('bottling_authorizations')->where('user_id', $userId)->count(),
        ];

        foreach ($stats as $label => $count) {
            $this->command->info("    {$label}: {$count}");
        }
    }
}
