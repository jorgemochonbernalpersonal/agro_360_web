<?php

namespace App\Http\Resources\Api;

use App\Models\FieldEquipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FieldEquipment */
class FieldEquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'equipment_type' => $this->equipment_type,
            'registration_number' => $this->registration_number,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'last_inspection_date' => $this->last_inspection_date?->toDateString(),
            'next_inspection_date' => $this->next_inspection_date?->toDateString(),
            'inspection_entity' => $this->inspection_entity,
            'notes' => $this->notes,
            'is_inspection_due' => $this->isInspectionDue(),
            'is_inspection_overdue' => $this->isInspectionOverdue(),
        ];
    }
}
