<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LossResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'wine_id'            => $this->wine_id,
            'wine_name'          => $this->wine?->name,
            'container_id'       => $this->container_id,
            'container_name'     => $this->container?->name,
            'loss_type'          => $this->loss_type,
            'loss_authorization' => $this->loss_authorization,
            'quantity'           => $this->quantity !== null ? (float) $this->quantity : null,
            'loss_date'          => $this->loss_date?->toDateString(),
            'notes'              => $this->notes,
            'created_at'         => $this->created_at->toIso8601String(),
        ];
    }
}
