<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryOenologistsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const FIRST_NAMES = [
        'Marcos', 'Elena', 'Alejandro', 'Carmen', 'Antonio', 'Isabel', 'Manuel', 'Ana',
        'Juan', 'María', 'Pedro', 'Laura', 'Diego', 'Sofía', 'Jorge', 'Lucía',
        'Álvaro', 'Marta', 'Sergio', 'Paula', 'Roberto', 'Cristina', 'Francisco', 'Patricia',
        'Miguel', 'Sandra', 'Andrés', 'Natalia', 'Carlos', 'Beatriz',
    ];

    private const LAST_NAMES = [
        'Rodríguez Santana', 'García López', 'Martínez Vega', 'Sánchez Medina', 'González Reyes',
        'Pérez Castro', 'Fernández Díaz', 'Torres Acosta', 'Ramírez Santos', 'Jiménez Herrera',
        'Moreno Delgado', 'Ruiz Cabrera', 'Ortega Navarro', 'López Flores', 'Suárez Leal',
        'Castro Betancor', 'Medina Santana', 'Vega González', 'Santos Rodríguez', 'Herrera García',
        'Delgado Pérez', 'Cabrera Torres', 'Navarro Ramírez', 'Flores Jiménez', 'Leal Moreno',
        'Betancor Ruiz', 'Santana Ortega', 'González López', 'Pérez Martínez', 'García Sánchez',
    ];

    private const SPECIALTIES = [
        'Especialista en variedades autóctonas de Gran Canaria.',
        'Responsable de análisis y control de calidad.',
        'Consultor externo. Colabora en temporada de vendimia.',
        'Experto en fermentaciones maloláctcas y estabilización.',
        'Especialista en vinos blancos y espumosos canarios.',
        'Enólogo de crianza y reservas en barrica.',
        'Técnico de viticultura y enología ecológica.',
        'Especialista en elaboración de vinos dulces naturales.',
        'Coordinador de recepciones y control de vendimia.',
        'Experto en análisis fisicoquímicos y microbiológicos.',
    ];

    public function run(): void
    {
        $this->cleanup();

        $now  = now();
        $rows = [];

        $fn  = self::FIRST_NAMES;
        $ln  = self::LAST_NAMES;
        $sp  = self::SPECIALTIES;

        for ($i = 0; $i < 450; $i++) {
            $firstName = $fn[$i % count($fn)];
            $lastName  = $ln[$i % count($ln)];
            $num       = $i + 1;

            $rows[] = [
                'user_id'        => self::WINERY_USER_ID,
                'name'           => $firstName,
                'surname'        => $lastName,
                'license_number' => 'OEN-GC-' . str_pad($num, 4, '0', STR_PAD_LEFT),
                'email'          => 'oen.' . $num . '@enologia-canarias.es',
                'phone'          => '+34 9' . str_pad(10000000 + ($i * 13 % 89000000), 8, '0', STR_PAD_LEFT),
                'active'         => $i % 8 !== 7,  // ~88% activos
                'notes'          => $sp[$i % count($sp)],
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('oenologists')->insert($chunk);
        }

        $this->command->info('✅ Enólogos: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('oenologists')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
