<?php

namespace App\Livewire\Supervisor\Campaigns;

use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'resumen';

    protected $queryString = [
        'activeTab' => ['except' => 'resumen', 'as' => 'tab'],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId        = Auth::id();
        $currentYear = now()->year;

        $wineryIds = SupervisorWinery::where('supervisor_id', $doId)
            ->pluck('winery_id');

        $viticulturistIds = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->pluck('viticulturist_id')
            ->unique();

        // ── Tab: resumen ──────────────────────────────────────────────────

        $campaignsByYear = DB::table('campaigns')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->select(
                'year',
                DB::raw('COUNT(*) as campaign_count'),
                DB::raw('SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_count')
            )
            ->groupBy('year')
            ->orderByDesc('year')
            ->get()
            ->keyBy('year');

        $harvestsByVintage = DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->where('status', 'active')
            ->select(
                'vintage',
                DB::raw('COALESCE(SUM(total_weight), 0) as total_kg'),
                DB::raw('COUNT(*) as reception_count')
            )
            ->groupBy('vintage')
            ->orderByDesc('vintage')
            ->get()
            ->keyBy('vintage');

        $allYears = collect($campaignsByYear->keys())
            ->merge($harvestsByVintage->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $summaryRows = $allYears->map(function ($year) use ($campaignsByYear, $harvestsByVintage) {
            $campaigns = $campaignsByYear->get($year);
            $harvests  = $harvestsByVintage->get($year);

            return (object) [
                'year'            => $year,
                'campaign_count'  => $campaigns?->campaign_count  ?? 0,
                'active_count'    => $campaigns?->active_count    ?? 0,
                'total_kg'        => $harvests?->total_kg         ?? 0,
                'reception_count' => $harvests?->reception_count  ?? 0,
            ];
        });

        // ── Tab: viticultores ─────────────────────────────────────────────

        $viticulturistList = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->paginate(15);

        $pageVitIds = $viticulturistList->pluck('id');

        $campaignSummaryByVit = DB::table('campaigns')
            ->whereIn('viticulturist_id', $pageVitIds)
            ->select(
                'viticulturist_id',
                DB::raw('COUNT(*) as total_campaigns'),
                DB::raw('SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as has_active'),
                DB::raw('MAX(year) as latest_year')
            )
            ->groupBy('viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $kgByVit = DB::table('harvests')
            ->join('plot_plantings', 'plot_plantings.id', '=', 'harvests.plot_planting_id')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->whereIn('plots.viticulturist_id', $pageVitIds)
            ->whereIn('harvests.winery_id', $wineryIds)
            ->where('harvests.status', 'active')
            ->where('harvests.vintage', $currentYear)
            ->select(
                'plots.viticulturist_id',
                DB::raw('COALESCE(SUM(harvests.total_weight), 0) as total_kg')
            )
            ->groupBy('plots.viticulturist_id')
            ->pluck('total_kg', 'viticulturist_id');

        $tabs = [
            'resumen'      => ['label' => 'Resumen por año',  'count' => $allYears->count()],
            'viticultores' => ['label' => 'Viticultores',     'count' => $viticulturistIds->count()],
        ];

        return view('livewire.supervisor.campaigns.index', [
            'tabs'                 => $tabs,
            'summaryRows'          => $summaryRows,
            'viticulturistList'    => $viticulturistList,
            'campaignSummaryByVit' => $campaignSummaryByVit,
            'kgByVit'              => $kgByVit,
            'currentYear'          => $currentYear,
        ]);
    }
}
