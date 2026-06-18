<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\WinerySupply;

class StoreWinerySupplyRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'supply_type' => 'required|string|in:'.implode(',', array_keys(WinerySupply::SUPPLY_TYPES)),
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
