<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera datos para que SILICIE (Libro II + Apertura) e INFOVI (Balance) muestren contenido.
 *
 * Crea:
 *  - wine_process_details: pasos de elaboración coherentes por tipo de vino (~80 registros)
 *  - wine_stock_snapshots: fotos de stock en fechas clave para apertura fiscal + cierre campaña (~40 registros)
 *
 * Depende de: WineryWinesSeeder (vinos existentes con volume_liters y wine_type).
 */
class WinerySilicieDataSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // Pipelines de elaboración por tipo de vino
    // Cada paso: [process_type, duration_days_min, duration_days_max, qty_factor (% del volumen total)]
    private const PIPELINES = [
        'red' => [
            ['destemming_crushing', 0, 0,   1.05],  // Despalillado: pierde 5% (raspa)
            ['maceration',         3, 10,   1.00],
            ['fermentation',       7, 21,   0.98],  // Fermentación: pierde 2% CO2
            ['pressing',           0, 1,    0.95],
            ['malolactic',        14, 30,   0.94],
            ['sulfitation',        0, 0,    0.94],
            ['racking',            0, 1,    0.93],
            ['aging',             90, 365,  0.92],
            ['fining',             7, 14,   0.91],
            ['filtration',         0, 1,    0.90],
        ],
        'white' => [
            ['pressing',           0, 0,    0.70],  // Prensado: jugo = ~70% peso
            ['settling',           1, 2,    0.68],
            ['fermentation',      10, 21,   0.66],
            ['sulfitation',        0, 0,    0.66],
            ['cold_stabilization', 7, 14,   0.65],
            ['fining',             7, 14,   0.64],
            ['filtration',         0, 1,    0.63],
        ],
        'rose' => [
            ['pressing',           0, 0,    0.72],
            ['settling',           1, 2,    0.70],
            ['fermentation',      10, 18,   0.68],
            ['sulfitation',        0, 0,    0.68],
            ['cold_stabilization', 5, 10,   0.67],
            ['filtration',         0, 1,    0.66],
        ],
        'sparkling' => [
            ['pressing',           0, 0,    0.70],
            ['settling',           1, 2,    0.68],
            ['fermentation',      10, 21,   0.66],
            ['blending',           0, 1,    0.65],
            ['fermentation',      30, 60,   0.64],  // 2ª fermentación en botella
            ['aging',             90, 270,  0.63],
            ['filtration',         0, 1,    0.62],
        ],
        'sweet' => [
            ['pressing',           0, 0,    0.65],
            ['settling',           1, 2,    0.63],
            ['fermentation',       5, 10,   0.62],  // Fermentación corta (azúcar residual)
            ['sulfitation',        0, 0,    0.62],
            ['cold_stabilization', 7, 14,   0.61],
            ['fining',             5, 10,   0.60],
            ['filtration',         0, 1,    0.59],
        ],
    ];

    // Observaciones por tipo de proceso
    private const PROCESS_NOTES = [
        'destemming_crushing'  => ['Despalillado mecánico. Estrujado suave para evitar oxidación de pepitas.', 'Despalillado total. Estrujado con rodillos a 2 rpm.'],
        'pressing'             => ['Prensado neumático a 1.2 bar. Separación de mosto flor y prensa.', 'Prensado suave. Tres ciclos de presión creciente.'],
        'settling'             => ['Desfangado estático 12h a 10°C. Trasiego de mosto limpio.', 'Desfangado con enzimas pectolíticas. 18h a 8°C.'],
        'fermentation'         => ['Fermentación controlada a 16°C con Saccharomyces cerevisiae.', 'Fermentación a 26°C. Remontados diarios.', 'Fermentación completada. Densidad final 0.992.'],
        'maceration'           => ['Maceración en frío 48h. Extracción de aromas varietales.', 'Maceración post-fermentativa 5 días. Color intenso y taninos maduros.'],
        'malolactic'           => ['FML espontánea completada. Ácido málico <0.1 g/L.', 'Inoculación con Oenococcus oeni. FML en 21 días.'],
        'aging'                => ['Crianza en barrica de roble francés 225L (tostado medio).', 'Crianza 6 meses roble americano. Notas de vainilla y coco.', 'Crianza sobre lías finas con bâtonnage quincenal.'],
        'racking'              => ['Trasiego a depósito inox. Separación de lías gruesas.', 'Trasiego con sulfitado. SO2 libre ajustado a 30 mg/L.'],
        'blending'             => ['Coupage 60% Listán Negro + 40% Negramoll. Ensayo previo aprobado.', 'Mezcla de bases para segunda fermentación.'],
        'fining'               => ['Clarificación con bentonita 40 g/hL. Prueba de estabilidad proteica OK.', 'Clarificación con albúmina de huevo. Suavizado de taninos.'],
        'filtration'           => ['Filtración por placas (1 μm). Brillantez y limpieza confirmadas.', 'Filtración amicróbica 0.45 μm pre-embotellado.'],
        'cold_stabilization'   => ['Estabilización tartárica a -4°C durante 10 días.', 'Estabilización con CMC (carboximetilcelulosa). Sin precipitados.'],
        'sulfitation'          => ['Sulfitado correctivo. SO2 libre: 25 mg/L, total: 85 mg/L.', 'Adición de SO2 post-FML. Ajuste a 30 mg/L libre.'],
        'enrichment'           => ['Enriquecimiento con MCR. Aumento de 0.5% vol.'],
        'concentration'        => ['Concentración parcial por ósmosis inversa.'],
        'acidification'        => ['Acidificación con ácido tartárico 1 g/L.'],
        'desacidification'     => ['Desacidificación con CaCO3.'],
        'bottling'             => ['Embotellado en línea. Llenado con nitrógeno. Corcho natural 45mm.', 'Embotellado con tapón de rosca. Lote verificado.'],
        'other'                => ['Operación de bodega registrada.'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ── Obtener vinos de la bodega ───────────────────────────────────────
        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($wines->isEmpty()) {
            $this->command->warn('  ⚠️  Sin vinos en la bodega. Ejecuta WineryWinesSeeder primero.');
            return;
        }

        // ── Obtener contenedores para asignar a procesos ────────────────────
        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        // (created_by references users table, not oenologists)

        // ── Obtener unidad de medida (litros) ────────────────────────────────
        $unitLiters = DB::table('units_of_measurement')
            ->where('symbol', 'L')
            ->value('id');

        // ═════════════════════════════════════════════════════════════════════
        // 1. WINE PROCESS DETAILS
        // ═════════════════════════════════════════════════════════════════════

        $totalProcesses = 0;
        $cidx = 0;

        foreach ($wines as $wine) {
            $wineType = $wine->wine_type;
            $volume   = (float) $wine->volume_liters;
            $vintage  = (int) $wine->vintage;

            // Select pipeline based on wine type
            $pipelineKey = match ($wineType) {
                'red'                     => 'red',
                'white'                   => 'white',
                'rose'                    => 'rose',
                'sparkling'               => 'sparkling',
                'sweet', 'semi_sweet'     => 'sweet',
                'fortified'               => 'red',   // Similar to red with fortification
                default                   => 'white',
            };

            $pipeline = self::PIPELINES[$pipelineKey];

            // Determine how many steps to include based on wine status
            $stepCount = match ($wine->status) {
                'in_progress' => max(2, (int) (count($pipeline) * 0.4)),  // Early steps
                'aged'        => max(3, (int) (count($pipeline) * 0.7)),  // Most steps
                'bottled'     => count($pipeline),                         // All steps + bottling
                'sold'        => count($pipeline),                         // All steps
                default       => count($pipeline),
            };

            // Start date: September of vintage year (harvest time)
            $startDate = \Carbon\Carbon::create($vintage, 9, mt_rand(1, 20));

            for ($s = 0; $s < $stepCount && $s < count($pipeline); $s++) {
                [$processType, $durMin, $durMax, $qtyFactor] = $pipeline[$s];

                $duration  = mt_rand($durMin, max($durMin, $durMax));
                $endDate   = (clone $startDate)->addDays($duration);
                $quantity  = $volume > 0 ? round($volume * $qtyFactor, 3) : null;

                $notes     = self::PROCESS_NOTES[$processType] ?? ['Proceso registrado.'];
                $note      = $notes[$cidx % count($notes)];

                $containerId = !empty($containers) ? $containers[$cidx % count($containers)] : null;

                DB::table('wine_process_details')->insert([
                    'wine_id'                => $wine->id,
                    'container_id'           => $containerId,
                    'process_type'           => $processType,
                    'start_date'             => $startDate->toDateString(),
                    'end_date'               => $duration > 0 ? $endDate->toDateString() : null,
                    'quantity'               => $quantity,
                    'unit_of_measurement_id' => $unitLiters,
                    'observations'           => $note,
                    'created_by'             => self::WINERY_USER_ID,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);

                // Advance start date for next step
                $startDate = (clone $endDate)->addDays(mt_rand(1, 3));
                $totalProcesses++;
                $cidx++;
            }

            // Add bottling step if status is bottled or sold (and not already last in pipeline)
            if (in_array($wine->status, ['bottled', 'sold']) && $pipeline[$stepCount - 1][0] !== 'bottling') {
                $notes = self::PROCESS_NOTES['bottling'];
                DB::table('wine_process_details')->insert([
                    'wine_id'                => $wine->id,
                    'container_id'           => null,
                    'process_type'           => 'bottling',
                    'start_date'             => $startDate->toDateString(),
                    'end_date'               => $startDate->toDateString(),
                    'quantity'               => $volume > 0 ? round($volume * 0.98, 3) : null,
                    'unit_of_measurement_id' => $unitLiters,
                    'observations'           => $notes[$cidx % count($notes)],
                    'created_by'             => self::WINERY_USER_ID,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);
                $totalProcesses++;
                $cidx++;
            }
        }

        $this->command->info("  ✅ Procesos de elaboración: {$totalProcesses} registros");

        // ═════════════════════════════════════════════════════════════════════
        // 2. WINE STOCK SNAPSHOTS
        // ═════════════════════════════════════════════════════════════════════

        // Snapshots en fechas clave:
        // - 31/12/2023 → Apertura fiscal 2024
        // - 31/07/2024 → Cierre campaña 2023 (INFOVI: ago 2023 → jul 2024)
        // - 31/12/2024 → Apertura fiscal 2025
        // - 31/07/2025 → Cierre campaña 2024 (INFOVI: ago 2024 → jul 2025)

        $snapshotDates = [
            '2023-12-31',
            '2024-07-31',
            '2024-12-31',
            '2025-07-31',
        ];

        $totalSnapshots = 0;

        foreach ($snapshotDates as $snapDate) {
            $snapCarbon = \Carbon\Carbon::parse($snapDate);
            $snapYear   = (int) $snapCarbon->format('Y');

            foreach ($wines as $wine) {
                $vintage = (int) $wine->vintage;
                $volume  = (float) $wine->volume_liters;

                // Only include wines that existed at the snapshot date
                // Wine from vintage X starts existing ~Sep X
                $wineExistsSince = \Carbon\Carbon::create($vintage, 9, 1);
                if ($snapCarbon->lt($wineExistsSince)) continue;

                // Estimate quantity at snapshot time based on status and age
                $monthsSinceVintage = $wineExistsSince->diffInMonths($snapCarbon);
                $factor = match (true) {
                    $wine->status === 'sold'                         => 0,        // Already sold
                    $monthsSinceVintage < 3                          => 0.95,     // Fresh: nearly full
                    $monthsSinceVintage < 12                         => 0.90,     // Aging: some evaporation
                    $monthsSinceVintage < 24                         => 0.85,     // Older: more loss
                    default                                          => 0.80,
                };

                // Sold wines: only show stock in pre-sale snapshots
                if ($wine->status === 'sold') {
                    // Assume sold in the year of the vintage + 1
                    if ($snapYear > $vintage + 1) continue;
                    $factor = 0.85;
                }

                $snapshotQty = round($volume * $factor + mt_rand(-50, 50), 3);
                if ($snapshotQty <= 0) continue;

                // Container count estimate
                $containerCount = max(1, (int) ceil($snapshotQty / 5000));

                DB::table('wine_stock_snapshots')->insert([
                    'user_id'            => self::WINERY_USER_ID,
                    'wine_id'            => $wine->id,
                    'snapshot_date'      => $snapDate,
                    'quantity_liters'    => $snapshotQty,
                    'container_count'    => $containerCount,
                    'alcohol_percentage' => round(mt_rand(115, 145) / 10, 2),
                    'vintage'            => $vintage,
                    'wine_type'          => $wine->wine_type,
                    'is_must'            => $wine->is_must ?? false,
                    'observations'       => "Snapshot automático — inventario a {$snapDate}.",
                    'created_by'         => self::WINERY_USER_ID,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $totalSnapshots++;
            }
        }

        $this->command->info("  ✅ Snapshots de stock: {$totalSnapshots} registros (" . count($snapshotDates) . " fechas)");
    }

    private function cleanup(): void
    {
        // Process details for winery wines
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        if ($wineIds->isNotEmpty()) {
            DB::table('wine_process_details')
                ->whereIn('wine_id', $wineIds)
                ->delete();
        }

        // Snapshots
        DB::table('wine_stock_snapshots')
            ->where('user_id', self::WINERY_USER_ID)
            ->delete();
    }
}
