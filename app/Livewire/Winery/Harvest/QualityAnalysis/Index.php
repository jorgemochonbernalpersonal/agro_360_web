<?php

namespace App\Livewire\Winery\Harvest\QualityAnalysis;

use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $campaignFilter      = '';
    public string $viticulturistFilter = '';

    protected $queryString = [
        'campaignFilter'      => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!$this->campaignFilter) {
            $campaign = Campaign::forViticulturist(Auth::id())->where('active', true)->first();
            if ($campaign) {
                $this->campaignFilter = (string) $campaign->id;
            }
        }
    }

    public function render()
    {
        $wineryId = Auth::id();

        $campaigns = Campaign::forViticulturist($wineryId)->orderByDesc('year')->get();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        if (Auth::user()->isProducer()) {
            $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
        }

        $query = Harvest::with(['batch.viticulturist', 'plotPlanting.grapeVariety'])
            ->where('winery_id', $wineryId)
            ->where('status', 'active')
            ->where('disqualified', false);

        if ($this->campaignFilter) {
            $query->whereHas('batch', fn($q) => $q->where('campaign_id', $this->campaignFilter));
        }

        if ($this->viticulturistFilter) {
            $query->whereHas('batch', fn($q) => $q->where('viticulturist_id', $this->viticulturistFilter));
        }

        $harvests = $query->get();

        // ── Por viticultor ────────────────────────────────────────────────────

        $byViticulturist = $harvests
            ->groupBy(fn($h) => $h->batch?->viticulturist_id ?? 'unknown')
            ->map(function ($group) {
                $name = $group->first()->batch?->viticulturist?->name ?? '—';
                return $this->buildQualityStats($name, $group);
            })
            ->sortByDesc('total_kg')
            ->values();

        // ── Por variedad ──────────────────────────────────────────────────────

        $byVariety = $harvests
            ->groupBy(fn($h) => $h->plotPlanting?->grapeVariety?->name ?? '—')
            ->map(function ($group, $variety) {
                return $this->buildQualityStats($variety, $group);
            })
            ->sortByDesc('total_kg')
            ->values();

        // ── Stats globales ────────────────────────────────────────────────────

        $withAlcohol = $harvests->whereNotNull('potential_alcohol');
        $withBaume   = $harvests->whereNotNull('baume_degree');
        $withAcidity = $harvests->whereNotNull('acidity_level');

        $globalStats = [
            'total_kg'      => $harvests->sum(fn($h) => (float) $h->total_weight),
            'total_entries' => $harvests->count(),
            'avg_alcohol'   => $withAlcohol->count() ? round($withAlcohol->avg(fn($h) => $h->potential_alcohol), 2) : null,
            'avg_baume'     => $withBaume->count()   ? round($withBaume->avg(fn($h) => $h->baume_degree), 2)      : null,
            'avg_acidity'   => $withAcidity->count() ? round($withAcidity->avg(fn($h) => $h->acidity_level), 2)   : null,
        ];

        // ── Comparativa multi-añada ───────────────────────────────────────────

        $allForComparison = Harvest::with(['batch.viticulturist', 'plotPlanting.grapeVariety'])
            ->where('winery_id', $wineryId)
            ->where('status', 'active')
            ->where('disqualified', false)
            ->whereNotNull('potential_alcohol')
            ->get();

        $compYears = $allForComparison
            ->map(fn($h) => $h->batch?->vintage_year)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $comparison = collect();
        if ($compYears->count() >= 2) {
            $comparison = $allForComparison
                ->groupBy(fn($h) => $h->batch?->viticulturist_id ?? 'unknown')
                ->map(function ($group) use ($compYears) {
                    $name  = $group->first()->batch?->viticulturist?->name ?? '—';
                    $byYear = $group->groupBy(fn($h) => $h->batch?->vintage_year);
                    return [
                        'name'  => $name,
                        'years' => $compYears->mapWithKeys(fn($y) => [
                            $y => [
                                'alcohol' => $byYear->has($y)
                                    ? round($byYear->get($y)->avg(fn($h) => $h->potential_alcohol), 2)
                                    : null,
                                'baume'   => $byYear->has($y)
                                    ? round($byYear->get($y)->whereNotNull('baume_degree')->avg(fn($h) => $h->baume_degree), 2)
                                    : null,
                                'acidity' => $byYear->has($y)
                                    ? round($byYear->get($y)->whereNotNull('acidity_level')->avg(fn($h) => $h->acidity_level), 2)
                                    : null,
                                'kg'      => round($byYear->get($y)?->sum(fn($h) => (float) $h->total_weight) ?? 0, 0),
                            ],
                        ]),
                    ];
                })
                ->sortBy('name')
                ->values();
        }

        return view('livewire.winery.harvest.quality-analysis.index', [
            'byViticulturist'      => $byViticulturist,
            'byVariety'            => $byVariety,
            'globalStats'          => $globalStats,
            'campaigns'            => $campaigns,
            'linkedViticulturists' => $linkedViticulturists,
            'comparison'           => $comparison,
            'compYears'            => $compYears,
        ])->layout('layouts.app');
    }

    protected function buildQualityStats(string $label, $group): array
    {
        $withAlcohol = $group->whereNotNull('potential_alcohol');
        $withBaume   = $group->whereNotNull('baume_degree');
        $withBrix    = $group->whereNotNull('brix_degree');
        $withAcidity = $group->whereNotNull('acidity_level');
        $withPh      = $group->whereNotNull('ph_level');

        $healthDist = $group->whereNotNull('health_status')
            ->groupBy('health_status')
            ->map->count()
            ->sortDesc();

        return [
            'label'       => $label,
            'total_kg'    => $group->sum(fn($h) => (float) $h->total_weight),
            'count'       => $group->count(),
            'avg_alcohol' => $withAlcohol->count() ? round($withAlcohol->avg(fn($h) => $h->potential_alcohol), 2) : null,
            'avg_baume'   => $withBaume->count()   ? round($withBaume->avg(fn($h) => $h->baume_degree), 2)        : null,
            'avg_brix'    => $withBrix->count()    ? round($withBrix->avg(fn($h) => $h->brix_degree), 2)          : null,
            'avg_acidity' => $withAcidity->count() ? round($withAcidity->avg(fn($h) => $h->acidity_level), 2)     : null,
            'avg_ph'      => $withPh->count()      ? round($withPh->avg(fn($h) => $h->ph_level), 2)               : null,
            'health_dist' => $healthDist,
        ];
    }
}
