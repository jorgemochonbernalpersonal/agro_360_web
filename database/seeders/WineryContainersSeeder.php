<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryContainersSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // container_types.id (insertados en migración)
    // 1=Barrica, 2=Depósito, 3=Tanque, 4=Tina, 5=Ánfora
    // container_materials.id
    // 1=Roble Francés, 2=Roble Americano, 3=Roble Húngaro,
    // 4=Acero Inoxidable, 5=Hormigón, 6=Cerámica, 7=Fibra de Vidrio

    private const DISTRIBUTION = [
        // [type_id, material_id, unit, capacity_range, qty, prefix, description]
        [2, 4, 'litros', [10000, 50000], 28, 'Depósito', 'Depósito de acero inoxidable para fermentación y almacenamiento'],
        [3, 4, 'litros', [2000,  10000], 22, 'Tanque',   'Tanque de fermentación de acero inoxidable'],
        [1, 1, 'litros', [225,     500], 20, 'Barrica',  'Barrica de roble francés para crianza'],
        [1, 2, 'litros', [225,     300], 10, 'Barrica',  'Barrica de roble americano para crianza'],
        [4, 5, 'litros', [3000,  20000],  8, 'Tina',     'Tina de hormigón para fermentación tradicional'],
        [5, 6, 'litros', [300,     600],  7, 'Ánfora',   'Ánfora de cerámica para crianza en barro'],
        [3, 7, 'litros', [5000,  15000],  5, 'Tanque',   'Tanque de fibra de vidrio para almacenamiento'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now  = now();
        $rows = [];
        $counter = 1;

        foreach (self::DISTRIBUTION as [$typeId, $materialId, $unit, $range, $qty, $prefix, $desc]) {
            for ($i = 1; $i <= $qty; $i++) {
                $capacity = $this->randomCapacity($range[0], $range[1]);
                $serial   = strtoupper(substr($prefix, 0, 3)) . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);

                $rows[] = [
                    'user_id'        => self::WINERY_USER_ID,
                    'name'           => "$prefix " . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'description'    => $desc,
                    'capacity'       => $capacity,
                    'unit'           => $unit,
                    'used_capacity'  => 0.00,
                    'quantity'       => 1,
                    'serial_number'  => $serial,
                    'type_id'        => $typeId,
                    'material_id'    => $materialId,
                    'purchase_date'  => $this->randomPurchaseDate(),
                    'archived'       => false,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];

                $counter++;
            }
        }

        DB::table('containers')->insert($rows);

        $this->command->info('✓ ' . count($rows) . ' contenedores creados para winery user_id=' . self::WINERY_USER_ID);
    }

    private function cleanup(): void
    {
        // Obtener IDs de los contenedores del seeder antes de borrar
        $containerIds = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->whereRaw("name REGEXP '^(Depósito|Tanque|Barrica|Tina|Ánfora) [0-9]'")
            ->pluck('id');

        if ($containerIds->isEmpty()) {
            return;
        }

        // Borrar registros dependientes en orden correcto para respetar FKs
        DB::table('wine_fermentation_controls')->whereIn('container_id', $containerIds)->delete();
        DB::table('wine_analyses')->whereIn('container_id', $containerIds)->delete();
        DB::table('cellar_operations')
            ->where(function ($q) use ($containerIds) {
                $q->whereIn('source_container_id', $containerIds)
                  ->orWhereIn('target_container_id', $containerIds);
            })->delete();
        DB::table('container_maintenances')->whereIn('container_id', $containerIds)->delete();
        DB::table('container_histories')->whereIn('container_id', $containerIds)->delete();
        DB::table('wine_bottlings')->whereIn('container_id', $containerIds)->delete();
        DB::table('wine_transfers')
            ->where(function ($q) use ($containerIds) {
                $q->whereIn('source_container_id', $containerIds)
                  ->orWhereIn('destination_container_id', $containerIds);
            })->delete();

        $deleted = DB::table('containers')
            ->whereIn('id', $containerIds)
            ->delete();

        if ($deleted > 0) {
            $this->command->line("  Eliminados $deleted contenedores anteriores del seeder.");
        }
    }

    private function randomCapacity(int $min, int $max): float
    {
        // Redondear a múltiplo de 50 para valores realistas
        $raw = mt_rand($min, $max);
        return (float) (round($raw / 50) * 50);
    }

    private function randomPurchaseDate(): string
    {
        $years = mt_rand(1, 8);
        return now()->subYears($years)->subDays(mt_rand(0, 365))->format('Y-m-d');
    }
}
