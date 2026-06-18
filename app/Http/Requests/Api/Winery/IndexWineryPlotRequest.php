<?php

namespace App\Http\Requests\Api\Winery;

class IndexWineryPlotRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',
            'viticulturist' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:100',
        ];
    }
}
