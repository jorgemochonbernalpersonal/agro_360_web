<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentMonth = now()->month;

        return [
            'id'                   => $this->id,
            'type'                 => $this->type,
            'name'                 => $this->name,
            'scientific_name'      => $this->scientific_name,
            'full_name'            => $this->full_name,
            'icon'                 => $this->icon,
            'description'          => $this->description,
            'symptoms'             => $this->symptoms,
            'lifecycle'            => $this->lifecycle,
            'risk_months'          => $this->risk_months ?? [],
            'is_in_risk_period'    => $this->isInRiskPeriod($currentMonth),
            'threshold'            => $this->threshold !== null ? (float) $this->threshold : null,
            'control_methods'      => $this->control_methods ?? [],
            'control_method_labels'=> $this->control_method_labels,
            'prevention_methods'   => $this->prevention_methods,
            'products'             => $this->whenLoaded('products', fn () =>
                $this->products->map(fn ($p) => [
                    'id'                 => $p->id,
                    'name'               => $p->name,
                    'active_ingredient'  => $p->active_ingredient ?? null,
                    'effectiveness'      => $p->pivot->effectiveness_rating ?? null,
                ])
            ),
        ];
    }
}
