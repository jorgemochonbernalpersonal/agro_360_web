<?php

namespace App\Http\Requests\Api\Winery;

class IndexContainerRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'room_id' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:empty,full,critical',
            'unit' => 'nullable|string|in:kg,litros',
            'per_page' => 'nullable|string|max:10',
        ];
    }
}
