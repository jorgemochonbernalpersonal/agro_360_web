<?php

namespace Database\Seeders;

use App\Models\ContainerType;
use Illuminate\Database\Seeder;

class ContainerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'es' => 'Barrica',  'en' => 'Barrel',               'ca' => 'Bóta',    'eu' => 'Upela',    'gl' => 'Barrica',
                'desc' => ['es' => 'Barrica de madera para crianza',  'en' => 'Wooden barrel for ageing',     'ca' => 'Bóta de fusta per a criança',  'eu' => 'Egurrezko upela hazkuntzarako',    'gl' => 'Barrica de madeira para crianza'],
            ],
            [
                'es' => 'Depósito', 'en' => 'Stainless Steel Tank',  'ca' => 'Dipòsit', 'eu' => 'Biltegia', 'gl' => 'Depósito',
                'desc' => ['es' => 'Depósito de acero inoxidable',    'en' => 'Stainless steel tank',         'ca' => 'Dipòsit d\'acer inoxidable',    'eu' => 'Altzairu herdoilgaitzezko biltegia', 'gl' => 'Depósito de aceiro inoxidable'],
            ],
            [
                'es' => 'Tanque',   'en' => 'Fermentation Tank',     'ca' => 'Tanc',    'eu' => 'Tanke',    'gl' => 'Tanque',
                'desc' => ['es' => 'Tanque de fermentación',          'en' => 'Fermentation tank',            'ca' => 'Tanc de fermentació',           'eu' => 'Hartzidura-tanke',                  'gl' => 'Tanque de fermentación'],
            ],
            [
                'es' => 'Tina',     'en' => 'Concrete Vat',          'ca' => 'Tina',    'eu' => 'Tina',     'gl' => 'Tina',
                'desc' => ['es' => 'Tina de hormigón',                'en' => 'Concrete vat',                 'ca' => 'Tina de formigó',               'eu' => 'Hormigoi-tina',                     'gl' => 'Tina de formigón'],
            ],
            [
                'es' => 'Ánfora',   'en' => 'Amphora',               'ca' => 'Àmfora',  'eu' => 'Anfora',   'gl' => 'Ánfora',
                'desc' => ['es' => 'Ánfora de cerámica',              'en' => 'Clay amphora',                 'ca' => 'Àmfora de ceràmica',            'eu' => 'Zeramikazko anfora',                'gl' => 'Ánfora de cerámica'],
            ],
        ];

        $locales = ['es', 'en', 'ca', 'eu', 'gl'];

        foreach ($types as $data) {
            $existing = ContainerType::where('name->es', $data['es'])->first();
            if ($existing) {
                foreach ($locales as $locale) {
                    $existing->setTranslation('name', $locale, $data[$locale]);
                    $existing->setTranslation('description', $locale, $data['desc'][$locale]);
                }
                $existing->save();
            } else {
                $name = $desc = [];
                foreach ($locales as $locale) {
                    $name[$locale] = $data[$locale];
                    $desc[$locale] = $data['desc'][$locale];
                }
                ContainerType::create(['name' => $name, 'description' => $desc]);
            }
        }
    }
}
