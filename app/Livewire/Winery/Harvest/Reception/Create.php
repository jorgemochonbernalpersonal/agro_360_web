<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    use WithOwnershipRules, WithRoleAwareRedirect, WithToastNotifications;

    // ── Context ─────────────────────────────────────────────────────────────
    public string $viticulturist_id = '';

    public string $plot_id = '';

    public string $plot_planting_id = '';

    // ── Datos de recepción ──────────────────────────────────────────────────
    public int $vintage_year = 0;

    public string $harvest_start_date = '';

    public string $harvest_ticket_number = '';

    public string $total_weight = '';

    public string $price_per_kg = '';

    // ── Calidad ─────────────────────────────────────────────────────────────
    public string $baume_degree = '';

    public string $brix_degree = '';

    public string $acidity_level = '';

    public string $ph_level = '';

    public string $potential_alcohol = '';

    // ── Estado sanitario ────────────────────────────────────────────────────
    public string $health_status = '';

    public string $sanitary_state_grapes = '';

    public string $sanitary_state_agraces = '';

    public string $sanitary_state_botrytis = '';

    public string $sanitary_state_oidium = '';

    public string $sanitary_state_mildew = '';

    // ── Transporte / trazabilidad ────────────────────────────────────────────
    public string $transport_document_number = '';

    public string $destination_rega_code = '';

    public string $vehicle_plate = '';

    public string $harvest_time = '';

    // ── Contenedor bodega ───────────────────────────────────────────────────
    public string $container_id = '';

    // ── Descarte ────────────────────────────────────────────────────────────
    public bool $disqualified = false;

    public string $disqualified_reason = '';

    public string $notes = '';

    // ── Estado reactivo (panel de control de límites) ───────────────────────
    public ?PlotPlanting $selectedPlanting = null;

    public float $totalReceivedInCampaign = 0;   // solo recepciones de esta bodega

    public ?array $harvestLimitInfo = null;

    /** @var array<int, mixed> */
    public array $availablePlots = [];

    /** @var array<int, mixed> */
    public array $availablePlantings = [];

    public function mount(): void
    {
        $this->harvest_start_date = now()->toDateString();
        $this->vintage_year = now()->year;

        // Producer: pre-select themselves as the viticulturist
        if (Auth::user()->isProducer() && ! request()->query('viticulturist_id')) {
            $this->viticulturist_id = (string) Auth::id();
            $this->updatedViticulturistId();
        }

        // Pre-populate from previous reception (passed via query params)
        if ($preVitic = request()->query('viticulturist_id')) {
            $this->viticulturist_id = $preVitic;
            $this->updatedViticulturistId();

            if ($prePlot = request()->query('plot_id')) {
                $this->plot_id = $prePlot;
                $this->updatedPlotId();
            }
        }
    }

    // ── Watchers ─────────────────────────────────────────────────────────────

    public function updatedViticulturistId(): void
    {
        $this->plot_id = '';
        $this->plot_planting_id = '';
        $this->availablePlots = [];
        $this->availablePlantings = [];
        $this->resetPlantingState();

        if (! $this->viticulturist_id) {
            return;
        }

        $wineryId = Auth::id();
        $isSelf = Auth::user()->isProducer() && (int) $this->viticulturist_id === $wineryId;
        $isLinked = $isSelf || WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $this->viticulturist_id)
            ->exists();

        if (! $isLinked) {
            $this->viticulturist_id = '';

            return;
        }

        $this->availablePlots = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->where('active', true)
            ->whereHas('plantings', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id = '';
        $this->availablePlantings = [];
        $this->resetPlantingState();

        if (! $this->plot_id) {
            return;
        }

        $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
            ->where('status', 'active')
            ->with('grapeVariety:id,name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'label' => ($p->grapeVariety?->name ?? $p->name ?? 'Sin variedad')
                    .($p->area_planted ? ' — '.number_format($p->area_planted, 2).' ha' : ''),
                'area' => $p->area_planted,
                'limit_kg' => $p->harvest_limit_kg,
                'planting_year' => $p->planting_year,
                'do' => $p->designation_of_origin,
            ])
            ->toArray();

        if (count($this->availablePlantings) === 1) {
            $this->plot_planting_id = (string) $this->availablePlantings[0]['id'];
            $this->loadPlantingState();
        }
    }

    public function updatedPlotPlantingId(): void
    {
        $this->loadPlantingState();
    }

    public function updatedTotalWeight(): void
    {
        $this->updateLimitInfo();
    }

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

    // ── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();

        $wineryId = Auth::id();

        // Guard: viticulturist must be linked to this winery
        // Exception: producer receiving their own grapes
        $isSelfReception = Auth::user()->isProducer() && (int) $this->viticulturist_id === $wineryId;
        abort_unless(
            $isSelfReception || WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $this->viticulturist_id)
                ->exists(),
            403,
            'El viticultor no está vinculado a esta bodega.'
        );

        // Guard: plot must belong to the viticulturist
        $plot = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->findOrFail($this->plot_id);

        // Guard: planting must belong to the plot
        $planting = PlotPlanting::where('plot_id', $plot->id)
            ->findOrFail($this->plot_planting_id);

        // Guard: container must belong to this winery
        $container = Container::where('user_id', $wineryId)->findOrFail((int) $this->container_id);
        $containerId = $container->id;

        $weight = (float) $this->total_weight;

        // Pre-check rápido (la validación real con lock se hace dentro del transaction)
        if (! $container->hasAvailableCapacity($weight)) {
            $this->addError('container_id',
                __('El contenedor «:name» no tiene capacidad suficiente. Disponible: :available kg.', ['name' => $container->name, 'available' => number_format($container->getAvailableCapacity(), 0)])
            );

            return null;
        }

        // Auto-get/create campaign for vintage year
        $campaign = Campaign::getOrCreateActiveForYear($wineryId, $this->vintage_year);
        abort_if(! $campaign, 500, 'No se pudo obtener la campaña.');

        // Guard: batch cerrado
        $existingBatch = GrapeReceptionBatch::where('winery_id', $wineryId)
            ->where('plot_planting_id', $planting->id)
            ->where('campaign_id', $campaign->id)
            ->first();
        if ($existingBatch && $existingBatch->status === 'closed') {
            $this->toastError(__('El lote de esta plantación está cerrado. Réabrelo desde el Cuadro de Mando antes de añadir más recepciones.'));

            return null;
        }

        $pricePerKg = $this->price_per_kg ? (float) $this->price_per_kg : null;
        $totalValue = ($weight && $pricePerKg) ? round($weight * $pricePerKg, 3) : null;

        try {
            DB::transaction(function () use ($wineryId, $campaign, $planting, $containerId, $weight, $pricePerKg, $totalValue) {

                // 1. Obtener o crear el batch acumulador (una sola entrada por bodega+plantación+campaña)
                $batch = GrapeReceptionBatch::firstOrCreate(
                    [
                        'winery_id' => $wineryId,
                        'plot_planting_id' => $planting->id,
                        'campaign_id' => $campaign->id,
                    ],
                    [
                        'viticulturist_id' => (int) $this->viticulturist_id,
                        'vintage_year' => $this->vintage_year,
                        'total_weight_kg' => 0,
                        'designation_of_origin' => $planting->designation_of_origin,
                        'status' => 'open',
                    ]
                );

                // 2. Registrar la carga individual (sin activity_id)
                Harvest::create([
                    'winery_id' => $wineryId,
                    'batch_id' => $batch->id,
                    'plot_planting_id' => $planting->id,
                    'container_id' => $containerId,
                    'harvest_start_date' => $this->harvest_start_date,
                    'harvest_time' => $this->harvest_time ?: null,
                    'vintage' => $this->vintage_year,
                    'total_weight' => $weight,
                    'baume_degree' => $this->baume_degree ?: null,
                    'brix_degree' => $this->brix_degree ?: null,
                    'acidity_level' => $this->acidity_level ?: null,
                    'ph_level' => $this->ph_level ?: null,
                    'potential_alcohol' => $this->potential_alcohol ?: null,
                    'health_status' => $this->health_status ?: null,
                    'harvest_ticket_number' => $this->harvest_ticket_number ?: null,
                    'sanitary_state_grapes' => $this->sanitary_state_grapes ?: null,
                    'sanitary_state_agraces' => $this->sanitary_state_agraces ?: null,
                    'sanitary_state_botrytis' => $this->sanitary_state_botrytis ?: null,
                    'sanitary_state_oidium' => $this->sanitary_state_oidium ?: null,
                    'sanitary_state_mildew' => $this->sanitary_state_mildew ?: null,
                    'transport_document_number' => $this->transport_document_number ?: null,
                    'destination_rega_code' => $this->destination_rega_code ?: null,
                    'vehicle_plate' => $this->vehicle_plate ?: null,
                    'destination_type' => 'winery',
                    'price_per_kg' => $pricePerKg,
                    'total_value' => $totalValue,
                    'status' => 'active',
                    'disqualified' => $this->disqualified,
                    'disqualified_reason' => ($this->disqualified && $this->disqualified_reason)
                        ? $this->disqualified_reason
                        : null,
                    'notes' => $this->notes ?: null,
                ]);
                // El HarvestObserver actualiza container.used_capacity automáticamente.

                // 3. Acumular en el batch
                $batch->increment('total_weight_kg', $weight);

                // 4. Feedback de calidad al viticultor (si tiene datos de calidad y no es el propio usuario)
                $hasQuality = $this->baume_degree || $this->potential_alcohol || $this->acidity_level || $this->ph_level;
                $isExternalViticulturist = $batch->viticulturist_id && $batch->viticulturist_id !== $wineryId;
                if ($hasQuality && $isExternalViticulturist) {
                    $viticulturist = \App\Models\User::find($batch->viticulturist_id);
                    $harvest = \App\Models\Harvest::where('batch_id', $batch->id)->latest()->first();
                    if ($viticulturist?->can_login && $harvest) {
                        $viticulturist->notify(new \App\Notifications\QualityFeedbackNotification(
                            $harvest->load('plotPlanting.grapeVariety', 'plotPlanting.plot'),
                            Auth::user()->name,
                        ));
                    }
                }
            });

            $this->toastSuccess(__('Recepción registrada correctamente.'));

            return $this->roleRedirect('grape-reception.index');

        } catch (\Exception $e) {
            \Log::error('Error al registrar recepción de uva', [
                'error' => $e->getMessage(),
                'winery' => Auth::id(),
                'planting' => $this->plot_planting_id,
            ]);
            $this->toastError(__('Error al guardar la recepción. Inténtalo de nuevo.'));
        }

        return null;
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $wineryId = Auth::id();

        // Solo viticultores que tienen parcelas activas con plantaciones activas
        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->whereHas('viticulturist.plots', fn ($q) => $q->where('active', true)
                ->whereHas('plantings', fn ($p) => $p->where('status', 'active'))
            )
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        // Producer: add themselves at the top if they have plots with plantings
        if (Auth::user()->isProducer()) {
            $hasPlotsWithPlantings = Plot::where('viticulturist_id', $wineryId)
                ->where('active', true)
                ->whereHas('plantings', fn ($p) => $p->where('status', 'active'))
                ->exists();
            if ($hasPlotsWithPlantings) {
                $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
            }
        }

        // Solo depósitos de kg con capacidad disponible
        $availableContainers = Container::where('user_id', $wineryId)
            ->where('archived', false)
            ->where('unit', 'kg')
            ->whereRaw('used_capacity < capacity')
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'used_capacity']);

        return view('livewire.winery.harvest.reception.create', [
            'linkedViticulturists' => $linkedViticulturists,
            'availableContainers' => $availableContainers,
        ])->layout('layouts.app');
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

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

        // Forecast confirmado de bodega para esta plantación+añada
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

    // ── Validación ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $isSelfReception = Auth::user()->isProducer() && (int) $this->viticulturist_id === Auth::id();

        return [
            'viticulturist_id' => $isSelfReception ? ['required'] : $this->linkedViticulturistRule(),
            'plot_id' => $isSelfReception ? $this->plotOwnershipRule() : $this->linkedPlotRule(),
            'plot_planting_id' => $isSelfReception ? $this->plotPlantingOwnershipRule(true) : $this->linkedPlotPlantingRule(true),
            'vintage_year' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'harvest_start_date' => ['required', 'date'],
            'harvest_ticket_number' => ['nullable', 'string', 'max:50'],
            'total_weight' => ['required', 'numeric', 'min:0.01'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0'],
            'baume_degree' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'brix_degree' => ['nullable', 'numeric', 'min:0', 'max:40'],
            'acidity_level' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'ph_level' => ['nullable', 'numeric', 'min:0', 'max:14'],
            'potential_alcohol' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'health_status' => ['nullable', 'in:sano,daño_leve,daño_moderado,daño_grave'],
            'sanitary_state_grapes' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_agraces' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_botrytis' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_oidium' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_mildew' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transport_document_number' => ['nullable', 'string', 'max:50'],
            'destination_rega_code' => ['nullable', 'string', 'max:20'],
            'vehicle_plate' => ['nullable', 'string', 'max:20'],
            'harvest_time' => ['nullable', 'date_format:H:i'],
            'container_id' => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())->where('unit', 'kg')],
            'disqualified' => ['boolean'],
            'disqualified_reason' => ['required_if:disqualified,true', 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'viticulturist_id.required' => __('Selecciona un viticultor.'),
            'vintage_year.required' => __('La añada es obligatoria.'),
            'plot_id.required' => __('Selecciona una parcela.'),
            'plot_planting_id.required' => __('Selecciona una plantación.'),
            'harvest_start_date.required' => __('La fecha de recepción es obligatoria.'),
            'total_weight.required' => __('El peso recibido es obligatorio.'),
            'total_weight.min' => __('El peso debe ser mayor que 0.'),
            'container_id.required' => __('Selecciona un depósito de destino.'),
        ];
    }
}
