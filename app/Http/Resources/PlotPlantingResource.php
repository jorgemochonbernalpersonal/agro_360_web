<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlotPlantingResource extends JsonResource
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
            'name' => $this->name,
            'planted_area' => (float) $this->planted_area,
            'plant_count' => $this->plant_count,
            'plant_spacing' => (float) $this->plant_spacing,
            'row_spacing' => (float) $this->row_spacing,
            'planting_date' => $this->planting_date?->toDateString(),
            'grape_variety' => $this->whenLoaded('grapeVariety', fn() => [
                'id' => $this->grapeVariety->id,
                'name' => $this->grapeVariety->name,
            ]),
            'training_system' => $this->whenLoaded('trainingSystem', fn() => [
                'id' => $this->trainingSystem->id,
                'name' => $this->trainingSystem->name,
            ]),
            'certification' => $this->whenLoaded('certification', fn() => [
                'id' => $this->certification->id,
                'name' => $this->certification->name,
            ]),
        ];
    }
}
