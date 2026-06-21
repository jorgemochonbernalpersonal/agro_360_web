<?php

namespace App\Livewire\Winery\Harvest\Forecasts;

use App\Livewire\Winery\AbstractCreate;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\GrapeReceptionBatch;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Create extends AbstractCreate
{
    public string $viticulturist_id = '';

    public string $plot_id = '';

    public string $plot_planting_id = '';

    public string $campaign_id = '';

    public string $vintage_year = '';

    public string $estimated_kg = '';

    public string $estimation_date = '';

    public string $status = 'draft';

    public string $notes = '';

    // Dinámico
    public $availablePlots = [];

    public $availablePlantings = [];

    // Límite PAC para referencia
    public ?float $pacLimit = null;

    public ?float $pacLimitRaw = null;

    public ?int $ageFactor = null;

    // Aforo del viticultor para referencia
    public ?float $viticEstimateKg = null;

    public ?string $viticEstimateStatus = null;

    // Kg ya recibidos para esta plantación+campaña
    public ?float $receivedKg = null;

    public function mount(): void
    {
        $this->estimation_date = now()->format('Y-m-d');

        $campaign = Campaign::forViticulturist(Auth::id())->where('active', true)->first();
        if ($campaign) {
            $this->campaign_id = (string) $campaign->id;
            $this->vintage_year = (string) $campaign->year;
        }

        if (Auth::user()->isProducer()) {
            $this->viticulturist_id = (string) Auth::id();
            $this->updatedViticulturistId();
        }
    }

    public function updatedViticulturistId(): void
    {
        $this->plot_id = '';
        $this->plot_planting_id = '';
        $this->availablePlantings = [];
        $this->pacLimit = null;
        $this->viticEstimateKg = null;
        $this->viticEstimateStatus = null;
        $this->receivedKg = null;
        $this->loadPlots();
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id = '';
        $this->pacLimit = null;
        $this->viticEstimateKg = null;
        $this->viticEstimateStatus = null;
        $this->receivedKg = null;
        $this->loadPlantings();
    }

    public function updatedPlotPlantingId(): void
    {
        $this->updatePacLimit();
    }

    public function updatedCampaignId(): void
    {
        $campaign = Campaign::find($this->campaign_id);
        if ($campaign) {
            $this->vintage_year = (string) $campaign->year;
        }
        $this->updatePacLimit();
    }

    protected function performCreate(): void
    {
        $wineryId = Auth::id();

        $isSelfForecast = Auth::user()->isProducer() && (int) $this->viticulturist_id === $wineryId;
        abort_unless(
            $isSelfForecast || WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $this->viticulturist_id)
                ->exists(),
            403
        );

        $planting = PlotPlanting::whereHas('plot', fn ($q) => $q->where('viticulturist_id', $this->viticulturist_id))
            ->findOrFail($this->plot_planting_id);

        $exists = WineryYieldForecast::where('winery_id', $wineryId)
            ->where('plot_planting_id', $planting->id)
            ->where('campaign_id', $this->campaign_id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'plot_planting_id' => __('Ya existe una previsión para esta plantación y campaña. Edita la existente.'),
            ]);
        }

        WineryYieldForecast::create([
            'winery_id' => $wineryId,
            'viticulturist_id' => (int) $this->viticulturist_id,
            'plot_planting_id' => $planting->id,
            'campaign_id' => (int) $this->campaign_id,
            'vintage_year' => (int) $this->vintage_year,
            'estimated_kg' => (float) $this->estimated_kg,
            'estimation_date' => $this->estimation_date,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Previsión de vendimia creada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.harvest-forecasts.index';
    }

    protected function viewData(): array
    {
        $wineryId = Auth::id();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        if (Auth::user()->isProducer()) {
            $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
        }

        return [
            'linkedViticulturists' => $linkedViticulturists,
            'campaigns' => Campaign::forViticulturist($wineryId)->orderBy('year', 'desc')->get(),
        ];
    }

    protected function loadPlots(): void
    {
        if (! $this->viticulturist_id) {
            $this->availablePlots = [];

            return;
        }

        $this->availablePlots = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->where('active', true)
            ->whereHas('plantings', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function loadPlantings(): void
    {
        if (! $this->plot_id) {
            $this->availablePlantings = [];

            return;
        }

        $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
            ->where('status', 'active')
            ->with('grapeVariety:id,name')
            ->orderBy('name')
            ->get();
    }

    protected function updatePacLimit(): void
    {
        $this->viticEstimateKg = null;
        $this->viticEstimateStatus = null;
        $this->receivedKg = null;

        if (! $this->plot_planting_id || ! $this->vintage_year) {
            $this->pacLimit = null;

            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if (! $planting) {
            $this->pacLimit = null;

            return;
        }

        $this->pacLimitRaw = $planting->harvest_limit_kg ? (float) $planting->harvest_limit_kg : null;
        $this->pacLimit = $planting->effectiveHarvestLimitKg((int) $this->vintage_year);

        if ($this->pacLimitRaw && $this->pacLimit !== null) {
            $this->ageFactor = $this->pacLimitRaw > 0
                ? (int) round($this->pacLimit / $this->pacLimitRaw * 100)
                : 100;
        } else {
            $this->ageFactor = null;
        }

        $viticEstimate = EstimatedYield::where('plot_planting_id', $planting->id)
            ->whereHas('campaign', fn ($q) => $q->where('year', (int) $this->vintage_year))
            ->orderByRaw("CASE status WHEN 'confirmed' THEN 0 ELSE 1 END")
            ->first();

        if ($viticEstimate) {
            $this->viticEstimateKg = (float) $viticEstimate->estimated_total_yield;
            $this->viticEstimateStatus = $viticEstimate->status;
        }

        if ($this->campaign_id) {
            $batch = GrapeReceptionBatch::where('winery_id', Auth::id())
                ->where('plot_planting_id', $this->plot_planting_id)
                ->where('campaign_id', $this->campaign_id)
                ->first();
            $this->receivedKg = $batch ? (float) $batch->total_weight_kg : 0;
        }
    }

    protected function rules(): array
    {
        return [
            'viticulturist_id' => $this->linkedViticulturistRule(),
            'plot_planting_id' => $this->linkedPlotPlantingRule(true),
            'campaign_id' => $this->linkedCampaignRule(),
            'vintage_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'estimated_kg' => ['required', 'numeric', 'min:1'],
            'estimation_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,confirmed'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'viticulturist_id.required' => __('Selecciona un viticultor.'),
            'plot_planting_id.required' => __('Selecciona una plantación.'),
            'campaign_id.required' => __('Selecciona una campaña.'),
            'estimated_kg.required' => __('Introduce los kg estimados.'),
            'estimated_kg.min' => __('Los kg estimados deben ser mayor que 0.'),
        ];
    }
}
