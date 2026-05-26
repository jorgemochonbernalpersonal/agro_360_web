<?php

namespace App\Livewire\Viticulturist\PlotCosts;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotCost;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $filter_campaign_id = '';
    public string $filter_plot_id = '';
    public string $filter_category = '';

    protected $queryString = [
        'filter_campaign_id' => ['except' => '', 'as' => 'campaign'],
        'filter_plot_id'     => ['except' => '', 'as' => 'plot'],
        'filter_category'    => ['except' => '', 'as' => 'category'],
    ];

    public function updatingFilterCampaignId(): void { $this->resetPage(); }
    public function updatingFilterPlotId(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        PlotCost::where('viticulturist_id', Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess(__('Coste eliminado correctamente.'));
    }

    public function render()
    {
        $user = Auth::user();

        $query = PlotCost::where('viticulturist_id', $user->id)
            ->with(['plot', 'campaign']);

        if ($this->filter_campaign_id) {
            $query->where('campaign_id', $this->filter_campaign_id);
        }
        if ($this->filter_plot_id) {
            $query->where('plot_id', $this->filter_plot_id);
        }
        if ($this->filter_category) {
            $query->where('category', $this->filter_category);
        }

        $costs = $query->orderByDesc('cost_date')->paginate(20);

        $base = PlotCost::where('viticulturist_id', $user->id);
        $filtered = (clone $base)
            ->when($this->filter_campaign_id, fn($q) => $q->where('campaign_id', $this->filter_campaign_id))
            ->when($this->filter_plot_id, fn($q) => $q->where('plot_id', $this->filter_plot_id))
            ->when($this->filter_category, fn($q) => $q->where('category', $this->filter_category));

        $stats = [
            'total'        => (clone $base)->count(),
            'total_amount' => (clone $base)->sum('amount'),
            'filtered_amount' => (clone $filtered)->sum('amount'),
        ];

        $campaigns = Campaign::where('viticulturist_id', $user->id)->orderByDesc('year')->get();
        $plots      = Plot::where('viticulturist_id', $user->id)->orderBy('name')->get();

        return view('livewire.viticulturist.plot-costs.index', [
            'costs'      => $costs,
            'stats'      => $stats,
            'campaigns'  => $campaigns,
            'plots'      => $plots,
            'categories' => PlotCost::CATEGORIES,
        ])->layout('layouts.app');
    }
}
