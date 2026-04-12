<?php

namespace App\Livewire\Producer\IntegratedEstate;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\PhenologyObservation;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $filterCampaign = '';
    public string $filterPlot     = '';

    public function mount(): void
    {
        $active = Campaign::where('viticulturist_id', Auth::id())
            ->where('status', 'active')
            ->orderByDesc('year')
            ->first();

        $this->filterCampaign = (string) ($active?->id ?? '');
    }

    public function render()
    {
        $userId    = Auth::id();
        $campaigns = Campaign::where('viticulturist_id', $userId)
            ->orderByDesc('year')
            ->get(['id', 'name', 'year']);

        $plotsQuery = Plot::where('user_id', $userId)->orderBy('name');
        $plots      = $plotsQuery->get(['id', 'name', 'municipality', 'area_ha']);

        $data = $this->filterCampaign
            ? $this->buildEstateData($userId, (int) $this->filterCampaign)
            : null;

        return view('livewire.producer.integrated-estate.index', [
            'campaigns' => $campaigns,
            'plots'     => $plots,
            'data'      => $data,
        ])->layout('layouts.app', [
            'title'       => 'Panel de Finca Integral',
            'description' => 'Vista unificada de parcelas, plantaciones, fenología y actividades',
        ]);
    }

    private function buildEstateData(int $userId, int $campaignId): array
    {
        $plotFilter = $this->filterPlot ? (int) $this->filterPlot : null;

        // ── Parcelas con plantaciones y último dato de teledetección ──────
        $plotsQuery = Plot::where('user_id', $userId)
            ->with([
                'plantings' => fn ($q) => $q->with('grapeVariety:id,name')
                    ->whereNull('removal_date')
                    ->orderBy('name'),
                'latestRemoteSensing',
            ]);

        if ($plotFilter) {
            $plotsQuery->where('id', $plotFilter);
        }

        $plotsWithData = $plotsQuery->orderBy('name')->get();

        // ── Stats generales ──────────────────────────────────────────────
        $totalArea      = $plotsWithData->sum('area_ha');
        $totalPlantings = $plotsWithData->sum(fn ($p) => $p->plantings->count());
        $varietyCounts  = $plotsWithData->flatMap->plantings
            ->groupBy(fn ($pl) => $pl->grapeVariety?->name ?? 'Sin variedad')
            ->map->count()
            ->sortDesc();

        // ── Fenología: últimas observaciones por parcela/plantación ──────
        $plantingIds = $plotsWithData->flatMap->plantings->pluck('id');

        $latestPhenology = PhenologyObservation::whereIn('plot_planting_id', $plantingIds)
            ->where('campaign_id', $campaignId)
            ->select('plot_planting_id', 'event', 'obs_date', 'bbch_code')
            ->orderByDesc('obs_date')
            ->get()
            ->groupBy('plot_planting_id')
            ->map->first();

        // ── Actividades recientes de la campaña ─────────────────────────
        $actQuery = AgriculturalActivity::where('viticulturist_id', $userId)
            ->where('campaign_id', $campaignId)
            ->with('plot:id,name');

        if ($plotFilter) {
            $actQuery->where('plot_id', $plotFilter);
        }

        $activityCounts = (clone $actQuery)
            ->select('activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type')
            ->toArray();

        $recentActivities = (clone $actQuery)
            ->orderByDesc('activity_date')
            ->take(8)
            ->get(['id', 'plot_id', 'activity_type', 'activity_date', 'phenological_stage']);

        // ── Rendimientos: estimado vs real por parcela ───────────────────
        $yieldPerPlot = [];
        foreach ($plotsWithData as $plot) {
            $plotYield = ['plot_name' => $plot->name, 'estimated' => 0, 'actual' => 0];
            foreach ($plot->plantings as $planting) {
                $variance = $planting->getYieldVariance($campaignId);
                if ($variance) {
                    $plotYield['estimated'] += $variance['estimated'];
                    $plotYield['actual']    += $variance['actual'];
                }
            }
            if ($plotYield['estimated'] > 0 || $plotYield['actual'] > 0) {
                $plotYield['variance_pct'] = $plotYield['estimated'] > 0
                    ? round(($plotYield['actual'] - $plotYield['estimated']) / $plotYield['estimated'] * 100, 1)
                    : null;
                $yieldPerPlot[] = $plotYield;
            }
        }

        // ── Life cycle stages de plantaciones ────────────────────────────
        $lifeCycleStages = $plotsWithData->flatMap->plantings
            ->groupBy(fn ($pl) => $pl->life_cycle_stage ?? 'desconocido')
            ->map->count()
            ->toArray();

        return [
            'plotsWithData'    => $plotsWithData,
            'totalArea'        => $totalArea,
            'totalPlantings'   => $totalPlantings,
            'varietyCounts'    => $varietyCounts,
            'latestPhenology'  => $latestPhenology,
            'activityCounts'   => $activityCounts,
            'recentActivities' => $recentActivities,
            'yieldPerPlot'     => $yieldPerPlot,
            'lifeCycleStages'  => $lifeCycleStages,
        ];
    }
}
