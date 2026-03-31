<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea registros de etiquetado vinculando embotellamientos y lotes de etiquetas.
 * Depende de: WineryBottlingSeeder, WineryLabelBatchesSeeder
 */
class WineryLabelingSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $bottlings = DB::table('wine_bottlings')
            ->where('user_id', self::WINERY_USER_ID)
            ->get(['id', 'wine_id', 'bottling_date', 'quantity_bottles'])
            ->toArray();

        $labelBatches = DB::table('label_batches')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereNotNull('wine_id')
            ->get(['id', 'wine_id', 'start_number', 'used_quantity', 'total_quantity'])
            ->keyBy('wine_id')
            ->toArray();

        if (empty($bottlings) || empty($labelBatches)) {
            $this->command->warn('Faltan embotellamientos o lotes de etiquetas.');
            return;
        }

        $rows = [];

        foreach ($bottlings as $bottling) {
            if (!isset($labelBatches[$bottling->wine_id])) {
                continue;
            }

            $batch    = $labelBatches[$bottling->wine_id];
            $qty      = min($bottling->quantity_bottles, $batch->total_quantity - $batch->used_quantity);

            if ($qty <= 0) {
                continue;
            }

            $fromNum = $batch->start_number + $batch->used_quantity;
            $toNum   = $fromNum + $qty - 1;
            $daysAfterBottling = mt_rand(3, 14);

            $rows[] = [
                'user_id'             => self::WINERY_USER_ID,
                'wine_id'             => $bottling->wine_id,
                'wine_bottling_id'    => $bottling->id,
                'label_batch_id'      => $batch->id,
                'labeling_date'       => now()->parse($bottling->bottling_date)->addDays($daysAfterBottling)->format('Y-m-d'),
                'quantity_labeled'    => $qty,
                'from_number'         => $fromNum,
                'to_number'           => $toNum,
                'notes'               => 'Etiquetado con máquina semiautomática KRONES. Control visual al 100%.',
                'created_by'          => self::WINERY_USER_ID,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('wine_labelings')->insert($rows);
        }

        $this->command->info('✅ Etiquetados: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('wine_labelings')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
