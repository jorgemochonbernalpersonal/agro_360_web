<?php

namespace App\Livewire\Winery\ProductSheets;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Wine;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search      = '';
    public string $typeFilter  = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return Wine::where('user_id', $this->wineryId())
            ->with([
                'analyses'    => fn($q) => $q->latest('analysis_date')->limit(1),
                'tastingNotes'=> fn($q) => $q->latest('evaluation_date')->limit(1),
                'oenologist:id,name,surname',
            ]);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$term]);
        }

        if ($this->typeFilter) {
            $query->where('wine_type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        } else {
            $query->whereNotIn('status', ['cancelled']);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('vintage')->orderBy('name');
    }

    protected function defaultOrderBy(): array { return ['vintage', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $base = Wine::where('user_id', $this->wineryId());

        $stats = [
            'total'         => (clone $base)->count(),
            'active'        => (clone $base)->whereIn('status', ['in_progress', 'aged'])->count(),
            'bottled'       => (clone $base)->where('status', 'bottled')->count(),
            'with_analysis' => (clone $base)->whereHas('analyses')->count(),
        ];

        return [
            'wines'      => $entries,
            'wineTypes'  => Wine::WINE_TYPES,
            'statuses'   => Wine::STATUSES,
            'stats'      => $stats,
        ];
    }
}
