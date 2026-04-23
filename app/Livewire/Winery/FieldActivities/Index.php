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
        $user   = Auth::user();
        $userId = Auth::id();
        $isViticulturistOnly = !$user->hasWineryAccess();

        if ($isViticulturistOnly) {
            // Pure viticulturist: show their own activities only
            $viticulturistIds      = collect([$userId]);
            $withoutCuadernoAccess = collect();
            $linkedViticulturists  = collect();
        } else {
            // Winery or Producer: show linked viticulturists with cuaderno access
            $viticulturistIds = WineryViticulturist::where('winery_id', $userId)
                ->where('notebook_access', true)
                ->pluck('viticulturist_id');

            if ($user->isProducer()) {
                $viticulturistIds = $viticulturistIds->push($userId)->unique();
            }

            // Viticulturists linked but without cuaderno consent, for the warning banner
            $withoutCuadernoAccess = WineryViticulturist::where('winery_id', $userId)
                ->where('notebook_access', false)
                ->whereHas('viticulturist', fn($q) => $q->where('can_login', true))
                ->with('viticulturist:id,name')
                ->get()
                ->pluck('viticulturist')
                ->filter()
                ->values();

            $linkedViticulturists = WineryViticulturist::where('winery_id', $userId)
                ->with('viticulturist:id,name')
                ->get()
                ->pluck('viticulturist')
                ->filter()
                ->sortBy('name')
                ->values();

            if ($user->isProducer()) {
                $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
            }
        }

        $campaigns = Campaign::whereIn('viticulturist_id', $viticulturistIds)
            ->orderByDesc('year')
            ->get(['id', 'year'])
            ->unique('year');

        $plots = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = AgriculturalActivity::whereIn('viticulturist_id', $viticulturistIds)
            ->with(['plot', 'plotPlanting.grapeVariety', 'viticulturist', 'campaign']);

        if ($this->viticulturistFilter) {
            $query->where('viticulturist_id', $this->viticulturistFilter);
        }
        if ($this->activityTypeFilter) {
            $query->where('activity_type', $this->activityTypeFilter);
        }
        if ($this->campaignFilter) {
            $query->where('campaign_id', $this->campaignFilter);
        }
        if ($this->plotFilter) {
            $query->where('plot_id', $this->plotFilter);
        }

        $stats = [
            'total'    => (clone $query)->count(),
            'harvest'  => (clone $query)->where('activity_type', 'harvest')->count(),
            'phyto'    => (clone $query)->where('activity_type', 'phytosanitary')->count(),
        ];

        $activities = (clone $query)->orderByDesc('activity_date')->paginate(12);

        return view('livewire.winery.field-activities.index', [
            'activities'            => $activities,
            'linkedViticulturists'  => $linkedViticulturists,
            'campaigns'             => $campaigns,
            'plots'                 => $plots,
            'stats'                 => $stats,
            'activityTypes'         => AgriculturalActivity::ACTIVITY_TYPES,
            'withoutCuadernoAccess' => $withoutCuadernoAccess,
            'isViticulturistOnly'   => $isViticulturistOnly,
        ])->layout('layouts.app');
    }
}
