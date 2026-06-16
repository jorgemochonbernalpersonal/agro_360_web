<?php

namespace App\Http\Resources\Api;

use App\Models\PhytosanitaryProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PhytosanitaryProduct */
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
            'withdrawal_period_days' => (int) $this->withdrawal_period_days,
            'description' => $this->description,
        ];
    }
}
