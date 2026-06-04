<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WineProcessStepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'process_type' => $this->process_type,
            'process_type_label' => $this->process_type_label,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_completed' => $this->isCompleted(),
            'quantity' => $this->quantity !== null ? (float) $this->quantity : null,
            'unit' => $this->whenLoaded('unitOfMeasurement', fn () => [
                'id' => $this->unitOfMeasurement->id,
                'name' => $this->unitOfMeasurement->name,
                'symbol' => $this->unitOfMeasurement->symbol ?? null,
            ]),
            'container' => $this->whenLoaded('container', fn () => $this->container ? [
                'id' => $this->container->id,
                'name' => $this->container->name,
            ] : null),
            'extra_containers' => $this->whenLoaded('containers', fn () => $this->containers->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'quantity' => $c->pivot->quantity !== null ? (float) $c->pivot->quantity : null,
                'unit_id' => $c->pivot->unit_of_measurement_id,
            ])
            ),
            'oenologist' => $this->whenLoaded('oenologist', fn () => $this->oenologist ? [
                'id' => $this->oenologist->id,
                'name' => $this->oenologist->name,
            ] : null),
            'observations' => $this->observations,
            'step_type' => $this->process_type,
            'step_date' => $this->start_date?->toDateString(),
            'description' => $this->observations,
            'notes' => $this->observations,
            'completed_at' => $this->isCompleted() ? $this->updated_at?->toIso8601String() : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
