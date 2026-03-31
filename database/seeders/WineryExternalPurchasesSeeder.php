<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryExternalPurchasesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $supplierIds = DB::table('suppliers')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $now  = now();
        $rows = [
            [
                'user_id'          => self::WINERY_USER_ID,
                'supplier_id'      => $supplierIds[0] ?? null,
                'supplier_name'    => 'Cooperativa Viticultores del Norte de Gran Canaria',
                'product_type'     => 'grape',
                'variety'          => 'Listán Negro',
                'origin'           => 'Gran Canaria — Zona Norte',
                'vintage_year'     => 2025,
                'quantity_kg'      => 4500.000,
                'price_per_kg'     => 0.9500,
                'total_price'      => 4275.00,
                'purchase_date'    => now()->subDays(60)->toDateString(),
                'delivery_date'    => now()->subDays(58)->toDateString(),
                'baume_degree'     => 13.2,
                'brix_degree'      => 24.0,
                'potential_alcohol'=> 13.5,
                'acidity_level'    => 5.8,
                'ph_level'         => 3.45,
                'status'           => 'processed',
                'notes'            => 'Uva para coupage tinto joven. Calidad buena.',
                'created_by'       => self::WINERY_USER_ID,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'user_id'          => self::WINERY_USER_ID,
                'supplier_id'      => $supplierIds[1] ?? null,
                'supplier_name'    => 'Bodegas Tafuriaste S.L.',
                'product_type'     => 'must',
                'variety'          => 'Malvasía Volcánica',
                'origin'           => 'Lanzarote — D.O. Lanzarote',
                'vintage_year'     => 2025,
                'quantity_kg'      => 2000.000,
                'price_per_kg'     => 1.2000,
                'total_price'      => 2400.00,
                'purchase_date'    => now()->subDays(45)->toDateString(),
                'delivery_date'    => now()->subDays(43)->toDateString(),
                'baume_degree'     => 12.8,
                'brix_degree'      => 23.5,
                'potential_alcohol'=> 13.1,
                'acidity_level'    => 6.2,
                'ph_level'         => 3.38,
                'status'           => 'processed',
                'notes'            => 'Mosto para blend malvasía dulce.',
                'created_by'       => self::WINERY_USER_ID,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'user_id'          => self::WINERY_USER_ID,
                'supplier_id'      => $supplierIds[2] ?? null,
                'supplier_name'    => 'Cooperativa Agrícola El Hierro',
                'product_type'     => 'grape',
                'variety'          => 'Negramoll',
                'origin'           => 'El Hierro — D.O. El Hierro',
                'vintage_year'     => 2025,
                'quantity_kg'      => 1800.000,
                'price_per_kg'     => 1.1000,
                'total_price'      => 1980.00,
                'purchase_date'    => now()->subDays(30)->toDateString(),
                'delivery_date'    => now()->subDays(28)->toDateString(),
                'baume_degree'     => 13.5,
                'brix_degree'      => 24.5,
                'potential_alcohol'=> 13.8,
                'acidity_level'    => 5.5,
                'ph_level'         => 3.50,
                'status'           => 'received',
                'notes'            => 'Uva Negramoll para rosado especial.',
                'created_by'       => self::WINERY_USER_ID,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'user_id'          => self::WINERY_USER_ID,
                'supplier_id'      => null,
                'supplier_name'    => 'Vinícola del Teide',
                'product_type'     => 'concentrated_must',
                'variety'          => 'Listán Blanco',
                'origin'           => 'Tenerife — D.O. Tacoronte-Acentejo',
                'vintage_year'     => 2024,
                'quantity_kg'      => 800.000,
                'price_per_kg'     => 2.5000,
                'total_price'      => 2000.00,
                'purchase_date'    => now()->subDays(90)->toDateString(),
                'delivery_date'    => now()->subDays(88)->toDateString(),
                'baume_degree'     => null,
                'brix_degree'      => null,
                'potential_alcohol'=> null,
                'acidity_level'    => null,
                'ph_level'         => null,
                'status'           => 'processed',
                'notes'            => 'Mosto concentrado para corrección.',
                'created_by'       => self::WINERY_USER_ID,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'user_id'          => self::WINERY_USER_ID,
                'supplier_id'      => $supplierIds[0] ?? null,
                'supplier_name'    => 'Cooperativa Viticultores del Norte de Gran Canaria',
                'product_type'     => 'grape',
                'variety'          => 'Vijariego Negro',
                'origin'           => 'Gran Canaria — Agaete',
                'vintage_year'     => 2025,
                'quantity_kg'      => 600.000,
                'price_per_kg'     => 1.4000,
                'total_price'      => 840.00,
                'purchase_date'    => now()->subDays(10)->toDateString(),
                'delivery_date'    => null,
                'baume_degree'     => 14.0,
                'brix_degree'      => 25.5,
                'potential_alcohol'=> 14.3,
                'acidity_level'    => 5.2,
                'ph_level'         => 3.55,
                'status'           => 'pending',
                'notes'            => 'Pedido pendiente de recepción. Variedad autóctona Agaete.',
                'created_by'       => self::WINERY_USER_ID,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::table('external_grape_purchases')->insert($rows);
        $this->command->info('✅ Compras uva/mosto externo: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('external_grape_purchases')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
