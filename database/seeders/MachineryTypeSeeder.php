<?php

namespace Database\Seeders;

use App\Models\MachineryType;
use Illuminate\Database\Seeder;

class MachineryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['es' => 'Tractor',              'en' => 'Tractor'],
            ['es' => 'Pulverizador',         'en' => 'Sprayer'],
            ['es' => 'Atomizador',           'en' => 'Atomiser'],
            ['es' => 'Vendimiadora',         'en' => 'Grape Harvester'],
            ['es' => 'Abonadora',            'en' => 'Fertiliser Spreader'],
            ['es' => 'Cisterna',             'en' => 'Tanker'],
            ['es' => 'Remolque',             'en' => 'Trailer'],
            ['es' => 'Carretilla elevadora', 'en' => 'Forklift'],
            ['es' => 'Motocultor',           'en' => 'Rotary Tiller'],
            ['es' => 'Desbrozadora',         'en' => 'Brushcutter'],
            ['es' => 'Otro',                 'en' => 'Other'],
        ];

        foreach ($types as $names) {
            $existing = MachineryType::where('name->es', $names['es'])->first();
            if ($existing) {
                $existing->setTranslation('name', 'en', $names['en'])->save();
            } else {
                MachineryType::create([
                    'name'   => $names,
                    'active' => true,
                ]);
            }
        }
    }
}
