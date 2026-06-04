<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea lotes de etiquetas vinculados a los vinos embotellados.
 * Depende de: WineryWinesSeeder
 */
class WineryLabelBatchesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('status', ['bottled', 'sold', 'aged'])
            ->get(['id', 'internal_code', 'vintage', 'wine_type'])
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('No hay vinos. Ejecuta WineryWinesSeeder primero.');

            return;
        }

        $rows = [];
        $labelStart = 1000000;

        foreach ($wines as $idx => $wine) {
            $numBatches = ($wine->vintage <= 2023) ? 2 : 1;

            for ($i = 0; $i < $numBatches; $i++) {
                $total = mt_rand(1500, 9000);
                $used = $i === 0 ? mt_rand((int) ($total * 0.5), $total) : mt_rand(100, (int) ($total * 0.3));
                $wasted = mt_rand(10, (int) ($total * 0.02) + 1);
                $startNum = $labelStart + ($idx * 10000) + ($i * $total);
                $endNum = $startNum + $total - 1;

                $source = match (true) {
                    $i === 0 && $wine->vintage === 2022 => 'do_assigned',
                    $i === 1 => 'other',
                    default => 'own',
                };

                $name = match ($source) {
                    'do_assigned' => 'Etiquetas D.O. Gran Canaria — '.$wine->internal_code.' ('.$wine->vintage.')',
                    'other' => 'Contraetiqueta trasera — '.$wine->internal_code.' L'.($i + 1),
                    default => 'Etiqueta principal — '.$wine->internal_code.' ('.$wine->vintage.')',
                };

                $rows[] = [
                    'user_id' => self::WINERY_USER_ID,
                    'wine_id' => $wine->id,
                    'name' => $name,
                    'source' => $source,
                    'start_number' => $startNum,
                    'end_number' => $endNum,
                    'total_quantity' => $total,
                    'used_quantity' => min($used, $total - $wasted),
                    'wasted_quantity' => $wasted,
                    'notes' => $source === 'do_assigned'
                        ? 'Contraetiquetas numeradas asignadas por el Consejo Regulador D.O. Gran Canaria.'
                        : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Lote genérico de bodega sin vino específico
        $rows[] = [
            'user_id' => self::WINERY_USER_ID,
            'wine_id' => null,
            'name' => 'Stock de cápsulas termoretráctiles — Negro mate',
            'source' => 'own',
            'start_number' => 9000000,
            'end_number' => 9010000,
            'total_quantity' => 10001,
            'used_quantity' => 3200,
            'wasted_quantity' => 45,
            'notes' => 'Cápsulas reutilizables para múltiples referencias. Stock general.',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('label_batches')->insert($rows);

        $this->command->info('✅ Lotes de etiquetas: '.count($rows).' registros');
    }

    private function cleanup(): void
    {
        DB::table('label_batches')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
