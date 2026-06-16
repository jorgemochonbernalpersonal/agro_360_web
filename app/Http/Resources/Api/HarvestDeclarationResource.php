<?php

namespace App\Http\Resources\Api;

use App\Models\HarvestDeclaration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HarvestDeclaration */
class HarvestDeclarationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'declaration_year' => $this->declaration_year,
            'declaration_date' => $this->declaration_date?->toDateString(),
            'submission_date' => $this->submission_date?->toDateString(),
            'authority' => $this->authority,
            'reference_number' => $this->reference_number,
            'total_surface_ha' => $this->total_surface_ha !== null ? (float) $this->total_surface_ha : null,
            'total_kg' => $this->total_kg !== null ? (float) $this->total_kg : null,
            'declaration_lines' => (array) $this->declaration_lines,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'notes' => $this->notes,
        ];
    }
}
