<?php

namespace App\Livewire\Winery\WineTransfers;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search      = '';
    public string $wineFilter  = '';
    public string $typeFilter  = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'wineFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingWineFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineFilter' => '', 'typeFilter' => ''];
    }

    public function delete(int $id): void
    {
        $transfer = WineTransfer::whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()))
            ->findOrFail($id);

        app(WineContainerStockService::class)->revertTransfer($transfer);
        $transfer->delete();
        $this->toastSuccess('Trasvase eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return WineTransfer::with(['wine', 'fromContainer', 'toContainer', 'unitOfMeasurement'])
            ->whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()));
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereHas('wine', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', [$term]));
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }

        if ($this->typeFilter) {
            $query->where('transfer_type', $this->typeFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('transfer_date');
    }

    protected function defaultOrderBy(): array { return ['transfer_date', 'desc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        $base = WineTransfer::whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()));

        $stats = [
            'total'     => (clone $base)->count(),
            'this_year' => (clone $base)->whereYear('transfer_date', now()->year)->count(),
            'rackings'  => (clone $base)->where('transfer_type', 'racking')->count(),
            'blendings' => (clone $base)->where('transfer_type', 'blending')->count(),
        ];

        return [
            'transfers' => $entries,
            'wines'     => Wine::where('user_id', $this->wineryId())->orderBy('name')->get(),
            'types'     => WineTransfer::TRANSFER_TYPES,
            'stats'     => $stats,
        ];
    }
}
