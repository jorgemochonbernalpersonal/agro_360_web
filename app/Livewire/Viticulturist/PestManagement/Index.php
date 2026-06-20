<?php

namespace App\Livewire\Viticulturist\PestManagement;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Pest;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $typeFilter = 'all';

    public bool $showOnlyRisk = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'showOnlyRisk' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShowOnlyRisk(): void
    {
        $this->resetPage();
    }

    protected function filterDefaults(): array
    {
        return [
            'search' => '',
            'typeFilter' => 'all',
            'showOnlyRisk' => false,
        ];
    }

    protected function baseQuery(): Builder
    {
        return Pest::query()->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('scientific_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->typeFilter !== 'all') {
            $query->byType($this->typeFilter);
        }

        if ($this->showOnlyRisk) {
            $query->inRiskPeriod();
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['name', 'asc'];
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('type')->orderBy('name');
    }

    protected function perPage(): int
    {
        return 12;
    }

    protected function viewData(mixed $entries): array
    {
        return [
            'pests' => $entries,
            'pestsInRisk' => Pest::active()->inRiskPeriod()->get(),
        ];
    }
}
