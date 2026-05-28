<?php

namespace App\Livewire\Winery\Cellar\Containers\Maintenance;

use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\ContainerWasteType;
use App\Models\UnitOfMeasurement;
use App\Models\WinerySupply;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

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

    public array $supplies = [];
    public array $wastes   = [];

    public function addSupply(): void
    {
        $this->supplies[] = [
            'winery_supply_id' => '', 'supply_name' => '',
            'quantity_used' => '', 'unit_of_measurement_id' => '', 'cost' => '',
        ];
    }

    public function removeSupply(int $index): void
    {
        array_splice($this->supplies, $index, 1);
    }

    public function addWaste(): void
    {
        $this->wastes[] = [
            'container_waste_type_id' => '', 'custom_waste_type' => '',
            'waste_date' => now()->toDateString(), 'quantity' => '',
            'unit_of_measurement_id' => '', 'disposal_method' => '',
            'cost' => '', 'notes' => '',
        ];
    }

    public function removeWaste(int $index): void
    {
        array_splice($this->wastes, $index, 1);
    }

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        $this->container = $container;
        $this->scheduled_date = now()->toDateString();
    }

    public function updatedMaintenanceType(): void
    {
        if (!$this->maintenance_name) {
            $this->maintenance_name = __(ContainerMaintenance::TYPES[$this->maintenance_type] ?? '');
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
            'supplies.*.quantity_used'          => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id' => 'nullable|exists:units_of_measurement,id',
            'supplies.*.cost'                   => 'nullable|numeric|min:0',
            'wastes.*.waste_date'               => 'nullable|date',
            'wastes.*.quantity'                 => 'nullable|numeric|min:0',
            'wastes.*.cost'                     => 'nullable|numeric|min:0',
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

        foreach ($this->supplies as $row) {
            if (empty($row['quantity_used'])) continue;
            $maintenance->supplies()->create([
                'winery_supply_id'       => $row['winery_supply_id'] ?: null,
                'supply_name'            => $row['supply_name'] ?: null,
                'quantity_used'          => $row['quantity_used'],
                'unit_of_measurement_id' => $row['unit_of_measurement_id'] ?: null,
                'cost'                   => $row['cost'] ?: null,
            ]);
        }

        foreach ($this->wastes as $row) {
            if (empty($row['waste_date'])) continue;
            $maintenance->wastes()->create([
                'container_waste_type_id' => $row['container_waste_type_id'] ?: null,
                'custom_waste_type'       => $row['custom_waste_type'] ?: null,
                'waste_date'              => $row['waste_date'],
                'quantity'                => $row['quantity'] ?: null,
                'unit_of_measurement_id'  => $row['unit_of_measurement_id'] ?: null,
                'disposal_method'         => $row['disposal_method'] ?: null,
                'cost'                    => $row['cost'] ?: null,
                'notes'                   => $row['notes'] ?: null,
            ]);
        }

        $this->toastSuccess(__('Mantenimiento registrado correctamente.'));
        $this->roleRedirect('containers.maintenance.index', $this->container);
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.maintenance.create', [
            'types'      => ContainerMaintenance::typeOptions(),
            'statuses'   => ContainerMaintenance::statusOptions(),
            'supplies'   => WinerySupply::where('user_id', Auth::id())->active()->orderBy('name')->get(),
            'units'      => UnitOfMeasurement::orderBy('name')->get(),
            'wasteTypes' => ContainerWasteType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
