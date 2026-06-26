<?php

namespace App\Http\Requests\Api\Viticulturist;

use Illuminate\Validation\Rule;

class StoreNotebookRequest extends ViticulturistApiRequest
{
    private const TYPE_SLUG_MAP = [
        'treatments' => 'phytosanitary',
        'fertilizations' => 'fertilization',
        'irrigations' => 'irrigation',
        'observations' => 'observation',
        'harvests' => 'harvest',
        'cultural-works' => 'cultural',
        'pruning' => 'pruning',
        'post-harvest-treatments' => 'post_harvest',
    ];

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->detailRules($this->input('activity_type'))
        );
    }

    protected function prepareForValidation(): void
    {
        $notebookType = $this->route('notebook_type');

        if ($notebookType) {
            $activityType = self::TYPE_SLUG_MAP[$notebookType] ?? $notebookType;
            $merge = ['activity_type' => $activityType];

            if (! $this->has('activity_date') && $this->has('date')) {
                $merge['activity_date'] = $this->input('date');
            }

            if (! $this->has('productive_buds_per_hectare') && $this->has('buds_per_vine')) {
                $merge['productive_buds_per_hectare'] = $this->input('buds_per_vine');
            }

            $this->merge($merge);
        }
    }

    private function baseRules(): array
    {
        return [
            'activity_type' => 'required|string|in:phytosanitary,fertilization,irrigation,cultural,observation,harvest,pruning,phenology,post_harvest',
            'plot_id' => 'required|integer',
            'activity_date' => 'required|date',
            'campaign_id' => 'nullable|integer',
            'phenological_stage' => 'nullable|string|max:100',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric|between:-20,60',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    private function detailRules(?string $type): array
    {
        return match ($type) {
            'phytosanitary' => [
                'product_id' => ['required', 'integer', Rule::exists('phytosanitary_products', 'id')->where(fn ($q) => $q->where(fn ($q2) => $q2->whereNull('user_id')->orWhere('user_id', $this->user()?->id)))],
                'pest_id' => 'nullable|integer|exists:pests,id',
                'dose_per_hectare' => 'nullable|numeric|min:0',
                'total_dose' => 'nullable|numeric|min:0',
                'area_treated' => 'nullable|numeric|min:0',
                'application_method' => 'nullable|string|max:100',
                'treatment_justification' => 'nullable|string|max:500',
                'applicator_ropo_number' => 'nullable|string|max:50',
                'reentry_period_days' => 'nullable|integer|min:0',
            ],
            'fertilization' => [
                'fertilizer_type' => 'nullable|string|max:100',
                'fertilizer_name' => 'nullable|string|max:255',
                'quantity' => 'nullable|numeric|min:0',
                'application_method' => 'nullable|string|max:100',
                'area_applied' => 'nullable|numeric|min:0',
                'nitrogen_uf' => 'nullable|numeric|min:0',
                'phosphorus_uf' => 'nullable|numeric|min:0',
                'potassium_uf' => 'nullable|numeric|min:0',
            ],
            'irrigation' => [
                'water_volume' => 'nullable|numeric|min:0',
                'water_volume_unit' => 'nullable|string|in:L,m3',
                'irrigation_method' => 'nullable|string|max:100',
                'duration_minutes' => 'nullable|integer|min:0',
                'is_fertirrigation' => 'boolean',
                'fertilizer_product' => 'nullable|string|max:255',
                'fertilizer_dose_per_ha' => 'nullable|numeric|min:0',
            ],
            'cultural' => [
                'work_type' => 'nullable|string|max:100',
                'hours_worked' => 'nullable|numeric|min:0',
                'workers_count' => 'nullable|integer|min:0',
                'residue_management' => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
                'description' => 'nullable|string|max:1000',
            ],
            'pruning' => [
                'pruning_type' => 'nullable|string|max:100',
                'productive_buds_per_hectare' => 'nullable|integer|min:0',
                'hours_worked' => 'nullable|numeric|min:0',
                'workers_count' => 'nullable|integer|min:0',
                'residue_management' => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
            ],
            'observation' => [
                'pest_id' => 'nullable|integer|exists:pests,id',
                'observation_type' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:2000',
                'severity' => 'nullable|string|max:50',
                'affected_area_percentage' => 'nullable|numeric|min:0|max:100',
                'threshold_exceeded' => 'boolean',
                'follow_up_date' => 'nullable|date',
                'action_taken' => 'nullable|string|max:500',
            ],
            'post_harvest' => [
                'application_type' => 'required|string|in:copper_treatment,sulfur_treatment,wound_sealing,foliar_application,trunk_treatment,other',
                'product_id' => 'nullable|integer|exists:phytosanitary_products,id',
                'treated_area_ha' => 'nullable|numeric|min:0',
                'dose_per_hectare' => 'nullable|numeric|min:0',
                'water_volume_liters' => 'nullable|numeric|min:0',
                'reentry_interval_hours' => 'nullable|integer|min:0|max:168',
            ],
            default => [],
        };
    }
}
