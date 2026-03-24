<?php

namespace App\Livewire\Supervisor\Oversight\Growers;

use App\Models\AgriculturalActivity;
use App\Models\Certification;
use App\Models\DoInspection;
use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public User $viticulturist;

    public function mount(User $viticulturist): void
    {
        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail();

        $this->viticulturist = $viticulturist;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId      = Auth::id();
        $viticulturistId   = $this->viticulturist->id;

        // Parcelas activas
        $plots = Plot::where('viticulturist_id', $viticulturistId)
            ->where('active', true)
            ->with(['municipality', 'plantings' => fn($q) => $q->where('status', 'active')->with('grapeVariety')])
            ->orderBy('name')
            ->get();

        $totalArea   = $plots->sum('area');
        $totalPlots  = $plots->count();

        // Bodegas a las que está asignado dentro del DO de este supervisor
        $wineryRelations = WineryViticulturist::where('supervisor_id', $supervisorId)
            ->where('viticulturist_id', $viticulturistId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->with('winery')
            ->get();

        // Acceso cuaderno: alguna de esas relaciones tiene acceso concedido
        $hasNotebookAccess = $wineryRelations->where('cuaderno_access', true)->isNotEmpty();

        // Últimas actividades del cuaderno (solo si hay acceso)
        $recentActivities = collect();
        if ($hasNotebookAccess) {
            $recentActivities = AgriculturalActivity::where('viticulturist_id', $viticulturistId)
                ->orderByDesc('activity_date')
                ->limit(10)
                ->get();
        }

        // Conteo de actividades por tipo en la campaña actual
        $activityCounts = DB::table('agricultural_activities')
            ->where('viticulturist_id', $viticulturistId)
            ->whereYear('activity_date', now()->year)
            ->select('activity_type', DB::raw('COUNT(*) as total'))
            ->groupBy('activity_type')
            ->pluck('total', 'activity_type');

        // Certificaciones
        $certifications = Certification::where('viticulturist_id', $viticulturistId)
            ->where('active', true)
            ->orderBy('expiry_date')
            ->get();

        // Inspecciones realizadas por este supervisor a este viticultor
        $inspections = DoInspection::where('supervisor_id', $supervisorId)
            ->where('subject_type', 'viticulturist')
            ->where('subject_id', $viticulturistId)
            ->orderByDesc('inspection_date')
            ->limit(5)
            ->get();

        // Alertas PAC: parcelas sin área elegible
        $plotsWithoutPac = $plots->filter(fn($p) => is_null($p->pac_eligible_area))->count();
        $lockedPlots     = $plots->filter(fn($p) => $p->is_locked)->count();

        return view('livewire.supervisor.oversight.growers.show', [
            'plots'             => $plots,
            'totalArea'         => $totalArea,
            'totalPlots'        => $totalPlots,
            'wineryRelations'   => $wineryRelations,
            'hasNotebookAccess' => $hasNotebookAccess,
            'recentActivities'  => $recentActivities,
            'activityCounts'    => $activityCounts,
            'certifications'    => $certifications,
            'inspections'       => $inspections,
            'plotsWithoutPac'   => $plotsWithoutPac,
            'lockedPlots'       => $lockedPlots,
        ]);
    }
}
