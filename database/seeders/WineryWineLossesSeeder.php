<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea registros de mermas de vino.
 * Depende de: WineryWinesSeeder, WineryContainersSeeder
 */
class WineryWineLossesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now    = now();
        $unitId = $this->getLitrosUnitId();

        if (!$unitId) {
            $this->command->warn('No se encontró unidad "Litros" en units_of_measurement.');
            return;
        }

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereNotIn('status', ['cancelled'])
            ->get(['id', 'status', 'vintage'])
            ->toArray();

        $containerIds = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        if (empty($wines) || empty($containerIds)) {
            $this->command->warn('Faltan vinos o contenedores.');
            return;
        }

        $lossTypes = ['evaporation', 'filtration', 'sampling', 'spillage', 'other'];

        // Merma natural por tipo de vino según estado
        $typeDist = [
            'in_progress' => ['sampling', 'evaporation'],
            'aged'        => ['evaporation', 'evaporation', 'filtration', 'sampling'],
            'bottled'     => ['filtration', 'sampling'],
            'sold'        => ['evaporation'],
        ];

        $rows = [];
        $cidx = 0;

        foreach ($wines as $wine) {
            $types   = $typeDist[$wine->status] ?? ['evaporation'];
            $numLoss = count($types);

            for ($i = 0; $i < $numLoss; $i++) {
                $lossType = $types[$i];
                $quantity = match ($lossType) {
                    'evaporation' => round(mt_rand(20, 150) + mt_rand(0, 99) / 100, 3),
                    'filtration'  => round(mt_rand(50, 300) + mt_rand(0, 99) / 100, 3),
                    'sampling'    => round(mt_rand(1, 5) + mt_rand(0, 99) / 100, 3),
                    'spillage'    => round(mt_rand(10, 80) + mt_rand(0, 99) / 100, 3),
                    default       => round(mt_rand(5, 50) + mt_rand(0, 99) / 100, 3),
                };

                $daysAgo  = ($numLoss - $i) * 20 + mt_rand(0, 5);

                $rows[] = [
                    'wine_id'               => $wine->id,
                    'container_id'          => $containerIds[$cidx % count($containerIds)],
                    'loss_type'             => $lossType,
                    'quantity'              => $quantity,
                    'unit_of_measurement_id'=> $unitId,
                    'loss_date'             => now()->subDays($daysAgo)->format('Y-m-d'),
                    'notes'                 => $this->noteForLoss($lossType),
                    'created_by'            => self::WINERY_USER_ID,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
                $cidx++;
            }
        }

        DB::table('wine_losses')->insert($rows);

        $this->command->info('✅ Mermas de vino: ' . count($rows) . ' registros');
    }

    private function noteForLoss(string $type): ?string
    {
        return match ($type) {
            'evaporation' => 'Merma natural por evaporación. Dentro de límites reglamentarios (≤3% anual).',
            'filtration'  => 'Pérdidas asociadas a proceso de filtración tangencial.',
            'sampling'    => 'Muestra para análisis fisicoquímico en laboratorio.',
            'spillage'    => 'Derrame accidental durante operación de traslado. Registrado en cuaderno de bodega.',
            default       => null,
        };
    }

    private function getLitrosUnitId(): ?int
    {
        return DB::table('units_of_measurement')->where('symbol', 'L')->value('id')
            ?? DB::table('units_of_measurement')->first()?->id;
    }

    private function cleanup(): void
    {
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('wine_losses')->whereIn('wine_id', $wineIds)->delete();
    }
}
