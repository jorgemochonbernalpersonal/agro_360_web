<?php

namespace App\Livewire\Winery\Harvest\Forecasts;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $campaignFilter = '';

    public string $viticulturistFilter = '';

    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'campaignFilter' => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCampaignFilter(): void
    {
        $this->resetPage();
    }

    public function updatingViticulturistFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function confirm(int $id): void
    {
        $forecast = WineryYieldForecast::where('winery_id', Auth::id())->findOrFail($id);
        $forecast->update(['status' => 'confirmed']);
        $this->toastSuccess(__('Previsión confirmada.'));
    }

    public function delete(int $id): void
    {
        $forecast = WineryYieldForecast::where('winery_id', Auth::id())->findOrFail($id);
        $forecast->delete();
        $this->toastSuccess(__('Previsión eliminada.'));
    }

    protected function filterDefaults(): array
    {
        return [
            'search' => '',
            'campaignFilter' => '',
            'viticulturistFilter' => '',
            'statusFilter' => '',
        ];
    }

    protected function baseQuery(): Builder
    {
        return WineryYieldForecast::with([
            'viticulturist:id,name',
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'campaign',
        ])->where('winery_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->campaignFilter) {
            $query->where('campaign_id', $this->campaignFilter);
        }

        if ($this->viticulturistFilter) {
            $query->where('viticulturist_id', $this->viticulturistFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('viticulturist', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                    ->orWhereHas('plotPlanting.grapeVariety', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                    )
                    ->orWhereHas('plotPlanting.plot', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                    );
            });
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('estimation_date');
    }

    protected function defaultOrderBy(): array
    {
        return ['estimation_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function viewData(mixed $entries): array
    {
        $wineryId = $this->wineryId();

        $campaigns = Campaign::forViticulturist($wineryId)->orderBy('year', 'desc')->get();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        if (Auth::user()->isProducer()) {
            $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
        }

        // Cargar kg recibidos por batch para calcular ejecución en la vista
        $collection = $entries->getCollection();
        $batchTotals = GrapeReceptionBatch::where('winery_id', $wineryId)
            ->whereIn('plot_planting_id', $collection->pluck('plot_planting_id'))
            ->whereIn('campaign_id', $collection->pluck('campaign_id'))
            ->get(['plot_planting_id', 'campaign_id', 'total_weight_kg'])
            ->keyBy(fn ($b) => $b->plot_planting_id.'_'.$b->campaign_id);

        $stats = [
            'total' => WineryYieldForecast::where('winery_id', $wineryId)->count(),
            'confirmed' => WineryYieldForecast::where('winery_id', $wineryId)->where('status', 'confirmed')->count(),
            'draft' => WineryYieldForecast::where('winery_id', $wineryId)->where('status', 'draft')->count(),
            'total_kg' => WineryYieldForecast::where('winery_id', $wineryId)->where('status', 'confirmed')
                ->when($this->campaignFilter, fn ($q) => $q->where('campaign_id', $this->campaignFilter))
                ->sum('estimated_kg'),
        ];

        return [
            'forecasts' => $entries,
            'campaigns' => $campaigns,
            'linkedViticulturists' => $linkedViticulturists,
            'batchTotals' => $batchTotals,
            'stats' => $stats,
        ];
    }
}
