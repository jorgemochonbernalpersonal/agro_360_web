<?php

namespace Database\Seeders;

use App\Models\GrapeVariety;
use Illuminate\Database\Seeder;

class GrapeVarietySeeder extends Seeder
{
    public function run(): void
    {
        $varieties = [
            ['code' => 'TEMP',   'color' => 'red',   'name' => ['es' => 'Tempranillo',     'en' => 'Tempranillo',       'ca' => 'Tempranillo'],       'description' => null, 'active' => true],
            ['code' => 'GARN-T', 'color' => 'red',   'name' => ['es' => 'Garnacha Tinta',  'en' => 'Grenache Noir',     'ca' => 'Garnatxa Negra'],    'description' => null, 'active' => true],
            ['code' => 'GRAC',   'color' => 'red',   'name' => ['es' => 'Graciano',         'en' => 'Graciano',          'ca' => 'Graciano'],          'description' => null, 'active' => true],
            ['code' => 'MAZU',   'color' => 'red',   'name' => ['es' => 'Mazuelo',          'en' => 'Carignan',          'ca' => 'Carinyena'],         'description' => null, 'active' => true],
            ['code' => 'VERD',   'color' => 'white', 'name' => ['es' => 'Verdejo',          'en' => 'Verdejo',           'ca' => 'Verdejo'],           'description' => null, 'active' => true],
            ['code' => 'VIURA',  'color' => 'white', 'name' => ['es' => 'Viura (Macabeo)', 'en' => 'Macabeo (Viura)',  'ca' => 'Macabeu (Viura)'],   'description' => null, 'active' => true],
            ['code' => 'AIREN',  'color' => 'white', 'name' => ['es' => 'Airén',            'en' => 'Airén',             'ca' => 'Airén'],             'description' => null, 'active' => true],
        ];

        foreach ($varieties as $data) {
            GrapeVariety::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
