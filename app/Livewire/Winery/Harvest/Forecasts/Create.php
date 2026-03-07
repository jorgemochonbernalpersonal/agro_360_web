<?php

namespace App\Livewire\Winery\Harvest\Forecasts;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $viticulturist_id  = '';
    public string $plot_id           = '';
    public string $plot_planting_id  = '';
    public string $campaign_id       = '';
    public string $vintage_year      = '';
    public string $estimated_kg      = '';
    public string $estimation_date   = '';
    public string $status            = 'draft';
    public string $notes             = '';

    // Dinámico
    public $availablePlots     = [];
    public $availablePlantings = [];

    // Límite PAC para referencia
    public ?float $pacLimit       = null;
    public ?float $pacLimitRaw    = null;
    public ?int   $ageFactor      = null;

    // Aforo del viticultor (EstimatedYield confirmado) para referencia
    public ?float  $viticEstimateKg     = null;
    public ?string $viticEstimateStatus = null; // 'confirmed' | 'draft' | null

    public function mount(): void
    {
        $this->estimation_date = now()->format('Y-m-d');

        // Campaña activa por defecto
        $campaign = Campaign::forViticulturist(Auth::id())->where('active', true)->first();
        if ($campaign) {
            $this->campaign_id   = (string) $campaign->id;
            $this->vintage_year  = (string) $campaign->year;
        }
    }

    public function updatedViticulturistId(): void
    {
        $this->plot_id          = '';
        $this->plot_planting_id = '';
        $this->availablePlantings = [];
        $this->pacLimit         = null;
        $this->viticEstimateKg  = null;
        $this->viticEstimateStatus = null;
        $this->loadPlots();
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id    = '';
        $this->pacLimit            = null;
        $this->viticEstimateKg     = null;
        $this->viticEstimateStatus = null;
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

    protected function loadPlots(): void
    {
        if (!$this->viticulturist_id) {
            $this->availablePlots = [];
            return;
        }

        $this->availablePlots = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->where('active', true)
            ->whereHas('plantings', fn($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function loadPlantings(): void
    {
        if (!$this->plot_id) {
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
        $this->viticEstimateKg     = null;
        $this->viticEstimateStatus = null;

        if (!$this->plot_planting_id || !$this->vintage_year) {
            $this->pacLimit = null;
            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if (!$planting) {
            $this->pacLimit = null;
            return;
        }

        $this->pacLimitRaw = $planting->harvest_limit_kg ? (float) $planting->harvest_limit_kg : null;
        $this->pacLimit    = $planting->effectiveHarvestLimitKg((int) $this->vintage_year);

        if ($this->pacLimitRaw && $this->pacLimit !== null) {
            $this->ageFactor = $this->pacLimitRaw > 0
                ? (int) round($this->pacLimit / $this->pacLimitRaw * 100)
                : 100;
        } else {
            $this->ageFactor = null;
        }

        // Aforo del viticultor para la misma plantación+añada (cualquier estado)
        $viticEstimate = EstimatedYield::where('plot_planting_id', $planting->id)
            ->whereHas('campaign', fn($q) => $q->where('year', (int) $this->vintage_year))
            ->orderByRaw("CASE status WHEN 'confirmed' THEN 0 ELSE 1 END")
            ->first();

        if ($viticEstimate) {
            $this->viticEstimateKg     = (float) $viticEstimate->estimated_total_yield;
            $this->viticEstimateStatus = $viticEstimate->status;
        }
    }

    protected function rules(): array
    {
        return [
            'viticulturist_id' => ['required', 'exists:users,id'],
            'plot_planting_id' => ['required', 'exists:plot_plantings,id'],
            'campaign_id'      => ['required', 'exists:campaigns,id'],
            'vintage_year'     => ['required', 'integer', 'min:2000', 'max:2100'],
            'estimated_kg'     => ['required', 'numeric', 'min:1'],
            'estimation_date'  => ['required', 'date'],
            'status'           => ['required', 'in:draft,confirmed'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'viticulturist_id.required' => 'Selecciona un viticultor.',
            'plot_planting_id.required' => 'Selecciona una plantación.',
            'campaign_id.required'      => 'Selecciona una campaña.',
            'estimated_kg.required'     => 'Introduce los kg estimados.',
            'estimated_kg.min'          => 'Los kg estimados deben ser mayor que 0.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $wineryId = Auth::id();

        // Guard: viticulturist must be linked
        abort_unless(
            WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $this->viticulturist_id)
                ->exists(),
            403
        );

        // Guard: planting must belong to viticulturist
        $planting = PlotPlanting::whereHas('plot', fn($q) =>
            $q->where('viticulturist_id', $this->viticulturist_id)
        )->findOrFail($this->plot_planting_id);

        // No duplicados por winery+planting+campaign
        $exists = WineryYieldForecast::where('winery_id', $wineryId)
            ->where('plot_planting_id', $planting->id)
            ->where('campaign_id', $this->campaign_id)
            ->exists();

        if ($exists) {
            $this->addError('plot_planting_id', 'Ya existe una previsión para esta plantación y campaña. Edita la existente.');
            return;
        }

        WineryYieldForecast::create([
            'winery_id'        => $wineryId,
            'viticulturist_id' => (int) $this->viticulturist_id,
            'plot_planting_id' => $planting->id,
            'campaign_id'      => (int) $this->campaign_id,
            'vintage_year'     => (int) $this->vintage_year,
            'estimated_kg'     => (float) $this->estimated_kg,
            'estimation_date'  => $this->estimation_date,
            'status'           => $this->status,
            'notes'            => $this->notes ?: null,
        ]);

        $this->toastSuccess('Previsión de vendimia creada correctamente.');
        redirect()->route('winery.harvest-forecasts.index');
    }

    public function render()
    {
        $wineryId = Auth::id();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        $campaigns = Campaign::forViticulturist($wineryId)->orderBy('year', 'desc')->get();

        return view('livewire.winery.harvest.forecasts.create', [
            'linkedViticulturists' => $linkedViticulturists,
            'campaigns'            => $campaigns,
        ])->layout('layouts.app');
    }
}
