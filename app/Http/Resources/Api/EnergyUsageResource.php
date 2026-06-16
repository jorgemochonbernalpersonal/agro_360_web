<?php

namespace App\Http\Resources\Api;

use App\Models\EnergyUsage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EnergyUsage */
class EnergyUsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'activity_id' => $this->activity_id,
            'machinery_id' => $this->machinery_id,
            'date' => $this->date->toDateString(),
            'energy_type' => $this->energy_type,
            'unit' => $this->unit,
            'quantity' => (float) $this->quantity,
            'cost_per_unit' => $this->cost_per_unit !== null ? (float) $this->cost_per_unit : null,
            'total_cost' => $this->total_cost !== null ? (float) $this->total_cost : null,
            'co2_kg_equivalent' => $this->co2_kg_equivalent !== null ? (float) $this->co2_kg_equivalent : null,
            'usage_description' => $this->usage_description,
            'notes' => $this->notes,
            'machinery_name' => $this->whenLoaded('machinery', fn () => $this->machinery->name),
        ];
    }
}
