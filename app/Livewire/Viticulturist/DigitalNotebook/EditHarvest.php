<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Livewire\Concerns\WithHarvestControlPanel;
use App\Livewire\Concerns\WithHarvestFormFields;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\Container;
use App\Models\Harvest;
use App\Services\HarvestNotebookService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @property-read mixed $viticulturists
 */
class EditHarvest extends Component
{
    use WithHarvestControlPanel, WithHarvestFormFields, WithRoleAwareRedirect, WithToastNotifications, WithUserFilters, WithViticulturistValidation;

    public $harvest;

    public $harvest_id;

    public $original_container_id = null;

    public $edit_notes = '';

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

    public function update()
    {
        $this->validate();

        $user = Auth::user();

        if (! $this->validateWorkType()) {
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
            $crewMemberId = $this->resolveCrewMemberId($user, $this->harvest->activity->crew_member_id);
            $activityData = $this->buildActivityData($crewMemberId);
            $harvestData = array_merge($this->buildHarvestData(), [
                'edited_at' => now(),
                'edited_by' => $user->id,
                'edit_notes' => $this->edit_notes ?: null,
            ]);

            app(HarvestNotebookService::class)->update($this->harvest, $activityData, $harvestData, $user);

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
        $this->loadAvailableContainers();

        return view('livewire.viticulturist.digital-notebook.edit-harvest',
            $this->harvestFormRenderData(Auth::user())
        )->layout('layouts.app');
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
        return array_merge($this->harvestBaseRules(), [
            'destination_type' => 'nullable|in:winery,direct_sale,cooperative,self_consumption,other',
            'transport_document_number' => 'nullable|string|max:50',
            'destination_rega_code' => 'nullable|string|max:20',
            'crew_id' => $this->crewOwnershipRule(),
            'crew_member_id' => 'nullable|exists:users,id',
            'edit_notes' => 'nullable|string|min:10',
        ]);
    }
}
