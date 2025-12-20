<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\HarvestContainer;
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

    public $container_type = 'caja';
    public $container_number = '';
    public $quantity = 1;
    public $weight = '';
    public $weight_per_unit = '';
    public $location = '';
    public $status = 'filled';
    public $filled_date = '';
    public $delivery_date = '';
    public $notes = '';

    public function mount($container)
    {
        $this->container_id = $container;
        $this->loadContainer();
    }

    public function loadContainer()
    {
        $user = Auth::user();

        // Cargar contenedor (puede o no tener cosecha asignada)
        $this->container = HarvestContainer::where(function($q) use ($user) {
            // Contenedores sin cosecha (del usuario a través de harvests que puedan tener)
            $q->whereNull('harvest_id')
              ->orWhereHas('harvest.activity', function($subQ) use ($user) {
                  $subQ->where('viticulturist_id', $user->id);
              });
        })
        ->with(['harvest.activity.plot', 'harvest.plotPlanting.grapeVariety', 'harvest.activity.campaign'])
        ->findOrFail($this->container_id);

        // Cargar datos del contenedor
        $this->container_type = $this->container->container_type;
        $this->container_number = $this->container->container_number ?? '';
        $this->quantity = $this->container->quantity;
        $this->weight = $this->container->weight;
        $this->weight_per_unit = $this->container->weight_per_unit ?? '';
        $this->location = $this->container->location ?? '';
        $this->status = $this->container->status;
        $this->filled_date = $this->container->filled_date ? $this->container->filled_date->format('Y-m-d') : '';
        $this->delivery_date = $this->container->delivery_date ? $this->container->delivery_date->format('Y-m-d') : '';
        $this->notes = $this->container->notes ?? '';
    }

    public function updatedWeight()
    {
        $this->calculateWeightPerUnit();
    }

    public function updatedQuantity()
    {
        $this->calculateWeightPerUnit();
    }

    protected function calculateWeightPerUnit()
    {
        if ($this->weight && $this->quantity && $this->quantity > 0) {
            $this->weight_per_unit = round($this->weight / $this->quantity, 3);
        } else {
            $this->weight_per_unit = '';
        }
    }

    protected function rules(): array
    {
        return [
            'container_type' => 'required|in:caja,pallet,contenedor,saco,cuba,other',
            'container_number' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'weight' => 'required|numeric|min:0.001',
            'weight_per_unit' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:filled,in_transit,delivered,stored,empty',
            'filled_date' => 'nullable|date',
            'delivery_date' => 'nullable|date|after_or_equal:filled_date',
            'notes' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $oldHarvestId = $this->container->harvest_id;
                
                $this->container->update([
                    'harvest_id' => null, // Desvincular de cosecha (los contenedores son independientes)
                    'container_type' => $this->container_type,
                    'container_number' => $this->container_number ?: null,
                    'quantity' => $this->quantity,
                    'weight' => $this->weight,
                    'weight_per_unit' => $this->weight_per_unit ?: null,
                    'location' => $this->location ?: null,
                    'status' => $this->status,
                    'filled_date' => $this->filled_date ?: null,
                    'delivery_date' => $this->delivery_date ?: null,
                    'notes' => $this->notes ?: null,
                ]);

                // Si tenía una cosecha asignada, actualizar el container_id de esa cosecha a null
                if ($oldHarvestId) {
                    Harvest::where('container_id', $this->container->id)
                        ->where('id', $oldHarvestId)
                        ->update(['container_id' => null]);
                }
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

