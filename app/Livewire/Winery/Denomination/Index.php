<?php

namespace App\Livewire\Winery\Denomination;

use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public string $tab = 'general';

    public function updatingTab(): void
    {
        $this->dispatch('denomination-tab-changed');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();

        // ── Supervisor (DO) de esta bodega ────────────────────────────────────
        $supervisorRelation = SupervisorWinery::where('winery_id', $wineryId)
            ->with('supervisor')
            ->first();

        $supervisor = $supervisorRelation?->supervisor;

        // ── Viticultores aportados por la DO a esta bodega ────────────────────
        $doViticulturists = collect();

        if ($supervisor) {
            $relations = WineryViticulturist::where('winery_id', $wineryId)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->where('supervisor_id', $supervisor->id)
                ->with('viticulturist')
                ->get();

            $viticulturistIds = $relations->pluck('viticulturist_id');

            $plotStats = DB::table('plots')
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

            $lastActivity = DB::table('agricultural_activities')
                ->whereIn('viticulturist_id', $viticulturistIds)
                ->select('viticulturist_id', DB::raw('MAX(activity_date) as last_activity'))
                ->groupBy('viticulturist_id')
                ->pluck('last_activity', 'viticulturist_id');

            $doViticulturists = $relations->map(function ($rel) use ($plotStats, $lastActivity) {
                return (object) [
                    'viticulturist' => $rel->viticulturist,
                    'plot_count' => $plotStats[$rel->viticulturist_id]?->plot_count ?? 0,
                    'total_area' => $plotStats[$rel->viticulturist_id]?->total_area ?? 0,
                    'last_activity' => $lastActivity[$rel->viticulturist_id] ?? null,
                ];
            });
        }

        // ── Módulos activos (abilities) ───────────────────────────────────────
        $grantedAbilities = Auth::user()->abilities()->orderBy('module')->orderBy('name')->get();

        return view('livewire.winery.denomination.index', [
            'supervisor' => $supervisor,
            'supervisorJoined' => $supervisorRelation?->created_at,
            'doViticulturists' => $doViticulturists,
            'grantedAbilities' => $grantedAbilities,
        ]);
    }
}
