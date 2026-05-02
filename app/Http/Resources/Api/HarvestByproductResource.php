<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestByproductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'campaign_id'        => $this->campaign_id,
            'date'               => $this->date?->toDateString(),
            'byproduct_type'     => $this->byproduct_type,
            'byproduct_label'    => $this->byproduct_type_label,
            'quantity_kg'        => (float) $this->quantity_kg,
            'destination_type'   => $this->destination_type,
            'destination_label'  => $this->destination_type_label,
            'destination_name'   => $this->destination_name,
            'document_reference' => $this->document_reference,
            'notes'              => $this->notes,
        ];
    }
}
