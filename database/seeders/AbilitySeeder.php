<?php

namespace Database\Seeders;

use App\Models\Ability;
use Illuminate\Database\Seeder;

class AbilitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Ability::SEEDED as $data) {
            Ability::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
