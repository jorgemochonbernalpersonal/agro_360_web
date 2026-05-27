<?php

namespace Database\Seeders;

use App\Models\MachineryType;
use Illuminate\Database\Seeder;

class MachineryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['es' => 'Tractor',              'en' => 'Tractor',             'ca' => 'Tractor',            'eu' => 'Traktore',               'gl' => 'Tractor'],
            ['es' => 'Pulverizador',         'en' => 'Sprayer',             'ca' => 'Polvoritzador',       'eu' => 'Pulberizatzailea',        'gl' => 'Pulverizador'],
            ['es' => 'Atomizador',           'en' => 'Atomiser',            'ca' => 'Atomitzador',         'eu' => 'Atomizatzailea',          'gl' => 'Atomizador'],
            ['es' => 'Vendimiadora',         'en' => 'Grape Harvester',     'ca' => 'Verematadora',        'eu' => 'Mahats-biltzailea',       'gl' => 'Vendimadora'],
            ['es' => 'Abonadora',            'en' => 'Fertiliser Spreader', 'ca' => 'Adobadora',           'eu' => 'Ongarriontzia',           'gl' => 'Aboadoira'],
            ['es' => 'Cisterna',             'en' => 'Tanker',              'ca' => 'Cisterna',            'eu' => 'Zisterna',                'gl' => 'Cisterna'],
            ['es' => 'Remolque',             'en' => 'Trailer',             'ca' => 'Remolc',              'eu' => 'Atoigarria',              'gl' => 'Remolque'],
            ['es' => 'Carretilla elevadora', 'en' => 'Forklift',            'ca' => 'Carretó elevador',    'eu' => 'Igogailu-orgatxoa',       'gl' => 'Carretilla elevadora'],
            ['es' => 'Motocultor',           'en' => 'Rotary Tiller',       'ca' => 'Motocultivador',      'eu' => 'Motolabea',               'gl' => 'Motocultivador'],
            ['es' => 'Desbrozadora',         'en' => 'Brushcutter',         'ca' => 'Desbrossadora',       'eu' => 'Sasi-ebakitzailea',       'gl' => 'Roçadeira'],
            ['es' => 'Otro',                 'en' => 'Other',               'ca' => 'Altre',               'eu' => 'Beste bat',               'gl' => 'Outro'],
        ];

        foreach ($types as $names) {
            $existing = MachineryType::where('name->es', $names['es'])->first();
            if ($existing) {
                foreach (['en', 'ca', 'eu', 'gl'] as $locale) {
                    $existing->setTranslation('name', $locale, $names[$locale]);
                }
                $existing->save();
            } else {
                MachineryType::create(['name' => $names, 'active' => true]);
            }
        }
    }
}
