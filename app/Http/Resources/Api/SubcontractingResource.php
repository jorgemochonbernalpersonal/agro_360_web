<?php

namespace App\Http\Resources\Api;

use App\Models\Subcontracting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Subcontracting */
class SubcontractingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plot_id' => $this->plot_id,
            'campaign_id' => $this->campaign_id,
            'service_type' => \App\Models\Subcontracting::SERVICE_TYPES[$this->service_type] ?? $this->service_type,
            'company_name' => $this->company_name,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'service_date' => $this->service_date?->toDateString(),
            'service_end_date' => $this->service_end_date?->toDateString(),
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'invoiced' => (bool) $this->invoiced,
            'invoice_number' => $this->invoice_number,
            'description' => $this->description,
            'notes' => $this->notes,
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
        ];
    }
}
