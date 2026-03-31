<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea registros de mantenimiento de depósitos y contenedores.
 * Depende de: WineryContainersSeeder
 */
class WineryContainerMaintenancesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // Traer un subconjunto de contenedores (no todos los 100)
        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->limit(40)
            ->get(['id', 'type_id', 'capacity'])
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('No hay contenedores. Ejecuta WineryContainersSeeder primero.');
            return;
        }

        $maintenanceTypes = [
            // [type, name, cost_range, performer]
            ['cleaning',          'Limpieza con agua caliente y sosa cáustica',     [80,  200],  'Equipo bodega'],
            ['sulfuring',         'Sulfitado preventivo con metabisulfito potásico', [30,  80],   'Marcos Rodríguez'],
            ['inspection',        'Revisión visual de juntas y válvulas',            [50,  120],  'Técnico externo'],
            ['tartrate_removal',  'Destartraje con solución alcalina caliente',      [150, 350],  'Equipo bodega'],
            ['repair',            'Reparación de válvula mariposa',                  [200, 600],  'Tecnivin SAT'],
            ['cleaning',          'Limpieza con agua fría y ácido cítrico',          [60,  150],  'Equipo bodega'],
        ];

        $rows = [];

        foreach ($containers as $idx => $container) {
            // Cada contenedor recibe 1-3 mantenimientos
            $numMaint = ($idx % 3) + 1;

            for ($i = 0; $i < $numMaint; $i++) {
                $mType = $maintenanceTypes[($idx + $i) % count($maintenanceTypes)];

                $scheduledDaysAgo = 60 - ($i * 20);
                $isCompleted      = $scheduledDaysAgo > 5;
                $isScheduled      = !$isCompleted && $i < 1;

                $status        = $isCompleted ? 'completed' : ($isScheduled ? 'scheduled' : 'cancelled');
                $scheduledDate = now()->subDays(abs($scheduledDaysAgo))->format('Y-m-d');
                $performedDate = $isCompleted ? now()->subDays(abs($scheduledDaysAgo) - mt_rand(0, 3))->format('Y-m-d') : null;
                $nextDate      = $isCompleted ? now()->addDays(180)->format('Y-m-d') : null;
                $cost          = $isCompleted ? round(mt_rand($mType[2][0], $mType[2][1]) + mt_rand(0, 99) / 100, 2) : null;

                $rows[] = [
                    'container_id'          => $container->id,
                    'maintenance_type'      => $mType[0],
                    'maintenance_name'      => $mType[1],
                    'scheduled_date'        => $scheduledDate,
                    'performed_date'        => $performedDate,
                    'next_maintenance_date' => $nextDate,
                    'status'                => $status,
                    'cost'                  => $cost,
                    'performed_by'          => $isCompleted ? $mType[3] : null,
                    'notes'                 => $i === 0 ? 'Mantenimiento periódico programado.' : null,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }
        }

        DB::table('container_maintenances')->insert($rows);

        $this->command->info('✅ Mantenimientos de contenedores: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        $containerIds = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('container_maintenances')->whereIn('container_id', $containerIds)->delete();
    }
}
