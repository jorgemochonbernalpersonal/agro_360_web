<?php

namespace App\Livewire\Viticulturist\GrapeTraceability;

use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterCampaign = '';
    public string $filterPlot     = '';
    public string $search         = '';

    protected $queryString = [
        'filterCampaign' => ['as' => 'campaign', 'except' => ''],
        'filterPlot'     => ['as' => 'plot',     'except' => ''],
        'search'         => ['as' => 'q',        'except' => ''],
    ];

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterCampaign(): void { $this->resetPage(); }
    public function updatingFilterPlot(): void     { $this->resetPage(); }

    public function mount(): void
    {
        // Default to active campaign
        $active = Campaign::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->first();
        if ($active) {
            $this->filterCampaign = (string) $active->id;
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Harvest::query()
            ->join('agricultural_activities as aa', 'harvests.activity_id', '=', 'aa.id')
            ->join('plots as p', 'aa.plot_id', '=', 'p.id')
            ->leftJoin('plot_plantings as pp', 'harvests.plot_planting_id', '=', 'pp.id')
            ->where('aa.viticulturist_id', $userId)
            ->where('harvests.status', '!=', 'cancelled')
            ->select(
                'harvests.*',
                'p.name as plot_name',
                'p.id as plot_id',
                'aa.campaign_id',
                'aa.activity_date',
                'pp.variety_name',
            );

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
                  ->orWhere('pp.variety_name', 'like', "%{$this->search}%");
            });
        }

        $entries = $query->orderByDesc('harvests.harvest_start_date')->paginate(20);

        // Stats
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

        return view('livewire.viticulturist.grape-traceability.index', [
            'entries'   => $entries,
            'stats'     => $stats,
            'campaigns' => Campaign::where('viticulturist_id', $userId)->orderByDesc('year')->get(['id', 'name', 'year']),
            'plots'     => Plot::where('user_id', $userId)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
