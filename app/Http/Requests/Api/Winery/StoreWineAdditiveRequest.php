<?php

namespace App\Http\Requests\Api\Winery;

class StoreWineAdditiveRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'required|integer|exists:wines,id',
            'wine_process_detail_id' => 'nullable|integer|exists:wine_process_details,id',
            'winery_supply_id' => 'nullable|integer|exists:winery_supplies,id',
            'oenologist_id' => 'nullable|integer|exists:oenologists,id',
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'additive_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'application_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
