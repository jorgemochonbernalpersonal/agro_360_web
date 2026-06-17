<?php

namespace App\Http\Resources\Api;

use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Plot */
class PlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'area' => (float) $this->area,
            'is_organic' => (bool) $this->is_organic,
            'active' => (bool) $this->active,
            'province' => $this->whenLoaded('province', fn () => [
                'id' => $this->province->id,
                'name' => $this->province->name,
            ]),
            'municipality' => $this->whenLoaded('municipality', fn () => [
                'id' => $this->municipality->id,
                'name' => $this->municipality->name,
            ]),
            'plantings' => $this->whenLoaded('plantings', fn () => $this->plantings->map(fn ($p) => [
                'id' => $p->id,
                'grape_variety' => $p->grapeVariety?->name,
                'area' => (float) $p->area,
                'planting_year' => $p->planting_year,
            ])
            ),
            'has_geometry' => (bool) ($this->has_geometry ?? false),
            'centroid' => $this->centroid_data ?? null,
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
