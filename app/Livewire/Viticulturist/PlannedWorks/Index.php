<?php

namespace App\Livewire\Viticulturist\PlannedWorks;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\PlannedWork;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab = 'pending';

    public string $search = '';

    public string $filterCategory = '';

    public string $filterPriority = '';

    public string $filterPlot = '';

    public string $filterCampaign = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab',      'except' => 'pending'],
        'search' => ['as' => 'q',        'except' => ''],
        'filterCategory' => ['as' => 'category', 'except' => ''],
        'filterPriority' => ['as' => 'priority', 'except' => ''],
        'filterPlot' => ['as' => 'plot',     'except' => ''],
        'filterCampaign' => ['as' => 'campaign', 'except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPriority(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlot(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    // ── Acciones ─────────────────────────────────────────────────────────────

    public function startWork(int $id): void
    {
        $this->findOwned(PlannedWork::class, $id)->markInProgress();
        $this->toastSuccess(__('Trabajo marcado como en progreso.'));
    }

    public function completeWork(int $id): void
    {
        $this->findOwned(PlannedWork::class, $id)->markCompleted();
        $this->toastSuccess(__('Trabajo marcado como completado.'));
    }

    public function cancelWork(int $id): void
    {
        $this->findOwned(PlannedWork::class, $id)->markCancelled();
        $this->toastSuccess(__('Trabajo cancelado.'));
    }

    public function reopen(int $id): void
    {
        $work = $this->findOwned(PlannedWork::class, $id);
        $work->update(['status' => 'pendiente', 'completed_at' => null]);
        $this->toastSuccess(__('Trabajo reabierto.'));
    }

    public function delete(int $id): void
    {
        $this->findOwned(PlannedWork::class, $id)->delete();
        $this->toastSuccess(__('Trabajo eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return [
            'search' => '', 'filterCategory' => '', 'filterPriority' => '',
            'filterPlot' => '', 'filterCampaign' => '',
        ];
    }

    // ── Query ────────────────────────────────────────────────────────────────

    protected function baseQuery(): Builder
    {
        $query = PlannedWork::where('viticulturist_id', $this->viticulturistId());

        return match ($this->currentTab) {
            'pending' => $query->whereIn('status', ['pendiente', 'en_progreso']),
            'completed' => $query->where('status', 'completada'),
            'cancelled' => $query->where('status', 'cancelada'),
            'overdue' => $query->where('status', 'pendiente')->whereDate('planned_date', '<', now()),
            default => $query,
        };
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }
        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }
        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }
        if ($this->filterPlot) {
            $query->where('plot_id', $this->filterPlot);
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['planned_date', 'asc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $base = PlannedWork::where('viticulturist_id', $userId);

        $stats = [
            'pending' => (clone $base)->whereIn('status', ['pendiente', 'en_progreso'])->count(),
            'overdue' => (clone $base)->overdue()->count(),
            'completed' => (clone $base)->completed()->count(),
            'cancelled' => (clone $base)->where('status', 'cancelada')->count(),
            'upcoming' => (clone $base)->upcoming(7)->count(),
        ];

        return [
            'entries' => $entries,
            'stats' => $stats,
            'categories' => PlannedWork::categoryOptions(),
            'priorities' => PlannedWork::priorityOptions(),
            'plots' => \App\Models\Plot::where('viticulturist_id', $userId)->orderBy('name')->get(['id', 'name']),
            'campaigns' => \App\Models\Campaign::where('viticulturist_id', $userId)->orderByDesc('year')->get(['id', 'name', 'year']),
        ];
    }
}
