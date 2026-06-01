<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
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
        $wineryId = Auth::id();

        $harvest = Harvest::where('winery_id', $wineryId)->findOrFail($id);

        // Guard: no anular una recepción ya facturada — dejaría el InvoiceItem
        // apuntando a una recepción cancelada y el stock vendido descuadrado.
        if ($harvest->isInvoiced()) {
            $this->toastError(__('No se puede anular una recepción incluida en una liquidación.'));
            return;
        }

        $harvest->update(['status' => 'cancelled']);

        // Recalcular acumulado del batch
        if ($harvest->batch_id) {
            $harvest->batch?->recalculateTotal();
        }

        $this->toastSuccess(__('Recepción anulada correctamente.'));
    }

    protected function baseQuery(): Builder
    {
        $wineryId = $this->wineryId();

        return Harvest::with([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
            'container',
            'delivery',
        ])->where('winery_id', $wineryId);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->campaignFilter) {
            $query->whereHas('batch', fn(Builder $q) =>
                $q->where('campaign_id', $this->campaignFilter)
            );
        }

        if ($this->viticulturistFilter) {
            $query->whereHas('batch', fn(Builder $q) =>
                $q->where('viticulturist_id', $this->viticulturistFilter)
            );
        }

        if ($this->disqualifiedFilter !== '') {
            $query->where('disqualified', (bool) $this->disqualifiedFilter);
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('batch.viticulturist', fn($q2) =>
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

        $linkedViticulturists = GrapeReceptionBatch::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->unique('id')
            ->values();

        // Stats: apply same scope but without pagination
        $statsQuery = $this->baseQuery();
        $this->applyFilters($statsQuery);
        $statsQuery->where('status', 'active');

        $allForStats = $statsQuery->with([
            'plotPlanting.grapeVariety',
            'batch.viticulturist',
        ])->get();

        $stats = [
            'total_kg'       => $allForStats->sum(fn($h) => (float) $h->total_weight),
            'total_count'    => $allForStats->count(),
            'disqualified_kg'=> $allForStats->where('disqualified', true)->sum(fn($h) => (float) $h->total_weight),
            'viticulturists' => $allForStats->map(fn($h) => $h->batch?->viticulturist_id)->unique()->filter()->count(),
        ];

        $byViticulturist = $allForStats
            ->groupBy(fn($h) => $h->batch?->viticulturist?->name ?? '—')
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
                'batch.viticulturist',
            ])->whereHas('batch', fn($q) =>
                $q->where('winery_id', $wineryId)
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
                        'viticulturist' => $first->batch?->viticulturist?->name ?? '—',
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
