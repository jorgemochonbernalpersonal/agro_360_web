<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SigpacUseSeeder extends Seeder
{
    public function run(): void
    {
        $uses = [
            ['code' => 'TA', 'description' => 'Tierra arable'],
            ['code' => 'TH', 'description' => 'Huerta'],
            ['code' => 'IV', 'description' => 'Invernaderos'],
            ['code' => 'PR', 'description' => 'Prados permanentes'],
            ['code' => 'PS', 'description' => 'Pastos permanentes'],
            ['code' => 'PA', 'description' => 'Pastizal'],
            ['code' => 'OL', 'description' => 'Olivar'],
            ['code' => 'VI', 'description' => 'Viñedo'],
            ['code' => 'FR', 'description' => 'Frutales'],
            ['code' => 'CI', 'description' => 'Cítricos'],
            ['code' => 'AL', 'description' => 'Almendro'],
            ['code' => 'AV', 'description' => 'Avellano'],
            ['code' => 'PI', 'description' => 'Pistacho'],
            ['code' => 'NO', 'description' => 'Nogal'],
            ['code' => 'CA', 'description' => 'Castaño'],
            ['code' => 'PL', 'description' => 'Platanera'],
            ['code' => 'AR', 'description' => 'Arroz'],
            ['code' => 'HT', 'description' => 'Hortalizas'],
            ['code' => 'FL', 'description' => 'Flores y plantas ornamentales'],
            ['code' => 'FO', 'description' => 'Forestal'],
            ['code' => 'IM', 'description' => 'Improductivo'],
            ['code' => 'ZU', 'description' => 'Zona urbana'],
            ['code' => 'AG', 'description' => 'Agua'],
            ['code' => 'ED', 'description' => 'Edificaciones'],
            ['code' => 'OC', 'description' => 'Otros cultivos'],
            ['code' => 'ZI', 'description' => 'Zonas improductivas'],
        ];

        DB::table('sigpac_use')->insert($uses);
    }
}
