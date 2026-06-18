<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\WineBottling;

class UpdateBottlingRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'container_id' => 'sometimes|nullable|integer|exists:containers,id',
            'product_lot_id' => 'sometimes|nullable|integer|exists:wine_lots,id',
            'oenologist_id' => 'sometimes|nullable|integer|exists:oenologists,id',
            'bottling_date' => 'sometimes|date',
            'bottle_format' => 'sometimes|string|in:'.implode(',', array_keys(WineBottling::BOTTLE_FORMATS)),
            'quantity_bottles' => 'sometimes|integer|min:1',
            'quantity_liters' => 'sometimes|numeric|min:0',
            'lot_number' => 'sometimes|nullable|string|max:100',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
