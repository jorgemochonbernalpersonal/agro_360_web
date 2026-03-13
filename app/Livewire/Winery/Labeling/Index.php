<?php

namespace App\Livewire\Winery\Labeling;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use App\Models\WineLabeling;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        $labeling = WineLabeling::where('user_id', $this->wineryId())->findOrFail($id);

        DB::transaction(function () use ($labeling) {
            if ($labeling->label_batch_id) {
                \App\Models\LabelBatch::where('id', $labeling->label_batch_id)
                    ->decrement('used_quantity', $labeling->quantity_labeled);
            }
            $labeling->delete();
        });

        $this->toastSuccess('Sesión de etiquetado eliminada.');
    }

    protected function baseQuery(): Builder
    {
        return WineLabeling::where('user_id', $this->wineryId())
            ->with(['wine', 'labelBatch', 'bottling']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereHas('wine', fn($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term]));
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('labeling_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['labeling_date', 'desc']; }

    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        $wines = Wine::where('user_id', $this->wineryId())->active()->orderBy('name')->get();

        return [
            'labelings' => $entries,
            'wines'     => $wines,
        ];
    }
}
