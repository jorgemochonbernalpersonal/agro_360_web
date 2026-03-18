<?php

namespace App\Livewire\Winery\WineAnalysis;

use App\Livewire\Winery\AbstractIndex;
use App\Models\WineAnalysis;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search       = '';
    public string $typeFilter   = '';
    public string $resultFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'resultFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingResultFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return [
            'search'       => '',
            'typeFilter'   => '',
            'resultFilter' => '',
        ];
    }

    public function delete(int $id): void
    {
        $analysis = WineAnalysis::where('user_id', $this->wineryId())->findOrFail($id);
        $analysis->delete();
        $this->toastSuccess('Análisis eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return WineAnalysis::with('wine')->where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
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
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('analysis_date');
    }

    protected function defaultOrderBy(): array { return ['analysis_date', 'desc']; }
    protected function perPage(): int          { return 20; }

    protected function viewData(mixed $entries): array
    {
        return [
            'analyses' => $entries,
            'types'    => WineAnalysis::ANALYSIS_TYPES,
            'results'  => WineAnalysis::RESULTS,
        ];
    }
}
