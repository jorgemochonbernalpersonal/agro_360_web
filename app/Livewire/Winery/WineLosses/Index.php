<?php

namespace App\Livewire\Winery\WineLosses;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineLoss;
use App\Services\WineContainerStockService;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
    public string $wineFilter = '';
    public string $typeFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'wineFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingWineFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineFilter' => '', 'typeFilter' => ''];
    }

    public function delete(int $id): void
    {
        $loss = WineLoss::whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()))
            ->findOrFail($id);

        app(WineContainerStockService::class)->revertLoss($loss);
        $loss->delete();
        $this->toastSuccess('Merma eliminada y stock restaurado.');
    }

    protected function baseQuery(): Builder
    {
        return WineLoss::with(['wine', 'container', 'unitOfMeasurement'])
            ->whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()));
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('wine', fn($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term]))
                  ->orWhereHas('container', fn($c) => $c->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }

        if ($this->typeFilter) {
            $query->where('loss_type', $this->typeFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('loss_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['loss_date', 'desc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        return [
            'losses'     => $entries,
            'wines'      => Wine::where('user_id', $this->wineryId())->orderBy('name')->get(),
            'lossTypes'  => WineLoss::LOSS_TYPES,
        ];
    }
}
