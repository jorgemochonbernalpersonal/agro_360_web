<?php

namespace App\Http\Resources\Api;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $clientName = $this->billing_company_name
            ?: trim("{$this->billing_first_name} {$this->billing_last_name}")
            ?: $this->client?->full_name;

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type,
            'subtotal' => (float) $this->subtotal,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'client_id' => $this->client_id,
            'client_name' => $clientName,
            'invoice_type' => $this->invoice_type,
            'gift' => (bool) $this->gift,
            'notes' => $this->observations,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
