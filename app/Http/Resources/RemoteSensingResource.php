<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemoteSensingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_date' => $this->image_date->toDateString(),
            'indices' => [
                'ndvi' => (float) $this->ndvi,
                'evi' => (float) $this->evi,
                'savi' => (float) $this->savi,
                'ndwi' => (float) $this->ndwi,
            ],
            'cloud_coverage' => (float) $this->cloud_coverage,
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'metadata' => $this->metadata,
        ];
    }
}
