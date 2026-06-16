<?php

namespace App\Http\Resources\Api;

use App\Models\ResidueManagement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ResidueManagement */
class ResidueManagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'plot_id' => $this->plot_id,
            'date' => $this->date?->toDateString(),
            'practice_type' => $this->practice_type,
            'material_type' => $this->material_type,
            'estimated_quantity' => $this->estimated_quantity !== null ? (float) $this->estimated_quantity : null,
            'quantity_unit' => $this->quantity_unit,
            'justification' => $this->justification,
            'notes' => $this->notes,
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
        ];
    }
}
