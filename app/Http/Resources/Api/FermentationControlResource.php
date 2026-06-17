<?php

namespace App\Http\Resources\Api;

use App\Models\WineFermentationControl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WineFermentationControl */
class FermentationControlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'wine_name' => $this->wine?->name,
            'container_id' => $this->container_id,
            'container_name' => $this->container?->name,
            'control_date' => $this->control_date->toIso8601String(),
            'temperature' => $this->temperature !== null ? (float) $this->temperature : null,
            'brix_degree' => $this->brix_degree !== null ? (float) $this->brix_degree : null,
            'baume_degree' => $this->baume_degree !== null ? (float) $this->baume_degree : null,
            'density' => $this->density !== null ? (float) $this->density : null,
            'ph' => $this->ph !== null ? (float) $this->ph : null,
            'volatile_acidity' => $this->volatile_acidity !== null ? (float) $this->volatile_acidity : null,
            'is_fermenting' => $this->isFermenting(),
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
