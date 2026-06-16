<?php

namespace App\Http\Resources\Api;

use App\Models\ResidueAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ResidueAnalysis */
class ResidueAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'analysis_date' => $this->analysis_date?->toDateString(),
            'sample_date' => $this->sample_date?->toDateString(),
            'laboratory_name' => $this->laboratory_name,
            'laboratory_accreditation' => $this->laboratory_accreditation,
            'sample_type' => $this->sample_type,
            'results' => (array) $this->results,
            'overall_compliant' => (bool) $this->overall_compliant,
            'notes' => $this->notes,
            'plot_planting' => $this->whenLoaded('plotPlanting', fn () => [
                'id' => $this->plotPlanting->id,
                'plot_name' => $this->plotPlanting->plot->name,
            ]),
        ];
    }
}
