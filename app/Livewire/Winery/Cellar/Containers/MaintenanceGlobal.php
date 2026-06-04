<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceGlobal extends AbstractIndex
{
    public string $search = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public string $containerFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'containerFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingContainerFilter(): void
    {
        $this->resetPage();
    }

    public function transition(int $id, string $status): void
    {
        $maintenance = ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $this->wineryId()))
            ->findOrFail($id);

        $updates = ['status' => $status];

        if (in_array($status, ['in_progress', 'in_review']) && ! $maintenance->performed_date) {
            $updates['performed_date'] = now()->toDateString();
        }

        if ($status === 'completed') {
            $updates['performed_date'] = $maintenance->performed_date?->toDateString() ?? now()->toDateString();
            if ($maintenance->next_maintenance_date) {
                $maintenance->container->update(['next_maintenance_date' => $maintenance->next_maintenance_date]);
            }
        }

        if ($status === 'cancelled' && $maintenance->status === 'scheduled') {
            $updates['performed_date'] = null;
        }

        $maintenance->update($updates);

        $labels = ContainerMaintenance::statusOptions();
        $this->toastSuccess(__('Estado actualizado a: :status', ['status' => $labels[$status] ?? $status]));
    }

    public function delete(int $id): void
    {
        ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $this->wineryId()))
            ->findOrFail($id)
            ->delete();
        $this->toastSuccess(__('Mantenimiento eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'statusFilter' => '', 'typeFilter' => '', 'containerFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return ContainerMaintenance::with(['container'])
            ->whereHas('container', fn ($q) => $q->where('user_id', $this->wineryId()));
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(description) LIKE ?', [$term])
                    ->orWhereHas('container', fn ($c) => $c->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('maintenance_type', $this->typeFilter);
        }

        if ($this->containerFilter) {
            $query->where('container_id', $this->containerFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('scheduled_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array
    {
        return ['scheduled_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function viewData(mixed $entries): array
    {
        $base = ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $this->wineryId()));

        $stats = [
            'total' => (clone $base)->count(),
            'scheduled' => (clone $base)->where('status', 'scheduled')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'total_cost' => (clone $base)->where('status', 'completed')->sum('cost'),
        ];

        return [
            'maintenances' => $entries,
            'containers' => Container::where('user_id', $this->wineryId())->where('archived', false)->orderBy('name')->get(),
            'types' => ContainerMaintenance::typeOptions(),
            'statuses' => ContainerMaintenance::statusOptions(),
            'stats' => $stats,
        ];
    }
}
