<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea registros de embotellado.
 * Depende de: WineryWinesSeeder, WineryOenologistsSeeder, WineryContainersSeeder
 */
class WineryBottlingSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('status', ['bottled', 'aged', 'sold'])
            ->get(['id', 'wine_type', 'vintage', 'volume_liters', 'internal_code'])
            ->toArray();

        $oenologists = DB::table('oenologists')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('type_id', [2, 3])
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('No hay vinos embotellados. Ejecuta WineryWinesSeeder primero.');

            return;
        }

        $formats = [
            '750' => ['liters' => 0.750,  'bottles_per_batch' => [2000, 8000]],
            '500' => ['liters' => 0.500,  'bottles_per_batch' => [500,  2400]],
            '375' => ['liters' => 0.375,  'bottles_per_batch' => [300,  1200]],
            '1500' => ['liters' => 1.500,  'bottles_per_batch' => [200,  600]],
        ];

        $rows = [];
        $cidx = 0;

        foreach ($wines as $wine) {
            $numBottlings = in_array($wine->vintage, [2022, 2023]) ? 2 : 1;
            $oenId = ! empty($oenologists) ? $oenologists[array_rand($oenologists)] : null;

            for ($i = 0; $i < $numBottlings; $i++) {
                $format = $i === 0 ? '750' : (in_array($wine->wine_type, ['sweet', 'sparkling']) ? '500' : '750');
                $fmtData = $formats[$format];
                $bottles = mt_rand($fmtData['bottles_per_batch'][0], $fmtData['bottles_per_batch'][1]);
                $liters = round($bottles * $fmtData['liters'], 3);
                $daysAgo = ($numBottlings - $i) * 45 + mt_rand(5, 20);
                $lotNumber = $wine->internal_code.'-L'.str_pad($i + 1, 2, '0', STR_PAD_LEFT).'-'.$wine->vintage;

                $rows[] = [
                    'user_id' => self::WINERY_USER_ID,
                    'wine_id' => $wine->id,
                    'wine_process_detail_id' => null,
                    'product_lot_id' => null, // se actualiza en WineryProductLotsSeeder
                    'oenologist_id' => $oenId,
                    'bottling_date' => now()->subDays($daysAgo)->format('Y-m-d'),
                    'bottle_format' => $format,
                    'quantity_bottles' => $bottles,
                    'quantity_liters' => $liters,
                    'lot_number' => $lotNumber,
                    'notes' => $i === 0
                        ? 'Embotellado principal de la añada. Línea de embotellado automática.'
                        : 'Embotellado especial formato magnum/media botella.',
                    'created_by' => self::WINERY_USER_ID,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $cidx++;
            }
        }

        DB::table('wine_bottlings')->insert($rows);

        $this->command->info('✅ Embotellamientos: '.count($rows).' registros');
    }

    private function cleanup(): void
    {
        DB::table('wine_bottlings')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
