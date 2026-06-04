<?php

namespace App\Http\Requests\Api\Winery;

class UpdateGrapePurchaseInvoiceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'invoice_number' => 'sometimes|nullable|string|max:50',
            'invoice_date' => 'sometimes|date',
            'payment_date' => 'sometimes|nullable|date',
            'payment_type' => 'sometimes|nullable|string|in:transfer,cash,card,check,other',
            'bank_name' => 'sometimes|nullable|string|max:100',
            'bank_account_number' => 'sometimes|nullable|string|max:50',
            'payment_details' => 'sometimes|nullable|string|max:500',
            'observations' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
