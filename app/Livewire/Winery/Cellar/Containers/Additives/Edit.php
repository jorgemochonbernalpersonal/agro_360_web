<?php

namespace App\Livewire\Winery\Cellar\Containers\Additives;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerAdditiveSupply;
use App\Models\UnitOfMeasurement;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithOwnershipRules, WithToastNotifications;

    public Container $container;

    public ContainerAdditiveSupply $additive;

    public string $winery_supply_id = '';

    public string $additive_name = '';

    public string $quantity = '';

    public string $unit_of_measurement_id = '';

    public string $additive_date = '';

    public string $notes = '';

    public function mount(Container $container, ContainerAdditiveSupply $additive): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        abort_if($additive->container_id !== $container->id, 404);

        $this->container = $container;
        $this->additive = $additive;

        $this->winery_supply_id = (string) ($additive->winery_supply_id ?? '');
        $this->additive_name = $additive->additive_name ?? '';
        $this->quantity = (string) $additive->quantity;
        $this->unit_of_measurement_id = (string) ($additive->unit_of_measurement_id ?? '');
        $this->additive_date = $additive->additive_date->toDateString();
        $this->notes = $additive->notes ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $this->additive->update([
            'winery_supply_id' => $this->winery_supply_id ?: null,
            'additive_name' => $this->additive_name ?: null,
            'quantity' => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'additive_date' => $this->additive_date,
            'notes' => $this->notes ?: null,
        ]);

        $this->toastSuccess(__('Aditivo actualizado correctamente.'));
        $this->redirect(roleRoute('containers.additives.index', $this->container), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.additives.edit', [
            'winerySupplies' => WinerySupply::where('user_id', Auth::id())->active()->orderBy('name')->get(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'winery_supply_id' => $this->ownedWinerySupplyRule(false),
            'additive_name' => ['nullable', 'string', 'max:200'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'additive_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
