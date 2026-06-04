<?php

namespace App\Livewire\Viticulturist\Pac\Surfaces;

use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterPac = ''; // 'with' | 'without'

    protected $queryString = [
        'search' => ['except' => ''],
        'filterPac' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPac(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterPac = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Plot::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->with(['municipality'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->filterPac === 'with', fn ($q) => $q->whereNotNull('pac_eligible_area'))
            ->when($this->filterPac === 'without', fn ($q) => $q->whereNull('pac_eligible_area'));

        $plots = $query->orderBy('name')->paginate(20);

        $allPlots = Plot::where('viticulturist_id', Auth::id())->where('active', true)->get();
        $stats = [
            'total' => $allPlots->count(),
            'with_pac' => $allPlots->whereNotNull('pac_eligible_area')->count(),
            'without_pac' => $allPlots->whereNull('pac_eligible_area')->count(),
            'total_area' => $allPlots->sum('area'),
            'total_eligible' => $allPlots->sum('pac_eligible_area'),
        ];

        return view('livewire.viticulturist.pac.surfaces.index', [
            'plots' => $plots,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
