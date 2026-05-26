<?php

namespace App\Livewire\Winery\Suppliers;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search         = '';
    public string $categoryFilter = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'categoryFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'categoryFilter' => ''];
    }

    public function delete(int $id): void
    {
        $supplier = Supplier::where('user_id', $this->wineryId())->findOrFail($id);
        $supplier->delete();
        $this->toastSuccess(__('Proveedor eliminado.'));
    }

    protected function baseQuery(): Builder
    {
        return Supplier::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(contact_person, \'\')) LIKE ?', [$term]);
            });
        }
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('name');
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        return [
            'suppliers'  => $entries,
            'categories' => Supplier::CATEGORIES,
        ];
    }
}
