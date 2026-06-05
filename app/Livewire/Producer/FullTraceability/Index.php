<?php

namespace App\Livewire\Producer\FullTraceability;

use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $filterCampaign = '';

    public string $filterPlot = '';

    public string $filterWine = '';

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
        $userId = Auth::id();
        $campaigns = Campaign::where('viticulturist_id', $userId)
            ->orderByDesc('year')
            ->get(['id', 'name', 'year']);

        $plots = Plot::where('viticulturist_id', $userId)->orderBy('name')->get(['id', 'name']);

        $wines = Wine::where('user_id', $userId)
            ->orderByDesc('vintage')
            ->orderBy('name')
            ->get(['id', 'name', 'vintage', 'wine_type']);

        $traceData = $this->filterCampaign
            ? $this->buildTraceability($userId)
            : null;

        return view('livewire.producer.full-traceability.index', [
            'campaigns' => $campaigns,
            'plots' => $plots,
            'wines' => $wines,
            'traceData' => $traceData,
        ])->layout('layouts.app', [
            'title' => __('Trazabilidad Cepa a Botella'),
            'description' => __('Flujo completo desde la parcela hasta el producto final'),
        ]);
    }

    private function buildTraceability(int $userId): array
    {
        $campaignId = (int) $this->filterCampaign;
        $plotFilter = $this->filterPlot ? (int) $this->filterPlot : null;
        $wineFilter = $this->filterWine ? (int) $this->filterWine : null;

        // ── Paso 1: Vendimias de campo (registros del cuaderno) ──────────
        $fieldQuery = Harvest::query()
            ->join('agricultural_activities as aa', 'harvests.activity_id', '=', 'aa.id')
            ->where('aa.viticulturist_id', $userId)
            ->where('aa.campaign_id', $campaignId)
            ->where('harvests.status', '!=', 'cancelled');

        if ($plotFilter) {
            $fieldQuery->where('aa.plot_id', $plotFilter);
        }

        $fieldStats = (clone $fieldQuery)->selectRaw('
            COUNT(*) as entries,
            COALESCE(SUM(harvests.total_weight), 0) as total_kg,
            COUNT(DISTINCT aa.plot_id) as plots_count
        ')->first();

        // ── Paso 2: Recepciones de bodega ────────────────────────────────
        $receptionQuery = Harvest::where('winery_id', $userId)
            ->whereNotNull('batch_id')
            ->whereHas('batch', fn ($q) => $q->where('vintage_year', Campaign::find($campaignId)?->year));

        if ($plotFilter) {
            $receptionQuery->whereHas('batch.plotPlanting.plot', fn ($q) => $q->where('id', $plotFilter));
        }

        $receptionStats = (clone $receptionQuery)->selectRaw('
            COUNT(*) as entries,
            COALESCE(SUM(total_weight), 0) as total_kg,
            COUNT(DISTINCT batch_id) as batches_count
        ')->first();

        $batches = GrapeReceptionBatch::where('winery_id', $userId)
            ->where('vintage_year', Campaign::find($campaignId)?->year)
            ->with(['plotPlanting.plot:id,name', 'plotPlanting.grapeVariety:id,name'])
            ->withCount('receptions')
            ->withSum('receptions', 'total_weight')
            ->orderByDesc('receptions_sum_total_weight')
            ->take(10)
            ->get();

        // ── Paso 3: Vinos elaborados ─────────────────────────────────────
        $wineQuery = Wine::where('user_id', $userId)
            ->where('vintage', Campaign::find($campaignId)?->year);

        if ($wineFilter) {
            $wineQuery->where('id', $wineFilter);
        }

        $winesData = (clone $wineQuery)
            ->withCount(['harvests', 'transfers', 'bottlings'])
            ->with('oenologist:id,name')
            ->orderBy('name')
            ->get();

        $wineStats = [
            'total_wines' => $winesData->count(),
            'total_volume' => $winesData->sum('current_volume'),
            'types' => $winesData->groupBy('wine_type')->map->count()->toArray(),
        ];

        // ── Paso 4: Productos finales (lotes) ───────────────────────────
        $wineIds = $winesData->pluck('id');

        $productStats = DB::table('wine_lots')
            ->whereIn('wine_id', $wineIds)
            ->selectRaw('
                COUNT(*) as total_lots,
                COALESCE(SUM(quantity), 0) as total_units,
                COALESCE(SUM(sold_qty), 0) as sold_units,
                COALESCE(SUM(quantity * price), 0) as total_value
            ')->first();

        // ── Flujo resumido por parcela ───────────────────────────────────
        $flowByPlot = DB::table('harvests as h')
            ->join('agricultural_activities as aa', 'h.activity_id', '=', 'aa.id')
            ->join('plots as p', 'aa.plot_id', '=', 'p.id')
            ->leftJoin('plot_plantings as pp', 'h.plot_planting_id', '=', 'pp.id')
            ->leftJoin('grape_varieties as gv', 'pp.grape_variety_id', '=', 'gv.id')
            ->where('aa.viticulturist_id', $userId)
            ->where('aa.campaign_id', $campaignId)
            ->where('h.status', '!=', 'cancelled')
            ->when($plotFilter, fn ($q) => $q->where('aa.plot_id', $plotFilter))
            ->select(
                'p.id as plot_id',
                'p.name as plot_name',
                'gv.name as variety',
                DB::raw('SUM(h.total_weight) as field_kg'),
                DB::raw('COUNT(h.id) as harvest_count'),
            )
            ->groupBy('p.id', 'p.name', 'gv.name')
            ->orderByDesc('field_kg')
            ->get();

        return [
            'fieldStats' => $fieldStats,
            'receptionStats' => $receptionStats,
            'batches' => $batches,
            'winesData' => $winesData,
            'wineStats' => $wineStats,
            'productStats' => $productStats,
            'flowByPlot' => $flowByPlot,
        ];
    }
}
