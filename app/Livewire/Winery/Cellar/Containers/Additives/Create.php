<?php

namespace App\Livewire\Winery\Cellar\Containers\Additives;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerAdditiveSupply;
use App\Models\ContainerCurrentState;
use App\Models\UnitOfMeasurement;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public Container $container;

    public string $winery_supply_id       = '';
    public string $additive_name          = '';
    public string $quantity               = '';
    public string $unit_of_measurement_id = '';
    public string $additive_date          = '';
    public string $notes                  = '';

    public function mount(Container $container): void
    {
        abort_if($container->user_id !== Auth::id(), 403);
        $this->container    = $container;
        $this->additive_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'winery_supply_id'       => ['nullable', 'exists:winery_supplies,id'],
            'additive_name'          => ['nullable', 'string', 'max:200'],
            'quantity'               => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'additive_date'          => ['required', 'date'],
            'notes'                  => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Obtener el estado actual del contenedor si existe
        $currentState = ContainerCurrentState::where('container_id', $this->container->id)
            ->latest()
            ->first();

        ContainerAdditiveSupply::create([
            'container_id'             => $this->container->id,
            'container_current_state_id' => $currentState?->id,
            'winery_supply_id'         => $this->winery_supply_id ?: null,
            'additive_name'            => $this->additive_name ?: null,
            'quantity'                 => $this->quantity,
            'unit_of_measurement_id'   => $this->unit_of_measurement_id ?: null,
            'additive_date'            => $this->additive_date,
            'created_by'               => Auth::id(),
            'notes'                    => $this->notes ?: null,
        ]);

        $this->toastSuccess('Aditivo registrado correctamente.');
        $this->redirect(route('winery.containers.additives.index', $this->container), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.containers.additives.create', [
            'winerySupplies' => WinerySupply::where('user_id', Auth::id())->active()->orderBy('name')->get(),
            'units'          => UnitOfMeasurement::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
