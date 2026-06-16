<?php

namespace App\Http\Resources\Api;

use App\Models\FertilizationPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FertilizationPlan */
class FertilizationPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'plan_year' => $this->plan_year,
            'nitrate_zone' => $this->nitrate_zone,
            'prepared_by' => $this->prepared_by,
            'approval_date' => $this->approval_date?->toDateString(),
            'total_surface_ha' => $this->total_surface_ha !== null ? (float) $this->total_surface_ha : null,
            'total_n_kg_ha' => $this->total_n_kg_ha !== null ? (float) $this->total_n_kg_ha : null,
            'total_p_kg_ha' => $this->total_p_kg_ha !== null ? (float) $this->total_p_kg_ha : null,
            'total_k_kg_ha' => $this->total_k_kg_ha !== null ? (float) $this->total_k_kg_ha : null,
            'plan_lines' => (array) $this->plan_lines,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
