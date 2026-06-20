<?php

namespace App\Livewire\Viticulturist\GrapeTraceability;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $filterCampaign = '';

    public string $filterPlot = '';

    public string $search = '';

    protected $queryString = [
        'filterCampaign' => ['as' => 'campaign', 'except' => ''],
        'filterPlot' => ['as' => 'plot', 'except' => ''],
        'search' => ['as' => 'q', 'except' => ''],
    ];

    public function mount(): void
    {
        $active = Campaign::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->first();

        if ($active) {
            $this->filterCampaign = (string) $active->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlot(): void
    {
        $this->resetPage();
    }

    protected function baseQuery(): Builder
    {
        return Harvest::query()
            ->join('agricultural_activities as aa', 'harvests.activity_id', '=', 'aa.id')
            ->join('plots as p', 'aa.plot_id', '=', 'p.id')
            ->leftJoin('plot_plantings as pp', 'harvests.plot_planting_id', '=', 'pp.id')
            ->leftJoin('grape_varieties as gv', 'pp.grape_variety_id', '=', 'gv.id')
            ->where('aa.viticulturist_id', $this->viticulturistId())
            ->where('harvests.status', '!=', 'cancelled')
            ->select(
                'harvests.*',
                'p.name as plot_name',
                'p.id as plot_id',
                'aa.campaign_id',
                'aa.activity_date',
                'gv.name as variety_name',
            );
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('aa.campaign_id', $this->filterCampaign);
        }

        if ($this->filterPlot) {
            $query->where('aa.plot_id', $this->filterPlot);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('p.name', 'like', "%{$this->search}%")
                    ->orWhere('harvests.destination', 'like', "%{$this->search}%")
                    ->orWhere('harvests.buyer_name', 'like', "%{$this->search}%")
                    ->orWhere('harvests.transport_document_number', 'like', "%{$this->search}%")
                    ->orWhere('gv.name', 'like', "%{$this->search}%");
            });
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('harvests.harvest_start_date');
    }

    protected function defaultOrderBy(): array
    {
        return ['harvests.harvest_start_date', 'desc'];
    }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => '', 'filterPlot' => '', 'search' => ''];
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();

        $statsQuery = Harvest::query()
            ->join('agricultural_activities as aa', 'harvests.activity_id', '=', 'aa.id')
            ->where('aa.viticulturist_id', $userId)
            ->where('harvests.status', '!=', 'cancelled');

        if ($this->filterCampaign) {
            $statsQuery->where('aa.campaign_id', $this->filterCampaign);
        }

        if ($this->filterPlot) {
            $statsQuery->where('aa.plot_id', $this->filterPlot);
        }

        $stats = $statsQuery->selectRaw('
            COUNT(*) as total_entries,
            COUNT(DISTINCT aa.plot_id) as plots_count,
            COALESCE(SUM(harvests.total_weight), 0) as total_kg,
            COUNT(DISTINCT harvests.destination) as destinations_count
        ')->first();

        return [
            'entries' => $entries,
            'stats' => $stats,
            'campaigns' => Campaign::where('viticulturist_id', $userId)->orderByDesc('year')->get(['id', 'name', 'year']),
            'plots' => Plot::where('viticulturist_id', $userId)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
