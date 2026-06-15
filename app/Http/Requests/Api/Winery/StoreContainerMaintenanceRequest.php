<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\ContainerMaintenance;

class StoreContainerMaintenanceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'container_id' => 'required|integer|exists:containers,id',
            'maintenance_type' => 'required|string|in:'.implode(',', array_keys(ContainerMaintenance::TYPES)),
            'maintenance_name' => 'required|string|max:255',
            'scheduled_date' => 'nullable|date',
            'performed_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status' => 'nullable|string|in:'.implode(',', array_keys(ContainerMaintenance::STATUSES)),
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'supplies' => 'nullable|array|max:20',
            'supplies.*.winery_supply_id' => 'nullable|integer|exists:winery_supplies,id',
            'supplies.*.supply_name' => 'nullable|string|max:255',
            'supplies.*.quantity_used' => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'supplies.*.cost' => 'nullable|numeric|min:0',
            'supplies.*.notes' => 'nullable|string|max:500',
            'wastes' => 'nullable|array|max:10',
            'wastes.*.container_waste_type_id' => 'nullable|integer|exists:container_waste_types,id',
            'wastes.*.custom_waste_type' => 'nullable|string|max:100',
            'wastes.*.waste_date' => 'nullable|date',
            'wastes.*.quantity' => 'nullable|numeric|min:0',
            'wastes.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'wastes.*.disposal_method' => 'nullable|string|max:255',
            'wastes.*.cost' => 'nullable|numeric|min:0',
            'wastes.*.notes' => 'nullable|string|max:500',
        ];
    }
}
