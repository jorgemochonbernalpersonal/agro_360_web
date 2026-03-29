<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgaetePlotsSeeder extends Seeder
{
    // IDs fijos — Agaete, Las Palmas, Canarias
    private const VITICULTURIST_ID     = 2;
    private const MUNICIPALITY_ID      = 5243;
    private const PROVINCE_ID          = 14;
    private const AUTONOMOUS_COMMUNITY = 5;
    private const TOTAL                = 450;

    // Parajes y pagos típicos de Agaete / Gran Canaria
    private const PARAJES = [
        'El Risco', 'La Culata', 'El Palmital', 'Valle de Agaete', 'El Barranco',
        'Las Longueras', 'El Sao', 'La Montañeta', 'Guayedra', 'El Hornillo',
        'Tamadaba', 'Tirma', 'La Cerca', 'El Juncalillo', 'Pinar de Tamadaba',
        'Las Cañadas', 'El Pinar', 'Hoya del Gamonal', 'Lomo Magullo', 'La Hoya',
        'El Llano', 'Cruz de Tejeda', 'El Tablero', 'Lomito Verde', 'La Umbría',
        'Lomo Largo', 'Montaña Alta', 'El Helechal', 'Las Lajas', 'El Molinillo',
        'El Batán', 'Los Helechos', 'Los Morales', 'La Caldera', 'La Degollada',
        'Llano del Polvo', 'Pinar Blanco', 'La Palangana', 'El Portezuelo',
        'Los Dragos', 'La Majada', 'El Almacigo', 'Lomo del Palo', 'El Pozuelo',
    ];

    private const PREFIJOS = [
        'Finca', 'Parcela', 'Lote', 'Viña', 'Viñedo', 'Pago', 'Suerte',
    ];

    public function run(): void
    {
        $now  = now();
        $rows = [];

        for ($i = 1; $i <= self::TOTAL; $i++) {
            $paraje  = self::PARAJES[array_rand(self::PARAJES)];
            $prefijo = self::PREFIJOS[array_rand(self::PREFIJOS)];
            $numero  = str_pad($i, 3, '0', STR_PAD_LEFT);
            $name    = "{$prefijo} {$paraje} {$numero}";

            $rows[] = [
                'name'                    => $name,
                'viticulturist_id'        => self::VITICULTURIST_ID,
                'area'                    => round(mt_rand(10, 350) / 100, 2), // 0.10 – 3.50 ha
                'active'                  => true,
                'autonomous_community_id' => self::AUTONOMOUS_COMMUNITY,
                'province_id'             => self::PROVINCE_ID,
                'municipality_id'         => self::MUNICIPALITY_ID,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        // Insert en bloques de 100 para no saturar la memoria
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('plots')->insert($chunk);
        }

        $this->command->info('AgaetePlotsSeeder: 450 parcelas insertadas.');
    }
}
