<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryCellarOperationsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('  ⚠️  Sin contenedores para operaciones de bodega.');
            return;
        }

        $now  = now();
        $rows = [];

        $operations = [
            ['type' => 'racking',       'label' => 'Trasiego fermentación tinto joven 2024',   'days' => 180, 'status' => 'completed'],
            ['type' => 'clarification', 'label' => 'Clarificación bentonita blanco malvasía',   'days' => 150, 'status' => 'completed'],
            ['type' => 'filtration',    'label' => 'Filtración lenticular tinto crianza 2022',  'days' => 120, 'status' => 'completed'],
            ['type' => 'stabilization', 'label' => 'Estabilización tartárica rosado Agaete',    'days' => 90,  'status' => 'completed'],
            ['type' => 'sulfiting',     'label' => 'Sulfitado preventivo depósitos vacíos',     'days' => 60,  'status' => 'completed'],
            ['type' => 'racking',       'label' => 'Trasiego barricas roble francés crianza',   'days' => 45,  'status' => 'completed'],
            ['type' => 'blending',      'label' => 'Coupage tinto reserva 2022 lotes A+B',      'days' => 30,  'status' => 'completed'],
            ['type' => 'filtration',    'label' => 'Filtración previa embotellado blanco',       'days' => 15,  'status' => 'completed'],
            ['type' => 'racking',       'label' => 'Trasiego vendimia 2025 — depósito 1→2',     'days' => 7,   'status' => 'in_progress'],
            ['type' => 'clarification', 'label' => 'Clarificación espumoso brut nature 2024',   'days' => 5,   'status' => 'planned'],
            ['type' => 'sulfiting',     'label' => 'Sulfitado lote tinto joven antes embotell.', 'days' => 3,  'status' => 'planned'],
            ['type' => 'stabilization', 'label' => 'Estabilización en frío blanco listán 2025', 'days' => 1,  'status' => 'planned'],
        ];

        foreach ($operations as $i => $op) {
            $src = $containers[$i % count($containers)];
            $dst = $containers[($i + 1) % count($containers)];

            $rows[] = [
                'user_id'           => self::WINERY_USER_ID,
                'operation_type'    => $op['type'],
                'operation_date'    => now()->subDays($op['days'])->toDateString(),
                'source_container_id' => $src !== $dst ? $src : null,
                'target_container_id' => $src !== $dst ? $dst : null,
                'volume_liters'     => rand(500, 8000),
                'responsible_person'=> 'Enólogo Demo',
                'status'            => $op['status'],
                'notes'             => $op['label'],
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        DB::table('cellar_operations')->insert($rows);
        $this->command->info('✅ Operaciones de bodega: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('cellar_operations')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
