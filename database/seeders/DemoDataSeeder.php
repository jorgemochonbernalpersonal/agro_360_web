<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plot;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        // Crear usuario demo
        $viticulturist = User::factory()->create([
            'name' => 'Juan Viticultor Demo',
            'email' => 'demo@agro365.es',
            'password' => bcrypt('password'),
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ Created demo viticulturist');

        // Crear campaña actual
        $campaign = Campaign::factory()->create([
            'user_id' => $viticulturist->id,
            'year' => now()->year,
            'name' => 'Campaña ' . now()->year,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        $this->command->info('✓ Created campaign ' . $campaign->year);

        // Crear parcelas
        $plots = Plot::factory()->count(10)->create([
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->command->info('✓ Created 10 demo plots');

        // Crear actividades agrícolas para cada parcela
        foreach ($plots as $plot) {
            // Tratamientos fitosanitarios
            \App\Models\AgriculturalActivity::factory()
                ->phytosanitary()
                ->count(3)
                ->create([
                    'plot_id' => $plot->id,
                    'campaign_id' => $campaign->id,
                ]);

            // Riegos
            \App\Models\AgriculturalActivity::factory()
                ->irrigation()
                ->count(5)
                ->create([
                    'plot_id' => $plot->id,
                    'campaign_id' => $campaign->id,
                ]);

            // Observaciones
            \App\Models\AgriculturalActivity::factory()
                ->observation()
                ->count(2)
                ->create([
                    'plot_id' => $plot->id,
                    'campaign_id' => $campaign->id,
                ]);
        }

        $this->command->info('✓ Created agricultural activities');

        // Crear suscripción activa
        \App\Models\Subscription::factory()->create([
            'user_id' => $viticulturist->id,
            'plan_type' => 'professional',
            'status' => 'active',
            'amount' => 99.00,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(355),
        ]);

        $this->command->info('✓ Created active subscription');

        $this->command->newLine();
        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Email: demo@agro365.es');
        $this->command->info('Password: password');
    }
}
