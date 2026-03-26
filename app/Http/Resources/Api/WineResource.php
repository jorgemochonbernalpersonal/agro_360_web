<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'vintage'             => $this->vintage,
            'wine_type'           => $this->wine_type,
            'aging_type'          => $this->aging_type,
            'category'            => $this->category,
            'status'              => $this->status,
            'variety'             => $this->variety,
            'volume_liters'       => (float) $this->volume_liters,
            'initial_quantity_kg' => (float) $this->initial_quantity_kg,
            'internal_code'       => $this->internal_code,
            'trace_token'         => $this->trace_token,
            'is_must'             => (bool) $this->is_must,
            'is_organic'          => (bool) $this->is_organic,
            'notes'               => $this->notes,
            'latest_fermentation' => $this->whenLoaded('fermentationControls', fn () =>
                $this->fermentationControls->sortByDesc('control_date')->first()
                    ? new FermentationControlResource(
                        $this->fermentationControls->sortByDesc('control_date')->first()
                    )
                    : null
            ),
            'active_containers'   => $this->whenLoaded('currentStates', fn () =>
                $this->currentStates->map(fn ($s) => [
                    'container_id'   => $s->container_id,
                    'container_name' => $s->container?->name,
                    'volume_liters'  => (float) $s->volume_liters,
                ])
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
