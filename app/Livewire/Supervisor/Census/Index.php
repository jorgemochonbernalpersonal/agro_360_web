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
            // Get winery IDs supervised by this DO
            $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

            // Count viticulturists per winery assigned via this DO
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

            $items           = $query->orderBy('name')->paginate(15);
            $vitCountByWinery = $vitCountByWinery;
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

        return view('livewire.supervisor.census.index', [
            'items'              => $items,
            'wineryCount'        => $wineryCount,
            'viticulturistCount' => $viticulturistCount,
            'vitCountByWinery'   => $vitCountByWinery,
        ]);
    }
}
