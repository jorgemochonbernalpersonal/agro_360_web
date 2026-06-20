<?php

namespace App\Livewire\Viticulturist\Pac\Surfaces;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Plot;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $filterPac = '';

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

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterPac' => ''];
    }

    protected function baseQuery(): Builder
    {
        return Plot::where('viticulturist_id', $this->viticulturistId())
            ->where('active', true)
            ->with(['municipality']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->filterPac === 'with') {
            $query->whereNotNull('pac_eligible_area');
        } elseif ($this->filterPac === 'without') {
            $query->whereNull('pac_eligible_area');
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['name', 'asc'];
    }

    protected function viewData(mixed $entries): array
    {
        $allPlots = Plot::where('viticulturist_id', $this->viticulturistId())->where('active', true)->get();

        $stats = [
            'total' => $allPlots->count(),
            'with_pac' => $allPlots->whereNotNull('pac_eligible_area')->count(),
            'without_pac' => $allPlots->whereNull('pac_eligible_area')->count(),
            'total_area' => $allPlots->sum('area'),
            'total_eligible' => $allPlots->sum('pac_eligible_area'),
        ];

        return [
            'plots' => $entries,
            'stats' => $stats,
        ];
    }
}
