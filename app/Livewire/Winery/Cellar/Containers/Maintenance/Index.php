<?php

namespace App\Livewire\Winery\Cellar\Containers\Maintenance;

use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public Container $container;
    public string $statusFilter = '';
    public string $typeFilter   = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'typeFilter'   => ['except' => ''],
    ];

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        $this->container = $container;
    }

    public function transition(int $id, string $status): void
    {
        $maintenance = ContainerMaintenance::where('container_id', $this->container->id)->findOrFail($id);

        $updates = ['status' => $status];

        if ($status === 'in_progress' && ! $maintenance->performed_date) {
            $updates['performed_date'] = now()->toDateString();
        }

        if ($status === 'in_review' && ! $maintenance->performed_date) {
            // Work is done; record performed date when sending for review
            $updates['performed_date'] = now()->toDateString();
        }

        if ($status === 'completed') {
            $updates['performed_date'] = $maintenance->performed_date?->toDateString() ?? now()->toDateString();
            if ($maintenance->next_maintenance_date) {
                $this->container->update(['next_maintenance_date' => $maintenance->next_maintenance_date]);
            }
        }

        if ($status === 'cancelled') {
            // Clear performed_date if cancelled before work started
            if (in_array($maintenance->status, ['scheduled'])) {
                $updates['performed_date'] = null;
            }
        }

        $maintenance->update($updates);

        $labels = ContainerMaintenance::statusOptions();
        $this->toastSuccess(__('Estado actualizado a: :status', ['status' => $labels[$status] ?? $status]));
    }

    public function markCompleted(int $id): void
    {
        $this->transition($id, 'completed');
    }

    public function cancel(int $id): void
    {
        $this->transition($id, 'cancelled');
    }

    public function render()
    {
        $query = ContainerMaintenance::where('container_id', $this->container->id)
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter,   fn($q) => $q->where('maintenance_type', $this->typeFilter));

        $maintenances = (clone $query)->orderByDesc('scheduled_date')->paginate(20);

        $allMaintenance = ContainerMaintenance::where('container_id', $this->container->id)->get(['status', 'cost']);
        $stats = [
            'scheduled'  => $allMaintenance->where('status', 'scheduled')->count(),
            'completed'  => $allMaintenance->where('status', 'completed')->count(),
            'total_cost' => $allMaintenance->where('status', 'completed')->sum('cost'),
        ];

        return view('livewire.winery.cellar.containers.maintenance.index', [
            'maintenances' => $maintenances,
            'stats'        => $stats,
            'types'        => ContainerMaintenance::typeOptions(),
            'statuses'     => ContainerMaintenance::statusOptions(),
        ])->layout('layouts.app');
    }
}
