<?php

namespace App\Http\Requests\Api\Winery;

class UpdateContainerRoomRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'description' => 'sometimes|nullable|string|max:500',
            'temperature' => 'sometimes|nullable|numeric|between:-10,40',
            'humidity' => 'sometimes|nullable|numeric|between:0,100',
            'capacity' => 'sometimes|nullable|integer|min:1',
        ];
    }
}
