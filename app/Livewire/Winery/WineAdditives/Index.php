<?php

namespace App\Livewire\Winery\WineAdditives;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineAdditive;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
    public string $wineFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'wineFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingWineFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineFilter' => ''];
    }

    public function delete(int $id): void
    {
        WineAdditive::whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()))
            ->findOrFail($id)
            ->delete();
        $this->toastSuccess(__('Aditivo eliminado.'));
    }

    protected function baseQuery(): Builder
    {
        return WineAdditive::with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()));
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(additive_name) LIKE ?', [$term])
                  ->orWhereHas('wine', fn($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('application_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['application_date', 'desc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        $base = WineAdditive::whereHas('wine', fn($q) => $q->where('user_id', $this->wineryId()));

        $stats = [
            'total'     => (clone $base)->count(),
            'this_year' => (clone $base)->whereYear('application_date', now()->year)->count(),
            'wines'     => (clone $base)->distinct('wine_id')->count('wine_id'),
        ];

        return [
            'additives' => $entries,
            'wines'     => Wine::where('user_id', $this->wineryId())->whereNotIn('status', ['cancelled'])->orderBy('name')->get(),
            'stats'     => $stats,
        ];
    }
}
