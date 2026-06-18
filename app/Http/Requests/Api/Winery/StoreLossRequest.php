<?php

namespace App\Http\Requests\Api\Winery;

class StoreLossRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'required|integer|exists:wines,id',
            'container_id' => 'required|integer|exists:containers,id',
            'loss_type' => 'required|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization' => 'required|string|in:authorized,processing,extraordinary,quality',
            'unit_of_measurement_id' => 'required|integer|exists:units_of_measurement,id',
            'quantity' => 'required|numeric|min:0.001',
            'loss_date' => 'required|date',
            'regulatory_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
