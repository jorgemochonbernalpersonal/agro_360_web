<?php

namespace App\Livewire\Winery\FermentationControls;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $wineFilter = '';

    public string $statusFilter = '';

    public string $containerFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'wineFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'containerFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingWineFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingContainerFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $control = WineFermentationControl::whereHas('wine', fn ($q) => $q->where('user_id', $this->wineryId()))
            ->findOrFail($id);
        $control->delete();
        $this->toastSuccess(__('Control eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineFilter' => '', 'statusFilter' => '', 'containerFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return WineFermentationControl::with(['wine', 'container'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', $this->wineryId()));
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->whereHas('wine', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$term]));
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }

        if ($this->statusFilter === 'fermenting') {
            $query->where(fn ($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2));
        } elseif ($this->statusFilter === 'done') {
            $query->where(function ($q) {
                $q->whereNull('density')->orWhere('density', '<=', 1.000);
            })->where(function ($q) {
                $q->whereNull('brix_degree')->orWhere('brix_degree', '<=', 2);
            });
        }

        if ($this->containerFilter) {
            $query->where('container_id', $this->containerFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('control_date');
    }

    protected function defaultOrderBy(): array
    {
        return ['control_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function viewData(mixed $entries): array
    {
        $base = WineFermentationControl::whereHas('wine', fn ($q) => $q->where('user_id', $this->wineryId()));

        $stats = [
            'total' => (clone $base)->count(),
            'this_year' => (clone $base)->whereYear('control_date', now()->year)->count(),
            'fermenting' => (clone $base)->where(fn ($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2))->count(),
            'done' => (clone $base)->where(fn ($q) => $q->whereNull('density')->orWhere('density', '<=', 1.000))->where(fn ($q) => $q->whereNull('brix_degree')->orWhere('brix_degree', '<=', 2))->count(),
        ];

        return [
            'controls' => $entries,
            'wines' => Wine::where('user_id', $this->wineryId())->orderBy('name')->get(),
            'containers' => Container::where('user_id', $this->wineryId())->where('archived', false)->orderBy('name')->get(),
            'stats' => $stats,
        ];
    }
}
