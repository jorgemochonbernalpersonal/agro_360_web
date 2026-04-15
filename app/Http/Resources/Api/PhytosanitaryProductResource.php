<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhytosanitaryProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'active_ingredient' => $this->active_ingredient,
            'registration_number' => $this->registration_number,
            'registration_expiry_date' => $this->registration_expiry_date?->toDateString(),
            'registration_status' => $this->registration_status,
            'manufacturer' => $this->manufacturer,
            'type' => $this->type,
            'toxicity_class' => $this->toxicity_class,
            'withdrawal_period_days' => $this->withdrawal_period_days !== null ? (int) $this->withdrawal_period_days : null,
            'description' => $this->description,
        ];
    }
}
