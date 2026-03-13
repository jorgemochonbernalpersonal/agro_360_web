<?php

namespace App\Livewire\Winery\LabelBatches;

use App\Livewire\Winery\AbstractIndex;
use App\Models\LabelBatch;
use App\Models\Wine;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
    public string $wineFilter = '';
    public string $sourceFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'wineFilter'   => ['except' => ''],
        'sourceFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void      { $this->resetPage(); }
    public function updatingWineFilter(): void  { $this->resetPage(); }
    public function updatingSourceFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineFilter' => '', 'sourceFilter' => ''];
    }

    public function delete(int $id): void
    {
        $batch = LabelBatch::where('user_id', $this->wineryId())->findOrFail($id);

        if (! $batch->canDelete()) {
            $this->toastError('No se puede eliminar un lote con etiquetas usadas o mermas registradas.');
            return;
        }

        $batch->delete();
        $this->toastSuccess('Lote de etiquetas eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return LabelBatch::where('user_id', $this->wineryId())->with('wine');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$term]);
        }

        if ($this->wineFilter) {
            $query->where('wine_id', $this->wineFilter);
        }

        if ($this->sourceFilter) {
            $query->where('source', $this->sourceFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['id', 'desc']; }

    protected function perPage(): int { return 20; }

    protected function viewData(mixed $entries): array
    {
        $wines = Wine::where('user_id', $this->wineryId())->active()->orderBy('name')->get();

        return [
            'batches' => $entries,
            'wines'   => $wines,
            'sources' => LabelBatch::SOURCES,
        ];
    }
}
