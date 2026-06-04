<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'wine_name' => $this->wine?->name,
            'from_container_id' => $this->from_container_id,
            'from_container_name' => $this->fromContainer?->name,
            'to_container_id' => $this->to_container_id,
            'to_container_name' => $this->toContainer?->name,
            'quantity' => $this->quantity !== null ? (float) $this->quantity : null,
            'unit_of_measurement_id' => $this->unit_of_measurement_id,
            'unit' => $this->unitOfMeasurement ? [
                'id' => $this->unitOfMeasurement->id,
                'name' => $this->unitOfMeasurement->name,
                'symbol' => $this->unitOfMeasurement->symbol ?? null,
            ] : null,
            'transfer_type' => $this->transfer_type,
            'transfer_date' => $this->transfer_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
