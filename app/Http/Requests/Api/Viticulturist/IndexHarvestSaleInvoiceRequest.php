<?php

namespace App\Http\Requests\Api\Viticulturist;

class IndexHarvestSaleInvoiceRequest extends ViticulturistApiRequest
{
    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,paid,overdue',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
