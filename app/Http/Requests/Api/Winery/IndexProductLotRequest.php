<?php

namespace App\Http\Requests\Api\Winery;

class IndexProductLotRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'nullable|integer',
            'unit' => 'nullable|string|in:litros,botellas,cajas',
            'archived' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
