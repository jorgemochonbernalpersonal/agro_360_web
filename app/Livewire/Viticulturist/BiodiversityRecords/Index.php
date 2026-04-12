<?php

namespace App\Livewire\Viticulturist\BiodiversityRecords;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\BiodiversityRecord;
use App\Models\Campaign;
use App\Models\Plot;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search            = '';
    public string $filterRecordType  = '';
    public string $filterPlot        = '';
    public string $filterCampaign    = '';

    protected $queryString = [
        'search'           => ['as' => 'q',        'except' => ''],
        'filterRecordType' => ['as' => 'type',     'except' => ''],
        'filterPlot'       => ['as' => 'plot',     'except' => ''],
        'filterCampaign'   => ['as' => 'campaign', 'except' => ''],
    ];

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFilterRecordType(): void { $this->resetPage(); }
    public function updatingFilterPlot(): void       { $this->resetPage(); }
    public function updatingFilterCampaign(): void   { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return [
            'search'           => '',
            'filterRecordType' => '',
            'filterPlot'       => '',
            'filterCampaign'   => '',
        ];
    }

    public function delete(int $id): void
    {
        $this->findOwned(BiodiversityRecord::class, $id)->delete();
        $this->toastSuccess('Registro eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return BiodiversityRecord::where('viticulturist_id', $this->viticulturistId())
            ->with('plot', 'campaign');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('species', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->filterRecordType) {
            $query->where('record_type', $this->filterRecordType);
        }
        if ($this->filterPlot) {
            $query->where('plot_id', $this->filterPlot);
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
    }

    protected function defaultOrderBy(): array { return ['record_date', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $base   = BiodiversityRecord::where('viticulturist_id', $userId);

        $stats = [
            'total'         => (clone $base)->count(),
            'total_area_m2' => (clone $base)->sum('area_m2'),
            'types_count'   => (clone $base)->distinct('record_type')->count('record_type'),
            'plots_count'   => (clone $base)->distinct('plot_id')->count('plot_id'),
        ];

        return [
            'entries'     => $entries,
            'recordTypes' => BiodiversityRecord::RECORD_TYPES,
            'plots'       => Plot::where('user_id', $userId)->orderBy('name')->get(['id', 'name']),
            'campaigns'   => Campaign::forViticulturist($userId)->orderByDesc('year')->get(['id', 'name', 'year']),
            'stats'       => $stats,
        ];
    }
}
