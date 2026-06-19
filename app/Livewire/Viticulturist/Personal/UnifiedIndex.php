<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Livewire\Concerns\WithCrewManagement;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithViticulturistOperations;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read mixed $viticulturists
 */
class UnifiedIndex extends Component
{
    use WithCrewManagement, WithPagination, WithToastNotifications, WithUserFilters, WithViticulturistOperations;

    public $viewMode = 'personal';

    public $search = '';

    public $wineryFilter = '';

    public $statusFilter = '';

    public $crewFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'wineryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'crewFilter' => ['except' => ''],
        'viewMode' => ['except' => 'personal'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingWineryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCrewFilter(): void
    {
        $this->resetPage();
    }

    public function switchView($mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->wineryFilter = '';
        $this->statusFilter = '';
        $this->crewFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $wineries = $user->wineries;

        return $this->viewMode === 'personal'
            ? $this->renderPersonalView($user, $wineries)
            : $this->renderCrewsView($user, $wineries);
    }

    private function renderPersonalView($user, $wineries)
    {
        $allVisibleViticulturists = $this->viticulturists;
        $visibleIds = $allVisibleViticulturists->pluck('id');

        $query = User::query()
            ->where('role', 'viticulturist')
            ->whereIn('id', $visibleIds);

        if ($this->search) {
            $search = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        if ($this->wineryFilter) {
            $viticulturistIds = WineryViticulturist::where('winery_id', $this->wineryFilter)
                ->pluck('viticulturist_id');
            $query->whereIn('id', $viticulturistIds);
        }

        if ($this->crewFilter) {
            $crewMemberIds = CrewMember::where('crew_id', $this->crewFilter)
                ->pluck('viticulturist_id');
            $query->whereIn('id', $crewMemberIds);
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'in_crew') {
                $query->whereIn('id', CrewMember::whereNotNull('crew_id')->pluck('viticulturist_id'));
            } elseif ($this->statusFilter === 'individual') {
                $query->whereIn('id', CrewMember::whereNull('crew_id')->pluck('viticulturist_id'));
            } elseif ($this->statusFilter === 'unassigned') {
                $query->whereNotIn('id', CrewMember::pluck('viticulturist_id'));
            }
        }

        $viticulturists = $query->orderBy('name')->paginate(15);

        $membersByViticulturist = CrewMember::with('crew')
            ->whereIn('viticulturist_id', $viticulturists->pluck('id'))
            ->get()
            ->keyBy('viticulturist_id');

        $crews = Crew::forViticulturist($user->id)->orderBy('name')->get();

        $allViticulturists = User::where('role', 'viticulturist')->whereIn('id', $visibleIds)->get();
        $allMembers = CrewMember::whereIn('viticulturist_id', $allViticulturists->pluck('id'))
            ->with('crew')->get()->keyBy('viticulturist_id');

        $inCrewCount = $allMembers->filter(fn ($m) => $m->crew_id !== null)->count();
        $individualCount = $allMembers->filter(fn ($m) => $m->crew_id === null)->count();
        $unassignedCount = $allViticulturists->count() - $allMembers->count();
        $crewsCount = Crew::forViticulturist($user->id)->count();

        $wineryRelations = WineryViticulturist::whereIn('viticulturist_id', $viticulturists->pluck('id'))
            ->with('winery')->get()->groupBy('viticulturist_id');
        $wineriesByViticulturist = $wineryRelations->map(fn ($r) => $r->pluck('winery')->filter()->unique('id')->values());

        return view('livewire.viticulturist.personal.unified-index', [
            'viticulturists' => $viticulturists,
            'membersByViticulturist' => $membersByViticulturist,
            'crews' => $crews,
            'wineries' => $wineries,
            'viticulturistsCount' => $allViticulturists->count(),
            'inCrewCount' => $inCrewCount,
            'individualCount' => $individualCount,
            'unassignedCount' => $unassignedCount,
            'crewsCount' => $crewsCount,
            'wineriesByViticulturist' => $wineriesByViticulturist,
        ])->layout('layouts.app', [
            'title' => __('Personal y Equipos - Agro365'),
            'description' => __('Gestiona tu equipo de trabajo: viticultores, cuadrillas y asignaciones. Organiza tu personal para optimizar las labores del viñedo.'),
        ]);
    }

    private function renderCrewsView($user, $wineries)
    {
        if (! $user->can('viewAny', Crew::class)) {
            abort(403, __('No tienes permiso para ver equipos.'));
        }

        $query = Crew::forViticulturist($user->id)
            ->with(['winery', 'viticulturist'])
            ->withCount(['members', 'activities']);

        if ($this->search) {
            $search = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
            });
        }

        if ($this->wineryFilter) {
            $query->forWinery($this->wineryFilter);
        }

        $crewsPaginated = $query->orderBy('created_at', 'desc')->paginate(10);

        $allCrews = Crew::forViticulturist($user->id)->get();
        $crewsCount = $allCrews->count();

        $allViticulturists = User::where('role', 'viticulturist')
            ->whereIn('id', WineryViticulturist::editableBy($user)->pluck('viticulturist_id'))
            ->count();

        return view('livewire.viticulturist.personal.unified-index', [
            'crewsPaginated' => $crewsPaginated,
            'crews' => $allCrews,
            'wineries' => $wineries,
            'viticulturistsCount' => $allViticulturists,
            'crewsCount' => $crewsCount,
        ])->layout('layouts.app', [
            'title' => __('Personal y Equipos - Agro365'),
            'description' => __('Gestiona tu equipo de trabajo: viticultores, cuadrillas y asignaciones. Organiza tu personal para optimizar las labores del viñedo.'),
        ]);
    }
}
