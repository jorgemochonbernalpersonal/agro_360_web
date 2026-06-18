<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\WinerySupply;

class UpdateWinerySupplyRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'commercial_name' => 'sometimes|nullable|string|max:255',
            'supply_type' => 'sometimes|string|in:'.implode(',', array_keys(WinerySupply::SUPPLY_TYPES)),
            'unit_of_measurement_id' => 'sometimes|nullable|integer|exists:unit_of_measurements,id',
            'current_stock' => 'sometimes|nullable|numeric|min:0',
            'min_stock_alert' => 'sometimes|nullable|numeric|min:0',
            'expiry_date' => 'sometimes|nullable|date',
            'active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}
