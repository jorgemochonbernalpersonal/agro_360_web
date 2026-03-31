<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 150 compras externas de uva/mosto distribuidas en campañas 2023-2025.
 * Variado realista: uva, mosto, mosto concentrado y mosto concentrado rectificado.
 * Proveedores canarios y peninsulares, variedades autóctonas y foráneas.
 */
class WineryExternalPurchasesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // ── Catálogos ────────────────────────────────────────────────────────────────

    private const SUPPLIERS = [
        ['name' => 'Cooperativa Viticultores del Norte de Gran Canaria', 'origin_hint' => 'Gran Canaria — Zona Norte'],
        ['name' => 'Bodegas Tafuriaste S.L.',                           'origin_hint' => 'Lanzarote — D.O. Lanzarote'],
        ['name' => 'Cooperativa Agrícola El Hierro',                    'origin_hint' => 'El Hierro — D.O. El Hierro'],
        ['name' => 'Vinícola del Teide',                                'origin_hint' => 'Tenerife — D.O. Ycoden-Daute-Isora'],
        ['name' => 'Bodegas Frontón de Oro',                            'origin_hint' => 'Gran Canaria — Telde'],
        ['name' => 'Cooperativa San Telmo de La Palma',                 'origin_hint' => 'La Palma — D.O. La Palma'],
        ['name' => 'Bodegas Tajinastes S.L.',                           'origin_hint' => 'Tenerife — D.O. Valle de la Orotava'],
        ['name' => 'Coop. Agrícola Gomera Sur',                         'origin_hint' => 'La Gomera — D.O. Valle Gran Rey'],
        ['name' => 'Viñedos Bentayga S.L.',                             'origin_hint' => 'Gran Canaria — Tejeda'],
        ['name' => 'Bodegas Cumbres de Abona',                          'origin_hint' => 'Tenerife — D.O. Abona'],
        ['name' => 'Viñedos Insulares S.A.',                            'origin_hint' => 'Gran Canaria — Agaete'],
        ['name' => 'Cooperativa Vitícola Taganana',                     'origin_hint' => 'Tenerife — D.O. Tacoronte-Acentejo'],
        ['name' => 'Mosto Concentrado Ibérico S.L.',                    'origin_hint' => 'Extremadura — D.O. Ribera del Guadiana'],
        ['name' => 'Enológica Peninsular S.A.',                         'origin_hint' => 'La Rioja — D.O.Ca. Rioja'],
    ];

    private const VARIETIES = [
        // Tintas
        ['variety' => 'Listán Negro',       'wine_type' => 'tinto',  'base_baume' => 13.0, 'base_brix' => 23.5, 'base_ph' => 3.45, 'base_acid' => 5.8],
        ['variety' => 'Negramoll',           'wine_type' => 'tinto',  'base_baume' => 13.2, 'base_brix' => 24.0, 'base_ph' => 3.48, 'base_acid' => 5.6],
        ['variety' => 'Vijariego Negro',     'wine_type' => 'tinto',  'base_baume' => 13.8, 'base_brix' => 25.0, 'base_ph' => 3.52, 'base_acid' => 5.4],
        ['variety' => 'Tintilla de Rota',    'wine_type' => 'tinto',  'base_baume' => 14.2, 'base_brix' => 25.8, 'base_ph' => 3.55, 'base_acid' => 5.2],
        ['variety' => 'Malvasía Rosada',     'wine_type' => 'rosado', 'base_baume' => 13.5, 'base_brix' => 24.5, 'base_ph' => 3.42, 'base_acid' => 6.0],
        ['variety' => 'Garnacha Tintorera',  'wine_type' => 'tinto',  'base_baume' => 14.0, 'base_brix' => 25.5, 'base_ph' => 3.50, 'base_acid' => 5.3],
        // Blancas
        ['variety' => 'Listán Blanco',       'wine_type' => 'blanco', 'base_baume' => 12.5, 'base_brix' => 22.5, 'base_ph' => 3.38, 'base_acid' => 6.2],
        ['variety' => 'Malvasía Volcánica',  'wine_type' => 'blanco', 'base_baume' => 12.8, 'base_brix' => 23.2, 'base_ph' => 3.40, 'base_acid' => 6.0],
        ['variety' => 'Vijariego Blanco',    'wine_type' => 'blanco', 'base_baume' => 12.2, 'base_brix' => 22.0, 'base_ph' => 3.35, 'base_acid' => 6.5],
        ['variety' => 'Gual',                'wine_type' => 'blanco', 'base_baume' => 13.0, 'base_brix' => 23.5, 'base_ph' => 3.42, 'base_acid' => 6.1],
        ['variety' => 'Marmajuelo',          'wine_type' => 'blanco', 'base_baume' => 12.0, 'base_brix' => 21.8, 'base_ph' => 3.32, 'base_acid' => 6.8],
        ['variety' => 'Albillo Criollo',     'wine_type' => 'blanco', 'base_baume' => 12.6, 'base_brix' => 22.8, 'base_ph' => 3.37, 'base_acid' => 6.3],
        ['variety' => 'Moscatel de Alejandría', 'wine_type' => 'blanco', 'base_baume' => 14.5, 'base_brix' => 26.5, 'base_ph' => 3.55, 'base_acid' => 5.5],
        ['variety' => 'Pedro Ximénez',       'wine_type' => 'blanco', 'base_baume' => 15.0, 'base_brix' => 27.0, 'base_ph' => 3.60, 'base_acid' => 4.8],
    ];

    // [product_type, weight_%_of_total, price_range, qty_range_kg, has_quality_params]
    private const PRODUCT_TYPE_DIST = [
        ['type' => 'grape',             'weight' => 65, 'price_min' => 0.75, 'price_max' => 1.60, 'qty_min' => 500,  'qty_max' => 8000,  'params' => true],
        ['type' => 'must',              'weight' => 20, 'price_min' => 0.90, 'price_max' => 1.80, 'qty_min' => 300,  'qty_max' => 4000,  'params' => true],
        ['type' => 'concentrated_must', 'weight' =>  9, 'price_min' => 1.80, 'price_max' => 3.00, 'qty_min' => 200,  'qty_max' => 1500,  'params' => false],
        ['type' => 'rectified_must',    'weight' =>  6, 'price_min' => 2.50, 'price_max' => 4.50, 'qty_min' => 100,  'qty_max' => 800,   'params' => false],
    ];

    // ── Campaigns ────────────────────────────────────────────────────────────────

    private const CAMPAIGNS = [
        ['year' => 2023, 'count' => 45, 'statuses' => ['processed' => 80, 'rejected' => 15, 'received' => 5,  'pending' => 0]],
        ['year' => 2024, 'count' => 55, 'statuses' => ['processed' => 70, 'rejected' => 10, 'received' => 15, 'pending' => 5]],
        ['year' => 2025, 'count' => 50, 'statuses' => ['processed' => 40, 'rejected' =>  5, 'received' => 30, 'pending' => 25]],
    ];

    // ── Notes by product type ────────────────────────────────────────────────────

    private const NOTES = [
        'grape' => [
            'Uva para coupage. Calidad comprobada en finca.',
            'Vendimia nocturna. Transporte en cajas de 15 kg.',
            'Uva seleccionada manualmente. Sanidad perfecta.',
            'Para vino de guarda. Alto potencial de crianza.',
            'Parcela ecológica certificada. Sin tratamientos desde 30 días.',
            'Uva de viña vieja (+30 años). Baja producción, alta concentración.',
            'Compra de última hora por escasez propia.',
            'Uva para elaboración de rosado. Corte precoz.',
        ],
        'must' => [
            'Mosto para blend y equilibrado de acidez.',
            'Mosto flor de primera presión.',
            'Para refuerzo alcohólico natural del lote principal.',
            'Mosto ecológico certificado por CRAEGA.',
            'Adquirido para completar volumen de campaña.',
        ],
        'concentrated_must' => [
            'Mosto concentrado para corrección de grado alcohólico.',
            'Uso enológico. Homologado por D.O.',
            'Corrección de déficit de azúcares en vendimia temprana.',
        ],
        'rectified_must' => [
            'Mosto concentrado rectificado sin colorantes ni aromas.',
            'Para chaptalización autorizada por el Consejo Regulador.',
            'Corrección puntual. Lote identificado en cuaderno de bodega.',
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->cleanup();

        $supplierIds = DB::table('suppliers')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $containerIds = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        $now  = now();
        $rows = [];
        $idx  = 0; // deterministic index for cycling arrays

        foreach (self::CAMPAIGNS as $campaign) {
            $year     = $campaign['year'];
            $count    = $campaign['count'];
            $statuses = $this->expandStatuses($campaign['statuses'], $count);
            shuffle($statuses);

            // Campaign harvest window: mid-August to end-October
            $campaignStart = \Carbon\Carbon::create($year, 8, 15);
            $campaignEnd   = \Carbon\Carbon::create($year, 10, 31);

            for ($i = 0; $i < $count; $i++) {
                $statusStr   = $statuses[$i];
                $productDef  = $this->pickProductType($idx);
                $variety     = self::VARIETIES[$idx % count(self::VARIETIES)];
                $supplierDef = self::SUPPLIERS[$idx % count(self::SUPPLIERS)];

                $purchaseDate = $this->randomDateBetween($campaignStart, $campaignEnd);
                $deliveryDate = $this->deliveryDate($statusStr, $purchaseDate);

                $quantityKg  = $this->randomInRange($productDef['qty_min'], $productDef['qty_max'], 50);
                $pricePerKg  = $this->randomDecimal($productDef['price_min'], $productDef['price_max'], 4);
                $totalPrice  = round($quantityKg * $pricePerKg, 2);

                [$baume, $brix, $potAlcohol, $acidity, $ph] = $productDef['params']
                    ? $this->qualityParams($variety, $idx)
                    : [null, null, null, null, null];

                $supplierId   = $supplierIds[$idx % max(1, count($supplierIds))] ?? null;
                $wineId       = ($statusStr === 'processed' && !empty($wineIds))
                    ? $wineIds[$idx % count($wineIds)]
                    : null;
                $containerId  = ($statusStr !== 'pending' && !empty($containerIds))
                    ? $containerIds[$idx % count($containerIds)]
                    : null;

                $notes = self::NOTES[$productDef['type']][$idx % count(self::NOTES[$productDef['type']])];

                $rows[] = [
                    'user_id'                  => self::WINERY_USER_ID,
                    'supplier_id'              => $supplierId,
                    'destination_container_id' => $containerId,
                    'wine_id'                  => $wineId,
                    'supplier_name'            => $supplierDef['name'],
                    'product_type'             => $productDef['type'],
                    'variety'                  => $variety['variety'],
                    'origin'                   => $supplierDef['origin_hint'],
                    'vintage_year'             => $year,
                    'quantity_kg'              => $quantityKg,
                    'price_per_kg'             => $pricePerKg,
                    'total_price'              => $totalPrice,
                    'purchase_date'            => $purchaseDate->format('Y-m-d'),
                    'delivery_date'            => $deliveryDate?->format('Y-m-d'),
                    'rega_code'                => $this->reGaCode($idx),
                    'quality_certificate'      => $this->qualityCert($idx, $statusStr),
                    'baume_degree'             => $baume,
                    'brix_degree'              => $brix,
                    'potential_alcohol'        => $potAlcohol,
                    'acidity_level'            => $acidity,
                    'ph_level'                 => $ph,
                    'status'                   => $statusStr,
                    'notes'                    => $notes,
                    'created_by'               => self::WINERY_USER_ID,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];

                $idx++;
            }
        }

        DB::table('external_grape_purchases')->insert($rows);

        $total    = count($rows);
        $byStatus = array_count_values(array_column($rows, 'status'));
        $byType   = array_count_values(array_column($rows, 'product_type'));

        $this->command->info("✅ Compras externas: {$total} registros");
        $this->command->line('   Por estado: ' . collect($byStatus)->map(fn ($v, $k) => "{$k}={$v}")->implode(', '));
        $this->command->line('   Por tipo:   ' . collect($byType)->map(fn ($v, $k) => "{$k}={$v}")->implode(', '));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function expandStatuses(array $weights, int $total): array
    {
        $result = [];
        foreach ($weights as $status => $pct) {
            $n = (int) round($total * $pct / 100);
            $result = array_merge($result, array_fill(0, $n, $status));
        }
        // Fill any rounding gap with the most common status
        while (count($result) < $total) {
            $result[] = array_key_first($weights);
        }
        return array_slice($result, 0, $total);
    }

    private function pickProductType(int $idx): array
    {
        // Build weighted array
        $pool = [];
        foreach (self::PRODUCT_TYPE_DIST as $def) {
            $pool = array_merge($pool, array_fill(0, $def['weight'], $def));
        }
        return $pool[$idx % count($pool)];
    }

    private function qualityParams(array $variety, int $idx): array
    {
        $offset = ($idx % 7) - 3; // -3..+3 variation
        $baume  = round($variety['base_baume'] + $offset * 0.1, 2);
        $brix   = round($variety['base_brix']  + $offset * 0.2, 2);
        $ph     = round($variety['base_ph']    + $offset * 0.01, 2);
        $acid   = round($variety['base_acid']  - $offset * 0.05, 2);
        $alc    = round($baume * 1.035, 2);

        return [$baume, $brix, $alc, $acid, $ph];
    }

    private function randomDateBetween(\Carbon\Carbon $start, \Carbon\Carbon $end): \Carbon\Carbon
    {
        $diff = $end->diffInDays($start);
        return $start->copy()->addDays((int) ($diff > 0 ? mt_rand(0, $diff) : 0));
    }

    private function deliveryDate(string $status, \Carbon\Carbon $purchaseDate): ?\Carbon\Carbon
    {
        return match ($status) {
            'pending'   => null,
            'rejected'  => $purchaseDate->copy()->addDays(mt_rand(1, 5)),
            default     => $purchaseDate->copy()->addDays(mt_rand(1, 3)),
        };
    }

    private function randomInRange(int $min, int $max, int $step = 1): float
    {
        $steps = (int) (($max - $min) / $step);
        return (float) ($min + mt_rand(0, $steps) * $step);
    }

    private function randomDecimal(float $min, float $max, int $decimals = 4): float
    {
        $factor = 10 ** $decimals;
        return round(mt_rand((int)($min * $factor), (int)($max * $factor)) / $factor, $decimals);
    }

    private function reGaCode(int $idx): ?string
    {
        // ~60% tienen REGA
        if ($idx % 5 === 0) {
            return null;
        }
        $province = ['35', '38', '35', '38', '07', '35'][$idx % 6];
        $num = str_pad((($idx * 13) % 9000) + 1000, 6, '0', STR_PAD_LEFT);
        return "ES{$province}{$num}GAN";
    }

    private function qualityCert(int $idx, string $status): ?string
    {
        if ($status === 'pending' || $idx % 4 === 0) {
            return null;
        }
        $certs = ['CCPAE-2024', 'CRAEGA-2024', 'CAAE-2024', 'BIOLCAN-2024', null, null];
        return $certs[$idx % count($certs)];
    }

    private function cleanup(): void
    {
        DB::table('external_grape_purchases')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
