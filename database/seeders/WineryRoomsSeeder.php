<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryRoomsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        DB::table('container_rooms')->insert([
            [
                'user_id'     => self::WINERY_USER_ID,
                'name'        => 'Nave Principal',
                'description' => 'Sala central de fermentación y crianza en depósitos de acero inoxidable.',
                'capacity'    => 40,
                'temperature' => 16.5,
                'humidity'    => 72.0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'user_id'     => self::WINERY_USER_ID,
                'name'        => 'Bodega de Barricas',
                'description' => 'Sala de crianza en barrica de roble francés y americano.',
                'capacity'    => 30,
                'temperature' => 14.0,
                'humidity'    => 80.0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'user_id'     => self::WINERY_USER_ID,
                'name'        => 'Sala de Embotellado',
                'description' => 'Área de embotellado, etiquetado y almacén de producto terminado.',
                'capacity'    => 15,
                'temperature' => 18.0,
                'humidity'    => 65.0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'user_id'     => self::WINERY_USER_ID,
                'name'        => 'Sala de Recepción de Uva',
                'description' => 'Zona de descarga, pesaje y control de calidad de la vendimia entrante.',
                'capacity'    => 10,
                'temperature' => 20.0,
                'humidity'    => 60.0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'user_id'     => self::WINERY_USER_ID,
                'name'        => 'Almacén de Insumos',
                'description' => 'Almacenamiento de productos enológicos, aditivos y material auxiliar.',
                'capacity'    => 5,
                'temperature' => 18.0,
                'humidity'    => 55.0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        $this->command->info('✅ Salas de bodega: 5 registros');
    }

    private function cleanup(): void
    {
        DB::table('container_rooms')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
