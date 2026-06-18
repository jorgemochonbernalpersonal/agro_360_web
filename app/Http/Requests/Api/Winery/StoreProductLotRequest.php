<?php

namespace App\Http\Requests\Api\Winery;

class StoreProductLotRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'nullable|integer|exists:wines,id',
            'name' => 'required|string|max:255',
            'vintage' => 'nullable|integer|min:1900|max:'.(now()->year + 2),
            'wine_type' => 'nullable|string|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type' => 'nullable|string|max:100',
            'agingtime' => 'nullable|integer|min:0',
            'alcohol' => 'nullable|numeric|min:0|max:25',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|in:litros,botellas,cajas',
            'price_per_unit' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'bottle_format' => 'nullable|string|max:50',
            'units_per_case' => 'nullable|integer|min:1',
            'ean' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:100',
            'residual_sugar' => 'nullable|numeric|min:0',
            'total_acidity' => 'nullable|numeric|min:0',
            'volatile_acidity' => 'nullable|numeric|min:0',
            'ph' => 'nullable|numeric|between:2,5',
            'winemaker' => 'nullable|string|max:255',
            'fermentation_vessel' => 'nullable|string|max:255',
            'oak_type' => 'nullable|string|max:100',
            'oak_months' => 'nullable|integer|min:0',
            'harvest_method' => 'nullable|string|max:100',
            'vine_age' => 'nullable|integer|min:0',
            'altitude' => 'nullable|integer|min:0',
            'soil_type' => 'nullable|string|max:100',
            'is_vegan' => 'nullable|boolean',
            'is_biodynamic' => 'nullable|boolean',
            'sulfites' => 'nullable|boolean',
            'ecological' => 'nullable|boolean',
            'description' => 'nullable|string|max:5000',
            'pairing' => 'nullable|string|max:2000',
            'tasting_notes' => 'nullable|string|max:2000',
            'consumption_recommendation' => 'nullable|string|max:1000',
            'recommended_temperature_min' => 'nullable|numeric|between:-5,30',
            'recommended_temperature_max' => 'nullable|numeric|between:-5,30',
            'awards_notes' => 'nullable|string|max:2000',
            'production_quantity' => 'nullable|integer|min:0',
            'bottling_date' => 'nullable|date',
            'release_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
