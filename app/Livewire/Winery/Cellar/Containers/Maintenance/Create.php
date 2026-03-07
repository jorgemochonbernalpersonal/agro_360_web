<?php

namespace App\Livewire\Winery\Cellar\Containers\Maintenance;

use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public Container $container;

    public string $maintenance_type        = 'cleaning';
    public string $maintenance_name        = '';
    public string $scheduled_date          = '';
    public string $performed_date          = '';
    public string $next_maintenance_date   = '';
    public string $status                  = 'scheduled';
    public string $cost                    = '';
    public string $performed_by            = '';
    public string $notes                   = '';

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        $this->container = $container;
        $this->scheduled_date = now()->toDateString();
    }

    public function updatedMaintenanceType(): void
    {
        if (!$this->maintenance_name) {
            $this->maintenance_name = ContainerMaintenance::TYPES[$this->maintenance_type] ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'maintenance_type'      => 'required|in:cleaning,sulfuring,inspection,repair,tartrate_removal,other',
            'maintenance_name'      => 'required|string|max:200',
            'scheduled_date'        => 'required|date',
            'performed_date'        => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status'                => 'required|in:scheduled,completed,cancelled',
            'cost'                  => 'nullable|numeric|min:0',
            'performed_by'          => 'nullable|string|max:200',
            'notes'                 => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $maintenance = ContainerMaintenance::create([
            'container_id'          => $this->container->id,
            'maintenance_type'      => $this->maintenance_type,
            'maintenance_name'      => $this->maintenance_name,
            'scheduled_date'        => $this->scheduled_date,
            'performed_date'        => $this->performed_date ?: null,
            'next_maintenance_date' => $this->next_maintenance_date ?: null,
            'status'                => $this->status,
            'cost'                  => $this->cost ?: null,
            'performed_by'          => $this->performed_by ?: null,
            'notes'                 => $this->notes ?: null,
        ]);

        // Actualizar next_maintenance_date en el contenedor si completado con fecha siguiente
        if ($maintenance->status === 'completed' && $maintenance->next_maintenance_date) {
            $this->container->update(['next_maintenance_date' => $maintenance->next_maintenance_date]);
        }

        $this->toastSuccess('Mantenimiento registrado correctamente.');
        $this->redirect(route('winery.containers.maintenance.index', $this->container), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.maintenance.create', [
            'types'    => ContainerMaintenance::TYPES,
            'statuses' => ContainerMaintenance::STATUSES,
        ])->layout('layouts.app');
    }
}
