<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            // IVA España
            [
                'name' => 'IVA General',
                'code' => 'IVA',
                'rate' => 21.00,
                'region' => 'España',
                'is_default' => true,
                'active' => true,
                'description' => 'IVA General del 21% aplicable en España peninsular',
            ],
            [
                'name' => 'IVA Reducido',
                'code' => 'IVA',
                'rate' => 10.00,
                'region' => 'España',
                'is_default' => false,
                'active' => true,
                'description' => 'IVA Reducido del 10%',
            ],
            [
                'name' => 'IVA Superreducido',
                'code' => 'IVA',
                'rate' => 4.00,
                'region' => 'España',
                'is_default' => false,
                'active' => true,
                'description' => 'IVA Superreducido del 4%',
            ],
            // IGIC Canarias
            [
                'name' => 'IGIC General',
                'code' => 'IGIC',
                'rate' => 7.00,
                'region' => 'Canarias',
                'is_default' => true,
                'active' => true,
                'description' => 'IGIC General del 7% aplicable en Canarias',
            ],
            [
                'name' => 'IGIC Reducido',
                'code' => 'IGIC',
                'rate' => 3.00,
                'region' => 'Canarias',
                'is_default' => false,
                'active' => true,
                'description' => 'IGIC Reducido del 3%',
            ],
            [
                'name' => 'IGIC Incrementado',
                'code' => 'IGIC',
                'rate' => 9.50,
                'region' => 'Canarias',
                'is_default' => false,
                'active' => true,
                'description' => 'IGIC Incrementado del 9.5%',
            ],
            // Exento
            [
                'name' => 'Exento',
                'code' => 'EXENTO',
                'rate' => 0.00,
                'region' => null,
                'is_default' => false,
                'active' => true,
                'description' => 'Operación exenta de impuestos',
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(
                [
                    'code' => $tax['code'],
                    'rate' => $tax['rate'],
                    'region' => $tax['region'],
                ],
                $tax
            );
        }

        $this->command->info('✅ Impuestos creados correctamente');
    }
}
