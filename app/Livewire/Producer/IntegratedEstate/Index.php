<?php

namespace App\Livewire\Producer\IntegratedEstate;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $filterCampaign = '';

    public string $filterPlot = '';

    public function mount(): void
    {
        $active = Campaign::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->orderByDesc('year')
            ->first();

        $this->filterCampaign = (string) ($active->id ?? '');
    }

    #[Computed(cache: true, seconds: 300)]
    public function campaigns()
    {
        return Campaign::where('viticulturist_id', Auth::id())
            ->orderByDesc('year')
            ->get(['id', 'name', 'year']);
    }

    #[Computed(cache: true, seconds: 300)]
    public function plots()
    {
        return Plot::where('viticulturist_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        $data = $this->filterCampaign
            ? $this->buildDashboardData(Auth::id(), (int) $this->filterCampaign)
            : null;

        return view('livewire.producer.integrated-estate.index', [
            'campaigns' => $this->campaigns,
            'plots' => $this->plots,
            'data' => $data,
        ])->layout('layouts.app', [
            'title' => __('Panel de Finca Integral'),
            'description' => __('Vista unificada de parcelas, plantaciones, fenología y actividades'),
        ]);
    }

    private function buildDashboardData(int $userId, int $campaignId): array
    {
        $plotFilter = $this->filterPlot ? (int) $this->filterPlot : null;

        // ── Parcelas (solo agregados, sin eager loading masivo) ───────────
        $plotsQuery = Plot::where('viticulturist_id', $userId);
        if ($plotFilter) {
            $plotsQuery->where('id', $plotFilter);
        }
        $plots = $plotsQuery->orderBy('name')->with('municipality:id,name')->get(['id', 'name', 'area', 'municipality_id']);

        $plotIds = $plots->pluck('id');

        // ── Plantaciones activas (una sola query, sin relaciones anidadas) ─
        $plantings = PlotPlanting::whereIn('plot_id', $plotIds)
            ->whereNull('uprooting_date')
            ->with('grapeVariety:id,name')
            ->get(['id', 'plot_id', 'planting_year', 'grape_variety_id']);

        $plantingIds = $plantings->pluck('id');

        // ── Stats generales ───────────────────────────────────────────────
        $totalArea = $plots->sum('area');
        $totalPlantings = $plantings->count();

        $varietyCounts = $plantings
            ->groupBy(fn ($pl) => $pl->grapeVariety->name ?? 'Sin variedad')
            ->map->count()
            ->sortDesc();

        $lifeCycleStages = $plantings
            ->groupBy(fn ($pl) => $pl->life_cycle_stage ?? 'desconocido')
            ->map->count()
            ->toArray();

        // ── Actividades por tipo (campaña) ────────────────────────────────
        $actQuery = AgriculturalActivity::where('viticulturist_id', $userId)
            ->where('campaign_id', $campaignId);

        if ($plotFilter) {
            $actQuery->where('plot_id', $plotFilter);
        }

        $activityCounts = (clone $actQuery)
            ->select('activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type')
            ->toArray();

        $recentActivities = (clone $actQuery)
            ->with('plot:id,name')
            ->orderByDesc('activity_date')
            ->take(8)
            ->get(['id', 'plot_id', 'activity_type', 'activity_date', 'phenological_stage']);

        // ── Rendimiento: batch query — 2 queries en vez de 2N ────────────
        $yieldPerPlot = $this->buildYieldSummary($plantings, $plotIds, $campaignId);

        return [
            'plots' => $plots,
            'totalArea' => $totalArea,
            'totalPlantings' => $totalPlantings,
            'varietyCounts' => $varietyCounts,
            'lifeCycleStages' => $lifeCycleStages,
            'activityCounts' => $activityCounts,
            'recentActivities' => $recentActivities,
            'yieldPerPlot' => $yieldPerPlot,
        ];
    }

    /**
     * Calcula rendimiento estimado vs real con 2 queries totales (no N+1).
     *
     * @param mixed $plantings
     * @param mixed $plotIds
     */
    private function buildYieldSummary($plantings, $plotIds, int $campaignId): array
    {
        if ($plantings->isEmpty()) {
            return [];
        }

        $plantingIds = $plantings->pluck('id');

        // Query 1: rendimientos estimados
        $estimated = EstimatedYield::whereIn('plot_planting_id', $plantingIds)
            ->where('campaign_id', $campaignId)
            ->pluck('estimated_total_yield', 'plot_planting_id');

        if ($estimated->isEmpty()) {
            return [];
        }

        // Query 2: kg reales cosechados (JOIN en vez de whereHas para evitar subquery)
        $actual = Harvest::whereIn('harvests.plot_planting_id', $estimated->keys())
            ->where('harvests.status', 'active')
            ->join('agricultural_activities as aa', 'aa.id', '=', 'harvests.activity_id')
            ->where('aa.campaign_id', $campaignId)
            ->selectRaw('harvests.plot_planting_id, SUM(harvests.total_weight) as total')
            ->groupBy('harvests.plot_planting_id')
            ->pluck('total', 'plot_planting_id');

        // Agrupar por parcela
        $plotNames = Plot::whereIn('id', $plotIds)->pluck('name', 'id');
        $plantingPlotMap = $plantings->pluck('plot_id', 'id');

        $perPlot = [];
        foreach ($estimated as $plantingId => $est) {
            $plotId = $plantingPlotMap[$plantingId] ?? null;
            $plotName = $plotNames[$plotId] ?? 'Desconocida';
            $act = (float) ($actual[$plantingId] ?? 0);
            $estVal = (float) $est;

            if (! isset($perPlot[$plotId])) {
                $perPlot[$plotId] = ['plot_name' => $plotName, 'estimated' => 0, 'actual' => 0];
            }
            $perPlot[$plotId]['estimated'] += $estVal;
            $perPlot[$plotId]['actual'] += $act;
        }

        $result = [];
        foreach ($perPlot as $row) {
            if ($row['estimated'] > 0 || $row['actual'] > 0) {
                $row['variance_pct'] = $row['estimated'] > 0
                    ? round(($row['actual'] - $row['estimated']) / $row['estimated'] * 100, 1)
                    : null;
                $result[] = $row;
            }
        }

        return $result;
    }
}
