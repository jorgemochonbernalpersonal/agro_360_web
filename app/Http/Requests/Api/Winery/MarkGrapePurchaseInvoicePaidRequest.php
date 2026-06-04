<?php

namespace App\Http\Requests\Api\Winery;

class MarkGrapePurchaseInvoicePaidRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'payment_date' => 'nullable|date',
            'payment_type' => 'nullable|string|in:transfer,cash,card,check,other',
        ];
    }
}
