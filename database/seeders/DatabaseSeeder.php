<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeders...');
        
        // Ejecutar seeders en orden de dependencias
        $this->call([
            AutonomousCommunitySeeder::class,  // Primero: Comunidades autónomas
            ProvinceSeeder::class,              // Segundo: Provincias (depende de comunidades)
            MunicipalitySeeder::class,          // Tercero: Municipios (depende de provincias)
            SigpacUseSeeder::class,            // Usos SIGPAC
            GrapeVarietySeeder::class,          // Variedades de uva base
            MachineryTypeSeeder::class,         // Tipos de maquinaria base
            TrainingSystemSeeder::class,        // Sistemas de conducción base
            PlotCatalogSeeder::class,           // Catálogos territoriales: suelos, riegos, topografías, propiedades, valles
            TaxSeeder::class,                   // Impuestos (IVA, IGIC)
        ]);
        
        $this->command->info('✅ Seeders completados.');
        
        // Opcional: Crear usuario completo para tests (comentar si no se necesita)
        // $this->call(CompleteTestUserSeeder::class);
        
        // Crear usuario de prueba (opcional)
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
