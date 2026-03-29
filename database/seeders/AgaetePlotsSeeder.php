<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgaetePlotsSeeder extends Seeder
{
    private const WINERY_ID          = 1;
    private const NUM_VITICULTURISTS = 50;
    private const PROVINCE_ID        = 14;   // Las Palmas
    private const AUTONOMOUS_COMMUNITY = 5;  // Canarias

    // SIGPAC municipality name → municipalities.id in DB
    private const MUN_DB_IDS = [
        'Agaete'   => 5243,
        'Agüimes'  => 5244,
        'Artenara' => 5247,
        'Arucas'   => 5248,
        'Firgas'   => 5250,
        'Gáldar'   => 5251,
        'Ingenio'  => 5253,
    ];

    private const PREFIJOS  = ['Finca', 'Parcela', 'Viña', 'Viñedo', 'Pago', 'Suerte', 'Lote'];
    private const APELLIDOS = [
        'González', 'Rodríguez', 'Hernández', 'García', 'López', 'Martín', 'Pérez',
        'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Reyes', 'Díaz', 'Morales',
        'Vega', 'Ortega', 'Castro', 'Navarro', 'Delgado', 'Cabrera', 'Medina',
        'Suárez', 'Quintero', 'Montes', 'Acosta',
    ];
    private const NOMBRES = [
        'Carlos', 'Antonio', 'Manuel', 'Juan', 'Francisco', 'Pedro', 'Alejandro',
        'José', 'Miguel', 'Luis', 'Javier', 'David', 'Daniel', 'Rafael',
        'Ana', 'María', 'Carmen', 'Isabel', 'Rosa', 'Elena', 'Laura', 'Marta',
    ];

    // ── Datos reales SIGPAC ────────────────────────────────────────────────────
    // Obtenidos de https://sigpac.mapa.es/fega/serviciosvisorsigpac/query/
    // Formato: [mun_name, mun_sigpac, ine_code, polygon, parcel, recinto, uso, area_ha, wkt]
    private function getSigpacData(): array
    {
        $raw = file_get_contents(database_path('seeders/data/sigpac_gran_canaria.json'));
        return json_decode($raw, true);
    }

    public function run(): void
    {
        $now = now();

        // ── 0. Limpiar datos anteriores (idempotente) ─────────────────────────
        $this->cleanup();

        // ── 1. Cargar datos SIGPAC reales ─────────────────────────────────────
        $sigpacData = $this->getSigpacData();
        $total      = count($sigpacData);
        $this->command->info("Cargados $total recintos SIGPAC reales de Gran Canaria.");

        // ── 2. Crear 50 viticultores ──────────────────────────────────────────
        $userRows = [];
        for ($i = 1; $i <= self::NUM_VITICULTURISTS; $i++) {
            $userRows[] = [
                'name'              => self::NOMBRES[array_rand(self::NOMBRES)] . ' ' . self::APELLIDOS[array_rand(self::APELLIDOS)],
                'email'             => "vit.agaete.{$i}@seed.test",
                'email_verified_at' => $now,
                'password'          => Hash::make('Seeder1234!'),
                'role'              => 'viticulturist',
                'can_login'         => false,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }
        DB::table('users')->insert($userRows);

        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->orderBy('id')->pluck('id')->toArray();

        // ── 3. Vincular viticultores a la bodega ──────────────────────────────
        $wvRows = array_map(fn($id) => [
            'winery_id'        => self::WINERY_ID,
            'viticulturist_id' => $id,
            'assigned_by'      => self::WINERY_ID,
            'source'           => 'own',
            'created_at'       => $now,
            'updated_at'       => $now,
        ], $vitIds);
        DB::table('winery_viticulturist')->insert($wvRows);

        // ── 4. Insertar sigpac_code, plot_geometry, plots y pivots ────────────
        $vitCount  = count($vitIds);
        $plotIndex = 0;

        foreach (array_chunk($sigpacData, 50) as $chunk) {
            foreach ($chunk as $rec) {
                $munDbId = self::MUN_DB_IDS[$rec['mun_name']] ?? 5243;
                $ineCode = $rec['ine_code'];

                // sigpac_code
                $code = sprintf(
                    '05%02d%03d000000%03d%05d%03d',
                    (int) 35, // province
                    (int) $ineCode,
                    $rec['polygon'],
                    $rec['parcel'],
                    $rec['recinto']
                );
                $sigpacId = DB::table('sigpac_code')->insertGetId([
                    'code_autonomous_community' => '05',
                    'code_province'             => '35',
                    'code_municipality'         => str_pad($ineCode, 3, '0', STR_PAD_LEFT),
                    'code_aggregate'            => '000',
                    'code_zone'                 => '000',
                    'code_polygon'              => str_pad($rec['polygon'], 3, '0', STR_PAD_LEFT),
                    'code_plot'                 => str_pad($rec['parcel'],  5, '0', STR_PAD_LEFT),
                    'code_enclosure'            => str_pad($rec['recinto'], 3, '0', STR_PAD_LEFT),
                    'code'                      => $code,
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ]);

                // plot_geometry (spatial WKT)
                $geomId = DB::table('plot_geometry')->insertGetId([
                    'coordinates' => DB::raw("ST_GeomFromText('" . $rec['wkt'] . "', 4326)"),
                    'centroid'    => DB::raw("ST_Centroid(ST_GeomFromText('" . $rec['wkt'] . "', 4326))"),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);

                // Nombre de parcela
                $prefijo  = self::PREFIJOS[array_rand(self::PREFIJOS)];
                $munShort = explode(' ', $rec['mun_name'])[0];
                $plotName = "$prefijo $munShort " . str_pad($plotIndex + 1, 3, '0', STR_PAD_LEFT);

                $plotId = DB::table('plots')->insertGetId([
                    'name'                    => $plotName,
                    'viticulturist_id'        => $vitIds[$plotIndex % $vitCount],
                    'area'                    => $rec['area_ha'] > 0 ? $rec['area_ha'] : round(mt_rand(10, 350) / 100, 2),
                    'active'                  => true,
                    'autonomous_community_id' => self::AUTONOMOUS_COMMUNITY,
                    'province_id'             => self::PROVINCE_ID,
                    'municipality_id'         => $munDbId,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);

                // multipart_plot_sigpac (estructura nueva con geometría)
                DB::table('multipart_plot_sigpac')->insert([
                    'plot_id'         => $plotId,
                    'sigpac_code_id'  => $sigpacId,
                    'plot_geometry_id'=> $geomId,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                // plot_sigpac_code (pivot legacy)
                DB::table('plot_sigpac_code')->insert([
                    'plot_id'        => $plotId,
                    'sigpac_code_id' => $sigpacId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                $plotIndex++;
            }

            $this->command->line("  → {$plotIndex} parcelas insertadas...");
        }

        $this->command->info('✓ ' . self::NUM_VITICULTURISTS . ' viticultores creados y vinculados a winery_id=' . self::WINERY_ID);
        $this->command->info("✓ {$plotIndex} parcelas con SIGPAC real (Gran Canaria)");
        $this->command->info('✓ plot_geometry + multipart_plot_sigpac + plot_sigpac_code insertados');
    }

    private function cleanup(): void
    {
        $this->command->line('Limpiando datos anteriores del seeder...');

        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->pluck('id');

        if ($vitIds->isEmpty()) {
            $this->command->line('  Nada que limpiar.');
            return;
        }

        $plotIds = DB::table('plots')
            ->whereIn('viticulturist_id', $vitIds)->pluck('id');

        $sigpacIds = DB::table('plot_sigpac_code')
            ->whereIn('plot_id', $plotIds)->pluck('sigpac_code_id');

        $geomIds = DB::table('multipart_plot_sigpac')
            ->whereIn('plot_id', $plotIds)->pluck('plot_geometry_id');

        DB::table('multipart_plot_sigpac')->whereIn('plot_id', $plotIds)->delete();
        DB::table('plot_sigpac_code')->whereIn('plot_id', $plotIds)->delete();
        DB::table('plots')->whereIn('id', $plotIds)->delete();
        DB::table('sigpac_code')->whereIn('id', $sigpacIds)->delete();
        DB::table('plot_geometry')->whereIn('id', $geomIds)->delete();
        DB::table('winery_viticulturist')->whereIn('viticulturist_id', $vitIds)->delete();
        DB::table('users')->whereIn('id', $vitIds)->delete();

        $this->command->line('  Limpiado: ' . $plotIds->count() . ' parcelas, ' . $vitIds->count() . ' viticultores.');
    }
}
