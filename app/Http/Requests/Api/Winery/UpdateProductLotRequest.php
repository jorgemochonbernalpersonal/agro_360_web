<?php

namespace App\Http\Requests\Api\Winery;

class UpdateProductLotRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'vintage' => 'sometimes|nullable|integer|min:1900|max:'.(now()->year + 2),
            'wine_type' => 'sometimes|nullable|string|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type' => 'sometimes|nullable|string|max:100',
            'agingtime' => 'sometimes|nullable|integer|min:0',
            'alcohol' => 'sometimes|nullable|numeric|min:0|max:25',
            'price_per_unit' => 'sometimes|nullable|numeric|min:0',
            'cost_price' => 'sometimes|nullable|numeric|min:0',
            'bottle_format' => 'sometimes|nullable|string|max:50',
            'units_per_case' => 'sometimes|nullable|integer|min:1',
            'ean' => 'sometimes|nullable|string|max:50',
            'sku' => 'sometimes|nullable|string|max:100',
            'residual_sugar' => 'sometimes|nullable|numeric|min:0',
            'total_acidity' => 'sometimes|nullable|numeric|min:0',
            'volatile_acidity' => 'sometimes|nullable|numeric|min:0',
            'ph' => 'sometimes|nullable|numeric|between:2,5',
            'winemaker' => 'sometimes|nullable|string|max:255',
            'fermentation_vessel' => 'sometimes|nullable|string|max:255',
            'oak_type' => 'sometimes|nullable|string|max:100',
            'oak_months' => 'sometimes|nullable|integer|min:0',
            'harvest_method' => 'sometimes|nullable|string|max:100',
            'vine_age' => 'sometimes|nullable|integer|min:0',
            'altitude' => 'sometimes|nullable|integer|min:0',
            'soil_type' => 'sometimes|nullable|string|max:100',
            'is_vegan' => 'sometimes|nullable|boolean',
            'is_biodynamic' => 'sometimes|nullable|boolean',
            'sulfites' => 'sometimes|nullable|boolean',
            'ecological' => 'sometimes|nullable|boolean',
            'description' => 'sometimes|nullable|string|max:5000',
            'pairing' => 'sometimes|nullable|string|max:2000',
            'tasting_notes' => 'sometimes|nullable|string|max:2000',
            'consumption_recommendation' => 'sometimes|nullable|string|max:1000',
            'recommended_temperature_min' => 'sometimes|nullable|numeric|between:-5,30',
            'recommended_temperature_max' => 'sometimes|nullable|numeric|between:-5,30',
            'awards_notes' => 'sometimes|nullable|string|max:2000',
            'production_quantity' => 'sometimes|nullable|integer|min:0',
            'bottling_date' => 'sometimes|nullable|date',
            'release_date' => 'sometimes|nullable|date',
            'archived' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
