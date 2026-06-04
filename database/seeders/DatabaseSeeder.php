<?php

namespace Database\Seeders;

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
            UnitSeeder::class,                  // Unidades de medida (L, kg, g...)
            PestSeeder::class,                  // Plagas y enfermedades del viñedo
            ContainerTypeSeeder::class,         // Tipos de contenedor (bodega)
        ]);

        $this->command->info('✅ Seeders completados.');
    }
}
