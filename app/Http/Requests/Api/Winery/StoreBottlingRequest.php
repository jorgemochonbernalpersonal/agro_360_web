<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\WineBottling;

class StoreBottlingRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'required|integer|exists:wines,id',
            'container_id' => 'nullable|integer|exists:containers,id',
            'wine_process_detail_id' => 'nullable|integer|exists:wine_process_details,id',
            'product_lot_id' => 'nullable|integer|exists:wine_lots,id',
            'oenologist_id' => 'nullable|integer|exists:oenologists,id',
            'bottling_date' => 'required|date',
            'bottle_format' => 'required|string|in:'.implode(',', array_keys(WineBottling::BOTTLE_FORMATS)),
            'quantity_bottles' => 'required|integer|min:1',
            'quantity_liters' => 'required|numeric|min:0',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'supplies' => 'nullable|array|max:20',
            'supplies.*.winery_supply_id' => 'nullable|integer|exists:winery_supplies,id',
            'supplies.*.supply_name' => 'required_without:supplies.*.winery_supply_id|string|max:255',
            'supplies.*.quantity' => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'supplies.*.notes' => 'nullable|string|max:500',
        ];
    }
}
