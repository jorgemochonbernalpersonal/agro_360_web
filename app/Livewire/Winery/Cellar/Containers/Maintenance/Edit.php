<?php

namespace App\Livewire\Winery\Cellar\Containers\Maintenance;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\UnitOfMeasurement;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public Container $container;

    public ContainerMaintenance $maintenance;

    public string $maintenance_type = '';

    public string $maintenance_name = '';

    public string $scheduled_date = '';

    public string $performed_date = '';

    public string $next_maintenance_date = '';

    public string $status = '';

    public string $cost = '';

    public string $performed_by = '';

    public string $notes = '';

    // Insumos usados — array de filas [winery_supply_id, supply_name, quantity_used, unit_of_measurement_id, cost]
    public array $supplies = [];

    // Residuos generados — array de filas [container_waste_type_id, custom_waste_type, waste_date, quantity, unit_of_measurement_id, disposal_method, cost, notes]
    public array $wastes = [];

    public function mount(Container $container, ContainerMaintenance $maintenance): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        abort_if($maintenance->container_id !== $container->id, 404);

        $this->container = $container;
        $this->maintenance = $maintenance;

        $this->maintenance_type = $maintenance->maintenance_type;
        $this->maintenance_name = $maintenance->maintenance_name;
        $this->scheduled_date = $maintenance->scheduled_date->toDateString();
        $this->performed_date = $maintenance->performed_date?->toDateString() ?? '';
        $this->next_maintenance_date = $maintenance->next_maintenance_date?->toDateString() ?? '';
        $this->status = $maintenance->status;
        $this->cost = $maintenance->cost !== null ? (string) $maintenance->cost : '';
        $this->performed_by = $maintenance->performed_by ?? '';
        $this->notes = $maintenance->notes ?? '';

        $this->supplies = $maintenance->supplies->map(fn ($s) => [
            'winery_supply_id' => (string) ($s->winery_supply_id ?? ''),
            'supply_name' => $s->supply_name ?? '',
            'quantity_used' => (string) $s->quantity_used,
            'unit_of_measurement_id' => (string) ($s->unit_of_measurement_id ?? ''),
            'cost' => $s->cost !== null ? (string) $s->cost : '',
        ])->toArray();

        $this->wastes = $maintenance->wastes->map(fn ($w) => [
            'container_waste_type_id' => (string) ($w->container_waste_type_id ?? ''),
            'custom_waste_type' => $w->custom_waste_type ?? '',
            'waste_date' => $w->waste_date->toDateString(),
            'quantity' => $w->quantity !== null ? (string) $w->quantity : '',
            'unit_of_measurement_id' => (string) ($w->unit_of_measurement_id ?? ''),
            'disposal_method' => $w->disposal_method ?? '',
            'cost' => $w->cost !== null ? (string) $w->cost : '',
            'notes' => $w->notes ?? '',
        ])->toArray();
    }

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

    public function save(): void
    {
        $this->validate();

        $this->maintenance->update([
            'maintenance_type' => $this->maintenance_type,
            'maintenance_name' => $this->maintenance_name,
            'scheduled_date' => $this->scheduled_date,
            'performed_date' => $this->performed_date ?: null,
            'next_maintenance_date' => $this->next_maintenance_date ?: null,
            'status' => $this->status,
            'cost' => $this->cost ?: null,
            'performed_by' => $this->performed_by ?: null,
            'notes' => $this->notes ?: null,
        ]);

        // Sincronizar insumos
        $this->maintenance->supplies()->delete();
        foreach ($this->supplies as $row) {
            if (empty($row['quantity_used'])) {
                continue;
            }
            $this->maintenance->supplies()->create([
                'winery_supply_id' => $row['winery_supply_id'] ?: null,
                'supply_name' => $row['supply_name'] ?: null,
                'quantity_used' => $row['quantity_used'],
                'unit_of_measurement_id' => $row['unit_of_measurement_id'] ?: null,
                'cost' => $row['cost'] ?: null,
            ]);
        }

        // Sincronizar residuos
        $this->maintenance->wastes()->delete();
        foreach ($this->wastes as $row) {
            if (empty($row['waste_date'])) {
                continue;
            }
            $this->maintenance->wastes()->create([
                'container_waste_type_id' => $row['container_waste_type_id'] ?: null,
                'custom_waste_type' => $row['custom_waste_type'] ?: null,
                'waste_date' => $row['waste_date'],
                'quantity' => $row['quantity'] ?: null,
                'unit_of_measurement_id' => $row['unit_of_measurement_id'] ?: null,
                'disposal_method' => $row['disposal_method'] ?: null,
                'cost' => $row['cost'] ?: null,
                'notes' => $row['notes'] ?: null,
            ]);
        }

        if ($this->maintenance->status === 'completed' && $this->maintenance->next_maintenance_date) {
            $this->container->update(['next_maintenance_date' => $this->maintenance->next_maintenance_date]);
        }

        $this->toastSuccess(__('Mantenimiento actualizado correctamente.'));
        $this->roleRedirect('containers.maintenance.index', $this->container);
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.maintenance.edit', [
            'types' => ContainerMaintenance::typeOptions(),
            'statuses' => ContainerMaintenance::statusOptions(),
            'supplies' => WinerySupply::where('user_id', Auth::id())->active()->orderBy('name')->get(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
            'wasteTypes' => \App\Models\ContainerWasteType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'maintenance_type' => 'required|in:cleaning,sulfuring,inspection,repair,tartrate_removal,other',
            'maintenance_name' => 'required|string|max:200',
            'scheduled_date' => 'required|date',
            'performed_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status' => 'required|in:scheduled,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'supplies.*.quantity_used' => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id' => 'nullable|exists:units_of_measurement,id',
            'supplies.*.cost' => 'nullable|numeric|min:0',
            'wastes.*.waste_date' => 'nullable|date',
            'wastes.*.quantity' => 'nullable|numeric|min:0',
            'wastes.*.cost' => 'nullable|numeric|min:0',
        ];
    }
}
