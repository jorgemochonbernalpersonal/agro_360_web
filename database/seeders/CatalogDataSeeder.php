<?php

namespace Database\Seeders;

use App\Models\GrapeVariety;
use App\Models\TrainingSystem;
use App\Models\Certification;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class CatalogDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding catalog data...');

        // Variedades de uva principales españolas
        $grapeVarieties = [
            // Tintas
            ['name' => 'Tempranillo', 'type' => 'red', 'code' => 'TEMP'],
            ['name' => 'Garnacha', 'type' => 'red', 'code' => 'GARN'],
            ['name' => 'Monastrell', 'type' => 'red', 'code' => 'MONA'],
            ['name' => 'Mencía', 'type' => 'red', 'code' => 'MENC'],
            ['name' => 'Bobal', 'type' => 'red', 'code' => 'BOBA'],
            ['name' => 'Cabernet Sauvignon', 'type' => 'red', 'code' => 'CABS'],
            ['name' => 'Merlot', 'type' => 'red', 'code' => 'MERL'],
            ['name' => 'Syrah', 'type' => 'red', 'code' => 'SYRA'],
            
            // Blancas
            ['name' => 'Verdejo', 'type' => 'white', 'code' => 'VERD'],
            ['name' => 'Albariño', 'type' => 'white', 'code' => 'ALBA'],
            ['name' => 'Macabeo', 'type' => 'white', 'code' => 'MACA'],
            ['name' => 'Godello', 'type' => 'white', 'code' => 'GODE'],
            ['name' => 'Airén', 'type' => 'white', 'code' => 'AIRE'],
            ['name' => 'Chardonnay', 'type' => 'white', 'code' => 'CHAR'],
        ];

        foreach ($grapeVarieties as $variety) {
            GrapeVariety::firstOrCreate(
                ['code' => $variety['code']],
                $variety
            );
        }

        $this->command->info('✓ Created ' . count($grapeVarieties) . ' grape varieties');

        // Sistemas de conducción
        $trainingSystems = [
            ['name' => 'Vaso', 'description' => 'Sistema tradicional en forma de vaso'],
            ['name' => 'Espaldera', 'description' => 'Sistema en espaldera con alambres'],
            ['name' => 'Cordón Royat', 'description' => 'Poda en cordón simple'],
            ['name' => 'Guyot', 'description' => 'Sistema Guyot simple o doble'],
            ['name' => 'Parral', 'description' => 'Sistema en parral horizontal'],
        ];

        foreach ($trainingSystems as $system) {
            TrainingSystem::firstOrCreate(
                ['name' => $system['name']],
                $system
            );
        }

        $this->command->info('✓ Created ' . count($trainingSystems) . ' training systems');

        // Certificaciones
        $certifications = [
            ['name' => 'Ecológico', 'code' => 'ECO', 'description' => 'Agricultura ecológica certificada'],
            ['name' => 'Biodinámica', 'code' => 'BIO', 'description' => 'Agricultura biodinámica'],
            ['name' => 'Vegano', 'code' => 'VEG', 'description' => 'Certificación vegana'],
            ['name' => 'Sostenible', 'code' => 'SOS', 'description' => 'Viticultura sostenible'],
        ];

        foreach ($certifications as $cert) {
            Certification::firstOrCreate(
                ['code' => $cert['code']],
                $cert
            );
        }

        $this->command->info('✓ Created ' . count($certifications) . ' certifications');

        // Impuestos
        $taxes = [
            ['name' => 'IVA General', 'rate' => 21.00, 'code' => 'IVA21'],
            ['name' => 'IVA Reducido', 'rate' => 10.00, 'code' => 'IVA10'],
            ['name' => 'IVA Superreducido', 'rate' => 4.00, 'code' => 'IVA4'],
            ['name' => 'Exento', 'rate' => 0.00, 'code' => 'EXENT'],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(
                ['code' => $tax['code']],
                $tax
            );
        }

        $this->command->info('✓ Created ' . count($taxes) . ' tax rates');

        $this->command->newLine();
        $this->command->info('Catalog data seeded successfully!');
    }
}
