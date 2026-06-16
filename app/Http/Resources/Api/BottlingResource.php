<?php

namespace App\Http\Resources\Api;

use App\Models\WineBottling;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WineBottling */
class BottlingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'wine_name' => $this->wine?->name,
            'container_name' => $this->container?->name,
            'product_lot_id' => $this->product_lot_id,
            'wine' => $this->whenLoaded('wine', fn () => [
                'id' => $this->wine->id,
                'name' => $this->wine->name,
            ]),
            'container' => $this->whenLoaded('container', fn () => $this->container ? [
                'id' => $this->container->id,
                'name' => $this->container->name,
            ] : null),
            'product_lot' => $this->whenLoaded('productLot', fn () => $this->productLot ? [
                'id' => $this->productLot->id,
                'name' => $this->productLot->name,
            ] : null),
            'oenologist' => $this->whenLoaded('oenologist', fn () => $this->oenologist ? [
                'id' => $this->oenologist->id,
                'name' => $this->oenologist->name,
            ] : null),
            'bottling_date' => $this->bottling_date->toDateString(),
            'bottle_format' => $this->bottle_format,
            'bottle_format_label' => $this->format_label,
            'quantity_bottles' => $this->quantity_bottles,
            'quantity_liters' => $this->quantity_liters !== null ? (float) $this->quantity_liters : null,
            'lot_number' => $this->lot_number,
            'notes' => $this->notes,
            'supplies' => $this->whenLoaded('supplies', fn () => $this->supplies->map(fn ($s) => [
                'id' => $s->id,
                'supply_name' => $s->supply_name,
                'quantity' => $s->quantity !== null ? (float) $s->quantity : null,
                'unit_id' => $s->unit_of_measurement_id,
                'winery_supply_id' => $s->winery_supply_id,
            ])
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
