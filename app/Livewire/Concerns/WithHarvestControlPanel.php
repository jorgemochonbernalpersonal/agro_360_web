<?php

namespace App\Livewire\Concerns;

use App\Models\GrapeReceptionBatch;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait WithHarvestControlPanel
{
    // Plantaciones y contenedores disponibles
    public $availablePlantings = [];

    public $availableContainers = [];

    // Plazos de seguridad (withdrawal periods)
    public bool $hasActiveWithdrawal = false;

    public array $activeWithdrawalTreatments = [];

    public bool $withdrawalAcknowledged = false;

    public string $withdrawalReason = '';

    // Panel de control de límite y rendimiento
    public $selectedPlanting = null;

    public $estimatedYield = null;

    public float $totalHarvestedInCampaign = 0;

    public $harvestLimitInfo = null;

    public $yieldVarianceInfo = null;

    public float $wineryReceivedKg = 0;

    public function updatedTotalWeight(): void
    {
        $this->calculateYield();
        $this->calculateTotalValue();
        $this->updateControlPanelData();
    }

    public function updatedPlotPlantingId(): void
    {
        $this->calculateYield();
        $this->loadControlPanelData();
    }

    public function updatedPricePerKg(): void
    {
        $this->calculateTotalValue();
    }

    protected function checkWithdrawalPeriods(): void
    {
        if (! $this->plot_id) {
            $this->resetWithdrawalWarning();

            return;
        }

        $plot = Plot::find($this->plot_id);
        if (! $plot) {
            $this->resetWithdrawalWarning();

            return;
        }

        $activeWithdrawals = $plot->activeWithdrawalPeriods();

        if ($activeWithdrawals->count() > 0) {
            $this->hasActiveWithdrawal = true;
            $this->activeWithdrawalTreatments = $activeWithdrawals->map(function ($activity) {
                $treatment = $activity->phytosanitaryTreatment;
                $product = $treatment->product;
                $withdrawalDays = $product->withdrawal_period_days;
                $safeDate = $activity->activity_date->copy()->addDays($withdrawalDays);

                return [
                    'product_name' => $product->name,
                    'application_date' => $activity->activity_date->format('d/m/Y'),
                    'withdrawal_days' => $withdrawalDays,
                    'safe_date' => $safeDate->format('d/m/Y'),
                    'days_remaining' => now()->diffInDays($safeDate, false),
                ];
            })->toArray();
        } else {
            $this->resetWithdrawalWarning();
        }
    }

    protected function resetWithdrawalWarning(): void
    {
        $this->hasActiveWithdrawal = false;
        $this->activeWithdrawalTreatments = [];
        $this->withdrawalAcknowledged = false;
        $this->withdrawalReason = '';
    }

    protected function calculateYield(): void
    {
        if (! $this->total_weight || ! $this->plot_planting_id) {
            $this->yield_per_hectare = '';

            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if ($planting && $planting->area_planted > 0) {
            $this->yield_per_hectare = round($this->total_weight / $planting->area_planted, 3);
        }
    }

    protected function calculateTotalValue(): void
    {
        if (! $this->total_weight || ! $this->price_per_kg) {
            $this->total_value = '';

            return;
        }

        $this->total_value = round($this->total_weight * $this->price_per_kg, 3);
    }

    protected function loadControlPanelData(): void
    {
        if (! $this->plot_planting_id || ! $this->campaign_id) {
            $this->selectedPlanting = null;
            $this->estimatedYield = null;
            $this->harvestLimitInfo = null;
            $this->yieldVarianceInfo = null;
            $this->totalHarvestedInCampaign = 0;
            $this->wineryReceivedKg = 0;

            return;
        }

        $this->selectedPlanting = PlotPlanting::with(['grapeVariety', 'plot'])->find($this->plot_planting_id);
        if (! $this->selectedPlanting) {
            return;
        }

        $this->estimatedYield = $this->selectedPlanting->getEstimatedYieldForCampaign($this->campaign_id);

        // Año de vendimia: preferir harvest_start_date, sino campaña
        $vintage = $this->harvest_start_date
            ? (int) Carbon::parse($this->harvest_start_date)->year
            : (\App\Models\Campaign::find($this->campaign_id)->year ?? now()->year);

        $this->totalHarvestedInCampaign = (float) $this->selectedPlanting->getTotalViticulturistYieldForVintage($vintage, Auth::id());

        $this->wineryReceivedKg = (float) GrapeReceptionBatch::where('viticulturist_id', Auth::id())
            ->where('plot_planting_id', $this->selectedPlanting->id)
            ->where('vintage_year', $vintage)
            ->sum('total_weight_kg');

        // In Edit: subtract the current harvest weight to avoid double-counting the record being edited
        if (property_exists($this, 'harvest') && $this->harvest && $this->harvest->id) {
            $this->totalHarvestedInCampaign = max(0, $this->totalHarvestedInCampaign - (float) $this->harvest->total_weight);
        }

        $effectiveLimit = $this->selectedPlanting->effectiveHarvestLimitKg($vintage);
        if ($effectiveLimit !== null) {
            $rawLimit = (float) $this->selectedPlanting->harvest_limit_kg;
            $this->harvestLimitInfo = [
                'limit' => $effectiveLimit,
                'raw_limit' => $rawLimit,
                'age_factor' => $rawLimit > 0 ? round($effectiveLimit / $rawLimit * 100) : 100,
                'harvested' => $this->totalHarvestedInCampaign,
                'remaining' => max(0, $effectiveLimit - $this->totalHarvestedInCampaign),
                'percentage' => $effectiveLimit > 0
                    ? round(($this->totalHarvestedInCampaign / $effectiveLimit) * 100, 1)
                    : 0,
            ];
        } else {
            $this->harvestLimitInfo = null;
        }

        $this->updateControlPanelData();
    }

    protected function updateControlPanelData(): void
    {
        if (! $this->selectedPlanting || ! $this->campaign_id) {
            return;
        }

        $newWeight = (float) ($this->total_weight ?: 0);

        if ($this->harvestLimitInfo) {
            $limit = $this->harvestLimitInfo['limit'];
            $newTotal = $this->totalHarvestedInCampaign + $newWeight;
            $this->harvestLimitInfo['new_total'] = $newTotal;
            $this->harvestLimitInfo['new_remaining'] = max(0, round($limit - $newTotal, 3));
            $this->harvestLimitInfo['new_percentage'] = $limit > 0 ? round(($newTotal / $limit) * 100, 1) : null;
            $this->harvestLimitInfo['exceeds'] = $newTotal > $limit;
        }

        $this->yieldVarianceInfo = $this->estimatedYield
            ? $this->selectedPlanting->getYieldVariance($this->campaign_id, $newWeight)
            : null;
    }
}
