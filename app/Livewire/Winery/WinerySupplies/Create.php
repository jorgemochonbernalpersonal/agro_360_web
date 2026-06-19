<?php

namespace App\Livewire\Winery\WinerySupplies;

use App\Livewire\Winery\AbstractCreate;
use App\Models\UnitOfMeasurement;
use App\Models\WinerySupply;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $commercial_name = '';

    public string $supply_type = 'other';

    public string $unit_of_measurement_id = '';

    public string $current_stock = '';

    public string $min_stock_alert = '';

    public string $expiry_date = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'commercial_name' => ['nullable', 'string', 'max:200'],
            'supply_type' => ['required', 'in:'.implode(',', array_keys(WinerySupply::SUPPLY_TYPES))],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock_alert' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performCreate(): void
    {
        WinerySupply::create([
            'user_id' => $this->ownerId(),
            'name' => $this->name,
            'commercial_name' => $this->commercial_name ?: null,
            'supply_type' => $this->supply_type,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'current_stock' => $this->current_stock !== '' ? $this->current_stock : null,
            'min_stock_alert' => $this->min_stock_alert !== '' ? $this->min_stock_alert : null,
            'expiry_date' => $this->expiry_date ?: null,
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Insumo «:name» creado correctamente.', ['name' => $this->name]);
    }

    protected function indexRoute(): string
    {
        return 'winery.winery-supplies.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => WinerySupply::supplyTypeOptions(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
        ];
    }
}
