<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\FieldApplicator;
use App\Models\Pest;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Services\NotebookActivityService;
use Illuminate\Support\Facades\Auth;

class CreatePhytosanitaryTreatment extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $product_id = '';

    public $dose_per_hectare = '';

    public $total_dose = '';

    public $area_treated = '';

    public $application_method = '';

    public $pest_id = '';

    public $wind_speed = '';

    public $humidity = '';

    // PAC obligatorios (RD 1311/2012)
    public $treatment_justification = '';

    public $field_applicator_id = '';

    public $applicator_ropo_number = '';

    public $reentry_period_days = '';

    public $spray_volume = '';

    public $water_volume_liters_ha = '';

    // Zona tampón hídrica
    public $buffer_zone_respected = false;

    public $distance_to_water_m = '';

    // Asesoramiento técnico
    public $under_advisory = false;

    public $advisory_recommendation_date = '';

    // IPM — Gestión Integrada de Plagas (RD 1311/2012)
    public $prior_non_chemical_methods = false;

    public $plague_monitoring = false;

    public $manual_mechanical_control = false;

    public $biological_control = false;

    public $cultural_preventions = false;

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();
    }

    // ─── Reactive ─────────────────────────────────────────────────────────────

    public function updatedFieldApplicatorId($value): void
    {
        if ($value) {
            $applicator = FieldApplicator::find($value);
            if ($applicator) {
                $this->applicator_ropo_number = $applicator->ropo_number;
            }
        }
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

    // ─── Computed ─────────────────────────────────────────────────────────────

    #[\Livewire\Attributes\Computed]
    public function applicationsThisCampaign(): int
    {
        if (! $this->product_id || ! $this->campaign_id) {
            return 0;
        }

        return PhytosanitaryTreatment::whereHas('activity', fn ($q) => $q
            ->where('viticulturist_id', Auth::id())
            ->where('campaign_id', $this->campaign_id)
        )->where('product_id', $this->product_id)->count();
    }

    #[\Livewire\Attributes\Computed]
    public function selectedProduct(): ?PhytosanitaryProduct
    {
        return $this->product_id ? PhytosanitaryProduct::find($this->product_id) : null;
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            app(NotebookActivityService::class)->createActivity(
                $this->activityData('phytosanitary'),
                fn (int $activityId) => PhytosanitaryTreatment::create([
                    'activity_id' => $activityId,
                    'field_applicator_id' => $this->field_applicator_id ?: null,
                    'product_id' => $this->product_id,
                    'dose_per_hectare' => $this->dose_per_hectare ?: null,
                    'total_dose' => $this->total_dose ?: null,
                    'area_treated' => $this->area_treated ?: null,
                    'application_method' => $this->application_method,
                    'pest_id' => $this->pest_id ?: null,
                    'wind_speed' => $this->wind_speed ?: null,
                    'humidity' => $this->humidity ?: null,
                    'treatment_justification' => $this->treatment_justification,
                    'applicator_ropo_number' => $this->applicator_ropo_number ?: null,
                    'reentry_period_days' => $this->reentry_period_days,
                    'spray_volume' => $this->spray_volume,
                    'water_volume_liters_ha' => $this->water_volume_liters_ha ?: null,
                    'buffer_zone_respected' => $this->buffer_zone_respected ?: null,
                    'distance_to_water_m' => $this->distance_to_water_m ?: null,
                    'under_advisory' => (bool) $this->under_advisory,
                    'advisory_recommendation_date' => $this->advisory_recommendation_date ?: null,
                    'prior_non_chemical_methods' => (bool) $this->prior_non_chemical_methods,
                    'plague_monitoring' => (bool) $this->plague_monitoring,
                    'manual_mechanical_control' => (bool) $this->manual_mechanical_control,
                    'biological_control' => (bool) $this->biological_control,
                    'cultural_preventions' => (bool) $this->cultural_preventions,
                ]),
            );

            $this->toastSuccess(__('Tratamiento fitosanitario registrado correctamente.'));

            return $this->viticulturistRoleRedirect('digital-notebook.treatment.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar tratamiento fitosanitario', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError(__('Error al registrar el tratamiento fitosanitario. Por favor, intenta de nuevo.'));
        }

        return null;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $user = Auth::user();

        return view('livewire.viticulturist.digital-notebook.create-phytosanitary-treatment', $this->renderData([
            'products' => PhytosanitaryProduct::forUser($user->id)->where('active', true)->orderBy('name')->get(),
            'pests' => Pest::active()->orderBy('name')->get(),
            'applicators' => FieldApplicator::forViticulturist($user->id)->active()->orderBy('name')->get(),
        ]))->layout('layouts.app');
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'product_id' => 'required|exists:phytosanitary_products,id',
            'dose_per_hectare' => 'required|numeric|min:0.01|max:100',
            'total_dose' => 'nullable|numeric|min:0',
            'area_treated' => 'required|numeric|min:0.01',
            'application_method' => 'nullable|string|max:50',
            'pest_id' => 'nullable|exists:pests,id',
            'wind_speed' => 'nullable|numeric|min:0',
            'humidity' => 'nullable|numeric|min:0|max:100',
            // PAC
            'treatment_justification' => 'required|string|min:10|max:500',
            'field_applicator_id' => 'nullable|exists:field_applicators,id',
            'applicator_ropo_number' => 'required|string|min:3|max:50',
            'reentry_period_days' => 'required|integer|min:0|max:365',
            'spray_volume' => 'required|numeric|min:0.01|max:10000',
            'water_volume_liters_ha' => 'nullable|numeric|min:0|max:10000',
            // Zona tampón
            'buffer_zone_respected' => 'nullable|boolean',
            'distance_to_water_m' => 'nullable|numeric|min:0|max:10000',
            // Asesoramiento e IPM
            'under_advisory' => 'boolean',
            'advisory_recommendation_date' => 'nullable|date|required_if:under_advisory,true',
            'prior_non_chemical_methods' => 'boolean',
            'plague_monitoring' => 'boolean',
            'manual_mechanical_control' => 'boolean',
            'biological_control' => 'boolean',
            'cultural_preventions' => 'boolean',
        ]);
    }
}
