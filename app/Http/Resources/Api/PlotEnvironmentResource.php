<?php

namespace App\Http\Resources\Api;

use App\Models\PlotEnvironment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlotEnvironment */
class PlotEnvironmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'plot_id' => $this->plot_id,
            'water_intake_nearby' => (bool) $this->water_intake_nearby,
            'water_intake_distance_m' => $this->water_intake_distance_m !== null ? (int) $this->water_intake_distance_m : null,
            'protected_zone_total' => (bool) $this->protected_zone_total,
            'protected_zone_partial' => (bool) $this->protected_zone_partial,
            'protection_zone_type' => $this->protection_zone_type,
            'buffer_zone_m' => $this->buffer_zone_m !== null ? (int) $this->buffer_zone_m : null,
            'slope_pct' => $this->slope_pct !== null ? (float) $this->slope_pct : null,
            'erosion_risk' => $this->erosion_risk,
            'notes' => $this->notes,
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
        ];
    }
}
