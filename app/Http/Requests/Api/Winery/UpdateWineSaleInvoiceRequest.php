<?php

namespace App\Http\Requests\Api\Winery;

use Illuminate\Validation\Rule;

class UpdateWineSaleInvoiceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'required', 'integer', Rule::exists('clients', 'id')->where('user_id', $this->user()->id)],
            // invoice_number/subtotal/tax_amount/total_amount NO son client-writable:
            // el número se asigna al emitir y los importes se recalculan server-side
            // desde las líneas de la factura (ver auditoría fix #5).
            'invoice_date' => 'nullable|date',
            'invoice_type' => 'nullable|string|in:standard,corrective,receipt',
            'status' => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,partial,paid,overdue',
            'payment_type' => 'nullable|string|in:transfer,cash,card,check,other',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'gift' => 'nullable|boolean',
            'observations' => 'nullable|string|max:2000',
        ];
    }
}
