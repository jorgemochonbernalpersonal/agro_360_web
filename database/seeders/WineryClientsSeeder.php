<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 clientes para la bodega (user_id=1).
 * 250 empresas + 200 particulares.
 */
class WineryClientsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // 10 tipos × 25 nombres = 250 combinaciones únicas para empresas
    private const BUSINESS_TYPES = [
        'Restaurante', 'Hotel', 'Vinoteca', 'Distribuciones', 'Grupo Gastronómico',
        'Bodegas', 'Hipermercado', 'Importaciones', 'Comercializadora', 'Cooperativa',
    ];
    private const BUSINESS_NAMES = [
        'El Palmeral', 'Mar de Nubes', 'Isla Verde', 'Las Cumbres', 'El Rincón',
        'Los Berrazales', 'La Caldera', 'El Carrizal', 'Las Palomeras', 'El Puerto',
        'La Montaña', 'El Valle', 'Los Volcanes', 'La Cumbre', 'El Drago',
        'La Aldea', 'El Pinar', 'Las Nieves', 'El Juncal', 'La Vega',
        'Los Llanos', 'La Cruz', 'El Paso', 'Los Pinos', 'Atlántico',
    ];

    // 20 nombres × 10 apellidos = 200 combinaciones únicas para particulares
    private const FIRST_NAMES = [
        'Antonio', 'María', 'Carlos', 'Isabel', 'Pedro', 'Laura', 'José', 'Ana',
        'Manuel', 'Carmen', 'Francisco', 'Lucía', 'David', 'Marta', 'Alejandro',
        'Sofía', 'Miguel', 'Paula', 'Jorge', 'Beatriz',
    ];
    private const LAST_NAMES = [
        'González Álvarez', 'Suárez Cabrera', 'Hernández Ramos', 'Falcón Betancor',
        'Medina Santana', 'Rodríguez Pérez', 'García Díaz', 'López Torres',
        'Martínez Vega', 'Sánchez Castro',
    ];

    private const PAYMENT_METHODS = ['transfer', 'transfer', 'transfer', 'cash', 'other'];

    public function run(): void
    {
        $this->cleanup();

        $now  = now();
        $rows = [];

        // ── 250 empresas ────────────────────────────────────────────────────────
        for ($i = 0; $i < 250; $i++) {
            $type       = self::BUSINESS_TYPES[$i % count(self::BUSINESS_TYPES)];
            $name       = self::BUSINESS_NAMES[(int)floor($i / count(self::BUSINESS_TYPES)) % count(self::BUSINESS_NAMES)];
            $companyDoc = 'B' . str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT);
            $payment    = self::PAYMENT_METHODS[$i % count(self::PAYMENT_METHODS)];
            $hasCae     = in_array($type, ['Distribuciones', 'Importaciones', 'Comercializadora']) && ($i % 2 === 0);
            $discount   = [0, 5, 8, 10, 12, 15, 18, 20][$i % 8];

            $rows[] = [
                'user_id'              => self::WINERY_USER_ID,
                'client_type'          => 'company',
                'company_name'         => "$type $name",
                'company_document'     => $companyDoc,
                'first_name'           => null,
                'last_name'            => null,
                'email'                => 'pedidos.' . ($i + 1) . '@cliente.agro365.demo',
                'phone'                => '+34 9' . str_pad(10000000 + ($i * 17 % 89000000), 8, '0', STR_PAD_LEFT),
                'payment_method'       => $payment,
                'default_discount'     => $discount,
                'has_cae'              => $hasCae,
                'cae_number'           => $hasCae ? 'CAE-GC-2020-' . str_pad($i, 5, '0', STR_PAD_LEFT) : null,
                'active'               => $i % 12 !== 11,   // ~92% activos
                'notes'                => null,
                'balance'              => 0,
                'account_number'       => null,
                'avatar'               => null,
                'particular_document'  => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        // ── 200 particulares ────────────────────────────────────────────────────
        for ($i = 0; $i < 200; $i++) {
            $firstName = self::FIRST_NAMES[$i % count(self::FIRST_NAMES)];
            $lastName  = self::LAST_NAMES[(int)floor($i / count(self::FIRST_NAMES)) % count(self::LAST_NAMES)];
            $payment   = ['cash', 'transfer', 'other'][$i % 3];
            $discount  = $i % 5 === 0 ? 5.00 : 0.00;

            $rows[] = [
                'user_id'              => self::WINERY_USER_ID,
                'client_type'          => 'individual',
                'company_name'         => null,
                'company_document'     => null,
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'email'                => strtolower(str_replace(' ', '.', $firstName)) . '.' . ($i + 1) . '@gmail.com',
                'phone'                => '+34 6' . str_pad(10000000 + ($i * 31 % 89000000), 8, '0', STR_PAD_LEFT),
                'payment_method'       => $payment,
                'default_discount'     => $discount,
                'has_cae'              => false,
                'cae_number'           => null,
                'active'               => $i % 15 !== 14,   // ~93% activos
                'notes'                => null,
                'balance'              => 0,
                'account_number'       => null,
                'avatar'               => null,
                'particular_document'  => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('clients')->insert($chunk);
        }

        $this->command->info('✅ Clientes: ' . count($rows) . ' registros (250 empresas + 200 particulares)');
    }

    private function cleanup(): void
    {
        DB::table('clients')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
