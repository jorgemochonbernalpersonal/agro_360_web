<?php

namespace App\Livewire\Concerns;

trait WithContainerAdditiveFormRules
{
    protected function additiveFormRules(): array
    {
        return [
            'winery_supply_id' => $this->ownedWinerySupplyRule(false),
            'additive_name' => ['nullable', 'string', 'max:200'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'additive_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
