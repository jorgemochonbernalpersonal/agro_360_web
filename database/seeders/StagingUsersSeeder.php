<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crea los 3 usuarios demo para staging.
 *
 * Uso:
 *   php artisan db:seed --class=StagingUsersSeeder
 *
 * A continuación correr en orden:
 *   php artisan db:seed --class=WineryDemoSeeder
 *   php artisan db:seed --class=ViticulturistDemoSeeder
 *   php artisan db:seed --class=ProducerDemoSeeder
 */
class StagingUsersSeeder extends Seeder
{
    private const USERS = [
        [
            'id' => 1,
            'name' => 'Demo Bodega',
            'email' => 'demo.bodega@agro365.es',
            'password' => 'demo1234',
            'role' => 'winery',
        ],
        [
            'id' => 2,
            'name' => 'Demo Productor',
            'email' => 'demo.productor@agro365.es',
            'password' => 'demo1234',
            'role' => 'producer',
        ],
        [
            'id' => 3,
            'name' => 'Demo Viticultor',
            'email' => 'demo.viticultor@agro365.es',
            'password' => 'demo1234',
            'role' => 'viticulturist',
        ],
    ];

    public function run(): void
    {
        foreach (self::USERS as $data) {
            User::updateOrCreate(
                ['id' => $data['id']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'email_verified_at' => now(),
                    'password_must_reset' => false,
                ]
            );

            $this->command->info("✅ Usuario creado: {$data['email']} (id={$data['id']}, role={$data['role']})");
        }

        $this->command->info('');
        $this->command->info('Credenciales demo: contraseña = demo1234');
    }
}
