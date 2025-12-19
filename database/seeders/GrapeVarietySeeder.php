<?php

namespace Database\Seeders;

use App\Models\GrapeVariety;
use Illuminate\Database\Seeder;

class GrapeVarietySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $varieties = [
            ['name' => 'Tempranillo',      'code' => 'TEMP',   'color' => 'red',   'description' => null, 'active' => true],
            ['name' => 'Garnacha Tinta',   'code' => 'GARN-T', 'color' => 'red',   'description' => null, 'active' => true],
            ['name' => 'Graciano',         'code' => 'GRAC',   'color' => 'red',   'description' => null, 'active' => true],
            ['name' => 'Mazuelo',          'code' => 'MAZU',   'color' => 'red',   'description' => null, 'active' => true],
            ['name' => 'Verdejo',          'code' => 'VERD',   'color' => 'white', 'description' => null, 'active' => true],
            ['name' => 'Viura (Macabeo)',  'code' => 'VIURA',  'color' => 'white', 'description' => null, 'active' => true],
            ['name' => 'Airén',            'code' => 'AIREN',  'color' => 'white', 'description' => null, 'active' => true],
        ];

        foreach ($varieties as $data) {
            GrapeVariety::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}


