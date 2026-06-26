<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait WithProductSaleFormRules
{
    protected function productSaleBaseRules(bool $allowArchivedLots = false): array
    {
        return [
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! \App\Models\Client::where('id', $value)
                        ->where('user_id', Auth::id())
                        ->exists()) {
                        $fail(__('El cliente seleccionado no es válido.'));
                    }
                },
            ],
            'payment_type' => 'nullable|in:cash,transfer,check,other',
            'observations' => 'nullable|string',
            'observations_invoice' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.wine_lot_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($allowArchivedLots) {
                    if ($value) {
                        $query = \App\Models\ProductLot::where('id', $value)
                            ->where('user_id', Auth::id());
                        if (! $allowArchivedLots) {
                            $query->where('archived', false);
                        }
                        if (! $query->exists()) {
                            $fail(__('El lote de vino seleccionado no es válido.'));
                        }
                    }
                },
            ],
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.sku' => 'nullable|string|max:100',
        ];
    }
}
