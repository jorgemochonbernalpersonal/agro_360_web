<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public string $vintageFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'vintageFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingVintageFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $wine = Wine::where('user_id', $this->wineryId())->findOrFail($id);
        $wine->delete();
        $this->toastSuccess("Vino «{$wine->name}» eliminado.");
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => '', 'vintageFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return Wine::where('user_id', $this->wineryId())
            ->withCount('processDetails');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(variety, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(internal_code, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('wine_type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->vintageFilter) {
            $query->where('vintage', $this->vintageFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('vintage')->orderBy('name');
    }

    protected function defaultOrderBy(): array
    {
        return ['vintage', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $vintages = Wine::where('user_id', $this->wineryId())
            ->whereNotNull('vintage')
            ->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        return [
            'wines' => $entries,
            'types' => Wine::wineTypeOptions(),
            'statuses' => Wine::statusOptions(),
            'vintages' => $vintages,
        ];
    }
}
