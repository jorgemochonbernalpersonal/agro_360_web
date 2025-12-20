<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sistema simplificado: solo 3 impuestos (Exento, IVA, IGIC)
     */
    public function run(): void
    {
        // Limpiar impuestos existentes
        $this->command->info('🗑️  Limpiando impuestos antiguos...');
        DB::table('user_taxes')->truncate();
        DB::table('taxes')->truncate();

        $this->command->info('📝 Creando impuestos simplificados...');

        $taxes = [
            // Exento
            [
                'name' => 'Exento',
                'code' => 'EXENTO',
                'rate' => 0.00,
                'region' => 'General',
                'is_default' => false,
                'active' => true,
                'description' => 'Exento de impuestos (0%)',
            ],
            
            // IVA España Peninsular
            [
                'name' => 'IVA (21%)',
                'code' => 'IVA',
                'rate' => 21.00,
                'region' => 'España Peninsular',
                'is_default' => false,
                'active' => true,
                'description' => 'Impuesto sobre el Valor Añadido - Tipo general',
            ],
            
            // IGIC Canarias
            [
                'name' => 'IGIC (7%)',
                'code' => 'IGIC',
                'rate' => 7.00,
                'region' => 'Islas Canarias',
                'is_default' => false,
                'active' => true,
                'description' => 'Impuesto General Indirecto Canario - Tipo general',
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::create($tax);
            $this->command->info("  ✓ {$tax['name']} ({$tax['rate']}%)");
        }

        $this->command->info('');
        $this->command->line('┌─────────────────────────────────────────┐');
        $this->command->line('│ ✅ Sistema de impuestos simplificado   │');
        $this->command->line('│                                         │');
        $this->command->line('│ • Exento (0%)                          │');
        $this->command->line('│ • IVA (21%) - España Peninsular        │');
        $this->command->line('│ • IGIC (7%) - Islas Canarias           │');
        $this->command->line('│                                         │');
        $this->command->line('│ Configuración: /viticulturist/settings │');
        $this->command->line('└─────────────────────────────────────────┘');
    }
}
