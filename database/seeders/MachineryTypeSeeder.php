<?php

namespace Database\Seeders;

use App\Models\MachineryType;
use Illuminate\Database\Seeder;

class MachineryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Tractor',
            'Pulverizador',
            'Atomizador',
            'Vendimiadora',
            'Abonadora',
            'Cisterna',
            'Remolque',
            'Carretilla elevadora',
            'Motocultor',
            'Desbrozadora',
            'Otro',
        ];

        foreach ($types as $name) {
            MachineryType::updateOrCreate(
                ['name' => $name],
                ['active' => true]
            );
        }
    }
}


