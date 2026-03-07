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

class Create extends Component
{
    use WithToastNotifications;

    public $plot_planting_id = '';
    public $campaign_id = '';
    public $estimated_yield_per_hectare = '';
    public $estimated_total_yield = '';
    public $estimation_date = '';
    public $estimation_method = 'visual';
    public $status = 'draft';
    public $notes = '';
    public $estimation_round = 1;

    // Campos de muestreo de campo
    public $thumbs_per_vine = '';
    public $bunches_per_plant = '';
    public $bunch_weight_grams = '';
    public $total_plants_sampled = '';
    public $sampling_area_pct = '';
    public $health_percentage = '';
    public $health_status = '';
    public bool $other_wineries = false;
    public $potential_alcohol = '';
    public $vintage = '';

    // Calculado en tiempo real desde muestreo
    public $auto_calculated_yield = '';

    // Para filtros
    public $plot_id = '';

    // Datos cargados
    public $availablePlantings = [];
    public $availableCampaigns = [];
    public $availablePlots = [];

    public function mount()
    {
        $user = Auth::user();
        
        // Obtener campaña activa por defecto
        $campaign = Campaign::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->first();
        
        if ($campaign) {
            $this->campaign_id = $campaign->id;
        }

        $this->estimation_date = now()->format('Y-m-d');
        
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
        $this->plot_id = '';
        $this->plot_planting_id = '';
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
        $this->calculateFromSampling();
        // Sugerir añada con el año de la campaña activa
        if ($this->campaign_id && !$this->vintage) {
            $campaign = \App\Models\Campaign::find($this->campaign_id);
            $this->vintage = $campaign?->year;
        }
    }

    public function updatedBunchesPerPlant() { $this->calculateFromSampling(); }
    public function updatedBunchWeightGrams() { $this->calculateFromSampling(); }
    public function updatedHealthPercentage() { $this->calculateFromSampling(); }

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

    protected function calculateFromSampling()
    {
        if (!$this->bunches_per_plant || !$this->bunch_weight_grams || !$this->plot_planting_id) {
            $this->auto_calculated_yield = '';
            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if ($planting && $planting->vine_count > 0) {
            $factor = $this->health_percentage ? ($this->health_percentage / 100) : 1;
            $this->auto_calculated_yield = round(
                ($this->bunches_per_plant * $this->bunch_weight_grams / 1000)
                * $planting->vine_count
                * $factor,
                2
            );
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

        // Cargar parcelas si hay campaña seleccionada
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
            'plot_planting_id'         => 'required|exists:plot_plantings,id',
            'campaign_id'              => 'required|exists:campaigns,id',
            'estimation_round'         => 'required|integer|min:1|max:4',
            'estimated_yield_per_hectare' => 'required|numeric|min:0.01',
            'estimated_total_yield'    => 'required|numeric|min:0.01',
            'estimation_date'          => 'required|date',
            'estimation_method'        => 'required|in:visual,sampling,historical,satellite,other',
            'status'                   => 'required|in:draft,confirmed,archived',
            'notes'                    => 'nullable|string',
            // Muestreo (opcionales)
            'thumbs_per_vine'          => 'nullable|integer|min:0|max:100',
            'bunches_per_plant'        => 'nullable|numeric|min:0|max:200',
            'bunch_weight_grams'       => 'nullable|numeric|min:0|max:2000',
            'total_plants_sampled'     => 'nullable|integer|min:1',
            'sampling_area_pct'        => 'nullable|numeric|min:0|max:100',
            'health_percentage'        => 'nullable|numeric|min:0|max:100',
            'health_status'            => 'nullable|string|in:excellent,good,botrytis_light,botrytis_moderate,oidium_light,oidium_moderate,mixed,poor',
            'other_wineries'           => 'boolean',
            'potential_alcohol'        => 'nullable|numeric|min:0|max:25',
            'vintage'                  => 'nullable|integer|min:1900|max:2100',
        ];
    }

    public function save()
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

        // Verificar que no existe ya la misma ronda para esta plantación y campaña
        $existing = EstimatedYield::where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->where('estimation_round', $this->estimation_round)
            ->first();

        if ($existing) {
            $roundLabel = \App\Models\EstimatedYield::ROUNDS[$this->estimation_round] ?? "Ronda {$this->estimation_round}";
            $this->toastError("Ya existe una estimación de {$roundLabel} para esta plantación y campaña. Edita la existente o elige otra ronda.");
            return;
        }

        try {
            DB::transaction(function () use ($user) {
                EstimatedYield::create([
                    'plot_planting_id'            => $this->plot_planting_id,
                    'campaign_id'                 => $this->campaign_id,
                    'estimated_by'                => $user->id,
                    'estimated_yield_per_hectare' => $this->estimated_yield_per_hectare,
                    'estimated_total_yield'       => $this->estimated_total_yield,
                    'estimation_date'             => $this->estimation_date,
                    'estimation_method'           => $this->estimation_method,
                    'status'                      => $this->status,
                    'estimation_round'            => $this->estimation_round,
                    'notes'                       => $this->notes ?: null,
                    // Muestreo
                    'thumbs_per_vine'             => $this->thumbs_per_vine ?: null,
                    'bunches_per_plant'           => $this->bunches_per_plant ?: null,
                    'bunch_weight_grams'          => $this->bunch_weight_grams ?: null,
                    'total_plants_sampled'        => $this->total_plants_sampled ?: null,
                    'sampling_area_pct'           => $this->sampling_area_pct ?: null,
                    'health_percentage'           => $this->health_percentage ?: null,
                    'health_status'               => $this->health_status ?: null,
                    'other_wineries'              => $this->other_wineries,
                    'potential_alcohol'           => $this->potential_alcohol ?: null,
                    'vintage'                     => $this->vintage ?: null,
                ]);
            });

            $this->toastSuccess('Rendimiento estimado creado exitosamente.');
            return redirect()->route('viticulturist.digital-notebook.estimated-yields.index');
        } catch (\Exception $e) {
            $this->toastError('Error al crear el rendimiento estimado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.estimated-yields.create', [
            'plantings' => $this->availablePlantings,
            'campaigns' => $this->availableCampaigns,
            'plots' => $this->availablePlots,
        ])->layout('layouts.app');
    }
}

