<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\WineProcessDetail;

class StoreWineProcessStepRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'process_type' => 'required|string|in:'.implode(',', array_keys(WineProcessDetail::PROCESS_TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'container_id' => 'nullable|integer|exists:containers,id',
            'oenologist_id' => 'nullable|integer|exists:oenologists,id',
            'quantity' => 'nullable|numeric|min:0',
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'observations' => 'nullable|string|max:2000',
            'extra_containers' => 'nullable|array|max:10',
            'extra_containers.*.container_id' => 'required|integer|exists:containers,id',
            'extra_containers.*.quantity' => 'nullable|numeric|min:0',
            'extra_containers.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
        ];
    }
}
