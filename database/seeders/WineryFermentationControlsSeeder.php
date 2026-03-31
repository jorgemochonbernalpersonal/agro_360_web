<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea controles de fermentación para los vinos en proceso/crianza.
 * Depende de: WineryWinesSeeder, WineryContainersSeeder
 */
class WineryFermentationControlsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // Vinos activos en fermentación o crianza
        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('status', ['in_progress', 'aged'])
            ->get(['id', 'wine_type', 'vintage'])
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('No hay vinos activos. Ejecuta WineryWinesSeeder primero.');
            return;
        }

        // Contenedores de fermentación (Depósito, Tanque, Tina)
        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('type_id', [2, 3, 4])
            ->where('archived', false)
            ->limit(30)
            ->pluck('id')
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('No hay contenedores. Ejecuta WineryContainersSeeder primero.');
            return;
        }

        $rows = [];
        $containerIdx = 0;

        foreach ($wines as $wine) {
            $containerId = $containers[$containerIdx % count($containers)];
            $containerIdx++;

            // Días de control según estado y tipo
            $numControls = $wine->status === 'in_progress' ? 8 : 3;

            // Fecha de inicio: ~40 días atrás para in_progress, ~180 para aged
            $startDaysAgo = $wine->status === 'in_progress' ? 35 : 200;

            for ($i = 0; $i < $numControls; $i++) {
                $daysAgo = $startDaysAgo - ($i * 4);
                $controlDate = now()->subDays($daysAgo)->format('Y-m-d');

                // Evolución realista de la fermentación
                $fermentationDay = $numControls - $i;
                $brix     = $wine->status === 'in_progress'
                    ? round(22 - ($i * 2.5) + (mt_rand(-5, 5) / 10), 1)
                    : round(mt_rand(0, 5) / 10, 1);
                $baume    = round($brix * 0.55, 1);
                $density  = round(1.090 - ($i * 0.008) + (mt_rand(-2, 2) / 1000), 4);
                $temp     = round(16 + (mt_rand(-20, 20) / 10), 1); // 14-18°C
                $ph       = round(3.3 + (mt_rand(-10, 10) / 100), 2);
                $va       = round(0.15 + ($i * 0.02) + (mt_rand(0, 5) / 100), 2);

                $rows[] = [
                    'wine_id'          => $wine->id,
                    'container_id'     => $containerId,
                    'control_date'     => $controlDate,
                    'temperature'      => $temp,
                    'brix_degree'      => $brix,
                    'baume_degree'     => $baume,
                    'density'          => $density,
                    'ph'               => $ph,
                    'volatile_acidity' => $va,
                    'notes'            => $i === 0 ? 'Control inicial de fermentación.' : null,
                    'created_by'       => self::WINERY_USER_ID,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        DB::table('wine_fermentation_controls')->insert($rows);

        $this->command->info('✅ Controles de fermentación: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('wine_fermentation_controls')->whereIn('wine_id', $wineIds)->delete();
    }
}
