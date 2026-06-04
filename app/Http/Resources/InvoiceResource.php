<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'delivery_note_code' => $this->delivery_note_code,
            'invoice_date' => $this->invoice_date->toDateString(),
            'delivery_note_date' => $this->delivery_note_date?->toDateString(),
            'status' => $this->status,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'nif_cif' => $this->client->nif_cif,
            ]),
            'totals' => [
                'subtotal' => (float) $this->subtotal,
                'tax_total' => (float) $this->tax_total,
                'total' => (float) $this->total,
            ],
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'observations' => $this->observations,
            'observations_invoice' => $this->observations_invoice,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
