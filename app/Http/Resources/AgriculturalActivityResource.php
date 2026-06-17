<?php

namespace App\Http\Resources;

use App\Models\AgriculturalActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AgriculturalActivity */
class AgriculturalActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->activity_type,
            'date' => $this->activity_date->toDateString(),
            'plot' => $this->whenLoaded('plot', fn () => [
                'id' => $this->plot->id,
                'name' => $this->plot->name,
            ]),
            'planting' => $this->whenLoaded('plotPlanting', fn () => [
                'id' => $this->plotPlanting->id,
                'name' => $this->plotPlanting->name,
                'grape_variety' => $this->plotPlanting->relationLoaded('grapeVariety') ? [
                    'id' => $this->plotPlanting->grapeVariety->id,
                    'name' => $this->plotPlanting->grapeVariety->name,
                ] : null,
            ]),
            'weather' => [
                'temperature' => $this->temperature,
                'humidity' => $this->humidity,
                'wind_speed' => $this->wind_speed,
            ],
            'crew' => $this->whenLoaded('crew', fn () => [
                'id' => $this->crew->id,
                'name' => $this->crew->name,
            ]),
            'crew_member' => $this->whenLoaded('crewMember', fn () => [
                'id' => $this->crewMember->id,
                'name' => $this->crewMember->name,
            ]),
            'machinery' => $this->whenLoaded('machinery', fn () => [
                'id' => $this->machinery->id,
                'name' => $this->machinery->name,
            ]),
            'details' => $this->getActivityDetails(),
            'notes' => $this->notes,
            'locked' => $this->isLocked(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Obtener detalles específicos por tipo de actividad
     */
    protected function getActivityDetails(): ?array
    {
        return match ($this->activity_type) {
            'phytosanitary' => $this->whenLoaded('phytosanitaryTreatment', fn () => [
                'product' => [
                    'id' => $this->phytosanitaryTreatment->product->id,
                    'name' => $this->phytosanitaryTreatment->product->name,
                ],
                'dose' => $this->phytosanitaryTreatment->dose,
                'dose_unit' => $this->phytosanitaryTreatment->dose_unit,
                'area_treated' => $this->phytosanitaryTreatment->area_treated,
            ]),
            'fertilization' => $this->whenLoaded('fertilization', fn () => [
                'fertilizer_name' => $this->fertilization->fertilizer_name,
                'dose' => $this->fertilization->dose,
                'dose_unit' => $this->fertilization->dose_unit,
            ]),
            'irrigation' => $this->whenLoaded('irrigation', fn () => [
                'water_volume' => $this->irrigation->water_volume,
                'irrigation_type' => $this->irrigation->irrigation_type,
            ]),
            'harvest' => $this->whenLoaded('harvest', fn () => [
                'quantity' => $this->harvest->quantity_kg,
                'quality' => $this->harvest->quality_rating,
            ]),
            default => null,
        };
    }
}
