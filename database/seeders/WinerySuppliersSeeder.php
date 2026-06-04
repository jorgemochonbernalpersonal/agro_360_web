<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 proveedores para la bodega (user_id=1).
 * 15 prefijos × 30 sufijos = 450 combinaciones únicas.
 */
class WinerySuppliersSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const PREFIXES = [
        'Suministros', 'Distribuciones', 'Servicios', 'Empresa', 'Soluciones',
        'Tecnología', 'Laboratorio', 'Cooperativa', 'Agrochem', 'Packaging',
        'Logística', 'Ingeniería', 'Comercial', 'Industrias', 'Abastecimientos',
    ];

    private const SUFFIXES = [
        'Atlántico', 'Canarias', 'Sur', 'Norte', 'Isla',
        'Agaete', 'Gran Canaria', 'Las Palmas', 'Insular', 'Premium',
        'Pro', 'Plus', 'Total', 'Tech', 'Expert',
        'Direct', 'Global', 'Local', 'Integral', 'Quality',
        'Service', 'Group', 'Trade', 'Market', 'Resources',
        'Enterprise', 'Materials', 'Products', 'Supplies', 'Systems',
    ];

    private const CATEGORIES = [
        'packaging', 'chemicals', 'equipment', 'services', 'grape',
        'labels', 'transport', 'maintenance', 'packaging', 'chemicals',
        'equipment', 'services', 'grape', 'labels', 'transport',
    ];

    private const CONTACT_NAMES = [
        'Pedro Jiménez', 'Laura Montes', 'Rodrigo Acosta', 'Ana Ortega', 'Miguel Vega',
        'Carmen Díaz', 'Sofía Leal', 'José Sánchez', 'Roberto Torres', 'Félix Ramírez',
        'María García', 'Carlos López', 'Isabel Martínez', 'Manuel Sánchez', 'Francisco González',
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();
        $rows = [];

        // 15 prefijos × 30 sufijos = 450 combinaciones únicas
        for ($i = 0; $i < 450; $i++) {
            $prefixIdx = (int) floor($i / count(self::SUFFIXES));   // 0-14
            $suffixIdx = $i % count(self::SUFFIXES);               // 0-29
            $catIdx = $prefixIdx % count(self::CATEGORIES);

            $prefix = self::PREFIXES[$prefixIdx];
            $suffix = self::SUFFIXES[$suffixIdx];
            $category = self::CATEGORIES[$catIdx];
            $contact = self::CONTACT_NAMES[$i % count(self::CONTACT_NAMES)];
            $vatNum = 'B'.str_pad(20000001 + $i, 8, '0', STR_PAD_LEFT);

            $rows[] = [
                'user_id' => self::WINERY_USER_ID,
                'name' => "$prefix $suffix S.L.",
                'contact_person' => $contact,
                'email' => 'ventas.'.($i + 1).'@proveedor.agro365.demo',
                'phone' => '+34 9'.str_pad(20000000 + ($i * 19 % 79000000), 8, '0', STR_PAD_LEFT),
                'address' => $suffix.', Gran Canaria',
                'vat_number' => $vatNum,
                'category' => $category,
                'active' => $i % 10 !== 9,   // ~90% activos
                'notes' => 'Proveedor de '.$category.' para bodega.',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('suppliers')->insert($chunk);
        }

        $this->command->info('✅ Proveedores: '.count($rows).' registros');
    }

    private function cleanup(): void
    {
        DB::table('suppliers')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
