<?php

namespace Database\Seeders;

use App\Models\TrainingSystem;
use Illuminate\Database\Seeder;

class TrainingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = [
            'Espaldera',
            'Vaso',
            'Parral',
            'Cordón Royat',
            'Guyot',
            'Doble cordón',
            'Otro',
        ];

        foreach ($systems as $name) {
            TrainingSystem::updateOrCreate(
                ['name' => $name],
                ['active' => true]
            );
        }
    }
}


