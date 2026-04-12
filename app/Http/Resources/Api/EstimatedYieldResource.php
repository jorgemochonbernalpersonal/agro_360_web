<?php

namespace App\Http\Resources\Api;

use App\Models\EstimatedYield;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimatedYieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                           => $this->id,
            'plot_planting_id'             => $this->plot_planting_id,
            'plot'                         => $this->whenLoaded('plotPlanting', fn () => [
                'id'        => $this->plotPlanting->id,
                'plot_id'   => $this->plotPlanting->plot_id,
                'plot_name' => $this->plotPlanting->plot?->name,
                'variety'   => $this->plotPlanting->grapeVariety?->name ?? null,
            ]),
            'campaign_id'                  => $this->campaign_id,
            'campaign_year'                => $this->whenLoaded('campaign', fn () => $this->campaign->year ?? null),
            'estimation_date'              => $this->estimation_date?->toDateString(),
            'estimation_round'             => $this->estimation_round,
            'estimation_round_label'       => EstimatedYield::ROUNDS[$this->estimation_round] ?? null,
            'estimation_method'            => $this->estimation_method,
            'status'                       => $this->status,
            'estimated_yield_per_hectare'  => $this->estimated_yield_per_hectare !== null ? (float) $this->estimated_yield_per_hectare : null,
            'estimated_total_yield'        => $this->estimated_total_yield !== null ? (float) $this->estimated_total_yield : null,
            'auto_calculated_yield'        => $this->auto_calculated_yield !== null ? (float) $this->auto_calculated_yield : null,
            'actual_total_yield'           => $this->actual_total_yield !== null ? (float) $this->actual_total_yield : null,
            'variance_percentage'          => $this->variance_percentage !== null ? (float) $this->variance_percentage : null,
            'sampling'                     => [
                'thumbs_per_vine'      => $this->thumbs_per_vine,
                'bunches_per_plant'    => $this->bunches_per_plant !== null ? (float) $this->bunches_per_plant : null,
                'bunch_weight_grams'   => $this->bunch_weight_grams !== null ? (float) $this->bunch_weight_grams : null,
                'total_plants_sampled' => $this->total_plants_sampled,
                'sampling_area_pct'    => $this->sampling_area_pct !== null ? (float) $this->sampling_area_pct : null,
                'health_percentage'    => $this->health_percentage !== null ? (float) $this->health_percentage : null,
                'health_status'        => $this->health_status,
                'health_status_label'  => EstimatedYield::HEALTH_STATUSES[$this->health_status] ?? null,
                'potential_alcohol'    => $this->potential_alcohol !== null ? (float) $this->potential_alcohol : null,
            ],
            'vintage'                      => $this->vintage,
            'other_wineries'               => (bool) $this->other_wineries,
            'notes'                        => $this->notes,
            'created_at'                   => $this->created_at->toIso8601String(),
        ];
    }
}
