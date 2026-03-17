<?php

namespace App\Livewire\Supervisor\Oversight\Wineries;

use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public User $winery;

    public function mount(User $winery): void
    {
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $winery->id)
            ->firstOrFail();

        $this->winery = $winery;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();
        $wineryId     = $this->winery->id;

        // ── Viticultores aportados por este supervisor a esta bodega ──────────
        $viticulturistRelations = WineryViticulturist::where('supervisor_id', $supervisorId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('winery_id', $wineryId)
            ->with('viticulturist')
            ->get();

        $viticulturistIds = $viticulturistRelations->pluck('viticulturist_id');

        // Parcelas y ha por viticultor
        $plotStatsByVit = DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->select(
                'viticulturist_id',
                DB::raw('COUNT(*) as plot_count'),
                DB::raw('COALESCE(SUM(area), 0) as total_area')
            )
            ->groupBy('viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        // Última actividad agrícola por viticultor
        $lastActivityByVit = DB::table('agricultural_activities')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->select('viticulturist_id', DB::raw('MAX(activity_date) as last_activity'))
            ->groupBy('viticulturist_id')
            ->pluck('last_activity', 'viticulturist_id');

        // ── Recepciones de vendimia de esta bodega ────────────────────────────
        $currentVintage = now()->year;

        $recentReceptions = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->whereNotNull('winery_id')
            ->orderByDesc('harvest_start_date')
            ->limit(10)
            ->select([
                'id',
                'harvest_start_date',
                'total_weight',
                'vintage',
                'baume_degree',
                'health_status',
                'plot_planting_id',
                'status',
            ])
            ->get();

        // Stats de la vendimia actual
        $vintageStats = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $currentVintage)
            ->selectRaw('
                COUNT(*) as reception_count,
                COALESCE(SUM(total_weight), 0) as total_kg,
                COALESCE(AVG(baume_degree), 0) as avg_baume
            ')
            ->first();

        // Desglose por variedad (via plot_plantings → grape_varieties)
        $varietyBreakdown = DB::table('harvests')
            ->join('plot_plantings', 'plot_plantings.id', '=', 'harvests.plot_planting_id')
            ->join('grape_varieties', 'grape_varieties.id', '=', 'plot_plantings.grape_variety_id')
            ->where('harvests.winery_id', $wineryId)
            ->where('harvests.vintage', $currentVintage)
            ->select(
                'grape_varieties.name as variety',
                DB::raw('COALESCE(SUM(harvests.total_weight), 0) as total_kg'),
                DB::raw('COUNT(*) as receptions')
            )
            ->groupBy('grape_varieties.id', 'grape_varieties.name')
            ->orderByDesc('total_kg')
            ->limit(8)
            ->get();

        // ── Contenedores de la bodega ─────────────────────────────────────────
        $containerCount = DB::table('containers')
            ->where('user_id', $wineryId)
            ->count();

        // ── Vinos activos ─────────────────────────────────────────────────────
        $wineCount = DB::table('wines')
            ->where('user_id', $wineryId)
            ->count();

        return view('livewire.supervisor.oversight.wineries.show', [
            'viticulturistRelations' => $viticulturistRelations,
            'plotStatsByVit'         => $plotStatsByVit,
            'lastActivityByVit'      => $lastActivityByVit,
            'recentReceptions'       => $recentReceptions,
            'vintageStats'           => $vintageStats,
            'varietyBreakdown'       => $varietyBreakdown,
            'currentVintage'         => $currentVintage,
            'containerCount'         => $containerCount,
            'wineCount'              => $wineCount,
        ]);
    }
}
