<?php

namespace App\Livewire\Concerns;

trait WithGrapePurchaseFormRules
{
    protected function grapePurchaseBaseRules(): array
    {
        return [
            'invoice_date'         => 'required|date',
            'payment_type'         => 'nullable|in:cash,transfer,check,other',
            'observations'         => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.quantity'     => 'required|numeric|min:0.001',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'lines.*.tax_rate'     => 'required|numeric|min:0|max:100',
            'lines.*.description'  => 'nullable|string|max:255',
        ];
    }
}
