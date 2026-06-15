<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\ContainerMaintenance;

class IndexContainerMaintenanceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'container_id' => 'nullable|integer',
            'status' => 'nullable|string|in:'.implode(',', array_keys(ContainerMaintenance::STATUSES)),
            'type' => 'nullable|string|in:'.implode(',', array_keys(ContainerMaintenance::TYPES)),
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
