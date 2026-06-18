<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\Supplier;

class UpdateSupplierRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|nullable|string|max:150',
            'email' => 'sometimes|nullable|email|max:150',
            'phone' => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string|max:500',
            'vat_number' => 'sometimes|nullable|string|max:30',
            'category' => 'sometimes|nullable|string|in:'.implode(',', array_keys(Supplier::CATEGORIES)),
            'active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}
