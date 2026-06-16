<?php

namespace App\Http\Resources\Api;

use App\Models\PhenologyObservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PhenologyObservation */
class PhenologyObservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plot_planting_id' => $this->plot_planting_id,
            'campaign_id' => $this->campaign_id,
            'event' => $this->event,
            'obs_date' => $this->obs_date?->toDateString(),
            'source' => $this->source,
            'confidence' => $this->confidence,
            'degree_days_accumulated' => $this->degree_days_accumulated !== null ? (float) $this->degree_days_accumulated : null,
            'bbch_code' => $this->bbch_code,
            'notes' => $this->notes,
            'plot' => $this->when($this->relationLoaded('plotPlanting'), function () {
                return [
                    'id' => $this->plotPlanting->id,
                    'plot_name' => $this->plotPlanting->plot->name ?? null,
                    'variety' => $this->plotPlanting->grapeVariety->name ?? null,
                ];
            }),
        ];
    }
}
