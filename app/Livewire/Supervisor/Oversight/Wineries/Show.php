<?php

namespace App\Livewire\Supervisor\Oversight\Wineries;

use App\Livewire\Concerns\WithViticulturistAssignment;
use App\Livewire\Concerns\WithWineryNotes;
use App\Models\Ability;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\SupervisorWineryNote;
use App\Models\User;
use App\Models\UserAbility;
use App\Models\WineryViticulturist;
use App\Notifications\WineryAbilityChangedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use WithViticulturistAssignment, WithWineryNotes;

    public User $winery;

    public function mount(User $winery): void
    {
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $winery->id)
            ->firstOrFail();

        $this->winery = $winery;
        $this->noteDate = now()->format('Y-m-d');
    }

    public function toggleAbility(int $abilityId): void
    {
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->firstOrFail();

        if (! $this->winery->abilities_configured) {
            $this->winery->update(['abilities_configured' => true]);
        }

        $ability = Ability::findOrFail($abilityId);

        $existing = UserAbility::where('user_id', $this->winery->id)
            ->where('ability_id', $abilityId)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->winery->notify(new WineryAbilityChangedNotification($ability, false, Auth::user()->name));
            $this->dispatch('toast', message: __('Módulo «:name» desactivado.', ['name' => $ability->name]), type: 'warning');
        } else {
            UserAbility::create([
                'user_id' => $this->winery->id,
                'ability_id' => $abilityId,
                'granted_by' => Auth::id(),
                'granted_at' => now(),
            ]);
            $this->winery->notify(new WineryAbilityChangedNotification($ability, true, Auth::user()->name));
            $this->dispatch('toast', message: __('Módulo «:name» activado.', ['name' => $ability->name]), type: 'success');
        }

        Cache::forget("winery:{$this->winery->id}:granted_abilities");
    }

    public function toggleAccess(): void
    {
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
        $wineryId = $this->winery->id;

        $viticulturistRelations = WineryViticulturist::where('supervisor_id', $supervisorId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('winery_id', $wineryId)
            ->with('viticulturist')
            ->get();

        $viticulturistIds = $viticulturistRelations->pluck('viticulturist_id');

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

        $lastActivityByVit = DB::table('agricultural_activities')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->select('viticulturist_id', DB::raw('MAX(activity_date) as last_activity'))
            ->groupBy('viticulturist_id')
            ->pluck('last_activity', 'viticulturist_id');

        $currentVintage = now()->year;

        $recentReceptions = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->whereNotNull('winery_id')
            ->orderByDesc('harvest_start_date')
            ->limit(10)
            ->select(['id', 'harvest_start_date', 'total_weight', 'vintage', 'baume_degree', 'health_status', 'plot_planting_id', 'status'])
            ->get();

        $vintageStats = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $currentVintage)
            ->selectRaw('COUNT(*) as reception_count, COALESCE(SUM(total_weight), 0) as total_kg, COALESCE(AVG(baume_degree), 0) as avg_baume')
            ->first();

        $varietyBreakdown = DB::table('harvests')
            ->join('plot_plantings', 'plot_plantings.id', '=', 'harvests.plot_planting_id')
            ->join('grape_varieties', 'grape_varieties.id', '=', 'plot_plantings.grape_variety_id')
            ->where('harvests.winery_id', $wineryId)
            ->where('harvests.vintage', $currentVintage)
            ->selectRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(grape_varieties.name, ?)) as variety, '.
                'COALESCE(SUM(harvests.total_weight), 0) as total_kg, '.
                'COUNT(*) as receptions',
                ['$."'.app()->getLocale().'"']
            )
            ->groupBy('grape_varieties.id', 'grape_varieties.name')
            ->orderByDesc('total_kg')
            ->limit(8)
            ->get();

        $assignedViticulturistIds = WineryViticulturist::where('supervisor_id', $supervisorId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('winery_id', $wineryId)
            ->pluck('viticulturist_id');

        $poolQuery = User::whereIn(
            'id',
            SupervisorViticulturist::where('supervisor_id', $supervisorId)->pluck('viticulturist_id')
        )->whereNotIn('id', $assignedViticulturistIds);

        if ($this->poolSearch) {
            $s = '%'.strtolower($this->poolSearch).'%';
            $poolQuery->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(name) LIKE ?', [$s])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$s]);
            });
        }

        $poolViticulturists = $poolQuery->orderBy('name')->get(['id', 'name', 'email']);
        $containerCount = DB::table('containers')->where('user_id', $wineryId)->count();
        $wineCount = DB::table('wines')->where('user_id', $wineryId)->count();
        $allAbilities = Ability::orderBy('module')->orderBy('name')->get();
        $grantedAbilityIds = UserAbility::where('user_id', $wineryId)->pluck('ability_id');

        $wineryNotes = SupervisorWineryNote::forSupervisor($supervisorId)
            ->forWinery($wineryId)
            ->orderByDesc('note_date')
            ->orderByDesc('id')
            ->get();

        return view('livewire.supervisor.oversight.wineries.show', [
            'viticulturistRelations' => $viticulturistRelations,
            'plotStatsByVit' => $plotStatsByVit,
            'lastActivityByVit' => $lastActivityByVit,
            'recentReceptions' => $recentReceptions,
            'vintageStats' => $vintageStats,
            'varietyBreakdown' => $varietyBreakdown,
            'currentVintage' => $currentVintage,
            'containerCount' => $containerCount,
            'wineCount' => $wineCount,
            'poolViticulturists' => $poolViticulturists,
            'allAbilities' => $allAbilities,
            'grantedAbilityIds' => $grantedAbilityIds,
            'wineryNotes' => $wineryNotes,
            'noteTypeLabels' => SupervisorWineryNote::typeLabelOptions(),
            'noteTypeIcons' => SupervisorWineryNote::TYPE_ICONS,
            'noteTypeColors' => SupervisorWineryNote::TYPE_COLORS,
        ]);
    }
}
