<?php

namespace App\Livewire\Viticulturist\AgriInsurance;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\AgriInsurance;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $filter_status = '';

    public string $filter_coverage_type = '';

    protected $queryString = [
        'filter_status' => ['except' => '', 'as' => 'status'],
        'filter_coverage_type' => ['except' => '', 'as' => 'coverage'],
    ];

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCoverageType(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->findOwned(AgriInsurance::class, $id)->delete();
        $this->toastSuccess(__('Seguro eliminado.'));
    }

    protected function baseQuery(): Builder
    {
        return AgriInsurance::where('viticulturist_id', $this->viticulturistId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filter_status) {
            $query->where('status', $this->filter_status);
        }
        if ($this->filter_coverage_type) {
            $query->where('coverage_type', $this->filter_coverage_type);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['end_date', 'desc'];
    }

    protected function filterDefaults(): array
    {
        return ['filter_status' => '', 'filter_coverage_type' => ''];
    }

    protected function viewData(mixed $entries): array
    {
        $base = AgriInsurance::where('viticulturist_id', $this->viticulturistId());
        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->active()->count(),
            'expiring' => (clone $base)->active()->whereBetween('end_date', [now(), now()->addDays(30)])->count(),
            'expired' => (clone $base)->where('status', 'expired')->count(),
        ];

        return [
            'insurances' => $entries,
            'stats' => $stats,
            'expiringSoon' => $stats['expiring'],
            'activeCount' => $stats['active'],
            'coverageTypes' => AgriInsurance::coverageTypeOptions(),
            'statuses' => AgriInsurance::statusOptions(),
        ];
    }
}
