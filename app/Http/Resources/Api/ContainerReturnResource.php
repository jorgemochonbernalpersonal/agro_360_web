<?php

namespace App\Http\Resources\Api;

use App\Models\PhytosanitaryContainerReturn;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PhytosanitaryContainerReturn */
class ContainerReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'date' => $this->date->toDateString(),
            'product_name' => $this->product_name,
            'registration_number' => $this->registration_number,
            'container_type' => $this->container_type,
            'container_size_liters' => $this->container_size_liters !== null ? (float) $this->container_size_liters : null,
            'containers_quantity' => $this->containers_quantity,
            'total_weight_kg' => $this->total_weight_kg !== null ? (float) $this->total_weight_kg : null,
            'collection_system' => $this->collection_system,
            'collection_point' => $this->collection_point,
            'transport_document' => $this->transport_document,
            'notes' => $this->notes,
            'phytosanitary_product' => $this->whenLoaded('phytosanitaryProduct', fn () => [
                'id' => $this->phytosanitaryProduct->id,
                'name' => $this->phytosanitaryProduct->commercial_name ?? $this->phytosanitaryProduct->name ?? null,
            ]),
        ];
    }
}
