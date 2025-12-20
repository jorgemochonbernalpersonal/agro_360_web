<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CypressTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario de prueba para Cypress (tu usuario personal)
        User::firstOrCreate(
            ['email' => 'bernalmochonjorge@gmail.com'],
            [
                'name' => 'Jorge Bernal',
                'password' => Hash::make('cocoteq22'),
                'role' => 'viticulturist',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Usuario de prueba para Cypress creado: bernalmochonjorge@gmail.com / cocoteq22');
    }
}

