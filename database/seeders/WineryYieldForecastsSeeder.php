<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryYieldForecastsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores vinculados. Saltando previsiones de rendimiento.');
            return;
        }

        $now         = now();
        $vintageYear = (int) now()->format('Y');

        // Intentar obtener campaigns de los viticultores vinculados
        $campaigns = DB::table('campaigns')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->orderByDesc('year')
            ->get();

        // Intentar obtener plot_plantings de esos viticultores
        $plotPlantings = DB::table('plot_plantings')
            ->join('plots', 'plot_plantings.plot_id', '=', 'plots.id')
            ->whereIn('plots.user_id', $viticulturistIds)
            ->select('plot_plantings.id', 'plots.user_id as viticulturist_id')
            ->get();

        if ($plotPlantings->isEmpty() || $campaigns->isEmpty()) {
            $this->command->warn('  ⚠️  Sin plantaciones o campañas de viticultores vinculados. Saltando previsiones.');
            return;
        }

        $rows = [];

        $forecasts = [
            ['pct_est' => 95,  'days_ago' => 120, 'status' => 'confirmed', 'received_pct' => 102, 'notes' => 'Previsión inicial confirmada. Condiciones climatológicas favorables.'],
            ['pct_est' => 80,  'days_ago' => 110, 'status' => 'confirmed', 'received_pct' => 88,  'notes' => 'Ligera reducción por granizo en marzo.'],
            ['pct_est' => 100, 'days_ago' => 100, 'status' => 'confirmed', 'received_pct' => 95,  'notes' => 'Añada con buena concentración aromática.'],
            ['pct_est' => 110, 'days_ago' => 90,  'status' => 'confirmed', 'received_pct' => 107, 'notes' => 'Producción ligeramente superior a lo estimado.'],
            ['pct_est' => 70,  'days_ago' => 80,  'status' => 'confirmed', 'received_pct' => 65,  'notes' => 'Reducción por sequía en junio-julio.'],
            ['pct_est' => 120, 'days_ago' => 30,  'status' => 'draft',     'received_pct' => null, 'notes' => 'Estimación optimista — cosecha tardía en curso.'],
            ['pct_est' => 90,  'days_ago' => 20,  'status' => 'draft',     'received_pct' => null, 'notes' => null],
            ['pct_est' => 85,  'days_ago' => 10,  'status' => 'draft',     'received_pct' => null, 'notes' => 'Pendiente confirmación en campo.'],
        ];

        $usedPairs = [];

        foreach ($forecasts as $i => $fc) {
            $planting  = $plotPlantings[$i % count($plotPlantings)];
            $vitId     = $planting->viticulturist_id;
            $campaign  = $campaigns->firstWhere('viticulturist_id', $vitId) ?? $campaigns->first();

            if (!$campaign) continue;

            // Evitar duplicado por unique constraint (winery_id, plot_planting_id, campaign_id)
            $pairKey = $planting->id . '_' . $campaign->id;
            if (isset($usedPairs[$pairKey])) continue;
            $usedPairs[$pairKey] = true;

            $estimatedKg = rand(800, 4500);
            $receivedKg  = $fc['received_pct'] ? round($estimatedKg * $fc['received_pct'] / 100, 3) : null;

            $rows[] = [
                'winery_id'        => self::WINERY_USER_ID,
                'viticulturist_id' => $vitId,
                'plot_planting_id' => $planting->id,
                'campaign_id'      => $campaign->id,
                'vintage_year'     => $vintageYear,
                'estimated_kg'     => $estimatedKg,
                'estimation_date'  => now()->subDays($fc['days_ago'])->toDateString(),
                'status'           => $fc['status'],
                'notes'            => $fc['notes'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (empty($rows)) {
            $this->command->warn('  ⚠️  No se generaron previsiones (sin combinaciones únicas disponibles).');
            return;
        }

        DB::table('winery_yield_forecasts')->insert($rows);
        $this->command->info('✅ Previsiones de rendimiento: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('winery_yield_forecasts')->where('winery_id', self::WINERY_USER_ID)->delete();
    }
}
