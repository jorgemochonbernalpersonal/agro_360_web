<?php

namespace App\Http\Resources\Api;

use App\Models\SoilAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SoilAnalysis */
class SoilAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plot_id' => $this->plot_id,
            'campaign_id' => $this->campaign_id,
            'analysis_date' => $this->analysis_date?->toDateString(),
            'laboratory' => $this->laboratory,
            'sample_depth_cm' => $this->sample_depth_cm !== null ? (int) $this->sample_depth_cm : null,
            'ph' => $this->ph !== null ? (float) $this->ph : null,
            'organic_matter' => $this->organic_matter !== null ? (float) $this->organic_matter : null,
            'nitrogen_total' => $this->nitrogen_total !== null ? (float) $this->nitrogen_total : null,
            'phosphorus' => $this->phosphorus !== null ? (float) $this->phosphorus : null,
            'potassium' => $this->potassium !== null ? (float) $this->potassium : null,
            'calcium' => $this->calcium !== null ? (float) $this->calcium : null,
            'magnesium' => $this->magnesium !== null ? (float) $this->magnesium : null,
            'texture_class' => $this->texture_class,
            'electrical_conductivity' => $this->electrical_conductivity !== null ? (float) $this->electrical_conductivity : null,
            'limestone' => $this->limestone !== null ? (float) $this->limestone : null,
            'notes' => $this->notes,
            'plot_name' => $this->whenLoaded('plot', fn () => $this->plot->name),
            'texture_label' => $this->texture_label,
            'ph_range' => $this->ph_range,
            'ph_label' => $this->ph_label,
        ];
    }
}
