<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Models\FieldApplicator;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditPhytosanitaryTreatment extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters, WithRoleAwareRedirect;

    public AgriculturalActivity   $activity;
    public PhytosanitaryTreatment $treatment;

    public $plot_id              = '';
    public $plot_planting_id     = '';
    public $availablePlantings   = [];
    public $activity_date        = '';
    public $phenological_stage   = '';
    public $product_id           = '';
    public $dose_per_hectare     = '';
    public $total_dose           = '';
    public $area_treated         = '';
    public $application_method   = '';
    public $pest_id              = '';
    public $workType             = '';
    public $crew_id              = '';
    public $crew_member_id       = '';
    public $machinery_id         = '';
    public $weather_conditions   = '';
    public $temperature          = '';
    public $wind_speed           = '';
    public $humidity             = '';
    public $notes                = '';
    public $campaign_id          = '';

    // PAC obligatorios
    public $treatment_justification    = '';
    public $field_applicator_id        = '';
    public $applicator_ropo_number     = '';
    public $reentry_period_days        = '';
    public $spray_volume               = '';
    public $water_volume_liters_ha     = '';

    // Zona tampón
    public $buffer_zone_respected = false;
    public $distance_to_water_m   = '';

    // Asesoramiento
    public $under_advisory                = false;
    public $advisory_recommendation_date  = '';

    // IPM
    public $prior_non_chemical_methods = false;
    public $plague_monitoring          = false;
    public $manual_mechanical_control  = false;
    public $biological_control         = false;
    public $cultural_preventions       = false;

    public function mount(AgriculturalActivity $activity): void
    {
        $this->activity = $activity->load(['phytosanitaryTreatment', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if ($this->activity->activity_type !== 'phytosanitary') {
            $this->toastError('Esta actividad no es un tratamiento fitosanitario.');
            $this->viticulturistRoleRedirect('digital-notebook.treatment.index', navigate: true);
            return;
        }

        if (!Auth::user()->can('update', $this->activity)) {
            abort(403);
        }

        if ($this->activity->isLocked()) {
            $this->toastError('No se puede editar una actividad bloqueada (PAC, >' . config('activities.lock_days', 7) . ' días).');
            $this->viticulturistRoleRedirect('digital-notebook.treatment.index', navigate: true);
            return;
        }

        $this->treatment = $this->activity->phytosanitaryTreatment;

        // Actividad
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
            $this->workType    = 'individual';
            $crewMember        = $this->activity->crewMember;
            $this->crew_member_id = $crewMember?->viticulturist_id ?? '';
        }

        // Tratamiento
        $this->product_id           = $this->treatment->product_id;
        $this->dose_per_hectare     = $this->treatment->dose_per_hectare;
        $this->total_dose           = $this->treatment->total_dose;
        $this->area_treated         = $this->treatment->area_treated;
        $this->application_method   = $this->treatment->application_method;
        $this->pest_id              = $this->treatment->pest_id;
        $this->wind_speed           = $this->treatment->wind_speed;
        $this->humidity             = $this->treatment->humidity;

        $this->treatment_justification      = $this->treatment->treatment_justification;
        $this->field_applicator_id          = $this->treatment->field_applicator_id ?? '';
        $this->applicator_ropo_number       = $this->treatment->applicator_ropo_number ?? '';
        $this->reentry_period_days          = $this->treatment->reentry_period_days;
        $this->spray_volume                 = $this->treatment->spray_volume;
        $this->water_volume_liters_ha       = $this->treatment->water_volume_liters_ha ?? '';
        $this->buffer_zone_respected        = (bool) ($this->treatment->buffer_zone_respected ?? false);
        $this->distance_to_water_m          = $this->treatment->distance_to_water_m ?? '';
        $this->under_advisory               = (bool) ($this->treatment->under_advisory ?? false);
        $this->advisory_recommendation_date = $this->treatment->advisory_recommendation_date
            ? \Carbon\Carbon::parse($this->treatment->advisory_recommendation_date)->format('Y-m-d') : '';
        $this->prior_non_chemical_methods   = (bool) ($this->treatment->prior_non_chemical_methods ?? false);
        $this->plague_monitoring            = (bool) ($this->treatment->plague_monitoring ?? false);
        $this->manual_mechanical_control    = (bool) ($this->treatment->manual_mechanical_control ?? false);
        $this->biological_control           = (bool) ($this->treatment->biological_control ?? false);
        $this->cultural_preventions         = (bool) ($this->treatment->cultural_preventions ?? false);

        if ($this->plot_id) {
            $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
                ->where('status', 'active')
                ->with('grapeVariety')
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedPlotId($value): void
    {
        $this->plot_planting_id   = '';
        $this->availablePlantings = $value
            ? PlotPlanting::where('plot_id', $value)->where('status', 'active')->with('grapeVariety')->orderBy('name')->get()
            : [];
    }

    public function updatedFieldApplicatorId($value): void
    {
        if ($value) {
            $applicator = FieldApplicator::find($value);
            if ($applicator) {
                $this->applicator_ropo_number = $applicator->ropo_number;
            }
        }
    }

    public function getApplicationsThisCampaignProperty(): int
    {
        if (!$this->product_id || !$this->campaign_id) {
            return 0;
        }

        return PhytosanitaryTreatment::whereHas('activity', fn ($q) => $q
            ->where('viticulturist_id', Auth::id())
            ->where('campaign_id', $this->campaign_id)
        )->where('product_id', $this->product_id)
         ->where('activity_id', '!=', $this->activity->id)
         ->count();
    }

    public function getSelectedProductProperty(): ?PhytosanitaryProduct
    {
        return $this->product_id ? PhytosanitaryProduct::find($this->product_id) : null;
    }

    protected function rules(): array
    {
        return [
            'plot_id'          => $this->plotOwnershipRule(),
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
            'campaign_id'           => $this->campaignOwnershipRule(),
            'activity_date'         => 'required|date',
            'phenological_stage'    => 'required|string|max:50',
            'product_id'            => 'required|exists:phytosanitary_products,id',
            'dose_per_hectare'      => 'required|numeric|min:0.01|max:100',
            'total_dose'            => 'nullable|numeric|min:0',
            'area_treated'          => 'required|numeric|min:0.01',
            'application_method'    => 'nullable|string|max:50',
            'pest_id'               => 'nullable|exists:pests,id',
            'crew_id'               => $this->crewOwnershipRule(),
            'crew_member_id'        => 'nullable|exists:users,id',
            'machinery_id'          => $this->machineryOwnershipRule(),
            'weather_conditions'    => 'nullable|string|max:255',
            'temperature'           => 'nullable|numeric',
            'wind_speed'            => 'nullable|numeric|min:0',
            'humidity'              => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string',
            'treatment_justification'   => 'required|string|min:10|max:500',
            'field_applicator_id'       => 'nullable|exists:field_applicators,id',
            'applicator_ropo_number'    => 'nullable|string|max:50|required_without:field_applicator_id',
            'reentry_period_days'       => 'required|integer|min:0|max:365',
            'spray_volume'              => 'required|numeric|min:0.01|max:10000',
            'water_volume_liters_ha'    => 'nullable|numeric|min:0|max:10000',
            'buffer_zone_respected'     => 'nullable|boolean',
            'distance_to_water_m'       => 'nullable|numeric|min:0|max:10000',
            'under_advisory'                => 'boolean',
            'advisory_recommendation_date'  => 'nullable|date|required_if:under_advisory,true',
            'prior_non_chemical_methods'    => 'boolean',
            'plague_monitoring'             => 'boolean',
            'manual_mechanical_control'     => 'boolean',
            'biological_control'            => 'boolean',
            'cultural_preventions'          => 'boolean',
        ];
    }

    public function updatedAreaTreated(): void
    {
        if ($this->area_treated && $this->dose_per_hectare) {
            $this->total_dose = round($this->area_treated * $this->dose_per_hectare, 3);
        }
    }

    public function updatedDosePerHectare(): void
    {
        if ($this->area_treated && $this->dose_per_hectare) {
            $this->total_dose = round($this->area_treated * $this->dose_per_hectare, 3);
        }
    }

    public function update(): void
    {
        $this->validate();

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
            DB::transaction(function () {
                $user = Auth::user();
                $crewMemberId = null;

                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $crewMember = CrewMember::firstOrCreate(
                        ['viticulturist_id' => $this->crew_member_id, 'assigned_by' => $user->id],
                        ['crew_id' => null]
                    );
                    $crewMemberId = $crewMember->id;
                }

                $this->activity->update([
                    'plot_id'           => $this->plot_id,
                    'plot_planting_id'  => $this->plot_planting_id ?: null,
                    'campaign_id'       => $this->campaign_id,
                    'phenological_stage'=> $this->phenological_stage,
                    'activity_date'     => $this->activity_date,
                    'crew_id'           => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id'    => $crewMemberId,
                    'machinery_id'      => $this->machinery_id ?: null,
                    'weather_conditions'=> $this->weather_conditions,
                    'temperature'       => $this->temperature ?: null,
                    'notes'             => $this->notes,
                ]);

                $this->treatment->update([
                    'field_applicator_id'           => $this->field_applicator_id ?: null,
                    'product_id'                    => $this->product_id,
                    'dose_per_hectare'              => $this->dose_per_hectare ?: null,
                    'total_dose'                    => $this->total_dose ?: null,
                    'area_treated'                  => $this->area_treated ?: null,
                    'application_method'            => $this->application_method,
                    'pest_id'                       => $this->pest_id ?: null,
                    'wind_speed'                    => $this->wind_speed ?: null,
                    'humidity'                      => $this->humidity ?: null,
                    'treatment_justification'       => $this->treatment_justification,
                    'applicator_ropo_number'        => $this->applicator_ropo_number ?: null,
                    'reentry_period_days'           => $this->reentry_period_days,
                    'spray_volume'                  => $this->spray_volume,
                    'water_volume_liters_ha'        => $this->water_volume_liters_ha ?: null,
                    'buffer_zone_respected'         => $this->buffer_zone_respected ? true : null,
                    'distance_to_water_m'           => $this->distance_to_water_m ?: null,
                    'under_advisory'                => (bool) $this->under_advisory,
                    'advisory_recommendation_date'  => $this->advisory_recommendation_date ?: null,
                    'prior_non_chemical_methods'    => (bool) $this->prior_non_chemical_methods,
                    'plague_monitoring'             => (bool) $this->plague_monitoring,
                    'manual_mechanical_control'     => (bool) $this->manual_mechanical_control,
                    'biological_control'            => (bool) $this->biological_control,
                    'cultural_preventions'          => (bool) $this->cultural_preventions,
                ]);
            });

            $this->toastSuccess('Tratamiento fitosanitario actualizado correctamente.');
            $this->viticulturistRoleRedirect('digital-notebook.treatment.index', navigate: true);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar tratamiento fitosanitario', [
                'error'       => $e->getMessage(),
                'user_id'     => Auth::id(),
                'activity_id' => $this->activity->id,
            ]);
            $this->toastError('Error al actualizar el tratamiento fitosanitario. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.viticulturist.digital-notebook.edit-phytosanitary-treatment', [
            'plots'             => Plot::forUser($user)->where('active', true)->orderBy('name')->get(),
            'products'          => PhytosanitaryProduct::forUser($user->id)->where('active', true)->orderBy('name')->get(),
            'pests'             => \App\Models\Pest::active()->orderBy('name')->get(),
            'crews'             => Crew::where('viticulturist_id', $user->id)->orderBy('name')->get(),
            'machinery'         => Machinery::forViticulturist($user->id)->active()->orderBy('name')->get(),
            'applicators'       => FieldApplicator::forViticulturist($user->id)->active()->orderBy('name')->get(),
            'campaign'          => Campaign::find($this->campaign_id),
            'allViticulturists' => $this->viticulturists,
        ])->layout('layouts.app', ['title' => 'Editar Tratamiento Fitosanitario - Agro365']);
    }
}
