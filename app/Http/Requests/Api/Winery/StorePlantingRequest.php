<?php

namespace App\Http\Requests\Api\Winery;

class StorePlantingRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'grape_variety_id' => 'required|integer|exists:grape_varieties,id',
            'area_planted' => 'required|numeric|min:0.001',
            'planting_year' => 'required|integer|min:1900|max:2100',
            'name' => 'nullable|string|max:255',
            'irrigated' => 'nullable|boolean',
        ];
    }
}
