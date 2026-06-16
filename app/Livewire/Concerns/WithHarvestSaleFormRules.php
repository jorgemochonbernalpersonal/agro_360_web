<?php

namespace App\Livewire\Concerns;

trait WithHarvestSaleFormRules
{
    protected function rules(): array
    {
        return [
            'buyer_name'           => 'required|string|max:255',
            'buyer_rega_code'      => 'nullable|string|max:30',
            'destination_type'     => 'required|in:own_winery,cooperative,third_party,other',
            'transport_document'   => 'nullable|string|max:50',
            'vehicle_plate'        => 'nullable|string|max:15',
            'delivery_date'        => 'required|date',
            'invoice_date'         => 'required|date',
            'payment_type'         => 'nullable|in:cash,transfer,check,other',
            'observations'         => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.harvest_id'   => 'required|exists:harvests,id',
            'lines.*.quantity'     => 'required|numeric|min:0.001',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'lines.*.tax_rate'     => 'required|numeric|min:0|max:100',
            'lines.*.description'  => 'nullable|string|max:255',
        ];
    }
}
