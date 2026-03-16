<?php

namespace App\Livewire\Supervisor\Statistics;

use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
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

        $totalWineries       = $wineryIds->count();
        $totalViticulturists = $viticulturistIds->count();

        $totalPlotAreaHa = (float) DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->sum('area');

        $totalKgCurrentVintage = (float) DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->where('vintage', $currentYear)
            ->where('status', 'active')
            ->sum('total_weight');

        $harvestByVintage = DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->where('status', 'active')
            ->select(
                'vintage',
                DB::raw('COALESCE(SUM(total_weight), 0) as total_kg'),
                DB::raw('COUNT(*) as reception_count'),
                DB::raw('ROUND(AVG(NULLIF(brix_degree, 0)), 2) as avg_brix')
            )
            ->groupBy('vintage')
            ->orderByDesc('vintage')
            ->get();

        return view('livewire.supervisor.statistics.index', [
            'totalWineries'         => $totalWineries,
            'totalViticulturists'   => $totalViticulturists,
            'totalPlotAreaHa'       => $totalPlotAreaHa,
            'totalKgCurrentVintage' => $totalKgCurrentVintage,
            'harvestByVintage'      => $harvestByVintage,
            'currentYear'           => $currentYear,
        ]);
    }
}
