<?php

namespace App\Livewire\Winery\WinerySupplies;

use App\Livewire\Winery\AbstractIndex;
use App\Models\WinerySupply;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
    public string $typeFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => ''];
    }

    public function delete(int $id): void
    {
        $supply = WinerySupply::where('user_id', $this->wineryId())->findOrFail($id);
        $supply->delete();
        $this->toastSuccess('Insumo eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return WinerySupply::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(commercial_name, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->typeFilter) {
            $query->where('supply_type', $this->typeFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('name');
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }
    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        return [
            'supplies' => $entries,
            'types'    => WinerySupply::SUPPLY_TYPES,
        ];
    }
}
