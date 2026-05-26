<?php

namespace App\Livewire\Winery\TastingNotes;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineTastingNote;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
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
        WineTastingNote::where('user_id', $this->wineryId())->findOrFail($id)->delete();
        $this->toastSuccess(__('Nota de cata eliminada.'));
    }

    protected function baseQuery(): Builder
    {
        return WineTastingNote::where('user_id', $this->wineryId())
            ->with(['wine', 'oenologist']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(evaluator_name,\'\')) LIKE ?', [$term])
                  ->orWhereHas('wine', fn($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('evaluation_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['evaluation_date', 'desc']; }

    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        $wines = Wine::where('user_id', $this->wineryId())->active()->orderBy('name')->get();

        return [
            'tastingNotes' => $entries,
            'wines'        => $wines,
        ];
    }
}
