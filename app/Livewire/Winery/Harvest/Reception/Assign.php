<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\Harvest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Assign extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public Harvest $harvest;
    public string  $container_id = '';

    public function mount(Harvest $harvest): void
    {
        $wineryId = Auth::id();

        abort_unless($harvest->winery_id === $wineryId, 403);

        $this->harvest = $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
            'container',
        ]);

        if ($harvest->container_id) {
            $this->container_id = (string) $harvest->container_id;
        }
    }

    public function save(): mixed
    {
        $this->validate([
            'container_id' => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())->where('unit', 'kg')],
        ], [
            'container_id.required' => 'Selecciona un contenedor.',
        ]);

        $wineryId  = Auth::id();
        $container = Container::where('user_id', $wineryId)->findOrFail((int) $this->container_id);

        if ($this->harvest->container_id === $container->id) {
            $this->toastError('Este contenedor ya está asignado a esta recepción.');
            return null;
        }

        // Validate available capacity on the target container (always, whether assigning or reassigning)
        $weight = (float) $this->harvest->total_weight;
        if (!$container->hasAvailableCapacity($weight)) {
            $available = number_format($container->getAvailableCapacity(), 0);
            $required  = number_format($weight, 0);
            $this->addError('container_id', "Capacidad insuficiente. Disponible: {$available} kg, Necesario: {$required} kg.");
            return null;
        }

        // HarvestObserver::updating() handles ContainerStockService::transferContainer()
        $this->harvest->update(['container_id' => $container->id]);

        $this->toastSuccess("Recepción asignada al contenedor «{$container->name}».");
        return $this->roleRedirect('grape-reception.index');
    }

    public function render()
    {
        $wineryId = Auth::id();

        $availableContainers = Container::where('user_id', $wineryId)
            ->where('archived', false)
            ->where('unit', 'kg')
            ->orderBy('name')
            ->get();

        $typesById = ContainerType::all()->keyBy('id');

        return view('livewire.winery.harvest.reception.assign', [
            'availableContainers' => $availableContainers,
            'typesById'           => $typesById,
        ])->layout('layouts.app');
    }
}
