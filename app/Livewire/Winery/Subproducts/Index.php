<?php

namespace App\Livewire\Winery\Subproducts;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineSubproduct;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search      = '';
    public string $typeFilter  = '';
    public string $wineFilter  = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'wineFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }
    public function updatingWineFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'wineFilter' => ''];
    }

    public function delete(int $id): void
    {
        WineSubproduct::where('user_id', $this->wineryId())->findOrFail($id)->delete();
        $this->toastSuccess('Subproducto eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return WineSubproduct::where('user_id', $this->wineryId())
            ->with(['wine', 'unit']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(lot_number,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(destination_name,\'\')) LIKE ?', [$term])
                  ->orWhereHas('wine', fn($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('subproduct_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['subproduct_date', 'desc']; }

    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        $wines = Wine::where('user_id', $this->wineryId())
            ->orderBy('name')
            ->get();

        $base = WineSubproduct::where('user_id', $this->wineryId());

        $stats = [
            'total'     => (clone $base)->count(),
            'this_year' => (clone $base)->whereYear('subproduct_date', now()->year)->count(),
        ];

        return [
            'subproducts' => $entries,
            'wines'       => $wines,
            'types'       => WineSubproduct::TYPES,
            'stats'       => $stats,
        ];
    }
}
