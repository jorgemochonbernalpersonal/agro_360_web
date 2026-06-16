<?php

namespace App\Http\Resources\Api;

use App\Models\PhytosanitaryAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PhytosanitaryAlert */
class PhytosanitaryAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'source' => $this->source,
            'alert_type' => $this->alert_type,
            'severity' => $this->severity,
            'affected_area' => $this->affected_area,
            'description' => $this->description,
            'recommendations' => $this->recommendations,
            'alert_date' => $this->alert_date->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_expired' => (bool) $this->is_expired,
        ];
    }
}
