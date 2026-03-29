<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgaetePlantingsSeeder extends Seeder
{
    // ── Sistemas de conducción con pesos (adaptados a Canarias) ───────────────
    // id: 1=Vaso, 2=Espaldera simple, 4=Cordón bilateral, 9=Pérgola/Parral
    private const TRAINING_WEIGHTS = [
        1 => 40, // Vaso (muy habitual en Canarias)
        2 => 30, // Espaldera simple
        4 => 15, // Cordón bilateral
        9 => 15, // Pérgola / Parral (latada canaria)
    ];

    // ── Variedades con pesos de frecuencia ────────────────────────────────────
    // 1=Tempranillo, 2=Garnacha Tinta, 3=Graciano, 4=Mazuelo
    // 5=Verdejo, 6=Viura, 7=Airén
    private const VARIETY_WEIGHTS = [
        1 => 28, // Tempranillo
        2 => 20, // Garnacha Tinta
        3 =>  8, // Graciano
        4 =>  6, // Mazuelo
        5 => 18, // Verdejo
        6 => 12, // Viura (Macabeo)
        7 =>  8, // Airén
    ];

    // ── Portainjertos frecuentes en Canarias ──────────────────────────────────
    private const ROOTSTOCKS = [
        '110R'              => 35,
        'Rupestris du Lot'  => 20,
        '1103P'             => 20,
        'SO4'               => 12,
        '41B'               =>  8,
        'Propio (pie franco)' => 5,
    ];

    private const DESIGNATIONS = [
        'DO Gran Canaria'  => 55,
        'Vino de la Tierra de Gran Canaria' => 25,
        null               => 20,
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ── 1. Obtener parcelas de los viticultores seeder ─────────────────
        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->pluck('id');

        if ($vitIds->isEmpty()) {
            $this->command->warn('No se encontraron viticultores del seeder Agaete. Ejecuta AgaetePlotsSeeder primero.');
            return;
        }

        $plots = DB::table('plots')
            ->whereIn('viticulturist_id', $vitIds)
            ->get(['id', 'area'])
            ->toArray();

        $this->command->info('Creando plantaciones para ' . count($plots) . ' parcelas...');

        // ── 2. Construir lookup de training_system_id disponibles ─────────
        $trainingIds = DB::table('training_systems')
            ->whereIn('id', array_keys(self::TRAINING_WEIGHTS))
            ->pluck('id')
            ->toArray();

        if (empty($trainingIds)) {
            $this->command->warn('No se encontraron sistemas de conducción. Usando NULL.');
        }

        // ── 3. Insertar plantaciones ───────────────────────────────────────
        $rows  = [];
        $total = 0;

        foreach ($plots as $plot) {
            $area = (float) $plot->area;
            if ($area <= 0) $area = 0.10;

            // Nº de variedades según tamaño de la parcela
            $numVarieties = $this->numVarieties($area);

            // Repartir área entre variedades
            $areas = $this->splitArea($area, $numVarieties);

            // Evitar repetir variedad en la misma parcela
            $usedVarieties = [];

            foreach ($areas as $plantedArea) {
                $varId       = $this->weightedRand(self::VARIETY_WEIGHTS, $usedVarieties);
                $usedVarieties[] = $varId;
                $trainId     = !empty($trainingIds) ? $this->weightedRand(array_fill_keys($trainingIds, 1) + array_intersect_key(self::TRAINING_WEIGHTS, array_flip($trainingIds))) : null;
                $density     = mt_rand(1800, 4200);
                $vineCount   = (int) round($density * $plantedArea);
                $plantYear   = mt_rand(1975, 2022);
                $harvestLim  = round($plantedArea * mt_rand(5000, 9000) / 100) * 100;
                $irrigated   = (mt_rand(1, 100) <= 22); // 22% riego
                $rootstock   = $this->weightedRand(self::ROOTSTOCKS);
                $do          = $this->weightedRand(self::DESIGNATIONS);
                $rightType   = $plantYear >= 2016
                    ? ($this->weightedRand(['nueva' => 50, 'replantacion' => 40, 'conversion' => 10]))
                    : null;
                $authDate    = ($rightType !== null)
                    ? now()->subYears(now()->year - $plantYear)->subDays(mt_rand(0, 180))->format('Y-m-d')
                    : null;
                $rowSpacing  = $this->roundTo(mt_rand(200, 350) / 100, 0.25);
                $vineSpacing = $this->roundTo(mt_rand(80, 160) / 100, 0.10);

                $rows[] = [
                    'plot_id'               => $plot->id,
                    'grape_variety_id'      => $varId,
                    'area_planted'          => round($plantedArea, 3),
                    'harvest_limit_kg'      => $harvestLim,
                    'planting_year'         => $plantYear,
                    'vine_count'            => $vineCount,
                    'density'               => $density,
                    'row_spacing'           => $rowSpacing,
                    'vine_spacing'          => $vineSpacing,
                    'rootstock'             => $rootstock,
                    'training_system_id'    => $trainId,
                    'irrigated'             => $irrigated,
                    'status'                => 'active',
                    'active'                => true,
                    'designation_of_origin' => $do,
                    'right_type'            => $rightType,
                    'authorization_date'    => $authDate,
                    'planting_authorization'=> $rightType !== null ? 'AUTH-GC-' . str_pad($plot->id, 5, '0', STR_PAD_LEFT) . '-' . $plantYear : null,
                    'notes'                 => null,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
                $total++;
            }
        }

        // Insertar en chunks de 500
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('plot_plantings')->insert($chunk);
        }

        $this->command->info("✓ {$total} plantaciones insertadas para " . count($plots) . ' parcelas.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function numVarieties(float $area): int
    {
        if ($area < 0.30) return 1;
        if ($area < 1.50) return (mt_rand(1, 100) <= 75) ? 1 : 2;
        if ($area < 5.00) return (mt_rand(1, 100) <= 55) ? 1 : 2;
        return (mt_rand(1, 100) <= 30) ? 1 : ((mt_rand(1, 100) <= 70) ? 2 : 3);
    }

    /**
     * Divide el área en $n partes realistas (múltiplos de 0.01 ha).
     *
     * @return float[]
     */
    private function splitArea(float $total, int $n): array
    {
        if ($n === 1) return [$total];

        $parts  = [];
        $remain = $total;

        for ($i = 1; $i < $n; $i++) {
            $minFrac = 0.20;
            $maxFrac = 1.00 - ($minFrac * ($n - $i));
            $frac    = ($minFrac + mt_rand(0, (int)(($maxFrac - $minFrac) * 100)) / 100);
            $part    = round($remain * $frac, 2);
            $parts[] = max($part, 0.01);
            $remain  = round($remain - $part, 3);
        }
        $parts[] = max(round($remain, 3), 0.01);

        return $parts;
    }

    /**
     * Selección aleatoria ponderada. Excluye las claves de $exclude.
     *
     * @param array<int|string,int> $weights
     * @param array $exclude  claves a ignorar (para evitar variedad repetida)
     */
    private function weightedRand(array $weights, array $exclude = []): mixed
    {
        $pool = array_filter($weights, fn($k) => !in_array($k, $exclude), ARRAY_FILTER_USE_KEY);
        if (empty($pool)) $pool = $weights; // fallback: sin excluir

        $total  = array_sum($pool);
        $rand   = mt_rand(1, $total);
        $cumul  = 0;
        foreach ($pool as $key => $weight) {
            $cumul += $weight;
            if ($rand <= $cumul) return $key;
        }
        return array_key_first($pool);
    }

    /** Redondea al múltiplo más cercano de $step */
    private function roundTo(float $value, float $step): float
    {
        return round($value / $step) * $step;
    }

    private function cleanup(): void
    {
        $vitIds = DB::table('users')
            ->where('email', 'like', 'vit.agaete.%@seed.test')
            ->pluck('id');

        if ($vitIds->isEmpty()) return;

        $plotIds = DB::table('plots')
            ->whereIn('viticulturist_id', $vitIds)
            ->pluck('id');

        if ($plotIds->isEmpty()) return;

        $deleted = DB::table('plot_plantings')
            ->whereIn('plot_id', $plotIds)
            ->delete();

        if ($deleted > 0) {
            $this->command->line("  Eliminadas {$deleted} plantaciones anteriores del seeder.");
        }
    }
}
