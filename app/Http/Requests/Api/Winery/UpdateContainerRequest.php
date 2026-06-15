<?php

namespace App\Http\Requests\Api\Winery;

class UpdateContainerRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'container_room_id' => 'nullable|integer|exists:container_rooms,id',
        ];
    }
}
