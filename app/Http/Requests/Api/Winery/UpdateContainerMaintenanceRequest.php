<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\ContainerMaintenance;

class UpdateContainerMaintenanceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'maintenance_type' => 'sometimes|string|in:'.implode(',', array_keys(ContainerMaintenance::TYPES)),
            'maintenance_name' => 'sometimes|string|max:255',
            'scheduled_date' => 'sometimes|nullable|date',
            'performed_date' => 'sometimes|nullable|date',
            'next_maintenance_date' => 'sometimes|nullable|date',
            'status' => 'sometimes|string|in:'.implode(',', array_keys(ContainerMaintenance::STATUSES)),
            'cost' => 'sometimes|nullable|numeric|min:0',
            'performed_by' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
