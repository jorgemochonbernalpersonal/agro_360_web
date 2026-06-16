<?php

namespace App\Http\Resources\Api;

use App\Models\ProductLot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductLot */
class ProductLotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wine_id' => $this->wine_id,
            'wine_name' => $this->wine?->name,
            'lot_number' => $this->name,
            'expiry_date' => $this->release_date?->toDateString(),
            'status' => $this->archived ? 'archived' : 'active',
            'wine' => $this->whenLoaded('wine', fn () => [
                'id' => $this->wine->id,
                'name' => $this->wine->name,
                'wine_type' => $this->wine->wine_type,
            ]),
            'name' => $this->name,
            'vintage' => $this->vintage,
            'wine_type' => $this->wine_type,
            'aging_type' => $this->aging_type,
            'agingtime' => $this->agingtime,
            'alcohol' => $this->alcohol !== null ? (float) $this->alcohol : null,
            // Stock
            'unit' => $this->unit,
            'quantity' => (float) $this->quantity,
            'initial_quantity' => (float) $this->initial_quantity,
            'available_quantity' => (float) $this->available_quantity,
            'reserved_quantity' => (float) $this->reserved_quantity,
            'sold_quantity' => (float) $this->sold_quantity,
            'fill_percent' => $this->fill_percent,
            // Precios
            'price_per_unit' => $this->price_per_unit !== null ? (float) $this->price_per_unit : null,
            'cost_price' => $this->cost_price !== null ? (float) $this->cost_price : null,
            // Formato / comercial
            'bottle_format' => $this->bottle_format,
            'units_per_case' => $this->units_per_case,
            'ean' => $this->ean,
            'sku' => $this->sku,
            // Analíticos
            'residual_sugar' => $this->residual_sugar !== null ? (float) $this->residual_sugar : null,
            'total_acidity' => $this->total_acidity !== null ? (float) $this->total_acidity : null,
            'volatile_acidity' => $this->volatile_acidity !== null ? (float) $this->volatile_acidity : null,
            'ph' => $this->ph !== null ? (float) $this->ph : null,
            // Winemaking
            'winemaker' => $this->winemaker,
            'fermentation_vessel' => $this->fermentation_vessel,
            'oak_type' => $this->oak_type,
            'oak_months' => $this->oak_months,
            'harvest_method' => $this->harvest_method,
            // Viñedo
            'vine_age' => $this->vine_age,
            'altitude' => $this->altitude,
            'soil_type' => $this->soil_type,
            // Certificaciones
            'is_vegan' => (bool) $this->is_vegan,
            'is_biodynamic' => (bool) $this->is_biodynamic,
            'sulfites' => (bool) $this->sulfites,
            'ecological' => (bool) $this->ecological,
            // Marketing / comercial
            'description' => $this->description,
            'pairing' => $this->pairing,
            'tasting_notes' => $this->tasting_notes,
            'consumption_recommendation' => $this->consumption_recommendation,
            'recommended_temperature_min' => $this->recommended_temperature_min !== null ? (float) $this->recommended_temperature_min : null,
            'recommended_temperature_max' => $this->recommended_temperature_max !== null ? (float) $this->recommended_temperature_max : null,
            'awards_notes' => $this->awards_notes,
            'production_quantity' => $this->production_quantity,
            'bottling_date' => $this->bottling_date?->toDateString(),
            'release_date' => $this->release_date?->toDateString(),
            // Estado
            'archived' => (bool) $this->archived,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
