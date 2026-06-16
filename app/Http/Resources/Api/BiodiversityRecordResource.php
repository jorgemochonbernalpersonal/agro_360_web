<?php

namespace App\Http\Resources\Api;

use App\Models\BiodiversityRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BiodiversityRecord */
class BiodiversityRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plot_id' => $this->plot_id,
            'campaign_id' => $this->campaign_id,
            'record_type' => $this->record_type,
            'description' => $this->description,
            'area_m2' => $this->area_m2 !== null ? (float) $this->area_m2 : null,
            'species' => $this->species,
            'record_date' => $this->record_date->toDateString(),
            'notes' => $this->notes,
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
        ];
    }
}
