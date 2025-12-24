<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\Container;
use App\Models\Harvest;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    use WithToastNotifications;

    public $container;
    public $container_id;

    public $name = '';
    public $description = '';
    public $serial_number = '';
    public $quantity = 1;
    public $capacity = '';
    public $purchase_date = '';
    public $next_maintenance_date = '';
    public $archived = false;

    public function mount($container)
    {
        $this->container_id = $container;
        $this->loadContainer();
    }

    public function loadContainer()
    {
        $user = Auth::user();

        // Cargar contenedor del usuario
        $this->container = Container::where('user_id', $user->id)
            ->with(['harvests.activity.plot', 'harvests.plotPlanting.grapeVariety', 'harvests.activity.campaign', 'currentState'])
            ->findOrFail($this->container_id);

        // Cargar datos del contenedor
        $this->name = $this->container->name;
        $this->description = $this->container->description ?? '';
        $this->serial_number = $this->container->serial_number ?? '';
        $this->quantity = $this->container->quantity;
        $this->capacity = $this->container->capacity;
        $this->purchase_date = $this->container->purchase_date ? $this->container->purchase_date->format('Y-m-d') : '';
        $this->next_maintenance_date = $this->container->next_maintenance_date ? $this->container->next_maintenance_date->format('Y-m-d') : '';
        $this->archived = $this->container->archived ?? false;
    }


    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'capacity' => 'required|numeric|min:0.001',
            'purchase_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date|after_or_equal:purchase_date',
            'archived' => 'boolean',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // Validar que la capacidad no sea menor que la capacidad usada
                if ($this->capacity < $this->container->used_capacity) {
                    throw new \Exception('La capacidad no puede ser menor que la capacidad usada actual (' . number_format($this->container->used_capacity, 2) . ' kg).');
                }
                
                $this->container->update([
                    'name' => $this->name,
                    'description' => $this->description ?: null,
                    'serial_number' => $this->serial_number ?: null,
                    'quantity' => $this->quantity,
                    'capacity' => $this->capacity,
                    'purchase_date' => $this->purchase_date ?: null,
                    'next_maintenance_date' => $this->next_maintenance_date ?: null,
                    'archived' => $this->archived,
                ]);
            });

            $this->toastSuccess('Contenedor actualizado exitosamente.');
            return redirect()->route('viticulturist.digital-notebook.containers.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar contenedor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al actualizar el contenedor. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.containers.edit')
            ->layout('layouts.app');
    }
}

