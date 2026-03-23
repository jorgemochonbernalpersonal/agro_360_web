<?php

namespace App\Livewire\Supervisor\Census;

use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public string $currentTab = 'wineries'; // 'wineries' | 'viticulturists'
    public string $search = '';

    public bool   $showAssignModal = false;
    public string $assignSearch    = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'wineries'],
        'search'     => ['except' => ''],
    ];

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->search     = '';
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
        $this->assignSearch    = '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignSearch    = '';
    }

    public function assignWinery(int $wineryId): void
    {
        $winery = User::where('id', $wineryId)
            ->whereIn('role', ['winery', 'producer'])
            ->firstOrFail();

        SupervisorWinery::firstOrCreate(
            [
                'supervisor_id' => Auth::id(),
                'winery_id'     => $winery->id,
            ],
            ['assigned_by' => Auth::id()]
        );

        $this->dispatch('toast', message: "{$winery->name} adscrita a la denominación.", type: 'success');
    }

    public function unassignWinery(int $wineryId): void
    {
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $wineryId)
            ->firstOrFail()
            ->delete();

        $this->dispatch('toast', message: 'Bodega desadscrita de la denominación.', type: 'warning');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $wineryCount = SupervisorWinery::where('supervisor_id', $doId)->count();

        $viticulturistCount = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->distinct('viticulturist_id')
            ->count('viticulturist_id');

        if ($this->currentTab === 'wineries') {
            $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

            $vitCountByWinery = WineryViticulturist::where('supervisor_id', $doId)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->select('winery_id', DB::raw('count(distinct viticulturist_id) as vit_count'))
                ->groupBy('winery_id')
                ->pluck('vit_count', 'winery_id');

            $query = User::whereIn('id', $wineryIds);

            if ($this->search) {
                $search = '%' . strtolower($this->search) . '%';
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$search])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                });
            }

            $items = $query->orderBy('name')->paginate(15);
        } else {
            $viticulturistIds = WineryViticulturist::where('supervisor_id', $doId)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->pluck('viticulturist_id')
                ->unique();

            $query = User::whereIn('id', $viticulturistIds)->withCount('plots');

            if ($this->search) {
                $search = '%' . strtolower($this->search) . '%';
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$search])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                });
            }

            $items            = $query->orderBy('name')->paginate(15);
            $vitCountByWinery = collect();
        }

        // Bodegas disponibles para asignar (solo cuando el modal está abierto)
        $availableWineries = collect();

        if ($this->showAssignModal) {
            $assignedWineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

            $availableQuery = User::whereIn('role', ['winery', 'producer'])
                ->whereNotIn('id', $assignedWineryIds);

            if ($this->assignSearch) {
                $s = '%' . strtolower($this->assignSearch) . '%';
                $availableQuery->where(function ($q) use ($s) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$s]);
                });
            }

            $availableWineries = $availableQuery->orderBy('name')->limit(20)->get(['id', 'name', 'email']);
        }

        return view('livewire.supervisor.census.index', [
            'items'              => $items,
            'wineryCount'        => $wineryCount,
            'viticulturistCount' => $viticulturistCount,
            'vitCountByWinery'   => $vitCountByWinery ?? collect(),
            'availableWineries'  => $availableWineries,
        ]);
    }
}
