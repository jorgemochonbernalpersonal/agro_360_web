<?php

namespace App\Livewire\Viticulturist\PlotCosts;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotCost;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $filter_campaign_id = '';

    public string $filter_plot_id = '';

    public string $filter_category = '';

    protected $queryString = [
        'filter_campaign_id' => ['except' => '', 'as' => 'campaign'],
        'filter_plot_id' => ['except' => '', 'as' => 'plot'],
        'filter_category' => ['except' => '', 'as' => 'category'],
    ];

    public function updatingFilterCampaignId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlotId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->findOwned(PlotCost::class, $id)->delete();
        $this->toastSuccess(__('Coste eliminado correctamente.'));
    }

    protected function baseQuery(): Builder
    {
        return PlotCost::where('viticulturist_id', $this->viticulturistId())
            ->with(['plot', 'campaign']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filter_campaign_id) {
            $query->where('campaign_id', $this->filter_campaign_id);
        }
        if ($this->filter_plot_id) {
            $query->where('plot_id', $this->filter_plot_id);
        }
        if ($this->filter_category) {
            $query->where('category', $this->filter_category);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['cost_date', 'desc'];
    }

    protected function filterDefaults(): array
    {
        return ['filter_campaign_id' => '', 'filter_plot_id' => '', 'filter_category' => ''];
    }

    protected function viewData(mixed $entries): array
    {
        $viticulturistId = $this->viticulturistId();
        $base = PlotCost::where('viticulturist_id', $viticulturistId);
        $filtered = (clone $base)
            ->when($this->filter_campaign_id, fn ($q) => $q->where('campaign_id', $this->filter_campaign_id))
            ->when($this->filter_plot_id, fn ($q) => $q->where('plot_id', $this->filter_plot_id))
            ->when($this->filter_category, fn ($q) => $q->where('category', $this->filter_category));

        $stats = [
            'total' => (clone $base)->count(),
            'total_amount' => (clone $base)->sum('amount'),
            'filtered_amount' => (clone $filtered)->sum('amount'),
        ];

        return [
            'costs' => $entries,
            'stats' => $stats,
            'campaigns' => Campaign::where('viticulturist_id', $viticulturistId)->orderByDesc('year')->get(),
            'plots' => Plot::where('viticulturist_id', $viticulturistId)->orderBy('name')->get(),
            'categories' => PlotCost::categoryOptions(),
        ];
    }
}
