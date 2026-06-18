<?php

namespace App\Http\Requests\Api\Winery;

class StoreTransferRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'required|integer|exists:wines,id',
            'from_container_id' => 'required|integer|exists:containers,id',
            'to_container_id' => 'required|integer|exists:containers,id|different:from_container_id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_of_measurement_id' => 'nullable|integer|exists:units_of_measurement,id',
            'transfer_type' => 'required|string|in:racking,blending,top_up,other',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
