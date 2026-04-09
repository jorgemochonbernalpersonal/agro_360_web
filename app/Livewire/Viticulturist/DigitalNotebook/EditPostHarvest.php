<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\PostHarvestTreatment;
use App\Models\PhytosanitaryProduct;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditPostHarvest extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters, WithRoleAwareRedirect;

    public AgriculturalActivity $activity;
    public PostHarvestTreatment $postHarvestTreatment;

    public $plot_id = '';
    public $plot_planting_id = '';
    public $availablePlantings = [];
    public $activity_date = '';
    public $phenological_stage = '';
    public $product_id = '';
    public $application_type = '';
    public $treated_area_ha = '';
    public $dose_per_hectare = '';
    public $dose_unit = 'kg/ha';
    public $water_volume_liters = '';
    public $reentry_interval_hours = '';
    public $workType = '';
    public $crew_id = '';
    public $crew_member_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $notes = '';
    public $campaign_id = '';

    public function mount(AgriculturalActivity $activity)
    {
        $this->activity = $activity->load(['postHarvestTreatment', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if ($this->activity->activity_type !== 'post_harvest') {
            $this->toastError('Esta actividad no es un tratamiento post-vendimia.');
            return $this->viticulturistRoleRedirect('digital-notebook.post-harvest.index', navigate: true);
        }

        if (!Auth::user()->can('update', $this->activity)) {
            abort(403, 'No tienes permiso para editar esta actividad.');
        }

        if ($this->activity->isLocked()) {
            $this->toastError('No se puede editar una actividad bloqueada. Las actividades se bloquean automáticamente después de ' . config('activities.lock_days', 7) . ' días para cumplimiento PAC.');
            return $this->viticulturistRoleRedirect('digital-notebook.post-harvest.index', navigate: true);
        }

        $this->postHarvestTreatment = $this->activity->postHarvestTreatment;

        $this->plot_id              = $this->activity->plot_id;
        $this->plot_planting_id     = $this->activity->plot_planting_id;
        $this->activity_date        = \Carbon\Carbon::parse($this->activity->activity_date)->format('Y-m-d');
        $this->phenological_stage   = $this->activity->phenological_stage;
        $this->campaign_id          = $this->activity->campaign_id;
        $this->weather_conditions   = $this->activity->weather_conditions;
        $this->temperature          = $this->activity->temperature;
        $this->notes                = $this->activity->notes;
        $this->machinery_id         = $this->activity->machinery_id;

        if ($this->activity->crew_id) {
            $this->workType = 'crew';
            $this->crew_id  = $this->activity->crew_id;
        } elseif ($this->activity->crew_member_id) {
            $this->workType       = 'individual';
            $this->crew_member_id = $this->activity->crewMember?->viticulturist_id ?? '';
        }

        $this->product_id              = $this->postHarvestTreatment->product_id ?? '';
        $this->application_type        = $this->postHarvestTreatment->application_type;
        $this->treated_area_ha         = $this->postHarvestTreatment->treated_area_ha;
        $this->dose_per_hectare        = $this->postHarvestTreatment->dose_per_hectare ?? '';
        $this->dose_unit               = $this->postHarvestTreatment->dose_unit ?? 'kg/ha';
        $this->water_volume_liters     = $this->postHarvestTreatment->water_volume_liters ?? '';
        $this->reentry_interval_hours  = $this->postHarvestTreatment->reentry_interval_hours ?? '';

        if ($this->plot_id) {
            $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
                ->where('status', 'active')->with('grapeVariety')->orderBy('name')->get();
        }
    }

    public function updatedPlotId($value)
    {
        $this->plot_planting_id   = '';
        $this->availablePlantings = $value
            ? PlotPlanting::where('plot_id', $value)->where('status', 'active')->with('grapeVariety')->orderBy('name')->get()
            : [];
    }

    protected function rules(): array
    {
        return [
            'plot_id' => $this->plotOwnershipRule(),
            'plot_planting_id' => [
                'nullable',
                'exists:plot_plantings,id',
                function ($attribute, $value, $fail) {
                    if ($this->plot_id) {
                        $plot = Plot::find($this->plot_id);
                        if ($plot && $plot->plantings()->where('status', 'active')->exists()) {
                            if (!$value) {
                                $fail('Debes seleccionar una plantación para esta parcela.');
                            } elseif (!PlotPlanting::where('id', $value)->where('plot_id', $this->plot_id)->exists()) {
                                $fail('La plantación seleccionada no pertenece a esta parcela.');
                            }
                        }
                    }
                },
            ],
            'campaign_id'        => 'required|exists:campaigns,id',
            'activity_date'      => 'required|date',
            'phenological_stage' => 'required|string|max:50',
            'product_id'         => 'nullable|exists:phytosanitary_products,id',
            'application_type'   => 'required|string|in:' . implode(',', array_keys(PostHarvestTreatment::APPLICATION_TYPES)),
            'treated_area_ha'    => 'required|numeric|min:0.001',
            'dose_per_hectare'   => 'nullable|numeric|min:0',
            'dose_unit'          => 'nullable|string|max:20',
            'water_volume_liters'    => 'nullable|numeric|min:0',
            'reentry_interval_hours' => 'nullable|integer|min:0|max:168',
            'crew_id'                => 'nullable|exists:crews,id',
            'crew_member_id'     => 'nullable|exists:users,id',
            'machinery_id'       => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature'        => 'nullable|numeric',
            'notes'              => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();
        $user = Auth::user();

        if (!$this->workType) {
            $this->addError('workType', 'Debes seleccionar si el trabajo lo realizó un equipo completo o un viticultor individual.');
            return;
        }
        if ($this->workType === 'crew' && !$this->crew_id) {
            $this->addError('crew_id', 'Debes seleccionar un equipo.');
            return;
        }
        if ($this->workType === 'individual' && !$this->crew_member_id) {
            $this->addError('crew_member_id', 'Debes seleccionar un viticultor.');
            return;
        }

        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () use ($user) {
                $crewMemberId = null;
                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $crewMember = CrewMember::firstOrCreate(
                        ['viticulturist_id' => $this->crew_member_id, 'assigned_by' => $user->id],
                        ['crew_id' => null]
                    );
                    $crewMemberId = $crewMember->id;
                }

                $this->activity->update([
                    'plot_id'            => $this->plot_id,
                    'plot_planting_id'   => $this->plot_planting_id ?: null,
                    'campaign_id'        => $this->campaign_id,
                    'phenological_stage' => $this->phenological_stage,
                    'activity_date'      => $this->activity_date,
                    'crew_id'            => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id'     => $crewMemberId,
                    'machinery_id'       => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature'        => $this->temperature ?: null,
                    'notes'              => $this->notes,
                ]);

                $this->postHarvestTreatment->update([
                    'product_id'             => $this->product_id ?: null,
                    'application_type'       => $this->application_type,
                    'treated_area_ha'        => $this->treated_area_ha,
                    'dose_per_hectare'       => $this->dose_per_hectare ?: null,
                    'dose_unit'              => $this->dose_per_hectare ? ($this->dose_unit ?: 'kg/ha') : null,
                    'water_volume_liters'    => $this->water_volume_liters ?: null,
                    'reentry_interval_hours' => $this->reentry_interval_hours !== '' ? (int) $this->reentry_interval_hours : null,
                    'notes'                  => $this->notes,
                ]);
            });

            $this->toastSuccess('Tratamiento post-vendimia actualizado correctamente.');
            return $this->viticulturistRoleRedirect('digital-notebook.post-harvest.index', navigate: true);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar tratamiento post-vendimia', ['error' => $e->getMessage(), 'user_id' => $user->id, 'activity_id' => $this->activity->id]);
            $this->toastError('Error al actualizar el tratamiento. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        return view('livewire.viticulturist.digital-notebook.edit-post-harvest', [
            'plots'            => Plot::forUser($user)->where('active', true)->orderBy('name')->get(),
            'crews'            => Crew::where('viticulturist_id', $user->id)->orderBy('name')->get(),
            'machinery'        => Machinery::forViticulturist($user->id)->active()->orderBy('name')->get(),
            'campaign'         => Campaign::find($this->campaign_id),
            'products'         => PhytosanitaryProduct::forUser($user->id)->where('active', true)->orderBy('name')->get(),
            'applicationTypes' => PostHarvestTreatment::APPLICATION_TYPES,
            'allViticulturists'=> $this->viticulturists,
        ])->layout('layouts.app', ['title' => 'Editar Tratamiento Post-Vendimia - Agro365']);
    }
}
