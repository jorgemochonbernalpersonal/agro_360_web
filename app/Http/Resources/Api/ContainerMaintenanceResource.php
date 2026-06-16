<?php

namespace App\Http\Resources\Api;

use App\Models\ContainerMaintenance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContainerMaintenance */
class ContainerMaintenanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'container_id' => $this->container_id,
            'container_name' => $this->container?->name,
            'maintenance_date' => $this->scheduled_date->toDateString(),
            'description' => $this->notes,
            'completed_at' => $this->status === 'completed' ? $this->updated_at?->toIso8601String() : null,
            'container' => $this->whenLoaded('container', fn () => [
                'id' => $this->container->id,
                'name' => $this->container->name,
            ]),
            'maintenance_type' => $this->maintenance_type,
            'maintenance_type_label' => $this->getTypeLabel(),
            'maintenance_name' => $this->maintenance_name,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'scheduled_date' => $this->scheduled_date->toDateString(),
            'performed_date' => $this->performed_date?->toDateString(),
            'next_maintenance_date' => $this->next_maintenance_date?->toDateString(),
            'cost' => $this->cost !== null ? (float) $this->cost : null,
            'performed_by' => $this->performed_by,
            'notes' => $this->notes,
            'supplies' => $this->whenLoaded('supplies', fn () => $this->supplies->map(fn ($s) => [
                'id' => $s->id,
                'supply_name' => $s->display_name,
                'winery_supply_id' => $s->winery_supply_id,
                'quantity_used' => $s->quantity_used !== null ? (float) $s->quantity_used : null,
                'unit_of_measurement_id' => $s->unit_of_measurement_id,
                'cost' => $s->cost !== null ? (float) $s->cost : null,
                'notes' => $s->notes,
            ])
            ),
            'wastes' => $this->whenLoaded('wastes', fn () => $this->wastes->map(fn ($w) => [
                'id' => $w->id,
                'waste_type' => $w->display_type,
                'container_waste_type_id' => $w->container_waste_type_id,
                'custom_waste_type' => $w->custom_waste_type,
                'waste_date' => $w->waste_date->toDateString(),
                'quantity' => $w->quantity !== null ? (float) $w->quantity : null,
                'unit_of_measurement_id' => $w->unit_of_measurement_id,
                'disposal_method' => $w->disposal_method,
                'cost' => $w->cost !== null ? (float) $w->cost : null,
                'notes' => $w->notes,
            ])
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
