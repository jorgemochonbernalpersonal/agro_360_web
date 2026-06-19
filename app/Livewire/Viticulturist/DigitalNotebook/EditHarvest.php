<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Livewire\Concerns\WithHarvestControlPanel;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Harvest;
use App\Models\Machinery;
use App\Models\PhenologyObservation;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * @property-read mixed $viticulturists
 */
class EditHarvest extends Component
{
    use WithHarvestControlPanel, WithRoleAwareRedirect, WithToastNotifications, WithUserFilters, WithViticulturistValidation;

    public $harvest;

    public $harvest_id;

    public $plot_id = '';

    public $plot_planting_id = '';

    public $container_id = '';

    public $original_container_id = null;

    public $activity_date = '';

    public $harvest_start_date = '';

    public $harvest_end_date = '';

    public $total_weight = '';

    public $yield_per_hectare = '';

    public $baume_degree = '';

    public $brix_degree = '';

    public $acidity_level = '';

    public $ph_level = '';

    public $potential_alcohol = '';

    public $color_rating = '';

    public $aroma_rating = '';

    public $health_status = '';

    public $destination_type = '';

    public $destination = '';

    public $transport_document_number = '';

    public $destination_rega_code = '';

    public $vehicle_plate = '';

    public $buyer_name = '';

    public $harvest_ticket_number = '';

    public $sanitary_state_grapes = '';

    public $sanitary_state_agraces = '';

    public $sanitary_state_botrytis = '';

    public $sanitary_state_oidium = '';

    public $sanitary_state_mildew = '';

    public $price_per_kg = '';

    public $total_value = '';

    public $workType = '';

    public $crew_id = '';

    public $crew_member_id = '';

    public $machinery_id = '';

    public $weather_conditions = '';

    public $temperature = '';

    public $notes = '';

    public $edit_notes = '';

    public $campaign_id = '';

    public function mount($harvest): void
    {
        $this->harvest_id = $harvest;
        $this->loadHarvest();
    }

    public function loadHarvest(): void
    {
        $user = Auth::user();

        $this->harvest = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
            ->with(['activity.plot', 'activity.campaign', 'plotPlanting.grapeVariety'])
            ->findOrFail($this->harvest_id);

        $activity = $this->harvest->activity;

        if (! $user->can('update', $activity)) {
            abort(403, __('No tienes permiso para editar esta actividad.'));
        }

        if ($activity->isLocked()) {
            $this->toastError(__('No se puede editar una actividad bloqueada. Las actividades se bloquean automáticamente después de :days días para cumplimiento PAC.', ['days' => config('activities.lock_days', 7)]));
            $this->viticulturistRoleRedirect('harvests.index');

            return;
        }

        $this->plot_id = $activity->plot_id;
        $this->plot_planting_id = $this->harvest->plot_planting_id;
        $this->activity_date = $activity->activity_date->format('Y-m-d');
        $this->campaign_id = $activity->campaign_id;
        $this->crew_id = $activity->crew_id ?? '';
        $this->crew_member_id = $activity->crew_member_id ?? '';
        $this->machinery_id = $activity->machinery_id ?? '';
        $this->weather_conditions = $activity->weather_conditions ?? '';
        $this->temperature = $activity->temperature ?? '';
        $this->notes = $activity->notes ?? '';

        if ($activity->crew_id) {
            $this->workType = 'crew';
        } elseif ($activity->crew_member_id) {
            $this->workType = 'individual';
        }

        $this->container_id = $this->harvest->container_id ?? '';
        $this->original_container_id = $this->harvest->container_id;
        $this->harvest_start_date = $this->harvest->harvest_start_date->format('Y-m-d');
        $this->harvest_end_date = $this->harvest->harvest_end_date ? $this->harvest->harvest_end_date->format('Y-m-d') : '';
        $this->total_weight = $this->harvest->total_weight;
        $this->yield_per_hectare = $this->harvest->yield_per_hectare;

        $this->loadAvailableContainers();

        $this->baume_degree = $this->harvest->baume_degree ?? '';
        $this->brix_degree = $this->harvest->brix_degree ?? '';
        $this->acidity_level = $this->harvest->acidity_level ?? '';
        $this->ph_level = $this->harvest->ph_level ?? '';
        $this->potential_alcohol = $this->harvest->potential_alcohol ?? '';
        $this->color_rating = $this->harvest->color_rating ?? '';
        $this->aroma_rating = $this->harvest->aroma_rating ?? '';
        $this->health_status = $this->harvest->health_status ?? '';
        $this->destination_type = $this->harvest->destination_type ?? '';
        $this->destination = $this->harvest->destination ?? '';
        $this->transport_document_number = $this->harvest->transport_document_number ?? '';
        $this->destination_rega_code = $this->harvest->destination_rega_code ?? '';
        $this->vehicle_plate = $this->harvest->vehicle_plate ?? '';
        $this->buyer_name = $this->harvest->buyer_name ?? '';
        $this->price_per_kg = $this->harvest->price_per_kg ?? '';
        $this->total_value = $this->harvest->total_value ?? '';
        $this->harvest_ticket_number = $this->harvest->harvest_ticket_number ?? '';
        $this->sanitary_state_grapes = $this->harvest->sanitary_state_grapes ?? '';
        $this->sanitary_state_agraces = $this->harvest->sanitary_state_agraces ?? '';
        $this->sanitary_state_botrytis = $this->harvest->sanitary_state_botrytis ?? '';
        $this->sanitary_state_oidium = $this->harvest->sanitary_state_oidium ?? '';
        $this->sanitary_state_mildew = $this->harvest->sanitary_state_mildew ?? '';
        $this->edit_notes = $this->harvest->edit_notes ?? '';

        $this->updatedPlotId($this->plot_id);
        $this->loadControlPanelData();
    }

