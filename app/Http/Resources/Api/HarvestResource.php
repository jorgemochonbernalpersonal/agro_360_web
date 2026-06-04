<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'harvest_start_date' => $this->harvest_start_date?->toDateString(),
            'vintage' => $this->vintage,
            'total_weight' => (float) $this->total_weight,
            'status' => $this->status,
            'disqualified' => (bool) $this->disqualified,
            'baume_degree' => $this->baume_degree !== null ? (float) $this->baume_degree : null,
            'brix_degree' => $this->brix_degree !== null ? (float) $this->brix_degree : null,
            'ph_level' => $this->ph_level !== null ? (float) $this->ph_level : null,
            'acidity_level' => $this->acidity_level !== null ? (float) $this->acidity_level : null,
            'price_per_kg' => $this->price_per_kg !== null ? (float) $this->price_per_kg : null,
            'notes' => $this->notes,
            'viticulturist' => $this->whenLoaded('batch', fn () => $this->batch?->viticulturist ? [
                'id' => $this->batch->viticulturist->id,
                'name' => $this->batch->viticulturist->name,
            ] : null
            ),
            'grape_variety' => $this->whenLoaded('plotPlanting', fn () => $this->plotPlanting?->grapeVariety?->name
            ),
            'container_id' => $this->container_id,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
