<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\Container;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithToastNotifications;

    public $name = '';
    public $description = '';
    public $serial_number = '';
    public $quantity = 1;
    public $capacity = '';
    public $purchase_date = '';
    public $next_maintenance_date = '';

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function loadData()
    {
        $user = Auth::user();
        
        // Cargar campañas
        $this->availableCampaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Cargar cosechas según filtros
        $this->loadHarvests();
    }

    public function updatedCampaignId()
    {
        $this->plot_id = '';
        $this->harvest_id = '';
        $this->loadHarvests();
    }

    public function updatedPlotId()
    {
        $this->harvest_id = '';
        $this->loadHarvests();
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
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                Container::create([
                    'user_id' => Auth::id(),
                    'name' => $this->name,
                    'description' => $this->description ?: null,
                    'serial_number' => $this->serial_number ?: null,
                    'quantity' => $this->quantity,
                    'capacity' => $this->capacity,
                    'used_capacity' => 0, // Inicialmente vacío
                    'purchase_date' => $this->purchase_date ?: null,
                    'next_maintenance_date' => $this->next_maintenance_date ?: null,
                    'archived' => false, // Por defecto activo (archived = false)
                    'unit_of_measurement_id' => 1, // kg por defecto
                    'type_id' => 1, // Tipo por defecto
                    'material_id' => 1, // Material por defecto
                ]);
            });

            $this->toastSuccess('Contenedor creado exitosamente.');
            return redirect()->route('viticulturist.digital-notebook.containers.index');
        } catch (\Exception $e) {
            \Log::error('Error al crear contenedor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al crear el contenedor. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.containers.create')
            ->layout('layouts.app');
    }
}

