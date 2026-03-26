<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'serial_number'    => $this->serial_number,
            'capacity'         => (float) $this->capacity,
            'used_capacity'    => (float) $this->used_capacity,
            'wine_volume_liters' => (float) $this->wine_volume_liters,
            'occupancy_pct'    => (float) $this->getOccupancyPercentage(),
            'available_capacity' => (float) $this->getAvailableCapacity(),
            'is_empty'         => $this->isEmpty(),
            'is_full'          => $this->isFull(),
            'archived'         => (bool) $this->archived,
            'type'             => $this->whenLoaded('containerType', fn () => [
                'id'   => $this->containerType->id,
                'name' => $this->containerType->name,
            ]),
            'material'         => $this->whenLoaded('containerMaterial', fn () => [
                'id'   => $this->containerMaterial->id,
                'name' => $this->containerMaterial->name,
            ]),
            'room'             => $this->whenLoaded('containerRoom', fn () => [
                'id'   => $this->containerRoom->id,
                'name' => $this->containerRoom->name,
            ]),
            'current_wines'    => $this->whenLoaded('currentStates', fn () =>
                $this->currentStates->map(fn ($s) => [
                    'wine_id'   => $s->wine_id,
                    'wine_name' => $s->wine?->name,
                    'volume'    => (float) $s->volume_liters,
                ])
            ),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
