<?php

namespace App\Http\Resources\Api;

use App\Models\MarketedHarvest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MarketedHarvest */
class MarketedHarvestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'harvest_id' => $this->harvest_id,
            'campaign_id' => $this->campaign_id,
            'delivery_date' => $this->delivery_date?->toDateString(),
            'quantity_kg' => (float) $this->quantity_kg,
            'destination_type' => $this->destination_type,
            'destination_label' => $this->destination_type_label,
            'buyer_name' => $this->buyer_name,
            'buyer_rega_code' => $this->buyer_rega_code,
            'transport_document' => $this->transport_document,
            'vehicle_plate' => $this->vehicle_plate,
            'price_per_kg' => $this->price_per_kg !== null ? (float) $this->price_per_kg : null,
            'total_value' => $this->total_value !== null ? (float) $this->total_value : null,
            'notes' => $this->notes,
        ];
    }
}
