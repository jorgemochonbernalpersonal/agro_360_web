<?php

namespace App\Livewire\Viticulturist\PhytosanitaryAlerts;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\PhytosanitaryAlert;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab = 'active';

    public string $search = '';

    public string $filterAlertType = '';

    public string $filterSeverity = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab',      'except' => 'active'],
        'search' => ['as' => 'q',         'except' => ''],
        'filterAlertType' => ['as' => 'type',      'except' => ''],
        'filterSeverity' => ['as' => 'severity',  'except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAlertType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSeverity(): void
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
        $this->findOwned(PhytosanitaryAlert::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Alerta archivada.'));
    }

    public function unarchive(int $id): void
    {
        $this->findOwned(PhytosanitaryAlert::class, $id)->update(['active' => true]);
        $this->toastSuccess(__('Alerta restaurada.'));
    }

    public function delete(int $id): void
    {
        $this->findOwned(PhytosanitaryAlert::class, $id)->delete();
        $this->toastSuccess(__('Alerta eliminada.'));
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterAlertType' => '', 'filterSeverity' => ''];
    }

    protected function baseQuery(): Builder
    {
        return PhytosanitaryAlert::where('viticulturist_id', $this->viticulturistId())
            ->where('active', $this->currentTab === 'active');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('affected_area', 'like', '%'.$this->search.'%');
            });
        }
        if ($this->filterAlertType) {
            $query->where('alert_type', $this->filterAlertType);
        }
        if ($this->filterSeverity) {
            $query->where('severity', $this->filterSeverity);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['alert_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 16;
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $baseQuery = PhytosanitaryAlert::where('viticulturist_id', $userId);
        $activeQ = (clone $baseQuery)->where('active', true);

        $stats = [
            'active' => $activeQ->count(),
            'archived' => (clone $baseQuery)->where('active', false)->count(),
            'critical' => (clone $activeQ)->where('severity', 'critica')->count(),
            'expired' => (clone $activeQ)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', now())
                ->count(),
        ];

        return [
            'entries' => $entries,
            'alertTypes' => PhytosanitaryAlert::alertTypeOptions(),
            'severities' => PhytosanitaryAlert::severityOptions(),
            'stats' => $stats,
        ];
    }
}
