<?php

namespace App\Http\Resources\Api;

use App\Models\PlotCost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlotCost */
class PlotCostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plot_id' => $this->plot_id,
            'campaign_id' => $this->campaign_id,
            'category' => $this->category,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'cost_date' => $this->cost_date?->toDateString(),
            'supplier' => $this->supplier,
            'invoice_reference' => $this->invoice_reference,
            'notes' => $this->notes,
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
        ];
    }
}
