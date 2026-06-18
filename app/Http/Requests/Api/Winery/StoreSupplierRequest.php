<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\Supplier;

class StoreSupplierRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'vat_number' => 'nullable|string|max:30',
            'category' => 'nullable|string|in:'.implode(',', array_keys(Supplier::CATEGORIES)),
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
