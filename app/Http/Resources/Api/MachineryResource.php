<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MachineryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'year' => $this->year !== null ? (int) $this->year : null,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'purchase_price' => $this->purchase_price !== null ? (float) $this->purchase_price : null,
            'current_value' => $this->current_value !== null ? (float) $this->current_value : null,
            'roma_registration' => $this->roma_registration,
            'is_rented' => (bool) $this->is_rented,
            'capacity' => $this->capacity,
            'last_revision_date' => $this->last_revision_date?->toDateString(),
            'notes' => $this->notes,
            'activities_count' => $this->whenCounted('activities'),
        ];
    }
}
