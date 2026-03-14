<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\GrapeVariety;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CypressTestUserSeeder extends Seeder
{
    /**
     * Crea usuarios de prueba genéricos para tests E2E con Cypress.
     * También crea los datos de apoyo necesarios para los flujos de negocio:
     * - WineryViticulturist (vínculo bodega ↔ viticultor)
     * - Contenedores de la bodega
     * - Campaña activa de la bodega
     * - Parcela + plantación del viticultor
     */
    public function run(): void
    {
        // ── Usuarios ─────────────────────────────────────────────────────────

        $viticulturist = User::firstOrCreate(
            ['email' => 'viticulturist@test.com'],
            [
                'name'                => 'Test Viticulturist',
                'password'            => Hash::make('password'),
                'role'                => 'viticulturist',
                'email_verified_at'   => now(),
                'can_login'           => true,
                'password_must_reset' => false,
            ]
        );

        if (!$viticulturist->is_beta_user) {
            $viticulturist->grantBetaAccess();
        }

        $this->command->info('✅ Usuario viticultor creado: viticulturist@test.com / password');

        $winery = User::firstOrCreate(
            ['email' => 'winery@test.com'],
            [
                'name'                => 'Test Winery',
                'password'            => Hash::make('password'),
                'role'                => 'winery',
                'email_verified_at'   => now(),
                'can_login'           => true,
                'password_must_reset' => false,
            ]
        );

        if (!$winery->is_beta_user) {
            $winery->grantBetaAccess();
        }

        $this->command->info('✅ Usuario bodega creado: winery@test.com / password');

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@test.com'],
            [
                'name'                => 'Test Supervisor',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'email_verified_at'   => now(),
                'can_login'           => true,
                'password_must_reset' => false,
            ]
        );

        if (!$supervisor->is_beta_user) {
            $supervisor->grantBetaAccess();
        }

        $this->command->info('✅ Usuario supervisor creado: supervisor@test.com / password');

        // ── Vínculo bodega ↔ viticultor ───────────────────────────────────────

        WineryViticulturist::firstOrCreate(
            [
                'winery_id'        => $winery->id,
                'viticulturist_id' => $viticulturist->id,
            ],
            [
                'assigned_by' => $winery->id,
                'source'      => 'own',
            ]
        );

        $this->command->info('✅ Vínculo WineryViticulturist creado');

        // ── Contenedores de la bodega ─────────────────────────────────────────

        $containerType = ContainerType::first();

        if ($containerType) {
            // Contenedor principal para recepciones
            $unitId = DB::table('units_of_measurement')->where('symbol', 'kg')->value('id')
                ?? DB::table('units_of_measurement')->first()?->id;

            Container::firstOrCreate(
                [
                    'user_id' => $winery->id,
                    'name'    => 'Depósito Cypress Test A',
                ],
                [
                    'type_id'                => $containerType->id,
                    'capacity'               => 50000,
                    'used_capacity'          => 0,
                    'unit_of_measurement_id' => $unitId,
                    'archived'               => false,
                ]
            );

            // Segundo contenedor para tests de trasvases
            Container::firstOrCreate(
                [
                    'user_id' => $winery->id,
                    'name'    => 'Depósito Cypress Test B',
                ],
                [
                    'type_id'                => $containerType->id,
                    'capacity'               => 20000,
                    'used_capacity'          => 0,
                    'unit_of_measurement_id' => $unitId,
                    'archived'               => false,
                ]
            );

            $this->command->info('✅ Contenedores de bodega creados');
        } else {
            $this->command->warn('⚠ No hay ContainerTypes — ejecuta las migraciones primero');
        }

        // ── Campaña activa de la bodega ───────────────────────────────────────

        $currentYear = now()->year;

        Campaign::firstOrCreate(
            [
                'viticulturist_id' => $winery->id,
                'year'             => $currentYear,
            ],
            [
                'name'       => "Vendimia Cypress {$currentYear}",
                'start_date' => "{$currentYear}-08-01",
                'end_date'   => "{$currentYear}-11-30",
                'active'     => true,
                'description' => 'Campaña de prueba para tests E2E Cypress',
            ]
        );

        $this->command->info("✅ Campaña bodega {$currentYear} creada");

        // ── Parcela + plantación del viticultor ───────────────────────────────

        // Necesitamos geografía — cogemos la primera disponible
        $communityId  = DB::table('autonomous_communities')->value('id');
        $provinceId   = DB::table('provinces')->where('autonomous_community_id', $communityId)->value('id');
        $municipalityId = DB::table('municipalities')->where('province_id', $provinceId)->value('id');

        if (!$communityId || !$provinceId || !$municipalityId) {
            $this->command->warn('⚠ No hay datos de geografía — ejecuta SpainGeographySeeder primero');
            return;
        }

        $plot = Plot::firstOrCreate(
            [
                'viticulturist_id' => $viticulturist->id,
                'name'             => 'Parcela Cypress Test',
            ],
            [
                'area'                    => 2.5,
                'autonomous_community_id' => $communityId,
                'province_id'             => $provinceId,
                'municipality_id'         => $municipalityId,
                'active'                  => true,
                'is_locked'               => false,
            ]
        );

        $this->command->info('✅ Parcela del viticultor creada');

        // Variedad de uva — Tempranillo si existe, si no la primera disponible
        $grapeVariety = GrapeVariety::where('code', 'TEM')->first()
            ?? GrapeVariety::first()
            ?? GrapeVariety::create([
                'name'   => 'Tempranillo Cypress',
                'code'   => 'CYP',
                'color'  => 'red',
                'active' => true,
            ]);

        PlotPlanting::firstOrCreate(
            [
                'plot_id' => $plot->id,
                'name'    => 'Plantación Cypress Test',
            ],
            [
                'grape_variety_id'  => $grapeVariety->id,
                'area_planted'      => 2.5,
                'harvest_limit_kg'  => 10000,
                'planting_year'     => 2010,
                'status'            => 'active',
                'active'            => true,
            ]
        );

        $this->command->info('✅ Plantación del viticultor creada');
        $this->command->info('');
        $this->command->info('🎉 Datos de apoyo para flujos E2E listos');
    }
}
