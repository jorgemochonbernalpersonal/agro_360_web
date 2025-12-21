<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CypressTestUserSeeder extends Seeder
{
    /**
     * Crea usuarios de prueba genéricos para tests E2E con Cypress
     * Estos usuarios se crean en la BD de test y se eliminan después de los tests
     */
    public function run(): void
    {
        // Usuario viticultor principal para tests
        $viticulturist = User::firstOrCreate(
            ['email' => 'viticulturist@test.com'],
            [
                'name' => 'Test Viticulturist',
                'password' => Hash::make('password'),
                'role' => 'viticulturist',
                'email_verified_at' => now(),
                'can_login' => true,
                'password_must_reset' => false,
            ]
        );

        // Otorgar acceso beta para que pueda acceder a las rutas
        if (!$viticulturist->is_beta_user) {
            $viticulturist->grantBetaAccess();
        }

        $this->command->info('✅ Usuario viticultor creado: viticulturist@test.com / password');

        // Usuario bodega para tests (si se necesita)
        $winery = User::firstOrCreate(
            ['email' => 'winery@test.com'],
            [
                'name' => 'Test Winery',
                'password' => Hash::make('password'),
                'role' => 'winery',
                'email_verified_at' => now(),
                'can_login' => true,
                'password_must_reset' => false,
            ]
        );

        // Otorgar acceso beta
        if (!$winery->is_beta_user) {
            $winery->grantBetaAccess();
        }

        $this->command->info('✅ Usuario bodega creado: winery@test.com / password');

        // Usuario supervisor para tests (si se necesita)
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@test.com'],
            [
                'name' => 'Test Supervisor',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'email_verified_at' => now(),
                'can_login' => true,
                'password_must_reset' => false,
            ]
        );

        // Otorgar acceso beta
        if (!$supervisor->is_beta_user) {
            $supervisor->grantBetaAccess();
        }

        $this->command->info('✅ Usuario supervisor creado: supervisor@test.com / password');
    }
}
