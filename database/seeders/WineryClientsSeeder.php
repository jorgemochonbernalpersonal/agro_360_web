<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryClientsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $clients = [
            // --- Restaurantes ---
            ['client_type' => 'company', 'company_name' => 'Restaurante El Palmeral', 'company_document' => 'B76543210', 'first_name' => null, 'last_name' => null, 'email' => 'compras@elpalmeral.es', 'phone' => '+34 928 271 450', 'payment_method' => 'transfer', 'default_discount' => 5.00, 'has_cae' => false, 'active' => true, 'notes' => 'Restaurante en Las Palmas. Compra regularmente tinto joven.'],
            ['client_type' => 'company', 'company_name' => 'Hotel Rural Agaete & Spa', 'company_document' => 'B12398745', 'first_name' => null, 'last_name' => null, 'email' => 'recepcion@hotelagaete.es', 'phone' => '+34 928 898 020', 'payment_method' => 'transfer', 'default_discount' => 8.00, 'has_cae' => false, 'active' => true, 'notes' => 'Hotel de lujo en Agaete. Selección premium para carta de vinos.'],
            ['client_type' => 'company', 'company_name' => 'Vinoteca La Cava Canaria', 'company_document' => 'B34129876', 'first_name' => null, 'last_name' => null, 'email' => 'pedidos@lacavacanaria.com', 'phone' => '+34 928 364 890', 'payment_method' => 'transfer', 'default_discount' => 10.00, 'has_cae' => false, 'active' => true, 'notes' => 'Tienda especializada en vinos de Canarias. Cliente fijo mensual.'],
            ['client_type' => 'company', 'company_name' => 'Restaurante Mar de Nubes', 'company_document' => 'B98712340', 'first_name' => null, 'last_name' => null, 'email' => 'cocina@mardenubes.es', 'phone' => '+34 928 452 311', 'payment_method' => 'other', 'default_discount' => 0.00, 'has_cae' => false, 'active' => true, 'notes' => 'Restaurante en Puerto de Las Nieves. Especialidad en mariscos.'],
            ['client_type' => 'company', 'company_name' => 'Grupo Gastronómico Isla Verde', 'company_document' => 'B56712309', 'first_name' => null, 'last_name' => null, 'email' => 'administracion@islaverde.es', 'phone' => '+34 928 500 120', 'payment_method' => 'transfer', 'default_discount' => 12.00, 'has_cae' => false, 'active' => true, 'notes' => 'Cadena de 4 restaurantes en GC. Pedidos bimensuales.'],

            // --- Distribuidores ---
            ['client_type' => 'company', 'company_name' => 'Distribuciones Atlántico S.L.', 'company_document' => 'B76100234', 'first_name' => null, 'last_name' => null, 'email' => 'ventas@distatlant.es', 'phone' => '+34 928 330 450', 'payment_method' => 'transfer', 'default_discount' => 15.00, 'has_cae' => true, 'cae_number' => 'CAE-GC-2019-00234', 'active' => true, 'notes' => 'Distribuidora regional. Opera en toda Gran Canaria y Fuerteventura.'],
            ['client_type' => 'company', 'company_name' => 'Vinos del Sur Distribución', 'company_document' => 'B22345678', 'first_name' => null, 'last_name' => null, 'email' => 'pedidos@vinossur.es', 'phone' => '+34 928 611 780', 'payment_method' => 'transfer', 'default_discount' => 18.00, 'has_cae' => true, 'cae_number' => 'CAE-GC-2020-00567', 'active' => true, 'notes' => 'Distribuidor para Península. Especializado en vinos de Denominación de Origen.'],
            ['client_type' => 'company', 'company_name' => 'Canarias Gourmet Exports', 'company_document' => 'B88901234', 'first_name' => null, 'last_name' => null, 'email' => 'export@canargourmet.com', 'phone' => '+34 928 470 019', 'payment_method' => 'transfer', 'default_discount' => 20.00, 'has_cae' => true, 'cae_number' => 'CAE-GC-2021-00089', 'active' => true, 'notes' => 'Exportador a Alemania y Países Bajos. Pedidos trimestrales de 500-2000 botellas.'],
            ['client_type' => 'company', 'company_name' => 'Bodegas Reunidas del Norte', 'company_document' => 'B44512036', 'first_name' => null, 'last_name' => null, 'email' => 'compras@bodrn.es', 'phone' => '+34 922 345 678', 'payment_method' => 'transfer', 'default_discount' => 15.00, 'has_cae' => true, 'cae_number' => 'CAE-TF-2018-00312', 'active' => true, 'notes' => 'Distribuidor en Tenerife y La Palma.'],

            // --- Supermercados / Retail ---
            ['client_type' => 'company', 'company_name' => 'Hiperdino GC', 'company_document' => 'A78234561', 'first_name' => null, 'last_name' => null, 'email' => 'proveedores@hiperdino.es', 'phone' => '+34 928 411 000', 'payment_method' => 'transfer', 'default_discount' => 10.00, 'has_cae' => false, 'active' => true, 'notes' => 'Cadena de supermercados. Sección vinos locales.'],
            ['client_type' => 'company', 'company_name' => 'El Corte Inglés Las Palmas', 'company_document' => 'A28023472', 'first_name' => null, 'last_name' => null, 'email' => 'proveedores.lpa@elcorteingles.es', 'phone' => '+34 928 266 000', 'payment_method' => 'transfer', 'default_discount' => 5.00, 'has_cae' => false, 'active' => true, 'notes' => 'Tienda gourmet. Solo vinos premium y añadas especiales.'],

            // --- Particulares / Coleccionistas ---
            ['client_type' => 'individual', 'company_name' => null, 'company_document' => null, 'first_name' => 'Antonio', 'last_name' => 'Herrera González', 'email' => 'antonio.herrera@gmail.com', 'phone' => '+34 629 001 234', 'payment_method' => 'cash', 'default_discount' => 0.00, 'has_cae' => false, 'active' => true, 'notes' => 'Coleccionista particular. Compra 2-3 cajas por campaña.'],
            ['client_type' => 'individual', 'company_name' => null, 'company_document' => null, 'first_name' => 'María', 'last_name' => 'Cabrera Delgado', 'email' => 'mcabrera@outlook.es', 'phone' => '+34 617 445 892', 'payment_method' => 'cash', 'default_discount' => 0.00, 'has_cae' => false, 'active' => true, 'notes' => 'Clienta habitual de la tienda online. Interesada en blancos y espumosos.'],
            ['client_type' => 'individual', 'company_name' => null, 'company_document' => null, 'first_name' => 'Carlos', 'last_name' => 'Navarro Suárez', 'email' => 'cnavarro@icloud.com', 'phone' => '+34 660 778 314', 'payment_method' => 'other', 'default_discount' => 5.00, 'has_cae' => false, 'active' => true, 'notes' => 'Sommelier freelance. Recomienda nuestros vinos en eventos de catas.'],
            ['client_type' => 'individual', 'company_name' => null, 'company_document' => null, 'first_name' => 'Isabel', 'last_name' => 'Flores Reyes', 'email' => 'isabelflores@gmail.com', 'phone' => '+34 635 129 007', 'payment_method' => 'transfer', 'default_discount' => 0.00, 'has_cae' => false, 'active' => false, 'notes' => 'Antigua clienta. Pendiente de reactivar cuenta.'],
        ];

        $rows = [];
        foreach ($clients as $c) {
            $rows[] = array_merge($c, [
                'user_id'       => self::WINERY_USER_ID,
                'balance'       => 0,
                'cae_number'    => $c['cae_number'] ?? null,
                'account_number'=> null,
                'avatar'        => null,
                'particular_document' => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        DB::table('clients')->insert($rows);

        $this->command->info('✅ Clientes: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        // Borrar facturas (y sus líneas) vinculadas a estos clientes antes de borrar clientes
        $clientIds = DB::table('clients')->where('user_id', self::WINERY_USER_ID)->pluck('id');
        if ($clientIds->isNotEmpty()) {
            $invoiceIds = DB::table('invoices')->whereIn('client_id', $clientIds)->pluck('id');
            if ($invoiceIds->isNotEmpty()) {
                DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
                DB::table('invoices')->whereIn('id', $invoiceIds)->delete();
            }
        }
        DB::table('clients')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
