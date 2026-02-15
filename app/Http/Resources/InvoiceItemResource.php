<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'tax' => [
                'id' => $this->tax_id,
                'rate' => (float) $this->tax_rate,
                'amount' => (float) $this->tax_amount,
            ],
            'total' => (float) $this->total,
            'harvest' => $this->whenLoaded('harvest', fn() => [
                'id' => $this->harvest->id,
                'quantity_kg' => $this->harvest->quantity_kg,
            ]),
        ];
    }
}
