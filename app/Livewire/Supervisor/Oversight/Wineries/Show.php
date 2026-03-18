<?php

namespace App\Livewire\Supervisor\Oversight\Wineries;

use App\Models\Ability;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\UserAbility;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public User $winery;

    public bool   $showAssignModal = false;
    public string $poolSearch      = '';

    public function updatingPoolSearch(): void
    {
        // reset handled in render — no pagination needed
    }

    public function openAssignModal(): void
    {
        $this->poolSearch      = '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->poolSearch      = '';
    }

    public function assignViticulturist(int $viticulturistId): void
    {
        // Guard: viticultor debe estar en el pool del supervisor
        SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $viticulturistId)
            ->firstOrFail();

        WineryViticulturist::firstOrCreate(
            [
                'winery_id'        => $this->winery->id,
                'viticulturist_id' => $viticulturistId,
            ],
            [
                'source'        => WineryViticulturist::SOURCE_SUPERVISOR,
                'supervisor_id' => Auth::id(),
                'assigned_by'   => Auth::id(),
            ]
        );

        $this->dispatch('toast', message: 'Viticultor asignado a la bodega.', type: 'success');
    }

    public function unassignViticulturist(int $viticulturistId): void
    {
        WineryViticulturist::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->where('viticulturist_id', $viticulturistId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->firstOrFail()
            ->delete();

        $this->dispatch('toast', message: 'Viticultor retirado de la bodega.', type: 'warning');
    }

    public function toggleAbility(int $abilityId): void
    {
        // Guard: la bodega debe pertenecer a este supervisor
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->firstOrFail();

        $ability = Ability::findOrFail($abilityId);

        $existing = UserAbility::where('user_id', $this->winery->id)
            ->where('ability_id', $abilityId)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->dispatch('toast', message: "Módulo «{$ability->name}» desactivado.", type: 'warning');
        } else {
            UserAbility::create([
                'user_id'    => $this->winery->id,
                'ability_id' => $abilityId,
                'granted_by' => Auth::id(),
                'granted_at' => now(),
            ]);
            $this->dispatch('toast', message: "Módulo «{$ability->name}» activado.", type: 'success');
        }
    }

    public function mount(User $winery): void
    {
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $winery->id)
            ->firstOrFail();

        $this->winery = $winery;
    }

    public function toggleAccess(): void
    {
        // Guard: verificar que esta bodega pertenece al supervisor
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->firstOrFail();

        $this->winery->update(['can_login' => ! $this->winery->can_login]);
        $this->winery->refresh();

        $msg = $this->winery->can_login
            ? 'Acceso restaurado. La bodega puede iniciar sesión.'
            : 'Bodega desactivada. No podrá iniciar sesión.';

        $this->dispatch('toast', message: $msg, type: $this->winery->can_login ? 'success' : 'warning');
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

        // ── Pool de viticultores del supervisor no asignados aún a esta bodega ──
        $assignedViticulturistIds = WineryViticulturist::where('supervisor_id', $supervisorId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('winery_id', $wineryId)
            ->pluck('viticulturist_id');

        $poolQuery = User::whereIn(
            'id',
            SupervisorViticulturist::where('supervisor_id', $supervisorId)->pluck('viticulturist_id')
        )->whereNotIn('id', $assignedViticulturistIds);

        if ($this->poolSearch) {
            $s = '%' . strtolower($this->poolSearch) . '%';
            $poolQuery->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(name) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$s]);
            });
        }

        $poolViticulturists = $poolQuery->orderBy('name')->get(['id', 'name', 'email']);

        // ── Contenedores de la bodega ─────────────────────────────────────────
        $containerCount = DB::table('containers')
            ->where('user_id', $wineryId)
            ->count();

        // ── Vinos activos ─────────────────────────────────────────────────────
        $wineCount = DB::table('wines')
            ->where('user_id', $wineryId)
            ->count();

        // ── Abilities ─────────────────────────────────────────────────────────
        $allAbilities      = Ability::orderBy('module')->orderBy('name')->get();
        $grantedAbilityIds = UserAbility::where('user_id', $wineryId)->pluck('ability_id');

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
            'poolViticulturists'     => $poolViticulturists,
            'allAbilities'           => $allAbilities,
            'grantedAbilityIds'      => $grantedAbilityIds,
        ]);
    }
}
