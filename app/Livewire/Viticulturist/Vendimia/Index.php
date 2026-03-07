<?php

namespace App\Livewire\Viticulturist\Vendimia;

use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $vintageFilter = '';

    protected $queryString = [
        'vintageFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!$this->vintageFilter) {
            $campaign = Campaign::forViticulturist(Auth::id())->where('active', true)->first();
            if ($campaign) {
                $this->vintageFilter = (string) $campaign->year;
            } else {
                $this->vintageFilter = (string) now()->year;
            }
        }
    }

    public function render()
    {
        $viticulturistId = Auth::id();

        // Campañas disponibles del viticulturist para el filtro
        $campaigns = Campaign::forViticulturist($viticulturistId)
            ->orderBy('year', 'desc')
            ->get();

        $vintageYear = (int) ($this->vintageFilter ?: now()->year);

        // 1. Batches de bodega (lo que cada bodega recibió de este viticulturist)
        $batches = GrapeReceptionBatch::with([
                'plotPlanting.grapeVariety',
                'plotPlanting.plot',
                'winery:id,name',
            ])
            ->where('viticulturist_id', $viticulturistId)
            ->where('vintage_year', $vintageYear)
            ->get()
            ->keyBy(fn($b) => $b->plot_planting_id . '_' . $b->winery_id);

        // 2. Forecasts de bodega para este viticulturist
        $forecasts = WineryYieldForecast::with([
                'plotPlanting.grapeVariety',
                'plotPlanting.plot',
                'winery:id,name',
            ])
            ->where('viticulturist_id', $viticulturistId)
            ->where('vintage_year', $vintageYear)
            ->get()
            ->keyBy(fn($f) => $f->plot_planting_id . '_' . $f->winery_id);

        // Aforos propios del viticultor para la añada (confirmados, última ronda por plantación)
        $plantingIds = $batches->pluck('plot_planting_id')
            ->merge($forecasts->pluck('plot_planting_id'))
            ->unique();

        $estimatedYields = EstimatedYield::whereIn('plot_planting_id', $plantingIds)
            ->whereHas('campaign', fn($q) => $q->where('year', $vintageYear)->where('viticulturist_id', $viticulturistId))
            ->where('status', 'confirmed')
            ->orderByDesc('estimation_round')
            ->get()
            ->keyBy('plot_planting_id');

        // Precargar totales de cosecha del viticultor por plantación (evita N+1)
        $allPlantingIds = $batches->pluck('plot_planting_id')
            ->merge($forecasts->pluck('plot_planting_id'))
            ->unique();

        $harvestTotals = Harvest::whereIn('plot_planting_id', $allPlantingIds)
            ->whereHas('activity', fn($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where('vintage', $vintageYear)
            ->where('status', 'active')
            ->groupBy('plot_planting_id')
            ->selectRaw('plot_planting_id, SUM(total_weight) as total')
            ->pluck('total', 'plot_planting_id');

        // Unión de claves únicas
        $keys = $batches->keys()->merge($forecasts->keys())->unique();

        $rows = $keys->map(function ($key) use ($batches, $forecasts, $estimatedYields, $harvestTotals, $viticulturistId, $vintageYear) {
            $batch    = $batches->get($key);
            $forecast = $forecasts->get($key);
            $source   = $batch ?? $forecast;

            $planting = $source->plotPlanting;
            $winery   = $source->winery;

            if (!$planting) return null;

            $receivedKg   = $batch    ? (float) $batch->total_weight_kg   : 0.0;
            $forecastKg   = ($forecast && $forecast->status === 'confirmed') ? (float) $forecast->estimated_kg : null;
            $myHarvestKg  = (float) ($harvestTotals->get($planting->id) ?? 0);
            $pacLimit     = $planting->effectiveHarvestLimitKg($vintageYear);
            $estimatedYield = $estimatedYields->get($planting->id);
            $estimatedKg  = $estimatedYield ? (float) $estimatedYield->estimated_total_yield : null;

            // Discrepancia cuaderno vs bodega
            $discrepancyKg  = null;
            $discrepancyPct = null;
            if ($myHarvestKg > 0 || $receivedKg > 0) {
                $discrepancyKg  = round(abs($myHarvestKg - $receivedKg), 1);
                $discrepancyPct = $myHarvestKg > 0
                    ? round($discrepancyKg / $myHarvestKg * 100, 1)
                    : null;
            }

            // Estado
            $status = match(true) {
                $myHarvestKg === 0.0 && $receivedKg === 0.0 => 'pending',
                $myHarvestKg > 0    && $receivedKg === 0.0  => 'not_received',
                $myHarvestKg === 0.0 && $receivedKg > 0     => 'received_only',
                $discrepancyPct !== null && $discrepancyPct > 5 => 'discrepancy',
                default                                      => 'ok',
            };

            return [
                'key'             => $key,
                'planting'        => $planting,
                'winery'          => $winery,
                'variety'         => $planting->grapeVariety?->name ?? $planting->name ?? '—',
                'plot'            => $planting->plot?->name ?? '—',
                'area'            => $planting->area_planted ? (float) $planting->area_planted : null,
                'pac_limit'       => $pacLimit,
                'estimated_kg'    => $estimatedKg,
                'forecast_kg'     => $forecastKg,
                'forecast_status' => $forecast?->status,
                'my_harvest_kg'   => $myHarvestKg,
                'received_kg'     => $receivedKg,
                'discrepancy_kg'  => $discrepancyKg,
                'discrepancy_pct' => $discrepancyPct,
                'status'          => $status,
            ];
        })
        ->filter()
        ->values();

        // Stats globales
        $stats = [
            'total_plantings'   => $rows->count(),
            'total_estimated'   => $rows->sum('estimated_kg'),
            'total_my_harvest'  => $rows->sum('my_harvest_kg'),
            'total_received'    => $rows->sum('received_kg'),
            'total_forecast'    => $rows->sum('forecast_kg'),
            'ok_count'          => $rows->where('status', 'ok')->count(),
            'discrepancy_count' => $rows->where('status', 'discrepancy')->count(),
            'not_received_count'=> $rows->where('status', 'not_received')->count(),
        ];

        return view('livewire.viticulturist.vendimia.index', [
            'rows'        => $rows,
            'stats'       => $stats,
            'campaigns'   => $campaigns,
            'vintageYear' => $vintageYear,
        ])->layout('layouts.app');
    }
}
