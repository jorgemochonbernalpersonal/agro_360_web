<?php

namespace Database\Seeders;

use App\Models\MachineryType;
use Illuminate\Database\Seeder;

class MachineryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['es' => 'Tractor',              'en' => 'Tractor',             'ca' => 'Tractor'],
            ['es' => 'Pulverizador',         'en' => 'Sprayer',             'ca' => 'Polvoritzador'],
            ['es' => 'Atomizador',           'en' => 'Atomiser',            'ca' => 'Atomitzador'],
            ['es' => 'Vendimiadora',         'en' => 'Grape Harvester',     'ca' => 'Verematadora'],
            ['es' => 'Abonadora',            'en' => 'Fertiliser Spreader', 'ca' => 'Adobadora'],
            ['es' => 'Cisterna',             'en' => 'Tanker',              'ca' => 'Cisterna'],
            ['es' => 'Remolque',             'en' => 'Trailer',             'ca' => 'Remolc'],
            ['es' => 'Carretilla elevadora', 'en' => 'Forklift',            'ca' => 'Carretó elevador'],
            ['es' => 'Motocultor',           'en' => 'Rotary Tiller',       'ca' => 'Motocultivador'],
            ['es' => 'Desbrozadora',         'en' => 'Brushcutter',         'ca' => 'Desbrossadora'],
            ['es' => 'Otro',                 'en' => 'Other',               'ca' => 'Altre'],
        ];

        foreach ($types as $names) {
            $existing = MachineryType::where('name->es', $names['es'])->first();
            if ($existing) {
                $existing->setTranslation('name', 'en', $names['en'])
                         ->setTranslation('name', 'ca', $names['ca'])
                         ->save();
            } else {
                MachineryType::create([
                    'name'   => $names,
                    'active' => true,
                ]);
            }
        }
    }
}
