<?php

namespace App\Livewire\Winery\FieldActivities;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $viticulturistFilter = '';
    public string $activityTypeFilter  = '';
    public string $campaignFilter      = '';
    public string $plotFilter          = '';

    protected $queryString = [
        'viticulturistFilter' => ['except' => ''],
        'activityTypeFilter'  => ['except' => ''],
        'campaignFilter'      => ['except' => ''],
        'plotFilter'          => ['except' => ''],
    ];

    public function updatingViticulturistFilter(): void { $this->resetPage(); }
    public function updatingActivityTypeFilter(): void  { $this->resetPage(); }
    public function updatingCampaignFilter(): void      { $this->resetPage(); }
    public function updatingPlotFilter(): void          { $this->resetPage(); }

    public function render()
    {
        $wineryId = Auth::id();

        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
            ->pluck('viticulturist_id');

        if (Auth::user()->isProducer()) {
            $viticulturistIds = $viticulturistIds->push($wineryId)->unique();
        }

        $query = AgriculturalActivity::with([
                'viticulturist:id,name',
                'plot:id,name',
                'plotPlanting.grapeVariety:id,name',
                'campaign:id,year',
            ])
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->when($this->viticulturistFilter, fn($q) => $q->where('viticulturist_id', $this->viticulturistFilter))
            ->when($this->activityTypeFilter, fn($q) => $q->where('activity_type', $this->activityTypeFilter))
            ->when($this->campaignFilter, fn($q) => $q->where('campaign_id', $this->campaignFilter))
            ->when($this->plotFilter, fn($q) => $q->where('plot_id', $this->plotFilter));

        $activities = (clone $query)
            ->orderByDesc('activity_date')
            ->paginate(30);

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->filter()
            ->sortBy('name')
            ->values();

        if (Auth::user()->isProducer()) {
            $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
        }

        $campaigns = Campaign::whereIn('viticulturist_id', $viticulturistIds)
            ->orderByDesc('year')
            ->get(['id', 'year'])
            ->unique('year');

        $plots = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $stats = [
            'total'    => (clone $query)->count(),
            'harvest'  => (clone $query)->where('activity_type', 'harvest')->count(),
            'phyto'    => (clone $query)->where('activity_type', 'phytosanitary')->count(),
        ];

        return view('livewire.winery.field-activities.index', [
            'activities'          => $activities,
            'linkedViticulturists'=> $linkedViticulturists,
            'campaigns'           => $campaigns,
            'plots'               => $plots,
            'stats'               => $stats,
            'activityTypes'       => AgriculturalActivity::ACTIVITY_TYPES,
        ])->layout('layouts.app');
    }
}
