<?php

namespace App\Livewire\Supervisor\Oversight\Growers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Models\Certification;
use App\Models\DoInspection;
use App\Models\NotebookAccessRequest;
use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\NotebookAccessRequestedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public User $viticulturist;

    public bool $showAssignWineryModal = false;

    public ?int $assignWineryId = null;

    public function mount(User $viticulturist): void
    {
        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail();

        $this->viticulturist = $viticulturist;
    }

    public function requestNotebookAccess(): void
    {
        $doId = Auth::id();

        SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->firstOrFail();

        if (! $this->viticulturist->can_login) {
            $this->toastError(__('El viticultor aún no ha activado su cuenta.'));

            return;
        }

        $existing = NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->whereIn('status', [NotebookAccessRequest::STATUS_PENDING, NotebookAccessRequest::STATUS_APPROVED])
            ->exists();

        if ($existing) {
            $this->toastError(__('Ya existe una solicitud activa o aprobada para este viticultor.'));

            return;
        }

        NotebookAccessRequest::updateOrCreate(
            ['supervisor_id' => $doId, 'viticulturist_id' => $this->viticulturist->id],
            ['winery_id' => null, 'status' => NotebookAccessRequest::STATUS_PENDING, 'requested_at' => now(), 'responded_at' => null],
        );

        $this->viticulturist->notify(new NotebookAccessRequestedNotification(Auth::user()));

        Cache::forget("nav_badge_notebook_access_{$this->viticulturist->id}");

        $this->toastSuccess(__('Solicitud de acceso al cuaderno enviada al viticultor.'));
    }

    public function revokeNotebookAccess(): void
    {
        $relation = SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $this->viticulturist->id)
            ->firstOrFail();

        $relation->revokeNotebookAccess();

        $this->toastSuccess(__('Acceso al cuaderno revocado.'));
    }

    // ── Winery assignment ─────────────────────────────────────────────────────

    public function openAssignWineryModal(): void
    {
        $this->assignWineryId = null;
        $this->showAssignWineryModal = true;
    }

    public function closeAssignWineryModal(): void
    {
        $this->showAssignWineryModal = false;
    }

    public function assignWinery(): void
    {
        $this->validate([
            'assignWineryId' => ['required', 'integer'],
        ], [
            'assignWineryId.required' => __('Selecciona una bodega.'),
        ]);

        $doId = Auth::id();

        SupervisorWinery::where('supervisor_id', $doId)
            ->where('winery_id', $this->assignWineryId)
            ->firstOrFail();

        $already = WineryViticulturist::where('winery_id', $this->assignWineryId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->exists();

        if ($already) {
            $this->toastError(__('El viticultor ya está asignado a esa bodega.'));

            return;
        }

        WineryViticulturist::create([
            'winery_id' => $this->assignWineryId,
            'viticulturist_id' => $this->viticulturist->id,
            'supervisor_id' => $doId,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $doId,
        ]);

        $this->showAssignWineryModal = false;
        $this->toastSuccess(__('Viticultor asignado a la bodega.'));
    }

    public function unassignWinery(int $wineryId): void
    {
        $doId = Auth::id();

        WineryViticulturist::where('supervisor_id', $doId)
            ->where('winery_id', $wineryId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->firstOrFail()
            ->delete();

        $this->toastSuccess(__('Asignación a bodega eliminada.'));
    }

    // ── Remove from pool ──────────────────────────────────────────────────────

    public function removeFromPool(): void
    {
        $doId = Auth::id();
        $relation = SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->firstOrFail();

        WineryViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->delete();

        NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->viticulturist->id)
            ->delete();

        $relation->delete();

        $this->redirect(route('supervisor.oversight.growers.index'), navigate: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();
        $viticulturistId = $this->viticulturist->id;

        // Parcelas activas
        $plots = Plot::where('viticulturist_id', $viticulturistId)
            ->where('active', true)
            ->with(['municipality', 'plantings' => fn ($q) => $q->where('status', 'active')->with('grapeVariety')])
            ->orderBy('name')
            ->get();

        $totalArea = $plots->sum('area');
        $totalPlots = $plots->count();

        // Bodegas a las que está asignado dentro del DO de este supervisor
        $wineryRelations = WineryViticulturist::where('supervisor_id', $supervisorId)
            ->where('viticulturist_id', $viticulturistId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->with('winery')
            ->get();

        // Acceso cuaderno: permiso directo DO→viticultor en supervisor_viticulturist
        $supervisorRelation = SupervisorViticulturist::where('supervisor_id', $supervisorId)
            ->where('viticulturist_id', $viticulturistId)
            ->first();

        $hasNotebookAccess = $supervisorRelation?->hasNotebookAccess() ?? false;

        $pendingNotebookRequest = ! $hasNotebookAccess
            ? NotebookAccessRequest::where('supervisor_id', $supervisorId)
                ->where('viticulturist_id', $viticulturistId)
                ->where('status', NotebookAccessRequest::STATUS_PENDING)
                ->exists()
            : false;

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
        $plotsWithoutPac = $plots->filter(fn ($p) => is_null($p->pac_eligible_area))->count();
        $lockedPlots = $plots->filter(fn ($p) => $p->is_locked)->count();

        // Bodegas del DO no asignadas todavía a este viticultor (para el modal)
        $assignedWineryIds = $wineryRelations->pluck('winery_id');
        $availableWineries = SupervisorWinery::where('supervisor_id', $supervisorId)
            ->whereNotIn('winery_id', $assignedWineryIds)
            ->with('winery:id,name')
            ->get()
            ->map(fn ($r) => $r->winery)
            ->filter()
            ->sortBy('name')
            ->values();

        return view('livewire.supervisor.oversight.growers.show', [
            'plots' => $plots,
            'totalArea' => $totalArea,
            'totalPlots' => $totalPlots,
            'wineryRelations' => $wineryRelations,
            'hasNotebookAccess' => $hasNotebookAccess,
            'pendingNotebookRequest' => $pendingNotebookRequest,
            'supervisorRelation' => $supervisorRelation,
            'recentActivities' => $recentActivities,
            'activityCounts' => $activityCounts,
            'certifications' => $certifications,
            'inspections' => $inspections,
            'plotsWithoutPac' => $plotsWithoutPac,
            'lockedPlots' => $lockedPlots,
            'availableWineries' => $availableWineries,
        ]);
    }
}
