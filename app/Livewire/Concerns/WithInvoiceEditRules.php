<?php

namespace App\Livewire\Concerns;

use App\Rules\InvoiceStateCoherence;

trait WithInvoiceEditRules
{
    protected function invoiceLockedRules(): array
    {
        return [
            'payment_status' => [
                'required',
                'in:unpaid,paid,overdue,refunded',
                new InvoiceStateCoherence(
                    $this->invoice->status,
                    request()->input('payment_status'),
                    $this->delivery_status
                ),
            ],
        ];
    }

    protected function invoiceUnlockedBaseRules(string $conceptTypes): array
    {
        return [
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Models\Client::where('id', $value)
                        ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                        ->exists()) {
                        $fail(__('El cliente seleccionado no es válido.'));
                    }
                },
            ],
            'client_address_id' => 'required|exists:client_addresses,id',
            'invoice_date' => 'nullable|date',
            'delivery_note_date' => 'nullable|date',
            'delivery_status' => [
                'required',
                'in:pending,in_transit,delivered,cancelled',
                new InvoiceStateCoherence(
                    $this->invoice->status,
                    $this->payment_status,
                    request()->input('delivery_status')
                ),
            ],
            'payment_status' => [
                'required',
                'in:unpaid,paid,overdue,refunded',
                new InvoiceStateCoherence(
                    $this->invoice->status,
                    request()->input('payment_status'),
                    $this->delivery_status
                ),
            ],
            'observations' => 'nullable|string',
            'observations_invoice' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.sku' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.concept_type' => "nullable|in:{$conceptTypes}",
        ];
    }
}
