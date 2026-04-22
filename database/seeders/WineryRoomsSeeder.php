<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryRoomsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // [nombre_base, descripcion, temp, humedad, capacidad]
    private const ROOM_TYPES = [
        ['Nave de Fermentación',   'Sala principal de fermentación alcohólica en depósitos de acero inoxidable.',  16.5, 72.0, 40],
        ['Bodega de Barricas',     'Crianza y envejecimiento en barrica de roble francés y americano.',             14.0, 80.0, 30],
        ['Sala de Embotellado',    'Área de embotellado, etiquetado y almacén de producto terminado.',             18.0, 65.0, 15],
        ['Sala de Recepción',      'Zona de descarga, pesaje y control de calidad de la vendimia entrante.',        20.0, 60.0, 10],
        ['Almacén de Insumos',     'Almacenamiento de productos enológicos, aditivos y material auxiliar.',         18.0, 55.0,  5],
        ['Depósito Auxiliar',      'Almacenamiento auxiliar de vinos en reposo y estabilización tartárica.',        16.0, 68.0, 20],
        ['Sala de Crianza',        'Envejecimiento de vinos de calidad, reservas y grandes reservas.',              14.5, 78.0, 25],
        ['Laboratorio Enológico',  'Control analítico, físico-químico y microbiológico de la elaboración.',         21.0, 52.0,  8],
        ['Sala de Elaboración',    'Proceso de elaboración: prensado, maceración y preparación del mosto.',         17.0, 70.0, 35],
        ['Nave de Almacenamiento', 'Almacén general de producto terminado y zona de expedición.',                   16.0, 63.0, 20],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now          = now();
        $rows         = [];
        $typeCounters = array_fill(0, count(self::ROOM_TYPES), 0);

        for ($i = 0; $i < 450; $i++) {
            $typeIdx = $i % count(self::ROOM_TYPES);
            $typeCounters[$typeIdx]++;
            [$baseName, $desc, $temp, $humidity, $capacity] = self::ROOM_TYPES[$typeIdx];

            // Pequeña variación en temperatura y humedad para realismo
            $tempVariation     = round(($i % 5 - 2) * 0.2, 1);
            $humidityVariation = round(($i % 5 - 2) * 0.5, 1);

            $rows[] = [
                'user_id'     => self::WINERY_USER_ID,
                'name'        => $baseName . ' ' . str_pad($typeCounters[$typeIdx], 2, '0', STR_PAD_LEFT),
                'description' => $desc,
                'capacity'    => $capacity,
                'temperature' => $temp + $tempVariation,
                'humidity'    => $humidity + $humidityVariation,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('container_rooms')->insert($chunk);
        }

        $this->command->info('✅ Salas de bodega: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('container_rooms')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
