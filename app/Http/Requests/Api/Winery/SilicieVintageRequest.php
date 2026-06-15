<?php

namespace App\Http\Requests\Api\Winery;

class SilicieVintageRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'vintage' => 'nullable|integer|min:1990|max:'.(now()->year + 1),
        ];
    }
}
