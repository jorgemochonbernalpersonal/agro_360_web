<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithHarvestReceptionFormRules;
use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Winery\Harvest\Reception\Traits\WithHarvestContextSelect;
use App\Livewire\Winery\Harvest\Reception\Traits\WithHarvestLimitPanel;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    use WithHarvestContextSelect,
        WithHarvestLimitPanel,
        WithHarvestReceptionFormRules,
        WithOwnershipRules,
        WithRoleAwareRedirect,
        WithToastNotifications;

    public int $vintage_year = 0;

    public string $harvest_start_date = '';

    public string $harvest_ticket_number = '';

    public string $total_weight = '';

    public string $price_per_kg = '';

    public string $baume_degree = '';

    public string $brix_degree = '';

    public string $acidity_level = '';

    public string $ph_level = '';

    public string $potential_alcohol = '';

    public string $health_status = '';

    public string $sanitary_state_grapes = '';

    public string $sanitary_state_agraces = '';

    public string $sanitary_state_botrytis = '';

    public string $sanitary_state_oidium = '';

    public string $sanitary_state_mildew = '';

    public string $transport_document_number = '';

    public string $destination_rega_code = '';

    public string $vehicle_plate = '';

    public string $harvest_time = '';

    public string $container_id = '';

    public bool $disqualified = false;

    public string $disqualified_reason = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->harvest_start_date = now()->toDateString();
        $this->vintage_year = now()->year;

        if (Auth::user()->isProducer() && ! request()->query('viticulturist_id')) {
            $this->viticulturist_id = (string) Auth::id();
            $this->updatedViticulturistId();
        }

        if ($preVitic = request()->query('viticulturist_id')) {
            $this->viticulturist_id = $preVitic;
            $this->updatedViticulturistId();

            if ($prePlot = request()->query('plot_id')) {
                $this->plot_id = $prePlot;
                $this->updatedPlotId();
            }
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

    public function save(): mixed
    {
        $this->validate();

        $wineryId = Auth::id();

        $isSelfReception = Auth::user()->isProducer() && (int) $this->viticulturist_id === $wineryId;
        abort_unless(
            $isSelfReception || WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $this->viticulturist_id)
                ->exists(),
            403,
            'El viticultor no está vinculado a esta bodega.'
        );

        $plot = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->findOrFail($this->plot_id);

        $planting = PlotPlanting::where('plot_id', $plot->id)
            ->findOrFail($this->plot_planting_id);

        $container = Container::where('user_id', $wineryId)->findOrFail((int) $this->container_id);
        $containerId = $container->id;

        $weight = (float) $this->total_weight;

        if (! $container->hasAvailableCapacity($weight)) {
            $this->addError('container_id',
                __('El contenedor «:name» no tiene capacidad suficiente. Disponible: :available kg.', ['name' => $container->name, 'available' => number_format((float) $container->getAvailableCapacity(), 0)])
            );

            return null;
        }

        $campaign = Campaign::getOrCreateActiveForYear($wineryId, $this->vintage_year);
        abort_if(! $campaign, 500, 'No se pudo obtener la campaña.');

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

                $batch->increment('total_weight_kg', $weight);

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

    public function render()
    {
        $wineryId = Auth::id();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->whereHas('viticulturist.plots', fn ($q) => $q->where('active', true)
                ->whereHas('plantings', fn ($p) => $p->where('status', 'active'))
            )
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        if (Auth::user()->isProducer()) {
            $hasPlotsWithPlantings = Plot::where('viticulturist_id', $wineryId)
                ->where('active', true)
                ->whereHas('plantings', fn ($p) => $p->where('status', 'active'))
                ->exists();
            if ($hasPlotsWithPlantings) {
                $linkedViticulturists = collect([Auth::user()])->merge($linkedViticulturists);
            }
        }

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

    protected function rules(): array
    {
        $isSelfReception = Auth::user()->isProducer() && (int) $this->viticulturist_id === Auth::id();

        return array_merge($this->receptionBaseRules(), [
            'viticulturist_id' => $isSelfReception ? ['required'] : $this->linkedViticulturistRule(),
            'plot_id' => $isSelfReception ? $this->plotOwnershipRule() : $this->linkedPlotRule(),
            'plot_planting_id' => $isSelfReception ? $this->plotPlantingOwnershipRule(true) : $this->linkedPlotPlantingRule(true),
            'vintage_year' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'disqualified_reason' => ['required_if:disqualified,true', 'nullable', 'string', 'max:500'],
        ]);
    }

    protected function messages(): array
    {
        return array_merge($this->receptionBaseMessages(), [
            'viticulturist_id.required' => __('Selecciona un viticultor.'),
            'vintage_year.required' => __('La añada es obligatoria.'),
            'plot_id.required' => __('Selecciona una parcela.'),
            'plot_planting_id.required' => __('Selecciona una plantación.'),
        ]);
    }
}
