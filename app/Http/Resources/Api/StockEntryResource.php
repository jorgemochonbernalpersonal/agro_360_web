<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'wine_name' => $this->wine?->name,
            'container_id' => $this->container_id,
            'container_name' => $this->container?->name,
            'quantity_liters' => $this->quantity_liters !== null ? (float) $this->quantity_liters : null,
            'entry_date' => $this->entry_date?->toDateString(),
            'source' => $this->source,
            'source_label' => $this->source_label,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
