<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\Wine;

class StoreWineRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'vintage' => 'required|integer|min:1900|max:'.(now()->year + 2),
            'wine_type' => 'required|string|in:'.implode(',', array_keys(Wine::WINE_TYPES)),
            'aging_type' => 'nullable|string|in:'.implode(',', array_keys(Wine::AGING_TYPES)),
            'category' => 'nullable|string|in:'.implode(',', array_keys(Wine::CATEGORIES)),
            'variety' => 'nullable|string|max:255',
            'volume_liters' => 'required|numeric|min:0.001',
            'initial_quantity_kg' => 'nullable|numeric|min:0',
            'internal_code' => 'nullable|string|max:100',
            'is_must' => 'nullable|boolean',
            'is_organic' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
