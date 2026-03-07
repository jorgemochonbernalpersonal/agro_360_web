<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $search               = '';
    public string $campaignFilter       = '';
    public string $viticulturistFilter  = '';
    public string $disqualifiedFilter   = '';

    protected $queryString = [
        'search'              => ['except' => ''],
        'campaignFilter'      => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
        'disqualifiedFilter'  => ['except' => ''],
    ];

    public function updatingSearch(): void              { $this->resetPage(); }
    public function updatingCampaignFilter(): void      { $this->resetPage(); }
    public function updatingViticulturistFilter(): void { $this->resetPage(); }
    public function updatingDisqualifiedFilter(): void  { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return [
            'search'             => '',
            'campaignFilter'     => '',
            'viticulturistFilter'=> '',
            'disqualifiedFilter' => '',
        ];
    }

    public function cancelReception(int $id): void
    {
        $wineryId         = Auth::id();
        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)->pluck('viticulturist_id');
        $campaignIds      = Campaign::forViticulturist($wineryId)->pluck('id');

        $harvest = Harvest::whereHas('activity', fn($q) =>
            $q->whereIn('viticulturist_id', $viticulturistIds)
              ->whereIn('campaign_id', $campaignIds)
        )->findOrFail($id);

        $harvest->update(['status' => 'cancelled']);

        $this->toastSuccess('Recepción anulada correctamente.');
    }

    protected function baseQuery(): Builder
    {
        $wineryId         = $this->wineryId();
        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)->pluck('viticulturist_id');
        $campaignIds      = Campaign::forViticulturist($wineryId)->pluck('id');

        return Harvest::with([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
        ])->whereHas('activity', function (Builder $q) use ($viticulturistIds, $campaignIds) {
            $q->whereIn('viticulturist_id', $viticulturistIds)
              ->whereIn('campaign_id', $campaignIds);
        });
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->campaignFilter) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('campaign_id', $this->campaignFilter)
            );
        }

        if ($this->viticulturistFilter) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('viticulturist_id', $this->viticulturistFilter)
            );
        }

        if ($this->disqualifiedFilter !== '') {
            $query->where('disqualified', (bool) $this->disqualifiedFilter);
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('activity.viticulturist', fn($q2) =>
                    $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                ->orWhereHas('plotPlanting.grapeVariety', fn($q2) =>
                    $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                ->orWhereHas('plotPlanting.plot', fn($q2) =>
                    $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                ->orWhereRaw('LOWER(IFNULL(harvest_ticket_number, \'\')) LIKE ?', [$term]);
            });
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('harvest_start_date');
    }

    protected function defaultOrderBy(): array { return ['harvest_start_date', 'desc']; }

    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        $wineryId = $this->wineryId();

        $campaigns = Campaign::forViticulturist($wineryId)->orderBy('year', 'desc')->get();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist');

        // Stats: apply same scope but without pagination
        $statsQuery = $this->baseQuery();
        $this->applyFilters($statsQuery);
        $statsQuery->where('status', 'active');

        $allForStats = $statsQuery->with([
            'plotPlanting.grapeVariety',
            'activity.viticulturist',
        ])->get();

        $stats = [
            'total_kg'       => $allForStats->sum(fn($h) => (float) $h->total_weight),
            'total_count'    => $allForStats->count(),
            'disqualified_kg'=> $allForStats->where('disqualified', true)->sum(fn($h) => (float) $h->total_weight),
            'viticulturists' => $allForStats->map(fn($h) => $h->activity?->viticulturist_id)->unique()->filter()->count(),
        ];

        $byViticulturist = $allForStats
            ->groupBy(fn($h) => $h->activity?->viticulturist?->name ?? '—')
            ->map(fn($group) => round($group->sum(fn($h) => (float) $h->total_weight), 0))
            ->sortDesc()
            ->take(10);

        $byVariety = $allForStats
            ->groupBy(fn($h) => $h->plotPlanting?->grapeVariety?->name ?? '—')
            ->map(fn($group) => round($group->sum(fn($h) => (float) $h->total_weight), 0))
            ->sortDesc();

        // Estado de límites por plantación (solo cuando hay campaña seleccionada)
        $limitStatus = collect();
        if ($this->campaignFilter) {
            $campaign = $campaigns->firstWhere('id', $this->campaignFilter);

            $harvestsInCampaign = Harvest::with([
                'plotPlanting.grapeVariety',
                'plotPlanting.plot',
                'activity.viticulturist',
            ])->whereHas('activity', fn($q) =>
                $q->whereIn('viticulturist_id', $viticulturistIds)
                  ->where('campaign_id', $this->campaignFilter)
            )->where('status', 'active')->get();

            $limitStatus = $harvestsInCampaign
                ->groupBy('plot_planting_id')
                ->map(function ($group) use ($campaign) {
                    $first    = $group->first();
                    $planting = $first->plotPlanting;
                    if (!$planting?->hasHarvestLimit()) {
                        return null;
                    }
                    $limit    = (float) $planting->effectiveHarvestLimitKg($campaign?->year);
                    $received = $group->sum(fn($h) => (float) $h->total_weight);
                    $pct      = $limit > 0 ? round(($received / $limit) * 100, 1) : null;
                    return [
                        'viticulturist' => $first->activity?->viticulturist?->name ?? '—',
                        'plot'          => $planting?->plot?->name ?? '—',
                        'variety'       => $planting?->grapeVariety?->name ?? '—',
                        'planting'      => $planting?->name ?? '—',
                        'received'      => $received,
                        'limit'         => $limit,
                        'pct'           => $pct,
                        'exceeded'      => $pct !== null && $pct > 100,
                    ];
                })
                ->filter()
                ->sortByDesc('pct')
                ->values();
        }

        return [
            'receptions'           => $entries,
            'campaigns'            => $campaigns,
            'linkedViticulturists' => $linkedViticulturists,
            'stats'                => $stats,
            'byViticulturist'      => $byViticulturist,
            'byVariety'            => $byVariety,
            'limitStatus'          => $limitStatus,
        ];
    }
}
