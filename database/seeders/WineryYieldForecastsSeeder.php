<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera previsiones de rendimiento y aforos del viticultor para la bodega (user_id=1).
 *
 * Crea:
 *  - winery_yield_forecasts: previsión de bodega para cada (planting, campaña, vintage)
 *  - estimated_yields: aforo del viticultor (confirmado) por cada (planting, campaña)
 *
 * Depende de: WineryGrapeReceptionsSeeder (viticultores, plots, plantings, campañas, batches).
 */
class WineryYieldForecastsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // ── Obtener viticultores vinculados ──────────────────────────────────
        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores vinculados. Saltando previsiones de rendimiento.');

            return;
        }

        // ── Obtener campañas de la bodega (creadas por WineryGrapeReceptionsSeeder) ──
        $wineryCampaigns = DB::table('campaigns')
            ->where('viticulturist_id', self::WINERY_USER_ID)
            ->pluck('id', 'year')
            ->toArray();

        if (empty($wineryCampaigns)) {
            $this->command->warn('  ⚠️  Sin campañas de bodega. Ejecuta WineryGrapeReceptionsSeeder primero.');

            return;
        }

        // ── Obtener plantaciones con sus viticultores ────────────────────────
        $plotPlantings = DB::table('plot_plantings')
            ->join('plots', 'plot_plantings.plot_id', '=', 'plots.id')
            ->whereIn('plots.viticulturist_id', $viticulturistIds)
            ->select(
                'plot_plantings.id',
                'plot_plantings.area_planted',
                'plot_plantings.harvest_limit_kg',
                'plot_plantings.planting_year',
                'plots.viticulturist_id'
            )
            ->get();

        if ($plotPlantings->isEmpty()) {
            $this->command->warn('  ⚠️  Sin plantaciones de viticultores vinculados.');

            return;
        }

        // ── Obtener campañas de viticultores (para estimated_yields) ─────────
        $vitCampaigns = DB::table('campaigns')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->get()
            ->groupBy('viticulturist_id');

        // ── Obtener batches reales para calcular forecasts coherentes ────────
        $batches = DB::table('grape_reception_batches')
            ->where('winery_id', self::WINERY_USER_ID)
            ->get()
            ->keyBy(fn ($b) => $b->plot_planting_id.'_'.$b->vintage_year);

        $forecastRows = [];
        $estimatedRows = [];
        $totalForecasts = 0;
        $totalEstimates = 0;
        $idx = 0;

        foreach ($plotPlantings as $planting) {
            foreach ([2023, 2024, 2025] as $vintage) {
                $wineryCampaignId = $wineryCampaigns[$vintage] ?? null;
                if (! $wineryCampaignId) {
                    continue;
                }

                $batchKey = $planting->id.'_'.$vintage;
                $batch = $batches->get($batchKey);
                $receivedKg = $batch ? (float) $batch->total_weight_kg : 0;

                // ── Previsión de bodega (winery_yield_forecast) ──────────

                // Estimate = received ± 5-20% para que haya variabilidad
                $variationPct = match ($vintage) {
                    2023 => mt_rand(-5, 10) / 100,   // retrospective: close to actual
                    2024 => mt_rand(-8, 12) / 100,   // good year: slightly over
                    2025 => mt_rand(-15, 20) / 100,  // current: more uncertainty
                };

                $estimatedKg = $receivedKg > 0
                    ? round($receivedKg * (1 + $variationPct), 3)
                    : round(($planting->area_planted ?? 1) * mt_rand(4000, 7000), 3);

                // Past vintages: confirmed; 2025: mix of confirmed and draft
                $forecastStatus = match ($vintage) {
                    2023, 2024 => 'confirmed',
                    2025 => $idx % 4 === 0 ? 'draft' : 'confirmed',
                };

                $daysAgo = match ($vintage) {
                    2023 => mt_rand(800, 950),
                    2024 => mt_rand(400, 550),
                    2025 => mt_rand(10, 120),
                };

                $forecastNotes = $this->forecastNote($vintage, $forecastStatus, $variationPct);

                $forecastRows[] = [
                    'winery_id' => self::WINERY_USER_ID,
                    'viticulturist_id' => $planting->viticulturist_id,
                    'plot_planting_id' => $planting->id,
                    'campaign_id' => $wineryCampaignId,
                    'vintage_year' => $vintage,
                    'estimated_kg' => max(100, $estimatedKg),
                    'estimation_date' => now()->subDays($daysAgo)->toDateString(),
                    'status' => $forecastStatus,
                    'notes' => $forecastNotes,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $totalForecasts++;

                // ── Aforo del viticultor (estimated_yield) ───────────────

                $vitCamps = $vitCampaigns->get($planting->viticulturist_id);
                $vitCampaignForYear = $vitCamps?->firstWhere('year', $vintage);

                if ($vitCampaignForYear) {
                    // Viticulturist estimate: close to actual but from field sampling perspective
                    $viticVariation = mt_rand(-10, 15) / 100;
                    $viticEstimate = $receivedKg > 0
                        ? round($receivedKg * (1 + $viticVariation), 2)
                        : round(($planting->area_planted ?? 1) * mt_rand(3500, 6500), 2);

                    $area = (float) ($planting->area_planted ?? 1);
                    $yieldPerHa = $area > 0 ? round($viticEstimate / $area, 2) : null;

                    // Field sampling data (realistic for Canarian viticulture)
                    $bunchesPerPlant = mt_rand(8, 18);
                    $bunchWeightGrams = mt_rand(120, 280);
                    $totalPlantsSampled = mt_rand(15, 40);

                    $estimatedRows[] = [
                        'plot_planting_id' => $planting->id,
                        'campaign_id' => $vitCampaignForYear->id,
                        'estimated_by' => $planting->viticulturist_id,
                        'vintage' => $vintage,
                        'estimated_yield_per_hectare' => $yieldPerHa,
                        'estimated_total_yield' => max(50, $viticEstimate),
                        'estimation_date' => now()->subDays($daysAgo + mt_rand(5, 30))->toDateString(),
                        'estimation_method' => ['visual', 'sampling', 'historical', 'satellite', 'other'][mt_rand(0, 4)],
                        'estimation_round' => $vintage < 2025 ? 4 : mt_rand(2, 3),
                        'status' => $vintage < 2025 ? 'confirmed' : ($idx % 5 === 0 ? 'draft' : 'confirmed'),
                        'active' => true,
                        'bunches_per_plant' => $bunchesPerPlant,
                        'bunch_weight_grams' => $bunchWeightGrams,
                        'total_plants_sampled' => $totalPlantsSampled,
                        'health_percentage' => round(mt_rand(85, 100), 1),
                        'health_status' => $idx % 8 === 0 ? 'good' : 'excellent',
                        'potential_alcohol' => round(mt_rand(115, 145) / 10, 1),
                        'notes' => $this->estimateNote($vintage),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $totalEstimates++;
                }

                $idx++;
            }
        }

        // ── Insertar por lotes ───────────────────────────────────────────────
        if (! empty($forecastRows)) {
            foreach (array_chunk($forecastRows, 50) as $chunk) {
                DB::table('winery_yield_forecasts')->insert($chunk);
            }
        }

        if (! empty($estimatedRows)) {
            foreach (array_chunk($estimatedRows, 50) as $chunk) {
                DB::table('estimated_yields')->insert($chunk);
            }
        }

        $this->command->info("✅ Previsiones de rendimiento: {$totalForecasts} forecasts + {$totalEstimates} aforos viticultor");
    }

    private function forecastNote(int $vintage, string $status, float $variation): string
    {
        if ($status === 'draft') {
            return 'Estimación preliminar pendiente de confirmación en campo.';
        }

        return match ($vintage) {
            2023 => $variation > 0
                ? 'Previsión ajustada. Condiciones climáticas normales para la añada.'
                : 'Producción ligeramente inferior por lluvias de agosto.',
            2024 => $variation > 0.05
                ? 'Excelente campaña 2024. Maduración óptima, rendimientos superiores.'
                : 'Añada 2024 con buena concentración aromática.',
            2025 => $variation > 0
                ? 'Previsión optimista para 2025. Primavera lluviosa favoreció el cuaje.'
                : 'Reducción estimada por episodio de calima en julio.',
        };
    }

    private function estimateNote(int $vintage): string
    {
        return match ($vintage) {
            2023 => 'Aforo de campo campaña 2023. Muestreo en parcela completa.',
            2024 => 'Estimación revisada tras envero. Buen estado sanitario general.',
            2025 => 'Aforo pre-vendimia 2025. Pendiente confirmación post-cuaje.',
        };
    }

    private function cleanup(): void
    {
        DB::table('winery_yield_forecasts')->where('winery_id', self::WINERY_USER_ID)->delete();

        // Limpiar estimated_yields de viticultores demo vinculados
        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (! empty($viticulturistIds)) {
            DB::table('estimated_yields')
                ->whereIn('estimated_by', $viticulturistIds)
                ->delete();
        }
    }
}
