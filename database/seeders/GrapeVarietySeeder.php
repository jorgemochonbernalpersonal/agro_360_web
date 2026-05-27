<?php

namespace Database\Seeders;

use App\Models\GrapeVariety;
use Illuminate\Database\Seeder;

class GrapeVarietySeeder extends Seeder
{
    public function run(): void
    {
        $varieties = [
            ['code' => 'TEMP',   'color' => 'red',   'name' => ['es' => 'Tempranillo',     'en' => 'Tempranillo',       'ca' => 'Tempranillo',      'eu' => 'Tempranillo',       'gl' => 'Tempranillo'],      'description' => null, 'active' => true],
            ['code' => 'GARN-T', 'color' => 'red',   'name' => ['es' => 'Garnacha Tinta',  'en' => 'Grenache Noir',     'ca' => 'Garnatxa Negra',   'eu' => 'Garnatxa Beltza',   'gl' => 'Garnacha Tinta'],   'description' => null, 'active' => true],
            ['code' => 'GRAC',   'color' => 'red',   'name' => ['es' => 'Graciano',         'en' => 'Graciano',          'ca' => 'Graciano',          'eu' => 'Graciano',           'gl' => 'Graciano'],         'description' => null, 'active' => true],
            ['code' => 'MAZU',   'color' => 'red',   'name' => ['es' => 'Mazuelo',          'en' => 'Carignan',          'ca' => 'Carinyena',         'eu' => 'Mazuelo',            'gl' => 'Mazuelo'],          'description' => null, 'active' => true],
            ['code' => 'VERD',   'color' => 'white', 'name' => ['es' => 'Verdejo',          'en' => 'Verdejo',           'ca' => 'Verdejo',           'eu' => 'Verdejo',            'gl' => 'Verdejo'],          'description' => null, 'active' => true],
            ['code' => 'VIURA',  'color' => 'white', 'name' => ['es' => 'Viura (Macabeo)', 'en' => 'Macabeo (Viura)',  'ca' => 'Macabeu (Viura)',  'eu' => 'Makabeo (Biura)',    'gl' => 'Macabeo (Viura)'],  'description' => null, 'active' => true],
            ['code' => 'AIREN',  'color' => 'white', 'name' => ['es' => 'Airén',            'en' => 'Airén',             'ca' => 'Airén',             'eu' => 'Airén',              'gl' => 'Airén'],            'description' => null, 'active' => true],
        ];

        foreach ($varieties as $data) {
            GrapeVariety::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
