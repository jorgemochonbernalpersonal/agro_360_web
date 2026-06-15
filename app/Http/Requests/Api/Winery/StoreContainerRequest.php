<?php

namespace App\Http\Requests\Api\Winery;

class StoreContainerRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'capacity' => 'required|numeric|min:0.1',
            'type_id' => 'nullable|integer|exists:container_types,id',
            'material_id' => 'nullable|integer|exists:container_materials,id',
            'container_room_id' => 'nullable|integer|exists:container_rooms,id',
            'serial_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
