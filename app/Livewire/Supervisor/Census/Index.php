<?php

namespace App\Livewire\Supervisor\Census;

use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $currentTab = 'wineries'; // 'wineries' | 'viticulturists'

    public string $search = '';

    public bool $showAssignModal = false;

    public string $assignSearch = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'wineries'],
        'search' => ['except' => ''],
    ];

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->search = '';
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function openAssignModal(): void
    {
        $this->assignSearch = '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignSearch = '';
    }

    public function assignWinery(int $wineryId): void
    {
        $winery = User::where('id', $wineryId)
            ->whereIn('role', ['winery', 'producer'])
            ->firstOrFail();

        SupervisorWinery::firstOrCreate(
            [
                'supervisor_id' => Auth::id(),
                'winery_id' => $winery->id,
            ],
            ['assigned_by' => Auth::id()]
        );

        $this->dispatch('toast', message: __(':name adscrita a la denominación.', ['name' => $winery->name]), type: 'success');
    }

    public function unassignWinery(int $wineryId): void
    {
        $relation = SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $wineryId)
            ->firstOrFail();

        // Regla de negocio: una D.O. debe mantener al menos una bodega asociada.
        // Una D.O. sin bodegas no tiene nada que supervisar (interfaz vacía).
        if (SupervisorWinery::where('supervisor_id', Auth::id())->count() <= 1) {
            $this->dispatch('toast', message: __('Una denominación de origen debe mantener al menos una bodega asociada.'), type: 'error');

            return;
        }

        $relation->delete();

        $this->dispatch('toast', message: __('Bodega desadscrita de la denominación.'), type: 'warning');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $wineryCount = SupervisorWinery::where('supervisor_id', $doId)->count();

        $viticulturistCount = SupervisorViticulturist::where('supervisor_id', $doId)->count();

        if ($this->currentTab === 'wineries') {
            $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

            $vitCountByWinery = WineryViticulturist::where('supervisor_id', $doId)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->select('winery_id', DB::raw('count(distinct viticulturist_id) as vit_count'))
                ->groupBy('winery_id')
                ->pluck('vit_count', 'winery_id');

            $query = User::whereIn('id', $wineryIds);

            if ($this->search) {
                $search = '%'.strtolower($this->search).'%';
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                });
            }

            $items = $query->orderBy('name')->paginate(15);
        } else {
            $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $doId)
                ->pluck('viticulturist_id');

            $query = User::whereIn('id', $viticulturistIds)->withCount('plots');

            if ($this->search) {
                $search = '%'.strtolower($this->search).'%';
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                });
            }

            $items = $query->orderBy('name')->paginate(15);
            $vitCountByWinery = collect();
        }

        // Bodegas disponibles para asignar (solo cuando el modal está abierto)
        $availableWineries = collect();

        if ($this->showAssignModal) {
            $assignedWineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

            $availableQuery = User::whereIn('role', ['winery', 'producer'])
                ->whereNotIn('id', $assignedWineryIds);

            if ($this->assignSearch) {
                $s = '%'.strtolower($this->assignSearch).'%';
                $availableQuery->where(function ($q) use ($s) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$s])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$s]);
                });
            }

            $availableWineries = $availableQuery->orderBy('name')->limit(20)->get(['id', 'name', 'email']);
        }

        return view('livewire.supervisor.census.index', [
            'items' => $items,
            'wineryCount' => $wineryCount,
            'viticulturistCount' => $viticulturistCount,
            'vitCountByWinery' => $vitCountByWinery ?? collect(),
            'availableWineries' => $availableWineries,
        ]);
    }
}
