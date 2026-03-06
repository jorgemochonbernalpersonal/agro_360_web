<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search               = '';
    public string $campaignFilter       = '';
    public string $viticulturistFilter  = '';

    protected $queryString = [
        'search'              => ['except' => ''],
        'campaignFilter'      => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void              { $this->resetPage(); }
    public function updatingCampaignFilter(): void      { $this->resetPage(); }
    public function updatingViticulturistFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'campaignFilter' => '', 'viticulturistFilter' => ''];
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

        return [
            'receptions'           => $entries,
            'campaigns'            => $campaigns,
            'linkedViticulturists' => $linkedViticulturists,
        ];
    }
}
