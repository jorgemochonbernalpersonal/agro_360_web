<?php

namespace App\Http\Resources\Api;

use App\Models\PlannedWork;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlannedWork */
class PlannedWorkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'plot_id' => $this->plot_id,
            'category' => $this->category,
            'title' => $this->title,
            'description' => $this->description,
            'planned_date' => $this->planned_date?->toDateString(),
            'planned_end_date' => $this->planned_end_date?->toDateString(),
            'priority' => $this->priority,
            'status' => $this->status,
            'notes' => $this->notes,
            'completed_at' => $this->completed_at?->toDateString(),
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
        ];
    }
}
