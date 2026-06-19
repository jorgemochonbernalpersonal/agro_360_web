<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Livewire\Concerns\WithEstimatedYieldForm;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\EstimatedYield;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use RuntimeException;

class Edit extends Component
{
    use WithEstimatedYieldForm, WithToastNotifications, WithViticulturistValidation;

    public $estimatedYield;

    public $estimated_yield_id;

    public function mount($estimatedYield): void
    {
        $this->estimated_yield_id = $estimatedYield;
        $this->loadEstimatedYield();
    }

    public function loadEstimatedYield(): void
    {
        $user = Auth::user();

        $this->estimatedYield = EstimatedYield::whereHas('plotPlanting.plot', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
            ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
            ->findOrFail($this->estimated_yield_id);

        $this->plot_planting_id = $this->estimatedYield->plot_planting_id;
        $this->campaign_id = $this->estimatedYield->campaign_id;
        $this->estimated_yield_per_hectare = $this->estimatedYield->estimated_yield_per_hectare;
        $this->estimated_total_yield = $this->estimatedYield->estimated_total_yield;
        $this->estimation_date = $this->estimatedYield->estimation_date->format('Y-m-d');
        $this->estimation_method = $this->estimatedYield->estimation_method;
        $this->status = $this->estimatedYield->status;
        $this->notes = $this->estimatedYield->notes ?? '';
        $this->estimation_round = $this->estimatedYield->estimation_round ?? 1;
        $this->thumbs_per_vine = $this->estimatedYield->thumbs_per_vine ?? '';
        $this->bunches_per_plant = $this->estimatedYield->bunches_per_plant ?? '';
        $this->bunch_weight_grams = $this->estimatedYield->bunch_weight_grams ?? '';
        $this->total_plants_sampled = $this->estimatedYield->total_plants_sampled ?? '';
        $this->sampling_area_pct = $this->estimatedYield->sampling_area_pct ?? '';
        $this->health_percentage = $this->estimatedYield->health_percentage ?? '';
        $this->health_status = $this->estimatedYield->health_status ?? '';
        $this->other_wineries = (bool) ($this->estimatedYield->other_wineries ?? false);
        $this->potential_alcohol = $this->estimatedYield->potential_alcohol ?? '';
        $this->vintage = $this->estimatedYield->vintage ?? '';
        $this->auto_calculated_yield = $this->estimatedYield->auto_calculated_yield ?? '';
        $this->plot_id = $this->estimatedYield->plotPlanting->plot_id;

        $this->loadData();
    }

    public function updatedCampaignId(): void
    {
        $this->loadPlantings();
    }

    public function updatedPlotPlantingId(): void
    {
        $this->calculateTotalYield();
        $this->calculateFromSampling();
    }

    public function update()
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
            ->where('id', '!=', $this->estimatedYield->id)
            ->first();

        if ($existing) {
            $roundLabel = EstimatedYield::ROUNDS[$this->estimation_round] ?? "Ronda {$this->estimation_round}";
            $this->toastError("Ya existe otra estimación de {$roundLabel} para esta plantación y campaña.");

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

            $this->toastSuccess(__('Rendimiento estimado actualizado exitosamente.'));
            $route = Auth::user()->isProducer()
                ? route('producer.digital-notebook.estimated-yields.index')
                : route('viticulturist.digital-notebook.estimated-yields.index');

            return $this->redirect($route, navigate: true);
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al actualizar el rendimiento estimado. Inténtalo de nuevo.'));
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
