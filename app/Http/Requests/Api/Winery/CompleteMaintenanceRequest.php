<?php

namespace App\Http\Requests\Api\Winery;

class CompleteMaintenanceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'performed_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
