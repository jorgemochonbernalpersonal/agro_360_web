<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea 20 vinos realistas para bodega user_id=1 (Agaete, Gran Canaria).
 * Depende de: WineryOenologistsSeeder
 */
class WineryWinesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // Obtener IDs de enólogos
        $oenologists = DB::table('oenologists')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $oen1 = $oenologists[0] ?? null;
        $oen2 = $oenologists[1] ?? null;

        $wines = [
            // ── TINTOS ──────────────────────────────────────────────────────────
            [
                'name'                 => 'Agaete Tinto Joven 2025',
                'vintage'              => 2025,
                'wine_type'            => 'red',
                'aging_type'           => 'joven',
                'category'             => 'Vino de mesa',
                'status'               => 'in_progress',
                'variety'              => 'Listán Negro 100%',
                'volume_liters'        => 18500.000,
                'initial_quantity_kg'  => 24000.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'TJ-2025-01',
                'notes'                => 'Vendimia 2025. En proceso de fermentación alcohólica.',
            ],
            [
                'name'                 => 'Negramoll Crianza 2023',
                'vintage'              => 2023,
                'wine_type'            => 'red',
                'aging_type'           => 'crianza',
                'category'             => 'Vino de calidad',
                'status'               => 'aged',
                'variety'              => 'Negramoll 80% · Listán Negro 20%',
                'volume_liters'        => 8200.000,
                'initial_quantity_kg'  => 11000.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'TC-2023-01',
                'notes'                => 'Crianza 12 meses en barrica de roble francés. Listo para embotellar.',
            ],
            [
                'name'                 => 'Gran Reserva Listán Negro 2022',
                'vintage'              => 2022,
                'wine_type'            => 'red',
                'aging_type'           => 'reserva',
                'category'             => 'Vino de calidad superior',
                'status'               => 'bottled',
                'variety'              => 'Listán Negro 100%',
                'volume_liters'        => 4800.000,
                'initial_quantity_kg'  => 6500.000,
                'is_organic'           => true,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'GR-2022-01',
                'notes'                => 'Producción limitada 6.400 botellas. Medalla de plata en concurso internacional.',
            ],
            [
                'name'                 => 'Tinto Barrica 2023',
                'vintage'              => 2023,
                'wine_type'            => 'red',
                'aging_type'           => 'barrica',
                'category'             => 'Vino de calidad',
                'status'               => 'aged',
                'variety'              => 'Listán Negro 70% · Negramoll 30%',
                'volume_liters'        => 6100.000,
                'initial_quantity_kg'  => 8200.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'TB-2023-02',
                'notes'                => '6 meses en barrica de roble americano.',
            ],
            [
                'name'                 => 'Tinto Ecológico 2024',
                'vintage'              => 2024,
                'wine_type'            => 'red',
                'aging_type'           => 'joven',
                'category'             => 'Vino ecológico',
                'status'               => 'bottled',
                'variety'              => 'Listán Negro 90% · Negramoll 10%',
                'volume_liters'        => 9500.000,
                'initial_quantity_kg'  => 12600.000,
                'is_organic'           => true,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'TE-2024-01',
                'notes'                => 'Certificación ecológica CAAE. Sin herbicidas.',
            ],
            [
                'name'                 => 'Tinto Maceración Carbónica 2025',
                'vintage'              => 2025,
                'wine_type'            => 'red',
                'aging_type'           => 'joven',
                'category'             => 'Vino de mesa',
                'status'               => 'in_progress',
                'variety'              => 'Listán Negro 100%',
                'volume_liters'        => 5200.000,
                'initial_quantity_kg'  => 7000.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'TMC-2025-01',
                'notes'                => 'Maceración carbónica tradicional. Fruta y viveza.',
            ],
            [
                'name'                 => 'Tinto Joven 2022',
                'vintage'              => 2022,
                'wine_type'            => 'red',
                'aging_type'           => 'joven',
                'category'             => 'Vino de mesa',
                'status'               => 'sold',
                'variety'              => 'Listán Negro 100%',
                'volume_liters'        => 0.000,
                'initial_quantity_kg'  => 15000.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'TJ-2022-01',
                'notes'                => 'Stock agotado. Muy buena acogida en mercado local.',
            ],

            // ── BLANCOS ──────────────────────────────────────────────────────────
            [
                'name'                 => 'Vijariego Blanco 2025',
                'vintage'              => 2025,
                'wine_type'            => 'white',
                'aging_type'           => 'joven',
                'category'             => 'Vino de calidad',
                'status'               => 'in_progress',
                'variety'              => 'Vijariego Negro 100%',
                'volume_liters'        => 7800.000,
                'initial_quantity_kg'  => 10500.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'VB-2025-01',
                'notes'                => 'Fermentación en frío a 16°C. Aromas tropicales característicos.',
            ],
            [
                'name'                 => 'Malvasía Seco 2024',
                'vintage'              => 2024,
                'wine_type'            => 'white',
                'aging_type'           => 'joven',
                'category'             => 'Vino de calidad superior',
                'status'               => 'bottled',
                'variety'              => 'Malvasía Volcánica 100%',
                'volume_liters'        => 5400.000,
                'initial_quantity_kg'  => 7200.000,
                'is_organic'           => true,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'MS-2024-01',
                'notes'                => 'Fermentación en depósito de acero. Mínimo SO2. Notas cítricas y florales.',
            ],
            [
                'name'                 => 'Listán Blanco Barrica 2023',
                'vintage'              => 2023,
                'wine_type'            => 'white',
                'aging_type'           => 'barrica',
                'category'             => 'Vino de calidad',
                'status'               => 'bottled',
                'variety'              => 'Listán Blanco 100%',
                'volume_liters'        => 3200.000,
                'initial_quantity_kg'  => 4300.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'LBB-2023-01',
                'notes'                => '4 meses en barrica de roble francés con bâtonnage semanal.',
            ],
            [
                'name'                 => 'Blanco Ecológico 2024',
                'vintage'              => 2024,
                'wine_type'            => 'white',
                'aging_type'           => 'joven',
                'category'             => 'Vino ecológico',
                'status'               => 'aged',
                'variety'              => 'Listán Blanco 60% · Vijariego 40%',
                'volume_liters'        => 11000.000,
                'initial_quantity_kg'  => 14800.000,
                'is_organic'           => true,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'BE-2024-01',
                'notes'                => 'Coupage singular. Sin adición de sulfitos.',
            ],
            [
                'name'                 => 'Blanco Joven 2025',
                'vintage'              => 2025,
                'wine_type'            => 'white',
                'aging_type'           => 'joven',
                'category'             => 'Vino de mesa',
                'status'               => 'in_progress',
                'variety'              => 'Listán Blanco 100%',
                'volume_liters'        => 14200.000,
                'initial_quantity_kg'  => 19000.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'BJ-2025-01',
                'notes'                => 'Mayor volumen de la campaña. Mercado general.',
            ],

            // ── ROSADOS ──────────────────────────────────────────────────────────
            [
                'name'                 => 'Rosado de Agaete 2024',
                'vintage'              => 2024,
                'wine_type'            => 'rose',
                'aging_type'           => 'joven',
                'category'             => 'Vino de calidad',
                'status'               => 'bottled',
                'variety'              => 'Listán Negro 100%',
                'volume_liters'        => 4100.000,
                'initial_quantity_kg'  => 5600.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'RA-2024-01',
                'notes'                => 'Método de sangrado. Color salmón intenso, muy frutal.',
            ],
            [
                'name'                 => 'Rosado Negramoll 2025',
                'vintage'              => 2025,
                'wine_type'            => 'rose',
                'aging_type'           => 'joven',
                'category'             => 'Vino de mesa',
                'status'               => 'in_progress',
                'variety'              => 'Negramoll 100%',
                'volume_liters'        => 3400.000,
                'initial_quantity_kg'  => 4600.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'RN-2025-01',
                'notes'                => 'En proceso de clarificación.',
            ],

            // ── ESPUMOSOS ────────────────────────────────────────────────────────
            [
                'name'                 => 'Brut Nature Gran Agaete 2022',
                'vintage'              => 2022,
                'wine_type'            => 'sparkling',
                'aging_type'           => 'reserva',
                'category'             => 'Vino espumoso de calidad',
                'status'               => 'bottled',
                'variety'              => 'Malvasía 70% · Listán Blanco 30%',
                'volume_liters'        => 2400.000,
                'initial_quantity_kg'  => 3200.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'ESP-2022-01',
                'notes'                => 'Método tradicional. 24 meses en botella. Tiraje manual.',
            ],
            [
                'name'                 => 'Espumoso Rosé 2023',
                'vintage'              => 2023,
                'wine_type'            => 'sparkling',
                'aging_type'           => 'joven',
                'category'             => 'Vino espumoso',
                'status'               => 'aged',
                'variety'              => 'Listán Negro 100%',
                'volume_liters'        => 1800.000,
                'initial_quantity_kg'  => 2400.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'ESPR-2023-01',
                'notes'                => 'Segunda fermentación en tanque. Fresco y afrutado.',
            ],

            // ── DULCES ───────────────────────────────────────────────────────────
            [
                'name'                 => 'Malvasía Dulce 2023',
                'vintage'              => 2023,
                'wine_type'            => 'sweet',
                'aging_type'           => 'crianza',
                'category'             => 'Vino generoso dulce',
                'status'               => 'bottled',
                'variety'              => 'Malvasía Volcánica 100%',
                'volume_liters'        => 1200.000,
                'initial_quantity_kg'  => 2000.000,
                'is_organic'           => true,
                'is_must'              => false,
                'oenologist_id'        => $oen1,
                'internal_code'        => 'MD-2023-01',
                'notes'                => 'Pasificación parcial en viña. 140g/L azúcar residual. Botellas de 500ml.',
            ],

            // ── MOSTO ────────────────────────────────────────────────────────────
            [
                'name'                 => 'Mosto Listán Blanco 2025',
                'vintage'              => 2025,
                'wine_type'            => 'white',
                'aging_type'           => null,
                'category'             => 'Mosto',
                'status'               => 'in_progress',
                'variety'              => 'Listán Blanco 100%',
                'volume_liters'        => 3100.000,
                'initial_quantity_kg'  => 4200.000,
                'is_organic'           => false,
                'is_must'              => true,
                'oenologist_id'        => null,
                'internal_code'        => 'MOSTO-2025-01',
                'notes'                => 'Mosto sulfitado para posterior fermentación controlada.',
            ],

            // ── VINO CANCELADO ────────────────────────────────────────────────────
            [
                'name'                 => 'Coupage Experimental 2023',
                'vintage'              => 2023,
                'wine_type'            => 'red',
                'aging_type'           => null,
                'category'             => null,
                'status'               => 'cancelled',
                'variety'              => 'Listán Negro 50% · Syrah 50%',
                'volume_liters'        => 0.000,
                'initial_quantity_kg'  => 3000.000,
                'is_organic'           => false,
                'is_must'              => false,
                'oenologist_id'        => $oen2,
                'internal_code'        => 'EXP-2023-01',
                'notes'                => 'Cancelado por problemas de acidez volátil excesiva. Derivado a vinagre.',
            ],
        ];

        $rows = [];
        foreach ($wines as $w) {
            $rows[] = array_merge($w, [
                'user_id'    => self::WINERY_USER_ID,
                'trace_token'=> null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('wines')->insert($rows);

        $this->command->info('✅ Vinos: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        // Las operaciones (transfers, losses, etc.) se eliminan en cascada
        DB::table('wines')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
