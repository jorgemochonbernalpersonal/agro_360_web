<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgaetePlotsSeeder extends Seeder
{
    private const WINERY_ID            = 1;
    private const NUM_VITICULTURISTS   = 50;
    private const NUM_PLOTS            = 450;
    private const MUNICIPALITY_ID      = 5243; // Agaete
    private const PROVINCE_ID          = 14;   // Las Palmas
    private const AUTONOMOUS_COMMUNITY = 5;    // Canarias

    private const PARAJES = [
        'El Risco', 'La Culata', 'El Palmital', 'Valle de Agaete', 'El Barranco',
        'Las Longueras', 'El Sao', 'La Montañeta', 'Guayedra', 'El Hornillo',
        'Tamadaba', 'Tirma', 'La Cerca', 'El Juncalillo', 'Pinar de Tamadaba',
        'Las Cañadas', 'El Pinar', 'Hoya del Gamonal', 'Lomo Magullo', 'La Hoya',
        'El Llano', 'El Tablero', 'Lomito Verde', 'La Umbría', 'Lomo Largo',
        'Montaña Alta', 'El Helechal', 'Las Lajas', 'El Molinillo', 'El Batán',
        'Los Helechos', 'Los Morales', 'La Caldera', 'La Degollada', 'El Pozuelo',
        'Los Dragos', 'La Majada', 'El Almacigo', 'Lomo del Palo', 'El Portezuelo',
    ];

    private const PREFIJOS = ['Finca', 'Parcela', 'Viña', 'Viñedo', 'Pago', 'Suerte', 'Lote'];

    private const APELLIDOS = [
        'González', 'Rodríguez', 'Hernández', 'García', 'López', 'Martín', 'Pérez',
        'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Reyes', 'Díaz', 'Morales',
        'Vega', 'Ortega', 'Castro', 'Navarro', 'Delgado', 'Cabrera', 'Medina',
        'Suárez', 'Quintero', 'Montes', 'Acosta', 'Alonso', 'Ramos', 'Fuentes',
    ];

    private const NOMBRES = [
        'Carlos', 'Antonio', 'Manuel', 'Juan', 'Francisco', 'Pedro', 'Alejandro',
        'José', 'Miguel', 'Luis', 'Javier', 'David', 'Daniel', 'Rafael', 'Sergio',
        'Ana', 'María', 'Carmen', 'Isabel', 'Rosa', 'Elena', 'Laura', 'Marta',
        'Lucía', 'Sofía',
    ];

    public function run(): void
    {
        $now = now();

        // ── 1. Crear 50 viticultores ────────────────────────────────────────────
        $viticulturistIds = [];
        $userRows         = [];
        $wvRows           = [];

        for ($i = 1; $i <= self::NUM_VITICULTURISTS; $i++) {
            $nombre   = self::NOMBRES[array_rand(self::NOMBRES)];
            $apellido = self::APELLIDOS[array_rand(self::APELLIDOS)];
            $email    = 'vit.agaete.' . $i . '@seed.test';

            $userRows[] = [
                'name'              => "{$nombre} {$apellido}",
                'email'             => $email,
                'email_verified_at' => $now,
                'password'          => Hash::make('Seeder1234!'),
                'role'              => 'viticulturist',
                'can_login'         => false, // solo para datos, no acceden
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        DB::table('users')->insert($userRows);

        // Recuperar los IDs recién insertados
        $viticulturistIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        // ── 2. Vincular los 50 viticultores a la bodega ─────────────────────────
        foreach ($viticulturistIds as $vitId) {
            $wvRows[] = [
                'winery_id'        => self::WINERY_ID,
                'viticulturist_id' => $vitId,
                'assigned_by'      => self::WINERY_ID,
                'source'           => 'own',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        DB::table('winery_viticulturist')->insert($wvRows);

        // ── 3. Crear 450 parcelas distribuidas entre los 50 viticultores ────────
        $plotRows = [];
        $vitCount = count($viticulturistIds);

        for ($i = 1; $i <= self::NUM_PLOTS; $i++) {
            $paraje  = self::PARAJES[array_rand(self::PARAJES)];
            $prefijo = self::PREFIJOS[array_rand(self::PREFIJOS)];
            $numero  = str_pad($i, 3, '0', STR_PAD_LEFT);

            $plotRows[] = [
                'name'                    => "{$prefijo} {$paraje} {$numero}",
                'viticulturist_id'        => $viticulturistIds[($i - 1) % $vitCount], // round-robin
                'area'                    => round(mt_rand(10, 350) / 100, 2),
                'active'                  => true,
                'autonomous_community_id' => self::AUTONOMOUS_COMMUNITY,
                'province_id'             => self::PROVINCE_ID,
                'municipality_id'         => self::MUNICIPALITY_ID,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        foreach (array_chunk($plotRows, 100) as $chunk) {
            DB::table('plots')->insert($chunk);
        }

        $this->command->info('✓ 50 viticultores creados y vinculados a winery_id=' . self::WINERY_ID);
        $this->command->info('✓ 450 parcelas en Agaete (9 por viticultor)');
        $this->command->line('  Para revertir: elimina usuarios con email like "vit.agaete.%@seed.test"');
    }
}
