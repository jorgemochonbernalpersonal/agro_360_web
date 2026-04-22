<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 vinos para bodega user_id=1 (Agaete, Gran Canaria).
 *   · 225 vinos de vendimias 2022–2025 (histórico)
 *   · 225 vinos de vendimia 2026       (campaña activa, prioridad demo)
 *
 * Depende de: WineryOenologistsSeeder
 */
class WineryWinesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // Rotación de tipos (10 slots): 30% tinto, 30% blanco, 20% rosado, 10% espumoso, 10% dulce
    private const TYPE_POOL = ['red', 'red', 'red', 'white', 'white', 'white', 'rose', 'rose', 'sparkling', 'sweet'];

    private const NAMES_BY_TYPE = [
        'red'      => ['Tinto Joven', 'Tinto Barrica', 'Tinto Crianza', 'Tinto Reserva', 'Gran Tinto', 'Tinto Ecológico', 'Tinto Maceración Carbónica'],
        'white'    => ['Blanco Joven', 'Blanco Barrica', 'Malvasía Seco', 'Vijariego', 'Blanco Ecológico', 'Listán Blanco', 'Marmajuelo'],
        'rose'     => ['Rosado', 'Rosado Joven', 'Rosado Ecológico', 'Rosé Barrica'],
        'sparkling'=> ['Espumoso Brut', 'Espumoso Rosé', 'Brut Nature', 'Cava Especial'],
        'sweet'    => ['Malvasía Dulce', 'Dulce Natural', 'Generoso', 'Mistela'],
    ];

    private const VARIETIES_BY_TYPE = [
        'red'      => ['Listán Negro 100%', 'Negramoll 80% · Listán Negro 20%', 'Listán Negro 70% · Negramoll 30%', 'Baboso Negro 100%', 'Tintilla 100%', 'Listán Negro 90% · Negramoll 10%', 'Listán Negro 60% · Baboso 40%'],
        'white'    => ['Listán Blanco 100%', 'Malvasía Volcánica 100%', 'Vijariego 100%', 'Marmajuelo 100%', 'Listán Blanco 60% · Vijariego 40%', 'Listán Blanco 80% · Marmajuelo 20%', 'Gual 100%'],
        'rose'     => ['Listán Negro 100%', 'Negramoll 100%', 'Listán Negro 70% · Negramoll 30%', 'Baboso Negro 100%'],
        'sparkling'=> ['Malvasía 70% · Listán Blanco 30%', 'Listán Negro 100%', 'Listán Blanco 100%', 'Vijariego 100%'],
        'sweet'    => ['Malvasía Volcánica 100%', 'Moscatel 100%', 'Listán Blanco 100%', 'Gual 100%'],
    ];

    private const AGING_POOL = ['joven', 'barrica', 'joven', 'crianza', 'joven', 'barrica', 'reserva'];

    // Volúmenes (litros) por tipo [min, max]
    private const VOLUME_RANGE = [
        'red'      => [6000,  25000],
        'white'    => [4000,  18000],
        'rose'     => [1500,   7000],
        'sparkling'=> [ 800,   4000],
        'sweet'    => [ 400,   2000],
    ];

    // Categorías por tipo
    private const CATEGORIES_BY_TYPE = [
        'red'      => ['Vino de mesa', 'Vino de calidad', 'Vino de calidad superior', 'Vino ecológico'],
        'white'    => ['Vino de mesa', 'Vino de calidad', 'Vino de calidad superior', 'Vino ecológico'],
        'rose'     => ['Vino de mesa', 'Vino de calidad', 'Vino ecológico'],
        'sparkling'=> ['Vino espumoso', 'Vino espumoso de calidad'],
        'sweet'    => ['Vino generoso dulce', 'Vino licoroso'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $oenologists = DB::table('oenologists')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        $rows = [];

        // ── Grupo A: 225 vinos de vendimias 2022–2025 ───────────────────────────
        for ($i = 0; $i < 225; $i++) {
            $rows[] = $this->buildWine($i, $oenologists, $now, isGroup2026: false);
        }

        // ── Grupo B: 225 vinos de vendimia 2026 ────────────────────────────────
        for ($i = 225; $i < 450; $i++) {
            $rows[] = $this->buildWine($i, $oenologists, $now, isGroup2026: true);
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('wines')->insert($chunk);
        }

        $totalHistorico = count(array_filter($rows, fn($r) => $r['vintage'] < 2026));
        $total2026      = count(array_filter($rows, fn($r) => $r['vintage'] === 2026));
        $this->command->info("✅ Vinos: " . count($rows) . " registros ({$totalHistorico} vendimias 2022–2025 + {$total2026} vendimia 2026)");
    }

    private function buildWine(int $i, array $oenologists, $now, bool $isGroup2026): array
    {
        $type    = self::TYPE_POOL[$i % count(self::TYPE_POOL)];
        $namePool= self::NAMES_BY_TYPE[$type];
        $baseName= $namePool[$i % count($namePool)];
        $variety = self::VARIETIES_BY_TYPE[$type][$i % count(self::VARIETIES_BY_TYPE[$type])];
        $aging   = self::AGING_POOL[$i % count(self::AGING_POOL)];
        $catPool = self::CATEGORIES_BY_TYPE[$type];
        $category= $catPool[$i % count($catPool)];
        $oenId   = !empty($oenologists) ? $oenologists[$i % count($oenologists)] : null;

        [$volMin, $volMax] = self::VOLUME_RANGE[$type];

        if ($isGroup2026) {
            $vintage = 2026;
            // 2026: principalmente in_progress, algunos aged recién iniciados
            $status  = match ($i % 6) {
                0       => 'aged',
                default => 'in_progress',
            };
            $volume = round(($volMin + ($i % 10) * ($volMax - $volMin) / 10) / 1000 * 1000, 3);
        } else {
            $vintagePool = [2022, 2023, 2024, 2025];
            $vintage     = $vintagePool[$i % 4];
            $status = match ($vintage) {
                2022    => ['sold', 'bottled', 'bottled', 'sold'][$i % 4],
                2023    => ['bottled', 'aged', 'bottled', 'aged'][$i % 4],
                2024    => ['bottled', 'bottled', 'aged', 'in_progress'][$i % 4],
                default => ['in_progress', 'aged', 'in_progress', 'aged'][$i % 4],
            };
            $volume = $status === 'sold'
                ? 0.000
                : round(($volMin + ($i % 10) * ($volMax - $volMin) / 10) / 1000 * 1000, 3);
        }

        $initialKg = $status === 'sold'
            ? round(($volMin + ($i % 8) * ($volMax - $volMin) / 8) * 1.35, 3)
            : round($volume * 1.35, 3);

        $isOrganic = $i % 9 === 0;
        $isMust    = false;

        return [
            'user_id'               => self::WINERY_USER_ID,
            'name'                  => "$baseName $vintage",
            'vintage'               => $vintage,
            'wine_type'             => $type,
            'aging_type'            => in_array($type, ['sweet', 'sparkling']) ? 'crianza' : $aging,
            'category'              => $category,
            'status'                => $status,
            'variety'               => $variety,
            'volume_liters'         => $volume,
            'initial_quantity_kg'   => $initialKg,
            'is_organic'            => $isOrganic,
            'is_must'               => $isMust,
            'oenologist_id'         => $oenId,
            'internal_code'         => 'WS-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
            'notes'                 => $isGroup2026
                ? "Vendimia 2026 — elaboración en curso."
                : "Vendimia $vintage — archivo histórico.",
            'trace_token'           => null,
            'created_at'            => $now,
            'updated_at'            => $now,
        ];
    }

    private function cleanup(): void
    {
        DB::table('wines')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
