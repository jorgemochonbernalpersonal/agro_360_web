<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WinerySuppliersSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $suppliers = [
            ['name' => 'Botella & Corcho Canarias S.L.',   'contact_person' => 'Pedro Jiménez',   'email' => 'ventas@botellaycorcho.es',      'phone' => '+34 928 411 234', 'address' => 'Pol. Industrial Arinaga, GC',        'vat_number' => 'B76543211', 'category' => 'packaging',  'active' => true,  'notes' => 'Botellas bordelesa 750ml, tapones de corcho natural y sintético. Entrega en 5 días.'],
            ['name' => 'Suministros Enológicos del Sur',   'contact_person' => 'Laura Montes',    'email' => 'pedidos@enologicossur.com',     'phone' => '+34 928 550 771', 'address' => 'C/ Triana 45, Las Palmas de GC',    'vat_number' => 'B22876543', 'category' => 'chemicals',  'active' => true,  'notes' => 'Sulfitos, clarificantes, levaduras seleccionadas, nutrientes. Distribuidor autorizado.'],
            ['name' => 'Agrochem Canarias',                'contact_person' => 'Rodrigo Acosta',  'email' => 'info@agrochemcanarias.es',      'phone' => '+34 928 302 190', 'address' => 'Las Palmas de GC',                  'vat_number' => 'B11234567', 'category' => 'chemicals',  'active' => true,  'notes' => 'Enzimas, ácidos, taninos enológicos. Proveedor oficial de productos fitosanitarios.'],
            ['name' => 'Etiquetas Craft Canarias',         'contact_person' => 'Ana Ortega',      'email' => 'studio@etiquetascraft.es',     'phone' => '+34 638 445 009', 'address' => 'Las Palmas de GC',                  'vat_number' => 'B44198723', 'category' => 'packaging',  'active' => true,  'notes' => 'Etiquetas offset y digitales. Diseño propio y personalizado.'],
            ['name' => 'Cooperativa Agrícola Agaete',      'contact_person' => 'Miguel Vega',     'email' => 'info@cooperativaagaete.es',    'phone' => '+34 928 898 100', 'address' => 'Agaete, Gran Canaria',              'vat_number' => 'F76890123', 'category' => 'grape',      'active' => true,  'notes' => 'Compra de uva excedente de socios. Proveedor de uva Listán Blanco y Negramoll.'],
            ['name' => 'Tecnivin Equipamiento S.A.',       'contact_person' => 'Carmen Díaz',     'email' => 'ventas@tecnivin.es',           'phone' => '+34 91 678 3412', 'address' => 'Pol. Industrial Vallecas, Madrid',  'vat_number' => 'A78234000', 'category' => 'equipment',  'active' => true,  'notes' => 'Prensas, bombas, filtros y equipo de bodega. SAT en Canarias.'],
            ['name' => 'Laboratorio Enológico Atlántico',  'contact_person' => 'Dra. Sofía Leal', 'email' => 'analisis@labatlantico.es',     'phone' => '+34 928 474 050', 'address' => 'Tfno. Dr. Zurita 20, Las Palmas', 'vat_number' => 'B88001234', 'category' => 'services',   'active' => true,  'notes' => 'Análisis fisicoquímicos y microbiológicos. Resultados en 48-72h.'],
            ['name' => 'Cajas Isla Packaging',             'contact_person' => 'José Sánchez',    'email' => 'pedidos@cajasisla.com',        'phone' => '+34 928 220 089', 'address' => 'Telde, Gran Canaria',               'vat_number' => 'B55123478', 'category' => 'packaging',  'active' => true,  'notes' => 'Cajas de cartón estándar y personalizadas. Bolsas de tela y estuches.'],
            ['name' => 'Filtros & Membranas Pro',          'contact_person' => 'Roberto Torres',  'email' => 'info@filtrosmembranaspro.es',  'phone' => '+34 96 321 5670', 'address' => 'Valencia',                          'vat_number' => 'B46123456', 'category' => 'equipment',  'active' => true,  'notes' => 'Filtros de placa, cartuchos y membranas para estabilización.'],
            ['name' => 'Grúas y Logística Canarias',       'contact_person' => 'Félix Ramírez',   'email' => 'logistica@grcanarias.com',     'phone' => '+34 928 900 340', 'address' => 'Puerto de Las Palmas',              'vat_number' => 'B66780012', 'category' => 'services',   'active' => false, 'notes' => 'Empresa de transporte y logística. Sin contrato activo.'],
        ];

        $rows = [];
        foreach ($suppliers as $s) {
            $rows[] = array_merge($s, [
                'user_id'    => self::WINERY_USER_ID,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('suppliers')->insert($rows);

        $this->command->info('✅ Proveedores: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('suppliers')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
