<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Litros',       'symbol' => 'L',        'category' => 'volume'],
            ['name' => 'Mililitros',   'symbol' => 'mL',       'category' => 'volume'],
            ['name' => 'Kilogramos',   'symbol' => 'kg',       'category' => 'weight'],
            ['name' => 'Gramos',       'symbol' => 'g',        'category' => 'weight'],
            ['name' => 'Unidades',     'symbol' => 'unidades', 'category' => 'count'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['symbol' => $unit['symbol']],
                ['name' => $unit['name'], 'category' => $unit['category'], 'active' => true]
            );
        }
    }
}
