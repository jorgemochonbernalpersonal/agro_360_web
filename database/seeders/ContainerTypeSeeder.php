<?php

namespace Database\Seeders;

use App\Models\ContainerType;
use Illuminate\Database\Seeder;

class ContainerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['es' => 'Barrica',  'en' => 'Barrel',               'ca' => 'Bóta',     'desc_es' => 'Barrica de madera para crianza',   'desc_en' => 'Wooden barrel for ageing',      'desc_ca' => 'Bóta de fusta per a criança'],
            ['es' => 'Depósito', 'en' => 'Stainless Steel Tank',  'ca' => 'Dipòsit',  'desc_es' => 'Depósito de acero inoxidable',     'desc_en' => 'Stainless steel tank',          'desc_ca' => 'Dipòsit d\'acer inoxidable'],
            ['es' => 'Tanque',   'en' => 'Fermentation Tank',     'ca' => 'Tanc',     'desc_es' => 'Tanque de fermentación',           'desc_en' => 'Fermentation tank',             'desc_ca' => 'Tanc de fermentació'],
            ['es' => 'Tina',     'en' => 'Concrete Vat',          'ca' => 'Tina',     'desc_es' => 'Tina de hormigón',                 'desc_en' => 'Concrete vat',                  'desc_ca' => 'Tina de formigó'],
            ['es' => 'Ánfora',   'en' => 'Amphora',               'ca' => 'Àmfora',   'desc_es' => 'Ánfora de cerámica',               'desc_en' => 'Clay amphora',                  'desc_ca' => 'Àmfora de ceràmica'],
        ];

        foreach ($types as $data) {
            $existing = ContainerType::where('name->es', $data['es'])->first();
            if ($existing) {
                $existing->setTranslation('name', 'en', $data['en'])
                         ->setTranslation('name', 'ca', $data['ca'])
                         ->setTranslation('description', 'en', $data['desc_en'])
                         ->setTranslation('description', 'ca', $data['desc_ca'])
                         ->save();
            } else {
                ContainerType::create([
                    'name'        => ['es' => $data['es'],      'en' => $data['en'],      'ca' => $data['ca']],
                    'description' => ['es' => $data['desc_es'], 'en' => $data['desc_en'], 'ca' => $data['desc_ca']],
                ]);
            }
        }
    }
}
