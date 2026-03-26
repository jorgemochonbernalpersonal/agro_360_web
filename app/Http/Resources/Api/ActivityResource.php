<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'activity_type'     => $this->activity_type,
            'activity_date'     => $this->activity_date?->toDateString(),
            'phenological_stage' => $this->phenological_stage,
            'weather_conditions' => $this->weather_conditions,
            'temperature'       => $this->temperature !== null ? (float) $this->temperature : null,
            'notes'             => $this->notes,
            'plot'              => $this->whenLoaded('plot', fn () => [
                'id'   => $this->plot->id,
                'name' => $this->plot->name,
            ]),
            'campaign_id'       => $this->campaign_id,
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}
