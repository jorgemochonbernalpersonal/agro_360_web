<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\HarvestContainer;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithToastNotifications;

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

    public function mount()
    {
        $this->filled_date = now()->format('Y-m-d');
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

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                HarvestContainer::create([
                    'harvest_id' => null, // Contenedor independiente
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

