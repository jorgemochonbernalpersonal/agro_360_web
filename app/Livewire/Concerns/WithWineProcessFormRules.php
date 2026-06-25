<?php

namespace App\Livewire\Concerns;

use App\Models\WineProcessDetail;

trait WithWineProcessFormRules
{
    protected function wineProcessFormRules(): array
    {
        return [
            'process_type' => ['required', 'in:'.implode(',', array_keys(WineProcessDetail::PROCESS_TYPES))],
            'container_id' => $this->ownedContainerRule(false),
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'observations' => ['nullable', 'string'],
            'extraContainers.*.container_id' => $this->ownedContainerRule(false),
            'extraContainers.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
