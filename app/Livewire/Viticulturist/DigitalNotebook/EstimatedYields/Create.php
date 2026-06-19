<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Livewire\Concerns\WithEstimatedYieldForm;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use RuntimeException;

class Create extends Component
{
    use WithEstimatedYieldForm, WithToastNotifications, WithViticulturistValidation;

    public function mount(): void
    {
        $user = Auth::user();

        $campaign = Campaign::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->first();

        if ($campaign) {
            $this->campaign_id = $campaign->id;
        }

        $this->estimation_date = now()->format('Y-m-d');

        $this->loadData();
    }

    public function updatedCampaignId(): void
    {
        $this->plot_id = '';
        $this->plot_planting_id = '';
        $this->loadPlantings();
    }

    public function updatedPlotPlantingId(): void
    {
        $this->calculateTotalYield();
        $this->calculateFromSampling();

        if ($this->campaign_id && ! $this->vintage) {
            $campaign = Campaign::find($this->campaign_id);
            $this->vintage = $campaign?->year;
        }
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        $planting = PlotPlanting::whereHas('plot', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })->find($this->plot_planting_id);

        if (! $planting) {
            $this->toastError(__('La plantación seleccionada no es válida.'));

            return;
        }

        $existing = EstimatedYield::where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->where('estimation_round', $this->estimation_round)
            ->first();

        if ($existing) {
            $roundLabel = EstimatedYield::ROUNDS[$this->estimation_round] ?? "Ronda {$this->estimation_round}";
            $this->toastError("Ya existe una estimación de {$roundLabel} para esta plantación y campaña. Edita la existente o elige otra ronda.");

            return;
        }

        try {
            DB::transaction(function () use ($user) {
                EstimatedYield::create([
                    'plot_planting_id' => $this->plot_planting_id,
                    'campaign_id' => $this->campaign_id,
                    'estimated_by' => $user->id,
                    'estimated_yield_per_hectare' => $this->estimated_yield_per_hectare,
                    'estimated_total_yield' => $this->estimated_total_yield,
                    'estimation_date' => $this->estimation_date,
                    'estimation_method' => $this->estimation_method,
                    'status' => $this->status,
                    'estimation_round' => $this->estimation_round,
                    'notes' => $this->notes ?: null,
                    'thumbs_per_vine' => $this->thumbs_per_vine ?: null,
                    'bunches_per_plant' => $this->bunches_per_plant ?: null,
                    'bunch_weight_grams' => $this->bunch_weight_grams ?: null,
                    'total_plants_sampled' => $this->total_plants_sampled ?: null,
                    'sampling_area_pct' => $this->sampling_area_pct ?: null,
                    'health_percentage' => $this->health_percentage ?: null,
                    'health_status' => $this->health_status ?: null,
                    'other_wineries' => $this->other_wineries,
                    'potential_alcohol' => $this->potential_alcohol ?: null,
                    'vintage' => $this->vintage ?: null,
                ]);
            });

            $this->toastSuccess(__('Rendimiento estimado creado exitosamente.'));
            $route = Auth::user()->isProducer()
                ? route('producer.digital-notebook.estimated-yields.index')
                : route('viticulturist.digital-notebook.estimated-yields.index');

            return $this->redirect($route, navigate: true);
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al crear el rendimiento estimado. Inténtalo de nuevo.'));
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
