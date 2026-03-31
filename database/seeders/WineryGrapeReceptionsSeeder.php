<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryGrapeReceptionsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        // Necesita viticultores vinculados a la bodega
        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores vinculados. Saltando recepciones de uva.');
            return;
        }

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        $now         = now();
        $vintageYear = (int) now()->format('Y');
        $batches     = [];
        $harvests    = [];

        $receptions = [
            ['weight' => 3200.0, 'days' => 75, 'baume' => 13.1, 'brix' => 23.8, 'ph' => 3.42, 'acidity' => 5.9, 'price' => 0.90, 'notes' => 'Listán Negro — parcela alta. Buena maduración.'],
            ['weight' => 2800.0, 'days' => 72, 'baume' => 12.9, 'brix' => 23.5, 'ph' => 3.38, 'acidity' => 6.1, 'price' => 0.90, 'notes' => 'Listán Negro — segunda recogida. Estado sanitario óptimo.'],
            ['weight' => 1500.0, 'days' => 68, 'baume' => 13.5, 'brix' => 24.5, 'ph' => 3.50, 'acidity' => 5.5, 'price' => 1.10, 'notes' => 'Negramoll — parcela joven. Alta expresión aromática.'],
            ['weight' => 1200.0, 'days' => 65, 'baume' => 12.5, 'brix' => 22.8, 'ph' => 3.35, 'acidity' => 6.5, 'price' => 0.95, 'notes' => 'Listán Blanco — fresco, para blanco joven.'],
            ['weight' => 900.0,  'days' => 60, 'baume' => 13.8, 'brix' => 25.0, 'ph' => 3.55, 'acidity' => 5.2, 'price' => 1.20, 'notes' => 'Vijariego — variedad autóctona. Producción limitada.'],
            ['weight' => 2100.0, 'days' => 55, 'baume' => 13.0, 'brix' => 23.6, 'ph' => 3.44, 'acidity' => 5.8, 'price' => 0.92, 'notes' => 'Listán Negro — cosecha tardía. Alta concentración.'],
            ['weight' => 750.0,  'days' => 50, 'baume' => 14.2, 'brix' => 25.8, 'ph' => 3.60, 'acidity' => 5.0, 'price' => 1.40, 'notes' => 'Malvasía Volcánica — parcela ceniza negra. Excepcional.'],
            ['weight' => 1800.0, 'days' => 45, 'baume' => 12.7, 'brix' => 23.1, 'ph' => 3.40, 'acidity' => 6.0, 'price' => 0.88, 'notes' => 'Tintilla — en proceso de elaboración rosado.'],
        ];

        // Pre-cargar una plot_planting_id por viticultor (requerida, NOT NULL)
        $plotPlantingByVitic = [];
        foreach ($viticulturistIds as $vid) {
            $ppId = DB::table('plot_plantings')
                ->whereIn('plot_id', DB::table('plots')->where('viticulturist_id', $vid)->pluck('id'))
                ->value('id');
            if ($ppId) {
                $plotPlantingByVitic[$vid] = $ppId;
            }
        }

        foreach ($receptions as $i => $rec) {
            $vitId      = $viticulturistIds[$i % count($viticulturistIds)];
            $containerId = !empty($containers) ? $containers[$i % count($containers)] : null;

            // Si el viticultor no tiene parcelas no podemos crear el batch
            if (!isset($plotPlantingByVitic[$vitId])) {
                $this->command->warn("  ⚠️  Viticultor {$vitId} sin parcelas. Saltando recepción.");
                continue;
            }

            // Crear o reutilizar batch para este viticultor en la añada actual
            $batchKey = $vitId . '_' . $vintageYear;
            if (!isset($batches[$batchKey])) {
                $batchId = DB::table('grape_reception_batches')->insertGetId([
                    'winery_id'         => self::WINERY_USER_ID,
                    'viticulturist_id'  => $vitId,
                    'plot_planting_id'  => $plotPlantingByVitic[$vitId],
                    'vintage_year'      => $vintageYear,
                    'total_weight_kg'   => 0,
                    'status'            => 'open',
                    'notes'             => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $batches[$batchKey] = $batchId;
            }

            $totalValue = $rec['weight'] * $rec['price'];

            // Usar withoutEvents para evitar HarvestObserver (que requiere stock complejo)
            $harvestId = DB::table('harvests')->insertGetId([
                'winery_id'          => self::WINERY_USER_ID,
                'batch_id'           => $batches[$batchKey],
                'harvest_start_date' => now()->subDays($rec['days'])->toDateString(),
                'total_weight'       => $rec['weight'],
                'vintage'            => $vintageYear,
                'container_id'       => $containerId,
                'baume_degree'       => $rec['baume'],
                'brix_degree'        => $rec['brix'],
                'ph_level'           => $rec['ph'],
                'acidity_level'      => $rec['acidity'],
                'price_per_kg'       => $rec['price'],
                'total_value'        => $totalValue,
                'health_status'      => 'sano',
                'destination_type'   => 'winery',
                'status'             => 'active',
                'notes'              => $rec['notes'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            $harvests[] = $harvestId;

            // Actualizar peso acumulado en el batch
            DB::table('grape_reception_batches')
                ->where('id', $batches[$batchKey])
                ->increment('total_weight_kg', $rec['weight']);
        }

        $this->command->info('✅ Recepciones de uva: ' . count($harvests) . ' registros');
    }

    private function cleanup(): void
    {
        // Limpiar harvests de bodega (con winery_id = 1, sin activity_id)
        $harvestIds = DB::table('harvests')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereNull('activity_id')
            ->pluck('id');

        if ($harvestIds->isNotEmpty()) {
            DB::table('harvest_stocks')->whereIn('harvest_id', $harvestIds)->delete();
            DB::table('harvests')->whereIn('id', $harvestIds)->delete();
        }

        // Limpiar batches de recepciones
        DB::table('grape_reception_batches')->where('winery_id', self::WINERY_USER_ID)->delete();
    }
}
