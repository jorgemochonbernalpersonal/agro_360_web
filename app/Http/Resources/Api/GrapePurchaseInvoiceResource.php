<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrapePurchaseInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'payment_date' => $this->payment_date?->toDateString(),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type,
            // Viticultor pagado
            'viticulturist_id' => $this->viticulturist_id,
            'viticulturist_name' => $this->viticulturist?->name,
            'notes' => $this->observations,
            'viticulturist' => $this->whenLoaded('viticulturist', fn () => $this->viticulturist ? [
                'id' => $this->viticulturist->id,
                'name' => $this->viticulturist->name,
                'email' => $this->viticulturist->email,
            ] : null),
            // Totales
            'subtotal' => $this->subtotal !== null ? (float) $this->subtotal : null,
            'tax_amount' => $this->tax_amount !== null ? (float) $this->tax_amount : null,
            'total_amount' => $this->total_amount !== null ? (float) $this->total_amount : null,
            'discount_amount' => $this->discount_amount !== null ? (float) $this->discount_amount : null,
            // Banco / pago
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'payment_details' => $this->payment_details,
            // Items (líneas de la liquidación)
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'harvest_id' => $item->harvest_id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'discount_percentage' => (float) $item->discount_percentage,
                'discount_amount' => (float) $item->discount_amount,
                'tax_rate' => (float) $item->tax_rate,
                'tax_amount' => (float) $item->tax_amount,
                'subtotal' => (float) $item->subtotal,
                'total' => (float) $item->total,
            ])
            ),
            'observations' => $this->observations,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
