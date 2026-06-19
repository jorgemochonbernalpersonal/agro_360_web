<?php

namespace App\Livewire\Winery\Harvest\Reception\Traits;

use App\Models\PlotPlanting;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;

trait WithHarvestLimitPanel
{
    public ?PlotPlanting $selectedPlanting = null;

    public float $totalReceivedInCampaign = 0;

    public ?array $harvestLimitInfo = null;

    public function updatedHarvestStartDate(): void
    {
        if ($this->selectedPlanting) {
            $this->totalReceivedInCampaign = $this->selectedPlanting
                ->getTotalWineryReceptionsForVintage($this->harvestVintage(), Auth::id());
            $this->updateLimitInfo();
        }
    }

    public function updatedVintageYear(): void
    {
        if ($this->selectedPlanting) {
            $this->totalReceivedInCampaign = $this->selectedPlanting
                ->getTotalWineryReceptionsForVintage($this->harvestVintage(), Auth::id());
            $this->updateLimitInfo();
        }
    }

    protected function loadPlantingState(): void
    {
        if (! $this->plot_planting_id) {
            $this->resetPlantingState();

            return;
        }

        $this->selectedPlanting = PlotPlanting::with('grapeVariety')->find($this->plot_planting_id);
        if (! $this->selectedPlanting) {
            return;
        }

        $vintage = $this->harvestVintage();
        $this->totalReceivedInCampaign = $this->selectedPlanting
            ->getTotalWineryReceptionsForVintage($vintage, Auth::id());

        $this->updateLimitInfo();
    }

    protected function updateLimitInfo(): void
    {
        if (! $this->selectedPlanting) {
            $this->harvestLimitInfo = null;

            return;
        }

        $vintage = $this->harvestVintage();
        $effectiveLimit = $this->selectedPlanting->effectiveHarvestLimitKg($vintage);

        if ($effectiveLimit === null) {
            $this->harvestLimitInfo = null;

            return;
        }

        $received = $this->totalReceivedInCampaign;
        $adding = (float) ($this->total_weight ?: 0);
        $newTotal = $received + $adding;
        $rawLimit = (float) $this->selectedPlanting->harvest_limit_kg;

        $forecast = WineryYieldForecast::where('winery_id', Auth::id())
            ->where('plot_planting_id', $this->selectedPlanting->id)
            ->where('vintage_year', $vintage)
            ->where('status', 'confirmed')
            ->first();

        $forecastKg = $forecast ? (float) $forecast->estimated_kg : null;
        $opLimit = $forecastKg !== null ? min($forecastKg, $effectiveLimit) : $effectiveLimit;

        $this->harvestLimitInfo = [
            'limit' => $opLimit,
            'pac_limit' => $effectiveLimit,
            'raw_limit' => $rawLimit,
            'age_factor' => $rawLimit > 0 ? round($effectiveLimit / $rawLimit * 100) : 100,
            'forecast_kg' => $forecastKg,
            'has_forecast' => $forecastKg !== null,
            'received' => $received,
            'adding' => $adding,
            'new_total' => $newTotal,
            'remaining' => max(0, $opLimit - $newTotal),
            'percentage' => $opLimit > 0 ? round(($newTotal / $opLimit) * 100, 1) : 0,
            'exceeds' => $newTotal > $opLimit,
            'exceeds_pac' => $newTotal > $effectiveLimit,
        ];
    }

    protected function harvestVintage(): int
    {
        return $this->vintage_year ?: now()->year;
    }

    protected function resetPlantingState(): void
    {
        $this->selectedPlanting = null;
        $this->totalReceivedInCampaign = 0;
        $this->harvestLimitInfo = null;
    }
}
