<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaterConcessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'concession_type' => \App\Models\WaterConcession::CONCESSION_TYPES[$this->concession_type] ?? $this->concession_type,
            'concession_number' => $this->concession_number,
            'water_body' => $this->water_body,
            'authority' => $this->authority,
            'concession_date' => $this->concession_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'max_volume_m3' => $this->max_volume_m3 !== null ? (float) $this->max_volume_m3 : null,
            'used_volume_m3' => $this->used_volume_m3 !== null ? (float) $this->used_volume_m3 : null,
            'surface_ha' => $this->surface_ha !== null ? (float) $this->surface_ha : null,
            'notes' => $this->notes,
            'is_expired' => (bool) $this->is_expired,
            'is_expiring_soon' => (bool) $this->is_expiring_soon,
        ];
    }
}