    public function updatedContainerId($value): void
    {
        if ($value && $value != $this->original_container_id) {
            $container = Container::where('user_id', auth()->id())->find($value);
            if ($container && $container->hasAvailableCapacity($this->total_weight ?? 0)) {
                $this->calculateYield();
                $this->calculateTotalValue();
                $this->updateControlPanelData();
            }
        }
    }

    public function updatedPlotId($value): void
    {
        if (! $value) {
            $this->availablePlantings = [];
            $this->plot_planting_id = '';
            $this->resetWithdrawalWarning();

            return;
        }

        $this->availablePlantings = PlotPlanting::where('plot_id', $value)
            ->where('status', 'active')
            ->with('grapeVariety')
            ->get();

        if ($this->availablePlantings->count() === 1) {
            $this->plot_planting_id = $this->availablePlantings->first()->id;
            $this->loadControlPanelData();
        } else {
            $this->selectedPlanting = null;
            $this->harvestLimitInfo = null;
            $this->yieldVarianceInfo = null;
        }

        $this->checkWithdrawalPeriods();
        $this->calculateYield();
    }

    public function update()
    {
        $this->validate();

        $user = Auth::user();

        if (! $this->workType) {
            $this->addError('workType', __('Debes seleccionar quién realizó el trabajo.'));

            return;
        }

        if ($this->workType === 'crew' && ! $this->crew_id) {
            $this->addError('crew_id', __('Debes seleccionar un equipo.'));

            return;
        }

        if ($this->workType === 'individual' && ! $this->crew_member_id) {
            $this->addError('crew_member_id', __('Debes seleccionar un viticultor.'));

            return;
        }

        $this->authorizeCreateActivityForPlot($this->plot_id);

        $container = null;
        if ($this->container_id) {
            $container = Container::where('user_id', $user->id)->where('id', $this->container_id)->first();
            if (! $container) {
                $this->addError('container_id', __('El contenedor seleccionado no existe.'));

                return;
            }
            if ($this->container_id != $this->original_container_id && ! $container->isAvailable()) {
                $this->addError('container_id', __('El contenedor seleccionado ya está asignado a otra cosecha.'));

                return;
            }
        }

        try {
            DB::transaction(function () use ($user, $container) {
                $crewMemberId = $this->harvest->activity->crew_member_id;

                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $crewMember = CrewMember::firstOrCreate(
                        ['viticulturist_id' => $this->crew_member_id, 'assigned_by' => $user->id],
                        ['crew_id' => null]
                    );
                    $crewMemberId = $crewMember->id;
                } elseif ($this->workType === 'crew') {
                    $crewMemberId = null;
                }

                $this->harvest->activity->update([
                    'plot_id' => $this->plot_id,
                    'campaign_id' => $this->campaign_id,
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id' => $crewMemberId,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                if ($this->container_id != $this->original_container_id) {
                    if ($this->original_container_id) {
                        Container::where('id', $this->original_container_id)
                            ->where('user_id', $user->id)
                            ->update(['harvest_id' => null]);
                    }
                    if ($container) {
                        $container->update(['harvest_id' => $this->harvest->id]);
                    }
                }

                $this->harvest->update([
                    'plot_planting_id' => $this->plot_planting_id,
                    'container_id' => $this->container_id ?: null,
                    'harvest_start_date' => $this->harvest_start_date,
                    'harvest_end_date' => $this->harvest_end_date ?: null,
                    'total_weight' => $this->total_weight,
                    'yield_per_hectare' => $this->yield_per_hectare,
                    'baume_degree' => $this->baume_degree ?: null,
                    'brix_degree' => $this->brix_degree ?: null,
                    'acidity_level' => $this->acidity_level ?: null,
                    'ph_level' => $this->ph_level ?: null,
                    'potential_alcohol' => $this->potential_alcohol ?: null,
                    'color_rating' => $this->color_rating ?: null,
                    'aroma_rating' => $this->aroma_rating ?: null,
                    'health_status' => $this->health_status ?: null,
                    'destination_type' => $this->destination_type ?: null,
                    'destination' => $this->destination,
                    'transport_document_number' => $this->transport_document_number ?: null,
                    'destination_rega_code' => $this->destination_rega_code ?: null,
                    'vehicle_plate' => $this->vehicle_plate ?: null,
                    'buyer_name' => $this->buyer_name,
                    'price_per_kg' => $this->price_per_kg ?: null,
                    'total_value' => $this->total_value ?: null,
                    'harvest_ticket_number' => $this->harvest_ticket_number ?: null,
                    'sanitary_state_grapes' => $this->sanitary_state_grapes !== '' ? $this->sanitary_state_grapes : null,
                    'sanitary_state_agraces' => $this->sanitary_state_agraces !== '' ? $this->sanitary_state_agraces : null,
                    'sanitary_state_botrytis' => $this->sanitary_state_botrytis !== '' ? $this->sanitary_state_botrytis : null,
                    'sanitary_state_oidium' => $this->sanitary_state_oidium !== '' ? $this->sanitary_state_oidium : null,
                    'sanitary_state_mildew' => $this->sanitary_state_mildew !== '' ? $this->sanitary_state_mildew : null,
                    'edited_at' => now(),
                    'edited_by' => $user->id,
                    'edit_notes' => $this->edit_notes ?: null,
                ]);

                PhenologyObservation::updateOrCreate(
                    [
                        'plot_planting_id' => $this->plot_planting_id,
                        'campaign_id' => $this->harvest->activity->campaign_id,
                        'event' => 'harvest',
                    ],
                    [
                        'viticulturist_id' => $user->id,
                        'obs_date' => $this->harvest_start_date,
                        'bbch_code' => 89,
                        'source' => 'manual',
                        'active' => true,
                    ]
                );
            });

            $this->toastSuccess(__('Cosecha actualizada correctamente.'));

            return $this->viticulturistRoleRedirect('digital-notebook.harvest.show', $this->harvest->id);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar cosecha', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'harvest_id' => $this->harvest->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al actualizar la cosecha. Por favor, intenta de nuevo.'));
        }
    }

    public function render()
    {
        $user = Auth::user();

        $this->loadAvailableContainers();

        $plots = Plot::forUser($user)
            ->where('active', true)
            ->whereHas('plantings', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $crews = Crew::where('viticulturist_id', $user->id)->orderBy('name')->get();
        $machinery = Machinery::forViticulturist($user->id)->active()->orderBy('name')->get();
        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.edit-harvest', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'allViticulturists' => $this->viticulturists,
        ])->layout('layouts.app');
    }

    protected function loadAvailableContainers(): void
    {
        $query = Container::available()
            ->whereDoesntHave('harvests')
            ->where('user_id', auth()->id());

        if ($this->original_container_id) {
            $query->orWhere(function ($q) {
                $q->where('id', $this->original_container_id)->where('user_id', auth()->id());
            });
        }

        $this->availableContainers = $query->orderBy('created_at', 'desc')->get();
    }

    protected function rules(): array
    {
        $rules = [
            'plot_id' => $this->plotOwnershipRule(),
            'plot_planting_id' => $this->plotPlantingOwnershipRule(required: true),
            'container_id' => $this->ownedContainerRule(required: false),
            'campaign_id' => $this->campaignOwnershipRule(),
            'activity_date' => 'required|date',
            'harvest_start_date' => 'required|date',
            'harvest_end_date' => 'nullable|date|after_or_equal:harvest_start_date',
            'total_weight' => 'required|numeric|min:0.01',
            'yield_per_hectare' => 'nullable|numeric|min:0',
            'baume_degree' => 'nullable|numeric|min:0|max:20',
            'brix_degree' => 'nullable|numeric|min:0|max:40',
            'acidity_level' => 'nullable|numeric|min:0|max:20',
            'ph_level' => 'nullable|numeric|min:0|max:14',
            'potential_alcohol' => 'nullable|numeric|min:0|max:25',
            'color_rating' => 'nullable|in:excelente,bueno,aceptable,deficiente',
            'aroma_rating' => 'nullable|in:excelente,bueno,aceptable,deficiente',
            'health_status' => 'nullable|in:sano,daño_leve,daño_moderado,daño_grave',
            'destination_type' => 'nullable|in:winery,direct_sale,cooperative,self_consumption,other',
            'destination' => 'nullable|string|max:255',
            'transport_document_number' => 'nullable|string|max:50',
            'destination_rega_code' => 'nullable|string|max:20',
            'vehicle_plate' => 'nullable|string|max:20',
            'buyer_name' => 'nullable|string|max:255',
            'price_per_kg' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'harvest_ticket_number' => 'nullable|string|max:50',
            'sanitary_state_grapes' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_agraces' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_botrytis' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_oidium' => 'nullable|numeric|min:0|max:100',
            'sanitary_state_mildew' => 'nullable|numeric|min:0|max:100',
            'crew_id' => $this->crewOwnershipRule(),
            'crew_member_id' => 'nullable|exists:users,id',
            'machinery_id' => $this->machineryOwnershipRule(),
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'edit_notes' => 'nullable|string|min:10',
        ];

        if ($this->hasActiveWithdrawal) {
            $rules['withdrawalAcknowledged'] = 'required|accepted';
            $rules['withdrawalReason'] = 'required|string|min:20';
        }

        return $rules;
    }
}
