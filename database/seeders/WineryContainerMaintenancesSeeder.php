<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 registros de mantenimiento de contenedores (1 por contenedor).
 * Depende de: WineryContainersSeeder
 */
class WineryContainerMaintenancesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const MAINTENANCE_TYPES = [
        ['cleaning',         'Limpieza con agua caliente y sosa cáustica',       [80,  200], 'Equipo bodega'],
        ['sulfuring',        'Sulfitado preventivo con metabisulfito potásico',   [30,   80], 'Enólogo jefe'],
        ['inspection',       'Revisión visual de juntas, válvulas y sellos',      [50,  120], 'Técnico externo'],
        ['tartrate_removal', 'Destartraje con solución alcalina caliente',        [150, 350], 'Equipo bodega'],
        ['repair',           'Reparación de válvula mariposa',                    [200, 600], 'Tecnivin SAT'],
        ['cleaning',         'Limpieza con agua fría y ácido cítrico',            [60,  150], 'Equipo bodega'],
        ['inspection',       'Inspección de nivel y sensor de temperatura',       [40,  100], 'Técnico externo'],
        ['sulfuring',        'Sulfitado barrica con pastilla de azufre',          [20,   60], 'Enólogo jefe'],
        ['repair',           'Sustitución de junta tórica en tapa superior',      [80,  250], 'Tecnivin SAT'],
        ['tartrate_removal', 'Limpieza ácida de incrustaciones tartáricas',       [120, 300], 'Equipo bodega'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->orderBy('id')
            ->get(['id', 'type_id', 'capacity'])
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('No hay contenedores. Ejecuta WineryContainersSeeder primero.');
            return;
        }

        $rows = [];

        for ($i = 0; $i < 450; $i++) {
            $container = $containers[$i % count($containers)];
            $mType     = self::MAINTENANCE_TYPES[$i % count(self::MAINTENANCE_TYPES)];

            // Distribuir fechas: énfasis en 2025-2026
            $daysAgo       = 365 - (int)round($i * 365 / 449);   // de 365 a 0 días atrás
            $scheduledDate = now()->subDays($daysAgo)->format('Y-m-d');
            $isCompleted   = $daysAgo > 3;
            $isCurrent     = !$isCompleted && $i % 3 === 0;
            $status        = $isCompleted ? 'completed' : ($isCurrent ? 'in_progress' : 'scheduled');

            $performedDate = $isCompleted ? now()->subDays(max(0, $daysAgo - 1))->format('Y-m-d') : null;
            $nextDate      = $isCompleted ? now()->addDays(180 - ($i % 90))->format('Y-m-d') : null;
            $cost          = $isCompleted
                ? round($mType[2][0] + ($i % ($mType[2][1] - $mType[2][0] + 1)), 2)
                : null;

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
                'notes'                 => "Mantenimiento Nº " . ($i + 1) . ". Contenedor ID {$container->id}.",
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('container_maintenances')->insert($chunk);
        }

        $completed = count(array_filter($rows, fn($r) => $r['status'] === 'completed'));
        $this->command->info("✅ Mantenimientos de contenedores: " . count($rows) . " registros ({$completed} completados)");
    }

    private function cleanup(): void
    {
        $containerIds = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('container_maintenances')->whereIn('container_id', $containerIds)->delete();
    }
}
