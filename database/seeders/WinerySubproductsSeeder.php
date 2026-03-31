<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea subproductos de bodega (orujos, lías, vinazas).
 * Depende de: WineryWinesSeeder
 */
class WinerySubproductsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now    = now();
        $unitId = $this->getKgUnitId();

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereNotIn('status', ['cancelled'])
            ->get(['id', 'vintage', 'wine_type', 'internal_code', 'initial_quantity_kg'])
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('No hay vinos. Ejecuta WineryWinesSeeder primero.');
            return;
        }

        $destinatarios = [
            'Destilería Artesanal Canaria S.L.',
            'AGRO-COMPOST Canarias',
            'Planta Autorizada de Residuos Orgánicos Las Palmas',
        ];

        $subproductTypes = [
            // [type, destination, destination_name_idx, qty_pct]
            ['orujo',  'distillery',       0, 0.12],  // ~12% del peso de uva
            ['lias',   'authorized_plant', 2, 0.03],  // ~3% del volumen en kg
            ['vinaza', 'authorized_plant', 2, 0.05],  // ~5%
            ['orujo',  'own_use',          null, 0.02],  // compostaje propio
        ];

        $rows = [];
        $idx  = 0;

        foreach ($wines as $wine) {
            $stData   = $subproductTypes[$idx % count($subproductTypes)];
            $qty      = round(($wine->initial_quantity_kg ?? 5000) * $stData[3], 3);
            $daysAgo  = mt_rand(10, 150);

            $rows[] = [
                'user_id'               => self::WINERY_USER_ID,
                'wine_id'               => $wine->id,
                'type'                  => $stData[0],
                'subproduct_date'       => now()->subDays($daysAgo)->format('Y-m-d'),
                'quantity'              => max($qty, 50),
                'unit_of_measurement_id'=> $unitId,
                'destination'           => $stData[1],
                'destination_name'      => $stData[2] !== null ? $destinatarios[$stData[2]] : null,
                'lot_number'            => 'SUB-' . $wine->internal_code . '-' . $wine->vintage . '-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'notes'                 => match ($stData[0]) {
                    'orujo'  => 'Orujo escurrido separado tras primer prensado. Entregado con albarán.',
                    'lias'   => 'Lías gruesas post-trasiego. Gestionadas como subproducto autorizado.',
                    'vinaza' => 'Aguas de lavado de depósitos. Tratamiento externo en planta autorizada.',
                    default  => null,
                },
                'created_by'            => self::WINERY_USER_ID,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];

            $idx++;
        }

        DB::table('wine_subproducts')->insert($rows);

        $this->command->info('✅ Subproductos: ' . count($rows) . ' registros');
    }

    private function getKgUnitId(): ?int
    {
        return DB::table('units_of_measurement')->where('symbol', 'kg')->value('id')
            ?? DB::table('units_of_measurement')->first()?->id;
    }

    private function cleanup(): void
    {
        DB::table('wine_subproducts')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
