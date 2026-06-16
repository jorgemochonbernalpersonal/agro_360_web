<?php

namespace App\Http\Resources;

use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Plot */
class PlotResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'area' => [
                'total' => (float) $this->area,
                'pac_eligible' => (float) $this->pac_eligible_area,
                'non_eligible' => (float) $this->non_eligible_area,
                'coefficient' => (float) $this->eligibility_coefficient,
            ],
            'tenure_regime' => $this->tenure_regime,
            'active' => $this->active,
            'locked' => [
                'is_locked' => $this->is_locked,
                'locked_at' => $this->locked_at?->toIso8601String(),
                'reason' => $this->lock_reason,
            ],
            'location' => [
                'autonomous_community_id' => $this->autonomous_community_id,
                'province_id' => $this->province_id,
                'municipality_id' => $this->municipality_id,
                'province' => $this->whenLoaded('province', fn () => [
                    'id' => $this->province->id,
                    'name' => $this->province->name,
                ]),
                'municipality' => $this->whenLoaded('municipality', fn () => [
                    'id' => $this->municipality->id,
                    'name' => $this->municipality->name,
                ]),
            ],
            'viticulturist' => $this->whenLoaded('viticulturist', fn () => new UserResource($this->viticulturist)),
            'plantings' => PlotPlantingResource::collection($this->whenLoaded('plantings')),
            'remote_sensing' => $this->when(
                $this->relationLoaded('latestRemoteSensing') && $this->latestRemoteSensing,
                fn () => new RemoteSensingResource($this->latestRemoteSensing)
            ),
            'alerts' => [
                'ndvi_threshold' => $this->ndvi_alert_threshold,
                'email_enabled' => $this->alert_email_enabled,
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
