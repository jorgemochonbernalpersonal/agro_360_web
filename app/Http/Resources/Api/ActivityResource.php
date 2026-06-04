<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activity_type' => $this->activity_type,
            'activity_date' => $this->activity_date?->toDateString(),
            'phenological_stage' => $this->phenological_stage,
            'weather_conditions' => $this->weather_conditions,
            'temperature' => $this->temperature !== null ? (float) $this->temperature : null,
            'notes' => $this->notes,
            'plot' => $this->whenLoaded('plot', fn () => [
                'id' => $this->plot->id,
                'name' => $this->plot->name,
            ]),
            'campaign_id' => $this->campaign_id,
            'is_locked' => (bool) $this->is_locked,
            'created_at' => $this->created_at->toIso8601String(),
            'details' => $this->resolveDetails(),
        ];
    }

    private function resolveDetails(): ?array
    {
        return match ($this->activity_type) {
            'phytosanitary' => $this->relationLoaded('phytosanitaryTreatment')
                ? $this->phytosanitaryTreatment?->only([
                    'product_id', 'pest_id', 'dose_per_hectare', 'total_dose',
                    'area_treated', 'application_method', 'treatment_justification',
                    'applicator_ropo_number', 'reentry_period_days',
                ])
                : null,

            'fertilization' => $this->relationLoaded('fertilization')
                ? $this->fertilization?->only([
                    'fertilizer_type', 'fertilizer_name', 'quantity', 'application_method',
                    'area_applied', 'nitrogen_uf', 'phosphorus_uf', 'potassium_uf',
                ])
                : null,

            'irrigation' => $this->relationLoaded('irrigation')
                ? $this->irrigation?->only([
                    'water_volume', 'water_volume_unit', 'irrigation_method',
                    'duration_minutes', 'is_fertirrigation',
                    'fertilizer_product', 'fertilizer_dose_per_ha',
                ])
                : null,

            'cultural', 'pruning' => $this->relationLoaded('culturalWork')
                ? $this->culturalWork?->only([
                    'work_type', 'pruning_type', 'productive_buds_per_hectare',
                    'residue_management', 'hours_worked', 'workers_count', 'description',
                ])
                : null,

            'observation' => $this->relationLoaded('observation')
                ? $this->observation?->only([
                    'pest_id', 'observation_type', 'description', 'severity',
                    'affected_area_percentage', 'threshold_exceeded',
                    'follow_up_date', 'action_taken',
                ])
                : null,

            'post_harvest' => $this->relationLoaded('postHarvestTreatment')
                ? $this->postHarvestTreatment?->only([
                    'product_id', 'application_type', 'treated_area_ha',
                    'dose_per_hectare', 'water_volume_liters', 'reentry_interval_hours',
                ])
                : null,

            default => null,
        };
    }
}
