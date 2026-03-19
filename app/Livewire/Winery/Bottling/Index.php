<?php

namespace App\Livewire\Winery\Bottling;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Services\WineContainerStockService;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search    = '';
    public string $wineFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'wineFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingWineFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineFilter' => ''];
    }

    public function delete(int $id): void
    {
        $bottling = WineBottling::where('user_id', $this->wineryId())->findOrFail($id);

        if ($bottling->product_lot_id) {
            $this->toastError('No se puede eliminar un embotellado vinculado a un lote de producto.');
            return;
        }

        app(WineContainerStockService::class)->revertBottling($bottling);
        $bottling->delete();
        $this->toastSuccess('Registro de embotellado eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return WineBottling::where('user_id', $this->wineryId())
            ->with(['wine', 'oenologist', 'productLot']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(lot_number, \'\')) LIKE ?', [$term])
                  ->orWhereHas('wine', fn($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('bottling_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['bottling_date', 'desc']; }

    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        $wines = Wine::where('user_id', $this->wineryId())
            ->active()
            ->orderBy('name')
            ->get();

        $total = WineBottling::where('user_id', $this->wineryId())->count();

        return [
            'bottlings' => $entries,
            'wines'     => $wines,
            'total'     => $total,
        ];
    }
}
