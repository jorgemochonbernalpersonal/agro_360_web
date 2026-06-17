<?php

namespace App\Http\Resources\Api;

use App\Models\WineAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WineAnalysis */
class WineAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'wine_name' => $this->wine?->name,
            'container_id' => $this->container_id,
            'container_name' => $this->container?->name,
            'analysis_date' => $this->analysis_date->toDateString(),
            'analysis_type' => $this->analysis_type,
            'laboratory' => (bool) $this->laboratory,
            'laboratory_name' => $this->laboratory_name,
            'alcoholic_strength' => $this->alcoholic_strength !== null ? (float) $this->alcoholic_strength : null,
            'alcohol' => $this->alcohol !== null ? (float) $this->alcohol : null,
            'total_acidity' => $this->total_acidity !== null ? (float) $this->total_acidity : null,
            'volatile_acidity' => $this->volatile_acidity !== null ? (float) $this->volatile_acidity : null,
            'residual_sugar' => $this->residual_sugar !== null ? (float) $this->residual_sugar : null,
            'ph' => $this->ph !== null ? (float) $this->ph : null,
            'density' => $this->density !== null ? (float) $this->density : null,
            'free_so2' => $this->free_so2 !== null ? (float) $this->free_so2 : null,
            'total_so2' => $this->total_so2 !== null ? (float) $this->total_so2 : null,
            'result' => $this->result,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
