<?php

namespace App\Http\Requests\Api\Winery;

class StoreContainerRoomRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'temperature' => 'nullable|numeric|between:-10,40',
            'humidity' => 'nullable|numeric|between:0,100',
            'capacity' => 'nullable|integer|min:1',
        ];
    }
}
