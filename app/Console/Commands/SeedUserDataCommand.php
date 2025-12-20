<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Database\Seeders\CompleteTestUserSeeder;

class SeedUserDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:seed-data {user : ID del usuario para poblar datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pobla datos de prueba (campañas 2024 y 2025) para un usuario específico con 20 elementos por tipo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user');
        
        // Verificar que el usuario existe
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ No se encontró el usuario con ID: {$userId}");
            return 1;
        }
        
        $this->info("🌱 Poblando datos para el usuario: {$user->name} ({$user->email})");
        $this->info("📊 Generando 20 elementos por cada tipo de dato...");
        $this->info("");
        
        // Asegurar que los datos base existen
        $this->info("🔄 Verificando datos base (comunidades, provincias, municipios)...");
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\AutonomousCommunitySeeder',
        ]);
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\ProvinceSeeder',
        ]);
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\MunicipalitySeeder',
        ]);
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\SigpacUseSeeder',
        ]);
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\GrapeVarietySeeder',
        ]);
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\TrainingSystemSeeder',
        ]);
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\MachineryTypeSeeder',
        ]);
        $this->info("✅ Datos base verificados");
        $this->info("");
        
        // Ejecutar el seeder completo para este usuario
        $seeder = new CompleteTestUserSeeder();
        $seeder->setCommand($this);
        $seeder->run($userId);
        
        $this->info("");
        $this->info("✅ Datos poblados exitosamente para el usuario ID: {$userId}");
        
        return 0;
    }
}
