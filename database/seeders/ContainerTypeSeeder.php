<?php

namespace Database\Seeders;

use App\Models\ContainerType;
use Illuminate\Database\Seeder;

class ContainerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['es' => 'Barrica',   'en' => 'Barrel',              'desc_es' => 'Barrica de madera para crianza',     'desc_en' => 'Wooden barrel for ageing'],
            ['es' => 'Depósito',  'en' => 'Stainless Steel Tank', 'desc_es' => 'Depósito de acero inoxidable',       'desc_en' => 'Stainless steel tank'],
            ['es' => 'Tanque',    'en' => 'Fermentation Tank',    'desc_es' => 'Tanque de fermentación',             'desc_en' => 'Fermentation tank'],
            ['es' => 'Tina',      'en' => 'Concrete Vat',         'desc_es' => 'Tina de hormigón',                   'desc_en' => 'Concrete vat'],
            ['es' => 'Ánfora',    'en' => 'Amphora',              'desc_es' => 'Ánfora de cerámica',                 'desc_en' => 'Clay amphora'],
        ];

        foreach ($types as $data) {
            $existing = ContainerType::where('name->es', $data['es'])->first();
            if ($existing) {
                $existing->setTranslation('name', 'en', $data['en'])
                         ->setTranslation('description', 'en', $data['desc_en'])
                         ->save();
            } else {
                ContainerType::create([
                    'name'        => ['es' => $data['es'],      'en' => $data['en']],
                    'description' => ['es' => $data['desc_es'], 'en' => $data['desc_en']],
                ]);
            }
        }
    }
}
