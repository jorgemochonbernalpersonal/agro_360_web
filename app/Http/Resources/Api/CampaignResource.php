<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'year'       => $this->year,
            'active'     => (bool) $this->active,
            'start_date' => $this->start_date?->toDateString(),
            'end_date'   => $this->end_date?->toDateString(),
            'locked'     => $this->locked_at !== null,
            'locked_at'  => $this->locked_at?->toIso8601String(),
            'mid_validation_signed'   => (bool) $this->mid_validation_signed,
            'final_validation_signed' => (bool) $this->final_validation_signed,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
