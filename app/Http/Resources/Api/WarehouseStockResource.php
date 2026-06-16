<?php

namespace App\Http\Resources\Api;

use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductStock */
class WarehouseStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'name' => $this->whenLoaded('warehouse', fn () => $this->warehouse->name),
            'product_name' => $this->product_name,
            'product_type' => $this->product_type,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'min_stock' => $this->min_stock !== null ? (float) $this->min_stock : null,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'notes' => $this->notes,
        ];
    }
}
