<?php

namespace App\Livewire\Winery\WineAnalysis;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Container;
use App\Models\WineAnalysis;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $typeFilter = '';

    public string $resultFilter = '';

    public string $containerFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'resultFilter' => ['except' => ''],
        'containerFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingResultFilter(): void
    {
        $this->resetPage();
    }

    public function updatingContainerFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $analysis = WineAnalysis::where('user_id', $this->wineryId())->findOrFail($id);
        $analysis->delete();
        $this->toastSuccess(__('Análisis eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return [
            'search' => '',
            'typeFilter' => '',
            'resultFilter' => '',
            'containerFilter' => '',
        ];
    }

    protected function baseQuery(): Builder
    {
        return WineAnalysis::with(['wine', 'container'])->where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(laboratory, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(sample_reference, \'\')) LIKE ?', [$term]);
            });
        }

        if ($this->typeFilter) {
            $query->where('analysis_type', $this->typeFilter);
        }

        if ($this->resultFilter) {
            $query->where('result', $this->resultFilter);
        }

        if ($this->containerFilter) {
            $query->where('container_id', $this->containerFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('analysis_date');
    }

    protected function defaultOrderBy(): array
    {
        return ['analysis_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function viewData(mixed $entries): array
    {
        $base = WineAnalysis::where('user_id', $this->wineryId());

        $stats = [
            'total' => (clone $base)->count(),
            'this_year' => (clone $base)->whereYear('analysis_date', now()->year)->count(),
            'passed' => (clone $base)->where('result', 'passed')->count(),
            'failed' => (clone $base)->where('result', 'failed')->count(),
        ];

        return [
            'analyses' => $entries,
            'types' => WineAnalysis::analysisTypeOptions(),
            'results' => WineAnalysis::resultOptions(),
            'containers' => Container::where('user_id', $this->wineryId())->where('archived', false)->orderBy('name')->get(),
            'stats' => $stats,
        ];
    }
}
