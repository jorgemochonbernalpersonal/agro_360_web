<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera datos densos para SILICIE e INFOVI en 4 años (2023-2026).
 *
 * Crea:
 *  - Vinos adicionales para 2026 + refuerzo 2023/2024 (~15 vinos extra)
 *  - wine_process_details: elaboración coherente por vino (~150+ registros)
 *  - wine_stock_snapshots: trimestrales desde 2023 hasta 2026 (~100+ registros)
 *  - Facturas suplementarias para 2023/2024 (SILICIE Libro IV vacío sin esto)
 *  - Compras externas para 2026 (SILICIE Libro I vacío sin esto)
 *  - Mermas adicionales distribuidas 2023-2026
 *  - Subproductos distribuidos 2023-2026
 *
 * Depende de: WineryWinesSeeder, WineryContainersSeeder, WineryClientsSeeder.
 */
class WinerySilicieDataSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const PIPELINES = [
        'red' => [
            ['destemming_crushing', 0, 0,   1.05],
            ['maceration',         3, 10,   1.00],
            ['fermentation',       7, 21,   0.98],
            ['pressing',           0, 1,    0.95],
            ['malolactic',        14, 30,   0.94],
            ['sulfitation',        0, 0,    0.94],
            ['racking',            0, 1,    0.93],
            ['aging',             90, 365,  0.92],
            ['fining',             7, 14,   0.91],
            ['filtration',         0, 1,    0.90],
        ],
        'white' => [
            ['pressing',           0, 0,    0.70],
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
            ['fermentation',      30, 60,   0.64],
            ['aging',             90, 270,  0.63],
            ['filtration',         0, 1,    0.62],
        ],
        'sweet' => [
            ['pressing',           0, 0,    0.65],
            ['settling',           1, 2,    0.63],
            ['fermentation',       5, 10,   0.62],
            ['sulfitation',        0, 0,    0.62],
            ['cold_stabilization', 7, 14,   0.61],
            ['fining',             5, 10,   0.60],
            ['filtration',         0, 1,    0.59],
        ],
    ];

    private const PROCESS_NOTES = [
        'destemming_crushing'  => ['Despalillado mecánico. Estrujado suave.', 'Despalillado total. Rodillos a 2 rpm.'],
        'pressing'             => ['Prensado neumático a 1.2 bar.', 'Prensado suave. Tres ciclos.'],
        'settling'             => ['Desfangado estático 12h a 10°C.', 'Desfangado enzimático 18h a 8°C.'],
        'fermentation'         => ['Fermentación controlada a 16°C.', 'Fermentación a 26°C con remontados.', 'Densidad final 0.992.'],
        'maceration'           => ['Maceración en frío 48h.', 'Maceración post-fermentativa 5 días.'],
        'malolactic'           => ['FML completada. Ác. málico <0.1 g/L.', 'Inoculación con Oenococcus oeni.'],
        'aging'                => ['Barrica roble francés 225L tostado medio.', 'Crianza 6 meses roble americano.', 'Sobre lías con bâtonnage.'],
        'racking'              => ['Trasiego a inox. Separación lías gruesas.', 'Trasiego con sulfitado. SO2 30 mg/L.'],
        'blending'             => ['Coupage 60/40 aprobado.', 'Mezcla bases para 2ª fermentación.'],
        'fining'               => ['Bentonita 40 g/hL. Estabilidad proteica OK.', 'Albúmina de huevo.'],
        'filtration'           => ['Placas 1 μm. Brillantez confirmada.', 'Amicróbica 0.45 μm pre-embotellado.'],
        'cold_stabilization'   => ['Tartárica a -4°C, 10 días.', 'CMC (carboximetilcelulosa).'],
        'sulfitation'          => ['SO2 libre: 25 mg/L, total: 85 mg/L.', 'Ajuste post-FML a 30 mg/L.'],
        'bottling'             => ['Llenado con N2. Corcho natural 45mm.', 'Tapón rosca. Lote verificado.'],
    ];

    // ── Vinos adicionales para densificar SILICIE ────────────────────────────
    private const EXTRA_WINES = [
        // 2026 (campaña actual)
        ['name' => 'Listán Negro Joven 2026',    'type' => 'red',       'vintage' => 2026, 'status' => 'in_progress', 'vol' => 22000, 'must' => false, 'cat' => 'DO'],
        ['name' => 'Negramoll Maceración 2026',   'type' => 'red',       'vintage' => 2026, 'status' => 'in_progress', 'vol' => 8500,  'must' => false, 'cat' => 'DO'],
        ['name' => 'Vijariego Blanco 2026',        'type' => 'white',     'vintage' => 2026, 'status' => 'in_progress', 'vol' => 12000, 'must' => false, 'cat' => 'DO'],
        ['name' => 'Malvasía Fermentación 2026',   'type' => 'white',     'vintage' => 2026, 'status' => 'in_progress', 'vol' => 6800,  'must' => false, 'cat' => 'DO'],
        ['name' => 'Rosado Negramoll 2026',        'type' => 'rose',      'vintage' => 2026, 'status' => 'in_progress', 'vol' => 4200,  'must' => false, 'cat' => 'IGP'],
        ['name' => 'Mosto Listán 2026',            'type' => 'white',     'vintage' => 2026, 'status' => 'in_progress', 'vol' => 5500,  'must' => true,  'cat' => 'VdM'],
        ['name' => 'Tinto Ecológico 2026',         'type' => 'red',       'vintage' => 2026, 'status' => 'in_progress', 'vol' => 7200,  'must' => false, 'cat' => 'DO'],
        ['name' => 'Blanco Marmajuelo 2026',       'type' => 'white',     'vintage' => 2026, 'status' => 'in_progress', 'vol' => 3800,  'must' => false, 'cat' => 'IGP'],
        // 2023 refuerzo (más variedad)
        ['name' => 'Tinto Reserva Agaete 2023',   'type' => 'red',       'vintage' => 2023, 'status' => 'bottled',     'vol' => 3500,  'must' => false, 'cat' => 'DO'],
        ['name' => 'Rosado Listán 2023',           'type' => 'rose',      'vintage' => 2023, 'status' => 'sold',        'vol' => 0,     'must' => false, 'cat' => 'IGP'],
        ['name' => 'Blanco Seco Agaete 2023',     'type' => 'white',     'vintage' => 2023, 'status' => 'bottled',     'vol' => 4200,  'must' => false, 'cat' => 'DO'],
        // 2024 refuerzo
        ['name' => 'Espumoso Brut Agaete 2024',   'type' => 'sparkling', 'vintage' => 2024, 'status' => 'aged',        'vol' => 2100,  'must' => false, 'cat' => 'DO'],
        ['name' => 'Tinto Crianza Agaete 2024',   'type' => 'red',       'vintage' => 2024, 'status' => 'aged',        'vol' => 5800,  'must' => false, 'cat' => 'DO'],
        ['name' => 'Dulce Malvasía 2024',          'type' => 'sweet',     'vintage' => 2024, 'status' => 'bottled',     'vol' => 1500,  'must' => false, 'cat' => 'DO'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ═══════════════════════════════════════════════════════════════════
        // 0. VINOS ADICIONALES
        // ═══════════════════════════════════════════════════════════════════

        $oenologistId = DB::table('oenologists')
            ->where('user_id', self::WINERY_USER_ID)
            ->value('id');

        $extraWineIds = [];
        foreach (self::EXTRA_WINES as $w) {
            $extraWineIds[] = DB::table('wines')->insertGetId([
                'user_id'          => self::WINERY_USER_ID,
                'oenologist_id'    => $oenologistId,
                'name'             => $w['name'],
                'wine_type'        => $w['type'],
                'vintage'          => $w['vintage'],
                'status'           => $w['status'],
                'volume_liters'    => $w['vol'],
                'is_must'          => $w['must'],
                'category'         => $w['cat'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
        $this->command->info("  ✅ Vinos adicionales: " . count($extraWineIds) . " registros (2023-2026)");

        // ═══════════════════════════════════════════════════════════════════
        // 1. WINE PROCESS DETAILS (todos los vinos de la bodega)
        // ═══════════════════════════════════════════════════════════════════

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('status', '!=', 'cancelled')
            ->get();

        $containers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        $unitLiters = DB::table('units_of_measurement')
            ->where('symbol', 'L')
            ->value('id');

        $totalProcesses = 0;
        $cidx = 0;

        foreach ($wines as $wine) {
            $pipelineKey = match ($wine->wine_type) {
                'red'                 => 'red',
                'white'               => 'white',
                'rose'                => 'rose',
                'sparkling'           => 'sparkling',
                'sweet', 'semi_sweet' => 'sweet',
                'fortified'           => 'red',
                default               => 'white',
            };

            $pipeline = self::PIPELINES[$pipelineKey];
            $volume   = (float) $wine->volume_liters;
            $vintage  = (int) $wine->vintage;

            $stepCount = match ($wine->status) {
                'in_progress' => max(2, (int) (count($pipeline) * 0.4)),
                'aged'        => max(3, (int) (count($pipeline) * 0.7)),
                default       => count($pipeline),
            };

            $startDate = \Carbon\Carbon::create($vintage, 9, mt_rand(1, 20));

            for ($s = 0; $s < $stepCount && $s < count($pipeline); $s++) {
                [$processType, $durMin, $durMax, $qtyFactor] = $pipeline[$s];
                $duration = mt_rand($durMin, max($durMin, $durMax));
                $endDate  = (clone $startDate)->addDays($duration);
                $quantity = $volume > 0 ? round($volume * $qtyFactor, 3) : null;

                $notes = self::PROCESS_NOTES[$processType] ?? ['Proceso registrado.'];

                DB::table('wine_process_details')->insert([
                    'wine_id'                => $wine->id,
                    'container_id'           => !empty($containers) ? $containers[$cidx % count($containers)] : null,
                    'process_type'           => $processType,
                    'start_date'             => $startDate->toDateString(),
                    'end_date'               => $duration > 0 ? $endDate->toDateString() : null,
                    'quantity'               => $quantity,
                    'unit_of_measurement_id' => $unitLiters,
                    'observations'           => $notes[$cidx % count($notes)],
                    'created_by'             => self::WINERY_USER_ID,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);

                $startDate = (clone $endDate)->addDays(mt_rand(1, 3));
                $totalProcesses++;
                $cidx++;
            }

            // Embotellado para vinos terminados
            if (in_array($wine->status, ['bottled', 'sold'])) {
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

        $this->command->info("  ✅ Procesos elaboración: {$totalProcesses} registros");

        // ═══════════════════════════════════════════════════════════════════
        // 2. WINE STOCK SNAPSHOTS (trimestrales 2023-2026)
        // ═══════════════════════════════════════════════════════════════════

        $snapshotDates = [
            // 2023
            '2023-03-31', '2023-06-30', '2023-09-30', '2023-12-31',
            // 2024
            '2024-03-31', '2024-06-30', '2024-07-31', '2024-09-30', '2024-12-31',
            // 2025
            '2025-03-31', '2025-06-30', '2025-07-31', '2025-09-30', '2025-12-31',
            // 2026
            '2026-01-31', '2026-03-31',
        ];

        $totalSnapshots = 0;

        foreach ($snapshotDates as $snapDate) {
            $snapCarbon = \Carbon\Carbon::parse($snapDate);

            foreach ($wines as $wine) {
                $vintage = (int) $wine->vintage;
                $volume  = (float) $wine->volume_liters;

                // Wine exists from ~Sep of its vintage
                $wineExistsSince = \Carbon\Carbon::create($vintage, 9, 1);
                if ($snapCarbon->lt($wineExistsSince)) continue;

                $monthsSince = $wineExistsSince->diffInMonths($snapCarbon);

                // Sold wines: only appear in snapshots near their vintage
                if ($wine->status === 'sold') {
                    if ($snapCarbon->year > $vintage + 1) continue;
                    $factor = 0.85;
                } else {
                    $factor = match (true) {
                        $monthsSince < 3  => 0.96,
                        $monthsSince < 6  => 0.93,
                        $monthsSince < 12 => 0.90,
                        $monthsSince < 24 => 0.85,
                        default           => 0.80,
                    };
                }

                $snapshotQty = round($volume * $factor + mt_rand(-50, 50), 3);
                if ($snapshotQty <= 0) continue;

                DB::table('wine_stock_snapshots')->insert([
                    'user_id'            => self::WINERY_USER_ID,
                    'wine_id'            => $wine->id,
                    'snapshot_date'      => $snapDate,
                    'quantity_liters'    => $snapshotQty,
                    'container_count'    => max(1, (int) ceil($snapshotQty / 5000)),
                    'alcohol_percentage' => round(mt_rand(115, 145) / 10, 2),
                    'vintage'            => $vintage,
                    'wine_type'          => $wine->wine_type,
                    'is_must'            => $wine->is_must ?? false,
                    'observations'       => "Inventario trimestral {$snapDate}.",
                    'created_by'         => self::WINERY_USER_ID,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $totalSnapshots++;
            }
        }

        $this->command->info("  ✅ Snapshots stock: {$totalSnapshots} registros (" . count($snapshotDates) . " fechas trimestrales)");

        // ═══════════════════════════════════════════════════════════════════
        // 3. FACTURAS SUPLEMENTARIAS (2023, 2024 — Libro IV Salidas)
        // ═══════════════════════════════════════════════════════════════════

        $clients = DB::table('clients')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $invoiceTemplates = [
            // 2023
            ['num' => 'FAC-2023-001', 'date' => '2023-03-15', 'client_idx' => 0, 'total' => 2850.00, 'items' => [
                ['name' => 'Tinto Joven 2022 — 750ml',    'qty' => 180, 'price' => 6.50],
                ['name' => 'Blanco Listán 2022 — 750ml',  'qty' => 120, 'price' => 7.00],
            ]],
            ['num' => 'FAC-2023-002', 'date' => '2023-06-20', 'client_idx' => 1, 'total' => 1980.00, 'items' => [
                ['name' => 'Espumoso Brut 2022 — 750ml',  'qty' => 60,  'price' => 18.00],
                ['name' => 'Rosado 2022 — 750ml',         'qty' => 90,  'price' => 7.50],
            ]],
            ['num' => 'FAC-2023-003', 'date' => '2023-09-10', 'client_idx' => 2, 'total' => 3200.00, 'items' => [
                ['name' => 'Tinto Barrica 2022 — 750ml',  'qty' => 144, 'price' => 11.00],
                ['name' => 'Malvasía Dulce 2022 — 500ml', 'qty' => 72,  'price' => 8.50],
            ]],
            ['num' => 'FAC-2023-004', 'date' => '2023-11-28', 'client_idx' => 3, 'total' => 4500.00, 'items' => [
                ['name' => 'Tinto Joven 2023 — 750ml',    'qty' => 300, 'price' => 7.00],
                ['name' => 'Blanco Vijariego 2023 — 750ml', 'qty' => 150, 'price' => 9.00],
            ]],
            ['num' => 'FAC-2023-005', 'date' => '2023-12-15', 'client_idx' => 4, 'total' => 2100.00, 'items' => [
                ['name' => 'Espumoso Brut 2022 — 750ml',  'qty' => 48,  'price' => 18.00],
                ['name' => 'Tinto Reserva 2022 — 750ml',  'qty' => 96,  'price' => 13.00],
            ]],
            // 2024
            ['num' => 'FAC-2024-001', 'date' => '2024-02-10', 'client_idx' => 5, 'total' => 3600.00, 'items' => [
                ['name' => 'Tinto Joven 2023 — 750ml',    'qty' => 240, 'price' => 7.00],
                ['name' => 'Blanco Barrica 2023 — 750ml', 'qty' => 96,  'price' => 12.50],
            ]],
            ['num' => 'FAC-2024-002', 'date' => '2024-04-22', 'client_idx' => 6, 'total' => 2200.00, 'items' => [
                ['name' => 'Rosado Listán 2023 — 750ml',  'qty' => 120, 'price' => 8.00],
                ['name' => 'Blanco Seco 2023 — 750ml',    'qty' => 96,  'price' => 8.50],
            ]],
            ['num' => 'FAC-2024-003', 'date' => '2024-07-05', 'client_idx' => 7, 'total' => 5100.00, 'items' => [
                ['name' => 'Tinto Ecológico 2024 — 750ml', 'qty' => 180, 'price' => 9.00],
                ['name' => 'Tinto Crianza 2023 — 750ml',  'qty' => 120, 'price' => 11.50],
                ['name' => 'Espumoso Rosé 2023 — 750ml',  'qty' => 72,  'price' => 19.00],
            ]],
            ['num' => 'FAC-2024-004', 'date' => '2024-09-18', 'client_idx' => 8, 'total' => 2800.00, 'items' => [
                ['name' => 'Malvasía Seco 2024 — 750ml',  'qty' => 144, 'price' => 10.00],
                ['name' => 'Blanco Marmajuelo 2024 — 750ml', 'qty' => 96, 'price' => 9.50],
            ]],
            ['num' => 'FAC-2024-005', 'date' => '2024-11-30', 'client_idx' => 9, 'total' => 3900.00, 'items' => [
                ['name' => 'Tinto Joven 2024 — 750ml',    'qty' => 240, 'price' => 7.50],
                ['name' => 'Rosado Agaete 2024 — 750ml',  'qty' => 120, 'price' => 8.00],
                ['name' => 'Dulce Malvasía 2024 — 500ml', 'qty' => 48,  'price' => 9.00],
            ]],
            ['num' => 'FAC-2024-006', 'date' => '2024-12-20', 'client_idx' => 10, 'total' => 4200.00, 'items' => [
                ['name' => 'Espumoso Brut 2024 — 750ml',  'qty' => 96,  'price' => 19.00],
                ['name' => 'Tinto Reserva 2023 — 750ml',  'qty' => 120, 'price' => 14.00],
            ]],
        ];

        $totalInvoices = 0;
        $totalItems    = 0;

        foreach ($invoiceTemplates as $inv) {
            $clientId = !empty($clients) ? $clients[$inv['client_idx'] % count($clients)] : null;

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'        => self::WINERY_USER_ID,
                'client_id'      => $clientId,
                'invoice_type'   => 'wine_sale',
                'invoice_number' => $inv['num'],
                'invoice_date'   => $inv['date'],
                'total_amount'   => $inv['total'],
                'status'         => 'paid',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $totalInvoices++;

            foreach ($inv['items'] as $idx => $item) {
                DB::table('invoice_items')->insert([
                    'invoice_id'  => $invoiceId,
                    'description' => $item['name'],
                    'quantity'    => $item['qty'],
                    'unit_price'  => $item['price'],
                    'unit'        => 'botella',
                    'total_price' => round($item['qty'] * $item['price'], 2),
                    'sort_order'  => $idx + 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
                $totalItems++;
            }
        }

        $this->command->info("  ✅ Facturas suplementarias: {$totalInvoices} facturas, {$totalItems} líneas (2023-2024)");

        // ═══════════════════════════════════════════════════════════════════
        // 4. COMPRAS EXTERNAS 2026 (Libro I Entradas)
        // ═══════════════════════════════════════════════════════════════════

        $varietyIds = DB::table('grape_varieties')
            ->whereIn('name', ['Listán Negro', 'Listán Blanco', 'Negramoll', 'Malvasía Volcánica'])
            ->pluck('id', 'name')
            ->toArray();

        $externalPurchases2026 = [
            ['type' => 'grape', 'supplier' => 'Cooperativa Vinícola del Norte',   'kg' => 8500,  'variety' => 'Listán Negro',      'protection' => 'DO',  'date' => '2026-09-05'],
            ['type' => 'grape', 'supplier' => 'Bodegas Hermanos Pérez',           'kg' => 5200,  'variety' => 'Negramoll',          'protection' => 'DO',  'date' => '2026-09-12'],
            ['type' => 'grape', 'supplier' => 'Finca El Tablero S.L.',            'kg' => 6800,  'variety' => 'Listán Blanco',      'protection' => 'IGP', 'date' => '2026-09-18'],
            ['type' => 'grape', 'supplier' => 'Viñedos del Barranco',             'kg' => 3200,  'variety' => 'Malvasía Volcánica', 'protection' => 'DO',  'date' => '2026-09-25'],
            ['type' => 'must',  'supplier' => 'Mostos Canarios S.A.',             'kg' => 4500,  'variety' => 'Listán Blanco',      'protection' => 'VdM', 'date' => '2026-10-02'],
            ['type' => 'grape', 'supplier' => 'Cooperativa Vinícola del Norte',   'kg' => 7100,  'variety' => 'Listán Negro',      'protection' => 'DO',  'date' => '2026-10-08'],
            ['type' => 'grape', 'supplier' => 'Finca La Aldea Viticultores',      'kg' => 4000,  'variety' => 'Negramoll',          'protection' => 'IGP', 'date' => '2026-10-15'],
            ['type' => 'must',  'supplier' => 'Mostos Canarios S.A.',             'kg' => 3000,  'variety' => 'Listán Negro',      'protection' => 'VdM', 'date' => '2026-10-20'],
        ];

        foreach ($externalPurchases2026 as $ep) {
            DB::table('external_grapes')->insert([
                'user_id'          => self::WINERY_USER_ID,
                'grape_type'       => $ep['type'],
                'supplier_name'    => $ep['supplier'],
                'total_weight_kg'  => $ep['kg'],
                'grape_variety_id' => $varietyIds[$ep['variety']] ?? null,
                'vintage_year'     => 2026,
                'protection_level' => $ep['protection'],
                'entry_date'       => $ep['date'],
                'status'           => 'processed',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        $this->command->info("  ✅ Compras externas 2026: " . count($externalPurchases2026) . " registros");

        // ═══════════════════════════════════════════════════════════════════
        // 5. MERMAS ADICIONALES distribuidas 2023-2026 (Libro IV)
        // ═══════════════════════════════════════════════════════════════════

        $lossTypes = ['evaporation', 'filtration', 'sampling', 'spillage'];
        $lossAuths = ['authorized', 'processing', 'quality'];
        $allWineIds = $wines->pluck('id')->toArray();
        $totalLosses = 0;

        foreach ([2023, 2024, 2025, 2026] as $year) {
            $yearWines = $wines->where('vintage', $year)->values();
            if ($yearWines->isEmpty()) continue;

            $lossCount = mt_rand(3, 6);
            for ($i = 0; $i < $lossCount; $i++) {
                $wine = $yearWines[$i % count($yearWines)];
                $month = mt_rand(1, 12);
                if ($year === 2026 && $month > 3) $month = mt_rand(1, 3);

                DB::table('wine_losses')->insert([
                    'wine_id'            => $wine->id,
                    'container_id'       => !empty($containers) ? $containers[mt_rand(0, count($containers) - 1)] : null,
                    'loss_type'          => $lossTypes[$i % count($lossTypes)],
                    'loss_authorization' => $lossAuths[$i % count($lossAuths)],
                    'quantity'           => round(mt_rand(15, 120), 2),
                    'loss_date'          => sprintf('%04d-%02d-%02d', $year, $month, mt_rand(1, 28)),
                    'notes'              => "Merma registrada — control periódico {$year}.",
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $totalLosses++;
            }
        }

        $this->command->info("  ✅ Mermas adicionales: {$totalLosses} registros (2023-2026)");

        // ═══════════════════════════════════════════════════════════════════
        // 6. SUBPRODUCTOS distribuidos 2023-2026 (Libro IV)
        // ═══════════════════════════════════════════════════════════════════

        $subproductTypes = ['orujo', 'lías', 'vinaza'];
        $destinations    = ['alcohol', 'animal_feed', 'composting', 'distillery'];
        $totalSubproducts = 0;

        foreach ([2023, 2024, 2025, 2026] as $year) {
            $yearWines = $wines->where('vintage', $year)->values();
            if ($yearWines->isEmpty()) continue;

            foreach ($subproductTypes as $spIdx => $spType) {
                $count = mt_rand(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    $wine  = $yearWines[$i % count($yearWines)];
                    $month = $spType === 'orujo' ? mt_rand(9, 11) : mt_rand(1, 12);
                    if ($year === 2026 && $month > 3) $month = mt_rand(1, 3);

                    DB::table('wine_subproducts')->insert([
                        'user_id'          => self::WINERY_USER_ID,
                        'wine_id'          => $wine->id,
                        'type'             => $spType,
                        'destination'      => $destinations[($spIdx + $i) % count($destinations)],
                        'destination_name' => match ($destinations[($spIdx + $i) % count($destinations)]) {
                            'distillery'  => 'Destilería Canarias S.L.',
                            'alcohol'     => 'Alcoholera del Sur',
                            'animal_feed' => 'Ganadería Tamadaba',
                            'composting'  => 'Compostaje Agro Norte',
                        },
                        'quantity'         => round(mt_rand(200, 2500), 2),
                        'subproduct_date'  => sprintf('%04d-%02d-%02d', $year, $month, mt_rand(1, 28)),
                        'lot_number'       => 'SUB-' . $year . '-' . str_pad($totalSubproducts + 1, 3, '0', STR_PAD_LEFT),
                        'notes'            => "Subproducto {$spType} campaña {$year}.",
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                    $totalSubproducts++;
                }
            }
        }

        $this->command->info("  ✅ Subproductos adicionales: {$totalSubproducts} registros (2023-2026)");
    }

    private function cleanup(): void
    {
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        if ($wineIds->isNotEmpty()) {
            DB::table('wine_process_details')->whereIn('wine_id', $wineIds)->delete();
        }

        DB::table('wine_stock_snapshots')->where('user_id', self::WINERY_USER_ID)->delete();

        // Facturas suplementarias (las que creó este seeder: FAC-2023-xxx y FAC-2024-xxx)
        $seederInvoiceIds = DB::table('invoices')
            ->where('user_id', self::WINERY_USER_ID)
            ->where(function ($q) {
                $q->where('invoice_number', 'LIKE', 'FAC-2023-%')
                  ->orWhere('invoice_number', 'LIKE', 'FAC-2024-%');
            })
            ->pluck('id');

        if ($seederInvoiceIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $seederInvoiceIds)->delete();
            DB::table('invoices')->whereIn('id', $seederInvoiceIds)->delete();
        }

        // Compras externas 2026
        DB::table('external_grapes')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('vintage_year', 2026)
            ->delete();

        // Mermas con nota del seeder
        DB::table('wine_losses')
            ->whereIn('wine_id', $wineIds)
            ->where('notes', 'LIKE', '%control periódico%')
            ->delete();

        // Subproductos con nota del seeder
        DB::table('wine_subproducts')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('notes', 'LIKE', '%Subproducto%campaña%')
            ->delete();

        // Vinos extra (los que creó este seeder — tienen nombres específicos)
        $extraNames = array_column(self::EXTRA_WINES, 'name');
        DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('name', $extraNames)
            ->delete();
    }
}
