<?php

namespace App\Http\Requests\Api\Winery;

class UpdateGrapePurchaseInvoiceItemRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:500',
            'quantity' => 'sometimes|numeric|min:0.001',
            'unit' => 'sometimes|string|max:20',
            'unit_price' => 'sometimes|numeric|min:0',
            'discount_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'tax_rate' => 'sometimes|nullable|numeric|min:0|max:100',
        ];
    }
}
