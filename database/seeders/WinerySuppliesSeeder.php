<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea el inventario de suministros de bodega (productos enológicos, limpieza, etc.).
 * Depende de: (ninguna)
 */
class WinerySuppliesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now     = now();
        $litroId = DB::table('units_of_measurement')->where('symbol', 'L')->value('id');
        $kgId    = DB::table('units_of_measurement')->where('symbol', 'kg')->value('id');
        $gId     = DB::table('units_of_measurement')->where('symbol', 'g')->value('id');
        $uId     = DB::table('units_of_measurement')->first()?->id; // fallback

        // [name, commercial_name, supply_type, unit_id, current_stock, min_stock, expiry_months_from_now, notes]
        $supplies = [
            ['Metabisulfito de Potasio', 'Vinifloc K50',        'sulfiting',   $kgId,    45.50,  10.0,  18, 'SO2 libre 57,5%. Uso: 10-15g/hL en trasiegos.'],
            ['Ácido Tartárico',          'Acidex',              'acid',        $kgId,    30.00,  5.0,   24, 'Para corrección de acidez total. Dosis máx 1,5g/L.'],
            ['Bentonita Sódica',         'Bentofine',           'fining',      $kgId,    22.00,  5.0,   36, 'Clarificante proteico. Dosis: 30-80g/hL.'],
            ['Levadura Seca Activa',     'Lalvin EC-1118',      'yeast',       $gId,  8500.00, 500.0,   6, 'Levadura para segunda fermentación. Alta tolerancia al SO2.'],
            ['Levadura Seca Activa',     'Lalvin 71B',          'yeast',       $gId,  5200.00, 500.0,   6, 'Ideal para blancos y rosados jóvenes. Esteres afrutados.'],
            ['Nutriente de Levadura',    'Fermaid K',           'nutrient',    $gId,  3600.00, 200.0,   8, 'Nutriente complejo: DAP + vitaminas + micronutrientes.'],
            ['Enzima Pectolítica',       'Lallzyme EX-V',       'enzyme',      $gId,  1200.00, 100.0,  12, 'Para maceraciones pre-fermentativas y extracción de color.'],
            ['Tanino Enológico',         'Tanin Galalcool',     'tannin',      $gId,  2400.00, 200.0,  24, 'Estabilización del color y estructura tánica en tintos.'],
            ['Gelatina Enológica',       'Gelatina 240 Bloom',  'fining',      $kgId,     8.50,  2.0,   6, 'Clarificante para vinos tintos. Dosis: 2-6g/hL.'],
            ['Caseinato Potásico',       'Casein Super',        'fining',      $kgId,    12.00,  3.0,   9, 'Fijador de oxidaciones en blancos. Dosis: 20-40g/hL.'],
            ['Soda Cáustica',            'NaOH Bodega 30%',    'cleaning',    $litroId,  80.00, 20.0,  12, 'Limpieza de depósitos y tuberías. Uso con EPI obligatorio.'],
            ['Ácido Peracético',         'Divosan Forte',       'cleaning',    $litroId,  40.00, 10.0,   6, 'Desinfectante sin enjuague para superficies alimentarias.'],
            ['Cartuchos de Filtración',  'Pall Profile II 1μm', 'filtration',  $uId,      48.00,  6.0,  24, 'Filtración de abrillantado pre-embotellado.'],
            ['Azúcar de Caña',           'Sacarosa Rectificada','sugar',       $kgId,    200.00, 25.0,  24, 'Chaptalización (si autorizado). Solo campañas con bajo grado.'],
            ['Reactivo Análisis SO2',    'Kit SO2 Ripper',      'analysis',    $uId,       6.00,  2.0,   3, 'Test rápido de SO2 libre en bodega. 6 unidades por caja.'],
        ];

        $rows = [];
        foreach ($supplies as $s) {
            $expiry = now()->addMonths($s[6])->format('Y-m-d');
            $rows[] = [
                'user_id'               => self::WINERY_USER_ID,
                'name'                  => $s[0],
                'commercial_name'       => $s[1],
                'supply_type'           => $s[2],
                'unit_of_measurement_id'=> $s[3],
                'current_stock'         => $s[4],
                'min_stock_alert'       => $s[5],
                'expiry_date'           => $expiry,
                'notes'                 => $s[7],
                'active'                => true,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
        }

        DB::table('winery_supplies')->insert($rows);

        $this->command->info('✅ Suministros de bodega: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('winery_supplies')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
