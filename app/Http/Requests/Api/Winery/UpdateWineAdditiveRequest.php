<?php

namespace App\Http\Requests\Api\Winery;

class UpdateWineAdditiveRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'winery_supply_id' => 'sometimes|nullable|integer|exists:winery_supplies,id',
            'oenologist_id' => 'sometimes|nullable|integer|exists:oenologists,id',
            'unit_of_measurement_id' => 'sometimes|nullable|integer|exists:unit_of_measurements,id',
            'additive_name' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|numeric|min:0',
            'application_date' => 'sometimes|date',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}
