<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Models\EstimatedYield;
use App\Models\PlotPlanting;
use App\Models\Campaign;
use App\Models\Plot;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    use WithToastNotifications;

    public $estimatedYield;
    public $estimated_yield_id;

    public $plot_planting_id = '';
    public $campaign_id = '';
    public $estimated_yield_per_hectare = '';
    public $estimated_total_yield = '';
    public $estimation_date = '';
    public $estimation_method = 'visual';
    public $status = 'draft';
    public $notes = '';

    // Para filtros
    public $plot_id = '';

    // Datos cargados
    public $availablePlantings = [];
    public $availableCampaigns = [];
    public $availablePlots = [];

    public function mount($estimatedYield)
    {
        $this->estimated_yield_id = $estimatedYield;
        $this->loadEstimatedYield();
    }

    public function loadEstimatedYield()
    {
        $user = Auth::user();

        $this->estimatedYield = EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
        ->findOrFail($this->estimated_yield_id);

        // Cargar datos
        $this->plot_planting_id = $this->estimatedYield->plot_planting_id;
        $this->campaign_id = $this->estimatedYield->campaign_id;
        $this->estimated_yield_per_hectare = $this->estimatedYield->estimated_yield_per_hectare;
        $this->estimated_total_yield = $this->estimatedYield->estimated_total_yield;
        $this->estimation_date = $this->estimatedYield->estimation_date->format('Y-m-d');
        $this->estimation_method = $this->estimatedYield->estimation_method;
        $this->status = $this->estimatedYield->status;
        $this->notes = $this->estimatedYield->notes ?? '';

        // Cargar parcela
        $this->plot_id = $this->estimatedYield->plotPlanting->plot_id;

        $this->loadData();
    }

    public function loadData()
    {
        $user = Auth::user();
        
        // Cargar campañas
        $this->availableCampaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Cargar plantaciones según filtros
        $this->loadPlantings();
    }

    public function updatedCampaignId()
    {
        $this->loadPlantings();
    }

    public function updatedPlotId()
    {
        $this->plot_planting_id = '';
        $this->loadPlantings();
    }

    public function updatedEstimatedYieldPerHectare()
    {
        $this->calculateTotalYield();
    }

    public function updatedPlotPlantingId()
    {
        $this->calculateTotalYield();
    }

    protected function calculateTotalYield()
    {
        if (!$this->estimated_yield_per_hectare || !$this->plot_planting_id) {
            $this->estimated_total_yield = '';
            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if ($planting && $planting->area_planted > 0) {
            $this->estimated_total_yield = round($this->estimated_yield_per_hectare * $planting->area_planted, 3);
        }
    }

    protected function loadPlantings()
    {
        $user = Auth::user();
        
        $query = PlotPlanting::whereHas('plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id)
              ->where('active', true);
        })
        ->where('status', 'active')
        ->with(['plot', 'grapeVariety']);

        if ($this->plot_id) {
            $query->where('plot_id', $this->plot_id);
        }

        $this->availablePlantings = $query->orderBy('plot_id')->get();

        // Cargar parcelas
        if ($this->campaign_id) {
            $this->availablePlots = Plot::whereHas('activities', function($q) {
                $q->where('campaign_id', $this->campaign_id)
                  ->where('viticulturist_id', Auth::id());
            })
            ->orWhere('viticulturist_id', $user->id)
            ->where('active', true)
            ->distinct()
            ->orderBy('name')
            ->get();
        } else {
            $this->availablePlots = Plot::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->orderBy('name')
                ->get();
        }
    }

    protected function rules(): array
    {
        return [
            'plot_planting_id' => 'required|exists:plot_plantings,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'estimated_yield_per_hectare' => 'required|numeric|min:0.01',
            'estimated_total_yield' => 'required|numeric|min:0.01',
            'estimation_date' => 'required|date',
            'estimation_method' => 'required|in:visual,sampling,historical,satellite,other',
            'status' => 'required|in:draft,confirmed,archived',
            'notes' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        $user = Auth::user();

        // Verificar que la plantación pertenece al usuario
        $planting = PlotPlanting::whereHas('plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })->find($this->plot_planting_id);

        if (!$planting) {
            $this->toastError('La plantación seleccionada no es válida.');
            return;
        }

        // Verificar que no existe ya otra estimación para esta plantación y campaña (excepto la actual)
        $existing = EstimatedYield::where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->where('id', '!=', $this->estimatedYield->id)
            ->first();

        if ($existing) {
            $this->toastError('Ya existe otra estimación de rendimiento para esta plantación y campaña.');
            return;
        }

        try {
            DB::transaction(function () {
                $this->estimatedYield->update([
                    'plot_planting_id' => $this->plot_planting_id,
                    'campaign_id' => $this->campaign_id,
                    'estimated_yield_per_hectare' => $this->estimated_yield_per_hectare,
                    'estimated_total_yield' => $this->estimated_total_yield,
                    'estimation_date' => $this->estimation_date,
                    'estimation_method' => $this->estimation_method,
                    'status' => $this->status,
                    'notes' => $this->notes ?: null,
                ]);
            });

            $this->toastSuccess('Rendimiento estimado actualizado exitosamente.');
            return redirect()->route('viticulturist.digital-notebook.estimated-yields.index');
        } catch (\Exception $e) {
            $this->toastError('Error al actualizar el rendimiento estimado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.estimated-yields.edit', [
            'plantings' => $this->availablePlantings,
            'campaigns' => $this->availableCampaigns,
            'plots' => $this->availablePlots,
        ])->layout('layouts.app');
    }
}

