<?php

namespace App\Http\Requests\Api\Winery;

use Illuminate\Validation\Rule;

class StoreWineSaleInvoiceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->where('user_id', $this->user()->id)],
            'invoice_number' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'invoice_type' => 'nullable|string|in:standard,corrective,receipt',
            'status' => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_type' => 'nullable|string|in:transfer,cash,card,check,other',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'total_amount' => 'nullable|numeric|min:0',
            'gift' => 'nullable|boolean',
            'observations' => 'nullable|string|max:2000',
        ];
    }
}
