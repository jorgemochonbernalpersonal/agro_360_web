<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ViticulturistTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'viticulturist@example.com'],
            [
                'name' => 'Test Viticulturist',
                'password' => Hash::make('password'),
                'role' => 'viticulturist',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Test viticulturist user created: viticulturist@example.com / password');
    }
}

