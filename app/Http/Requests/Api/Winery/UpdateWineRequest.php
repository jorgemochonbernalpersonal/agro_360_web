<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\Wine;

class UpdateWineRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'vintage' => 'sometimes|nullable|integer|min:1900|max:'.(now()->year + 2),
            'wine_type' => 'sometimes|string|in:'.implode(',', array_keys(Wine::WINE_TYPES)),
            'aging_type' => 'sometimes|nullable|string|in:'.implode(',', array_keys(Wine::AGING_TYPES)),
            'category' => 'sometimes|nullable|string|in:'.implode(',', array_keys(Wine::CATEGORIES)),
            'status' => 'sometimes|string|in:'.implode(',', array_keys(Wine::STATUSES)),
            'variety' => 'sometimes|nullable|string|max:255',
            'volume_liters' => 'sometimes|nullable|numeric|min:0',
            'internal_code' => 'sometimes|nullable|string|max:100',
            'is_must' => 'sometimes|nullable|boolean',
            'is_organic' => 'sometimes|nullable|boolean',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
