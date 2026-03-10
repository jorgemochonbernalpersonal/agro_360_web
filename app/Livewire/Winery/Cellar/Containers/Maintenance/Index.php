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

    public function markCompleted(int $id): void
    {
        $maintenance = ContainerMaintenance::where('container_id', $this->container->id)->findOrFail($id);
        $maintenance->update([
            'status'         => 'completed',
            'performed_date' => now()->toDateString(),
        ]);

        // Actualizar next_maintenance_date en el contenedor si hay una fecha programada
        if ($maintenance->next_maintenance_date) {
            $this->container->update([
                'next_maintenance_date' => $maintenance->next_maintenance_date,
            ]);
        }

        $this->toastSuccess('Mantenimiento marcado como completado.');
    }

    public function cancel(int $id): void
    {
        ContainerMaintenance::where('container_id', $this->container->id)
            ->findOrFail($id)
            ->update(['status' => 'cancelled']);
        $this->toastSuccess('Mantenimiento cancelado.');
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
            'types'        => ContainerMaintenance::TYPES,
            'statuses'     => ContainerMaintenance::STATUSES,
        ])->layout('layouts.app');
    }
}
