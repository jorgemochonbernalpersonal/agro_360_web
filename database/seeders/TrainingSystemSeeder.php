<?php

namespace Database\Seeders;

use App\Models\TrainingSystem;
use Illuminate\Database\Seeder;

class TrainingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $systems = [
            ['name' => 'Vaso',                         'description' => 'Sistema tradicional sin soporte. Cepa libre con varios brazos. Muy extendido en secano.'],
            ['name' => 'Espaldera simple',              'description' => 'Un plano vertical con alambres. El sistema más habitual en producción moderna.'],
            ['name' => 'Espaldera doble (Doble Guyot)', 'description' => 'Dos varas podadas sobre espaldera. Equilibrio entre producción y calidad.'],
            ['name' => 'Cordón bilateral',              'description' => 'Dos brazos horizontales permanentes con pitones. Fácil mecanización.'],
            ['name' => 'Cordón unilateral',             'description' => 'Un solo brazo horizontal. Usado en parcelas estrechas o pendiente.'],
            ['name' => 'Cordón Royat',                  'description' => 'Variante del cordón unilateral con poda corta en espolones.'],
            ['name' => 'Guyot simple',                  'description' => 'Una vara larga y un pulgar. Clásico en Burdeos y Borgoña.'],
            ['name' => 'Lira',                          'description' => 'Doble plano inclinado en V. Aumenta la superficie foliar expuesta al sol.'],
            ['name' => 'Pérgola / Parral',              'description' => 'Emparrado horizontal elevado. Típico del noroeste peninsular (Albariño, Txakoli).'],
            ['name' => 'Tendido',                       'description' => 'Variante de vaso con brazos rastreros. Tradicional en Jerez y Condado de Huelva.'],
            ['name' => 'Scott Henry',                   'description' => 'Doble cortina dividida verticalmente. Alta densidad foliar en viñedos vigorosos.'],
            ['name' => 'GDC (Geneva Double Curtain)',   'description' => 'Doble cortina horizontal. Alta producción con buena mecanización.'],
            ['name' => 'Otro',                          'description' => null],
        ];

        foreach ($systems as $data) {
            TrainingSystem::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description'], 'active' => true]
            );
        }
    }
}
