<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\Harvest;
use Illuminate\Validation\Validator;

class StoreGrapePurchaseInvoiceItemRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'harvest_id' => 'nullable|integer|exists:harvests,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('harvest_id')) {
                return;
            }

            $invalid = Harvest::where('id', $this->input('harvest_id'))
                ->where('winery_id', '!=', $this->user()->id)
                ->exists();

            if ($invalid) {
                $validator->errors()->add('harvest_id', __('La recepción de uva no pertenece a esta bodega.'));
            }
        });
    }
}
