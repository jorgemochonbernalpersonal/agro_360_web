<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 operaciones de bodega distribuidas en 2024–2026.
 * Depende de: WineryContainersSeeder
 */
class WineryCellarOperationsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const OP_TYPES = [
        ['racking',        'Trasiego entre depósitos',                 [500,  8000]],
        ['clarification',  'Clarificación con bentonita',              [300,  5000]],
        ['filtration',     'Filtración lenticular previa embotellado', [200,  6000]],
        ['stabilization',  'Estabilización tartárica en frío',         [400,  7000]],
        ['sulfiting',      'Sulfitado preventivo SO₂',                 [100,  3000]],
        ['blending',       'Coupage de lotes para ensamblaje',         [1000, 15000]],
        ['decanting',      'Decantación estática en depósito',         [200,  4000]],
        ['pumping_over',   'Remontado en fermentación tinto',          [500,  5000]],
        ['délestage',      'Délestage — vaciado y rellenado',          [800,  6000]],
        ['homogenization', 'Homogeneización depósito blend',           [300,  8000]],
    ];

    private const PERSONS = [
        'Marcos Rodríguez', 'Elena Castro', 'Alejandro Pérez',
        'Equipo bodega A', 'Equipo bodega B', 'Técnico externo',
    ];

    private const STATUS_POOL = ['completed', 'completed', 'completed', 'in_progress', 'planned'];

    public function run(): void
    {
        $this->cleanup();

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($containers)) {
            $this->command->warn('  ⚠️  Sin contenedores para operaciones de bodega.');
            return;
        }

        $now  = now();
        $rows = [];

        for ($i = 0; $i < 450; $i++) {
            [$opType, $label, $volRange] = self::OP_TYPES[$i % count(self::OP_TYPES)];

            $src    = $containers[$i % count($containers)];
            $dst    = $containers[($i + 1) % count($containers)];
            $status = self::STATUS_POOL[$i % count(self::STATUS_POOL)];

            // Fechas distribuidas en 2024–2026 (énfasis 2026)
            // i=0 → 730 días atrás (≈ 2024-04), i=449 → 0 días atrás (hoy)
            $daysAgo = (int)round(730 - $i * 730 / 449);
            $opDate  = now()->subDays($daysAgo)->toDateString();

            $volume    = $volRange[0] + ($i % ($volRange[1] - $volRange[0] + 1));
            $person    = self::PERSONS[$i % count(self::PERSONS)];

            $rows[] = [
                'user_id'              => self::WINERY_USER_ID,
                'operation_type'       => $opType,
                'operation_date'       => $opDate,
                'source_container_id'  => $src !== $dst ? $src : null,
                'target_container_id'  => $src !== $dst ? $dst : null,
                'volume_liters'        => $volume,
                'responsible_person'   => $person,
                'status'               => $status,
                'notes'                => "$label — operación Nº " . ($i + 1) . ".",
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('cellar_operations')->insert($chunk);
        }

        $completed = count(array_filter($rows, fn($r) => $r['status'] === 'completed'));
        $this->command->info("✅ Operaciones de bodega: " . count($rows) . " registros ({$completed} completadas)");
    }

    private function cleanup(): void
    {
        DB::table('cellar_operations')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
