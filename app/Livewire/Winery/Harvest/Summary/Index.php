<?php

namespace App\Livewire\Winery\Harvest\Summary;

use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\GrapeReceptionBatch;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $search              = '';
    public string $campaignFilter      = '';
    public string $viticulturistFilter = '';
    public string $varietyFilter       = '';
    public string $alertFilter         = ''; // 'exceeded' | 'at_risk' | ''

    protected $queryString = [
        'search'              => ['except' => ''],
        'campaignFilter'      => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
        'varietyFilter'       => ['except' => ''],
        'alertFilter'         => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!$this->campaignFilter) {
            $campaign = Campaign::forViticulturist(Auth::id())
                ->where('active', true)
                ->first();
            if ($campaign) {
                $this->campaignFilter = (string) $campaign->id;
            }
        }
    }

    public function updatingSearch(): void              { }
    public function updatingCampaignFilter(): void      { }
    public function updatingViticulturistFilter(): void { }
    public function updatingVarietyFilter(): void       { }
    public function updatingAlertFilter(): void         { }

    public function render()
    {
        $wineryId  = Auth::id();
        $campaigns = Campaign::forViticulturist($wineryId)->orderBy('year', 'desc')->get();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        $campaign    = $this->campaignFilter ? $campaigns->firstWhere('id', $this->campaignFilter) : null;
        $vintageYear = $campaign?->year ?? now()->year;

        // ── Fuentes de datos ──────────────────────────────────────────────

        // 1. Batches reales (recepciones ya iniciadas)
        $batchQuery = GrapeReceptionBatch::with([
                'viticulturist:id,name',
                'plotPlanting.grapeVariety',
                'plotPlanting.plot',
            ])
            ->where('winery_id', $wineryId)
            ->where('vintage_year', $vintageYear);

        if ($this->viticulturistFilter) {
            $batchQuery->where('viticulturist_id', $this->viticulturistFilter);
        }

        $batches = $batchQuery->get()->keyBy(fn($b) => $b->plot_planting_id . '_' . $b->campaign_id);

        // 2. Forecasts de bodega (confirmados y borradores)
        $forecastQuery = WineryYieldForecast::with([
                'viticulturist:id,name',
                'plotPlanting.grapeVariety',
                'plotPlanting.plot',
            ])
            ->where('winery_id', $wineryId)
            ->where('vintage_year', $vintageYear);

        if ($this->campaignFilter) {
            $forecastQuery->where('campaign_id', $this->campaignFilter);
        }

        if ($this->viticulturistFilter) {
            $forecastQuery->where('viticulturist_id', $this->viticulturistFilter);
        }

        $forecasts = $forecastQuery->get()->keyBy(fn($f) => $f->plot_planting_id . '_' . $f->campaign_id);

        // 3. Aforos del viticultor (EstimatedYield confirmados para esta añada)
        $viticulturistIds = $linkedViticulturists->pluck('id');

        $estimatedYieldQuery = EstimatedYield::with(['plotPlanting'])
            ->whereHas('campaign', fn($q) => $q->where('year', $vintageYear))
            ->whereHas('plotPlanting.plot', fn($q) =>
                $q->whereIn('viticulturist_id', $viticulturistIds)
            )
            ->where('status', 'confirmed');

        $estimatedYields = $estimatedYieldQuery->get()
            ->keyBy('plot_planting_id');

        // ── Construir filas únicas por (planting + campaign) ─────────────

        $keys = $batches->keys()->merge($forecasts->keys())->unique();

        $rows = $keys->map(function ($key) use ($batches, $forecasts, $estimatedYields, $campaign, $vintageYear) {
            $batch    = $batches->get($key);
            $forecast = $forecasts->get($key);

            // Resolver planting y viticulturist desde cualquiera de las dos fuentes
            $source      = $batch ?? $forecast;
            $planting    = $source?->plotPlanting;
            $viticulturist = $source?->viticulturist;

            if (!$planting) return null;

            $plantingId      = $planting->id;
            $estimatedYield  = $estimatedYields->get($plantingId);

            $pacLimit       = $planting->effectiveHarvestLimitKg($vintageYear);
            $forecastKg     = $forecast?->status === 'confirmed' ? (float) $forecast->estimated_kg : null;
            $viticEstimate  = $estimatedYield ? (float) $estimatedYield->estimated_total_yield : null;
            $receivedKg     = $batch ? (float) $batch->total_weight_kg : 0;

            // Límite operativo: forecast confirmado vs PAC (el menor)
            $opLimit = $forecastKg !== null && $pacLimit !== null
                ? min($forecastKg, $pacLimit)
                : ($forecastKg ?? $pacLimit);

            $pctOfOpLimit = ($opLimit && $opLimit > 0)
                ? round(($receivedKg / $opLimit) * 100, 1)
                : null;

            $pctOfPac = ($pacLimit && $pacLimit > 0)
                ? round(($receivedKg / $pacLimit) * 100, 1)
                : null;

            $exceeded   = $opLimit !== null && $receivedKg > $opLimit;
            $exceededPac = $pacLimit !== null && $receivedKg > $pacLimit;
            $atRisk     = !$exceeded && $pctOfOpLimit !== null && $pctOfOpLimit >= 80;

            return [
                'key'              => $key,
                'viticulturist'    => $viticulturist,
                'planting'         => $planting,
                'variety'          => $planting->grapeVariety?->name ?? $planting->name ?? '—',
                'plot'             => $planting->plot?->name ?? '—',
                'area'             => $planting->area_planted ? (float) $planting->area_planted : null,
                'pac_limit'        => $pacLimit,
                'vitic_estimate'   => $viticEstimate,
                'forecast_kg'      => $forecastKg,
                'forecast_status'  => $forecast?->status,
                'op_limit'         => $opLimit,
                'received_kg'      => $receivedKg,
                'pct_op_limit'     => $pctOfOpLimit,
                'pct_pac'          => $pctOfPac,
                'remaining'        => $opLimit !== null ? max(0, $opLimit - $receivedKg) : null,
                'exceeded'         => $exceeded,
                'exceeded_pac'     => $exceededPac,
                'at_risk'          => $atRisk,
                'batch_status'     => $batch?->status,
            ];
        })
        ->filter()
        ->values();

        // ── Filtros adicionales ───────────────────────────────────────────

        if ($this->search) {
            $term = mb_strtolower($this->search);
            $rows = $rows->filter(fn($r) =>
                str_contains(mb_strtolower($r['viticulturist']?->name ?? ''), $term) ||
                str_contains(mb_strtolower($r['variety']), $term) ||
                str_contains(mb_strtolower($r['plot']), $term)
            )->values();
        }

        if ($this->varietyFilter) {
            $rows = $rows->filter(fn($r) => str_contains(
                mb_strtolower($r['variety']),
                mb_strtolower($this->varietyFilter)
            ))->values();
        }

        if ($this->alertFilter === 'exceeded') {
            $rows = $rows->filter(fn($r) => $r['exceeded'] || $r['exceeded_pac'])->values();
        } elseif ($this->alertFilter === 'at_risk') {
            $rows = $rows->filter(fn($r) => $r['at_risk'])->values();
        }

        // ── Stats globales ────────────────────────────────────────────────

        $allRows = $rows; // ya filtrados
        $stats = [
            'total_plantings'    => $allRows->count(),
            'total_received_kg'  => $allRows->sum('received_kg'),
            'total_forecast_kg'  => $allRows->sum('forecast_kg'),
            'total_pac_kg'       => $allRows->sum('pac_limit'),
            'total_vitic_est_kg' => $allRows->sum('vitic_estimate'),
            'exceeded_count'     => $allRows->where('exceeded', true)->count(),
            'at_risk_count'      => $allRows->where('at_risk', true)->count(),
            'viticulturists'     => $allRows->pluck('viticulturist.id')->unique()->filter()->count(),
        ];

        // Variedades únicas para filtro
        $varieties = $allRows->pluck('variety')->unique()->sort()->values();

        return view('livewire.winery.harvest.summary.index', [
            'rows'                 => $rows,
            'stats'                => $stats,
            'campaigns'            => $campaigns,
            'linkedViticulturists' => $linkedViticulturists,
            'varieties'            => $varieties,
            'campaign'             => $campaign,
            'vintageYear'          => $vintageYear,
        ])->layout('layouts.app');
    }
}
