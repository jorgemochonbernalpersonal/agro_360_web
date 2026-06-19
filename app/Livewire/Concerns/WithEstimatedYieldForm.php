<?php

namespace App\Livewire\Concerns;

use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;

trait WithEstimatedYieldForm
{
    public $plot_planting_id = '';

    public $campaign_id = '';

    public $estimated_yield_per_hectare = '';

    public $estimated_total_yield = '';

    public $estimation_date = '';

    public $estimation_method = 'visual';

    public $status = 'draft';

    public $notes = '';

    public $estimation_round = 1;

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

    public $auto_calculated_yield = '';

    public $plot_id = '';

    public $availablePlantings = [];

    public $availableCampaigns = [];

    public $availablePlots = [];

    public function loadData(): void
    {
        $user = Auth::user();

        $this->availableCampaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        $this->loadPlantings();
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id = '';
        $this->loadPlantings();
    }

    public function updatedEstimatedYieldPerHectare(): void
    {
        $this->calculateTotalYield();
    }

    public function updatedBunchesPerPlant(): void
    {
        $this->calculateFromSampling();
    }

    public function updatedBunchWeightGrams(): void
    {
        $this->calculateFromSampling();
    }

    public function updatedHealthPercentage(): void
    {
        $this->calculateFromSampling();
    }

    protected function calculateTotalYield(): void
    {
        if (! $this->estimated_yield_per_hectare || ! $this->plot_planting_id) {
            $this->estimated_total_yield = '';

            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if ($planting && $planting->area_planted > 0) {
            $this->estimated_total_yield = round($this->estimated_yield_per_hectare * $planting->area_planted, 3);
        }
    }

    protected function calculateFromSampling(): void
    {
        if (! $this->bunches_per_plant || ! $this->bunch_weight_grams || ! $this->plot_planting_id) {
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

    protected function loadPlantings(): void
    {
        $user = Auth::user();

        $query = PlotPlanting::whereHas('plot', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id)
                ->where('active', true);
        })
            ->where('status', 'active')
            ->with(['plot', 'grapeVariety']);

        if ($this->plot_id) {
            $query->where('plot_id', $this->plot_id);
        }

        $this->availablePlantings = $query->orderBy('plot_id')->get();

        if ($this->campaign_id) {
            $campaignId = $this->campaign_id;
            $this->availablePlots = Plot::where('active', true)
                ->where(function ($q) use ($user, $campaignId) {
                    $q->whereHas('agriculturalActivities', function ($aq) use ($user, $campaignId) {
                        $aq->where('campaign_id', $campaignId)
                            ->where('viticulturist_id', $user->id);
                    })
                        ->orWhere('viticulturist_id', $user->id);
                })
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
            'plot_planting_id' => $this->plotPlantingOwnershipRule(required: true),
            'campaign_id' => $this->campaignOwnershipRule(),
            'estimation_round' => 'required|integer|min:1|max:4',
            'estimated_yield_per_hectare' => 'required|numeric|min:0.01',
            'estimated_total_yield' => 'required|numeric|min:0.01',
            'estimation_date' => 'required|date',
            'estimation_method' => 'required|in:visual,sampling,historical,satellite,other',
            'status' => 'required|in:draft,confirmed,archived',
            'notes' => 'nullable|string',
            'thumbs_per_vine' => 'nullable|integer|min:0|max:100',
            'bunches_per_plant' => 'nullable|numeric|min:0|max:200',
            'bunch_weight_grams' => 'nullable|numeric|min:0|max:2000',
            'total_plants_sampled' => 'nullable|integer|min:1',
            'sampling_area_pct' => 'nullable|numeric|min:0|max:100',
            'health_percentage' => 'nullable|numeric|min:0|max:100',
            'health_status' => 'nullable|string|in:excellent,good,botrytis_light,botrytis_moderate,oidium_light,oidium_moderate,mixed,poor',
            'other_wineries' => 'boolean',
            'potential_alcohol' => 'nullable|numeric|min:0|max:25',
            'vintage' => 'nullable|integer|min:1900|max:2100',
        ];
    }
}
