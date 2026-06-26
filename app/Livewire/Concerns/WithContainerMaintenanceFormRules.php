<?php

namespace App\Livewire\Concerns;

trait WithContainerMaintenanceFormRules
{
    protected function maintenanceFormRules(): array
    {
        return [
            'maintenance_type' => 'required|in:cleaning,sulfuring,inspection,repair,tartrate_removal,other',
            'maintenance_name' => 'required|string|max:200',
            'scheduled_date' => 'required|date',
            'performed_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status' => 'required|in:scheduled,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'supplies.*.quantity_used' => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id' => 'nullable|exists:units_of_measurement,id',
            'supplies.*.cost' => 'nullable|numeric|min:0',
            'wastes.*.waste_date' => 'nullable|date',
            'wastes.*.quantity' => 'nullable|numeric|min:0',
            'wastes.*.cost' => 'nullable|numeric|min:0',
        ];
    }
}
