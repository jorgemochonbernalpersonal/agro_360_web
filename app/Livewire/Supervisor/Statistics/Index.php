<?php

namespace App\Livewire\Supervisor\Statistics;

use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();
        $currentYear = now()->year;

        $wineryIds = SupervisorWinery::where('supervisor_id', $doId)
            ->pluck('winery_id');

        // Full viticulturist pool (all DO-managed viticultors, not just winery-assigned)
        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        // ── Top-level stats ───────────────────────────────────────────────────

        $totalWineries = $wineryIds->count();
        $totalViticulturists = $poolIds->count();

        $totalPlotAreaHa = (float) DB::table('plots')
            ->whereIn('viticulturist_id', $poolIds)
            ->where('active', true)
            ->sum('area');

        $totalKgCurrentVintage = (float) DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->where('vintage', $currentYear)
            ->where('status', 'active')
            ->sum('total_weight');

        // ── Pool composition ──────────────────────────────────────────────────

        $poolUsers = DB::table('users')
            ->whereIn('id', $poolIds)
            ->select('id', 'can_login', 'invitation_token', 'invitation_expires_at')
            ->get();

        $poolComposition = [
            'active' => $poolUsers->filter(fn ($u) => $u->can_login)->count(),
            'invited' => $poolUsers->filter(fn ($u) => ! $u->can_login && $u->invitation_token && $u->invitation_expires_at && now()->lt($u->invitation_expires_at))->count(),
            'ghost' => $poolUsers->filter(fn ($u) => ! $u->can_login && (! $u->invitation_token || (bool) ($u->invitation_expires_at && now()->gt($u->invitation_expires_at))))->count(),
        ];

        $notebookAccessCount = SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('notebook_access', true)
            ->count();

        // ── Organic plots ─────────────────────────────────────────────────────

        $plotStats = DB::table('plots')
            ->whereIn('viticulturist_id', $poolIds)
            ->where('active', true)
            ->select(
                DB::raw('COUNT(*) as total_plots'),
                DB::raw('SUM(CASE WHEN is_organic = 1 THEN 1 ELSE 0 END) as organic_plots'),
                DB::raw('COALESCE(SUM(area), 0) as total_area'),
                DB::raw('COALESCE(SUM(CASE WHEN is_organic = 1 THEN area ELSE 0 END), 0) as organic_area'),
            )
            ->first();

        $organicPct = $plotStats->total_plots > 0
            ? round(($plotStats->organic_plots / $plotStats->total_plots) * 100, 1)
            : 0;

        // ── Top grape varieties ───────────────────────────────────────────────

        $topVarieties = DB::table('plot_plantings')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->join('grape_varieties', 'grape_varieties.id', '=', 'plot_plantings.grape_variety_id')
            ->whereIn('plots.viticulturist_id', $poolIds)
            ->where('plots.active', true)
            ->where('plot_plantings.status', 'active')
            ->selectRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(grape_varieties.name, ?)) as variety_name, '.
                'grape_varieties.color as variety_color, '.
                'COUNT(DISTINCT plot_plantings.id) as planting_count, '.
                'COALESCE(SUM(plot_plantings.area_planted), 0) as total_area',
                ['$."'.app()->getLocale().'"']
            )
            ->groupBy('grape_varieties.id', 'grape_varieties.name', 'grape_varieties.color')
            ->orderByDesc('total_area')
            ->limit(8)
            ->get();

        // ── Activity breakdown (current year) ─────────────────────────────────

        $activityBreakdown = DB::table('agricultural_activities')
            ->join('plots', 'plots.id', '=', 'agricultural_activities.plot_id')
            ->whereIn('plots.viticulturist_id', $poolIds)
            ->whereYear('agricultural_activities.activity_date', $currentYear)
            ->select(
                'agricultural_activities.activity_type',
                DB::raw('COUNT(*) as total'),
            )
            ->groupBy('agricultural_activities.activity_type')
            ->orderByDesc('total')
            ->get();

        // ── Harvest by vintage ────────────────────────────────────────────────

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
            'totalWineries' => $totalWineries,
            'totalViticulturists' => $totalViticulturists,
            'totalPlotAreaHa' => $totalPlotAreaHa,
            'totalKgCurrentVintage' => $totalKgCurrentVintage,
            'currentYear' => $currentYear,
            'poolComposition' => $poolComposition,
            'notebookAccessCount' => $notebookAccessCount,
            'plotStats' => $plotStats,
            'organicPct' => $organicPct,
            'topVarieties' => $topVarieties,
            'activityBreakdown' => $activityBreakdown,
            'harvestByVintage' => $harvestByVintage,
        ]);
    }
}
