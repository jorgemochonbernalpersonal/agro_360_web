<?php

namespace App\Livewire\Viticulturist\WaterConcessions;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\WaterConcession;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab = 'active';

    public string $search = '';

    public string $filterConcessionType = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab',  'except' => 'active'],
        'search' => ['as' => 'q',    'except' => ''],
        'filterConcessionType' => ['as' => 'type', 'except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterConcessionType(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $this->findOwned(WaterConcession::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Concesión archivada.'));
    }

    public function unarchive(int $id): void
    {
        $this->findOwned(WaterConcession::class, $id)->update(['active' => true]);
        $this->toastSuccess(__('Concesión restaurada.'));
    }

    public function delete(int $id): void
    {
        $this->findOwned(WaterConcession::class, $id)->delete();
        $this->toastSuccess(__('Concesión eliminada.'));
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterConcessionType' => ''];
    }

    protected function baseQuery(): Builder
    {
        return WaterConcession::where('viticulturist_id', $this->viticulturistId())
            ->where('active', $this->currentTab === 'active');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('water_body', 'like', '%'.$this->search.'%')
                    ->orWhere('authority', 'like', '%'.$this->search.'%')
                    ->orWhere('concession_number', 'like', '%'.$this->search.'%');
            });
        }
        if ($this->filterConcessionType) {
            $query->where('concession_type', $this->filterConcessionType);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['concession_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $baseQuery = WaterConcession::where('viticulturist_id', $userId);
        $activeQ = (clone $baseQuery)->where('active', true);

        $stats = [
            'active' => $activeQ->count(),
            'archived' => (clone $baseQuery)->where('active', false)->count(),
            'total_m3' => (clone $activeQ)->sum('max_volume_m3'),
            'used_m3' => (clone $activeQ)->sum('used_volume_m3'),
            'expiring_soon' => (clone $activeQ)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>', now())
                ->whereDate('expiry_date', '<=', now()->addDays(90))
                ->count(),
        ];

        return [
            'entries' => $entries,
            'concessionTypes' => WaterConcession::CONCESSION_TYPES,
            'stats' => $stats,
        ];
    }
}
