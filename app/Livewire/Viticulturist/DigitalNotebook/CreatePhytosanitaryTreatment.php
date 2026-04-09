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
use App\Livewire\Concerns\WithRoleAwareRedirect;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePhytosanitaryTreatment extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters, WithRoleAwareRedirect;

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
    public $workType             = ''; // 'crew' o 'individual'
    public $crew_id              = '';
    public $crew_member_id       = '';
    public $machinery_id         = '';
    public $weather_conditions   = '';
    public $temperature          = '';
    public $wind_speed           = '';
    public $humidity             = '';
    public $notes                = '';
    public $campaign_id          = '';

    // Campos PAC obligatorios
    public $treatment_justification    = '';
    public $field_applicator_id        = '';
    public $applicator_ropo_number     = '';
    public $reentry_period_days        = '';
    public $spray_volume               = '';
    public $water_volume_liters_ha     = '';

    // Zona tampón hídrica
    public $buffer_zone_respected  = false;
    public $distance_to_water_m    = '';

    // Asesoramiento técnico
    public $under_advisory                = false;
    public $advisory_recommendation_date  = '';

    // Flags IPM — Gestión Integrada de Plagas
    public $prior_non_chemical_methods = false;
    public $plague_monitoring          = false;
    public $manual_mechanical_control  = false;
    public $biological_control         = false;
    public $cultural_preventions       = false;

    public function mount(): void
    {
        $this->authorizeCreateActivity();

        $this->activity_date = now()->format('Y-m-d');

        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());

        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return $this->viticulturistRoleRedirect('campaign.create');
        }

        $this->campaign_id = $campaign->id;
    }

    public function updatedPlotId($value): void
    {
        $this->plot_planting_id = '';
        $this->availablePlantings = $value
            ? PlotPlanting::where('plot_id', $value)
                ->where('status', 'active')
                ->with('grapeVariety')
                ->orderBy('name')
                ->get()
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
        )->where('product_id', $this->product_id)->count();
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
            // PAC obligatorios (RD 1311/2012)
            'treatment_justification'   => 'required|string|min:10|max:500',
            'field_applicator_id'       => 'nullable|exists:field_applicators,id',
            'applicator_ropo_number'    => 'nullable|string|max:50|required_without:field_applicator_id',
            'reentry_period_days'       => 'required|integer|min:0|max:365',
            'spray_volume'              => 'required|numeric|min:0.01|max:10000',
            'water_volume_liters_ha'    => 'nullable|numeric|min:0|max:10000',
            // Zona tampón
            'buffer_zone_respected' => 'nullable|boolean',
            'distance_to_water_m'   => 'nullable|numeric|min:0|max:10000',
            // Asesoramiento e IPM
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

    public function save(): void
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

                $activity = AgriculturalActivity::create([
                    'plot_id'           => $this->plot_id,
                    'plot_planting_id'  => $this->plot_planting_id ?: null,
                    'viticulturist_id'  => $user->id,
                    'campaign_id'       => $this->campaign_id,
                    'activity_type'     => 'phytosanitary',
                    'phenological_stage'=> $this->phenological_stage,
                    'activity_date'     => $this->activity_date,
                    'crew_id'           => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id'    => $crewMemberId,
                    'machinery_id'      => $this->machinery_id ?: null,
                    'weather_conditions'=> $this->weather_conditions,
                    'temperature'       => $this->temperature ?: null,
                    'notes'             => $this->notes,
                ]);

                PhytosanitaryTreatment::create([
                    'activity_id'                   => $activity->id,
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

            $this->toastSuccess('Tratamiento fitosanitario registrado correctamente.');
            return $this->viticulturistRoleRedirect('digital-notebook.treatment.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar tratamiento fitosanitario', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
                'plot_id' => $this->plot_id,
            ]);
            $this->toastError('Error al registrar el tratamiento fitosanitario. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.viticulturist.digital-notebook.create-phytosanitary-treatment', [
            'plots'             => Plot::forUser($user)->where('active', true)->orderBy('name')->get(),
            'products'          => PhytosanitaryProduct::forUser($user->id)->where('active', true)->orderBy('name')->get(),
            'pests'             => \App\Models\Pest::active()->orderBy('name')->get(),
            'crews'             => Crew::where('viticulturist_id', $user->id)->orderBy('name')->get(),
            'machinery'         => Machinery::forViticulturist($user->id)->active()->orderBy('name')->get(),
            'applicators'       => FieldApplicator::forViticulturist($user->id)->active()->orderBy('name')->get(),
            'campaign'          => Campaign::find($this->campaign_id),
            'allViticulturists' => $this->viticulturists,
        ])->layout('layouts.app');
    }
}
