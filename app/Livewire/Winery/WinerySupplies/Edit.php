<?php

namespace App\Livewire\Winery\WinerySupplies;

use App\Livewire\Winery\AbstractEdit;
use App\Models\UnitOfMeasurement;
use App\Models\WinerySupply;

class Edit extends AbstractEdit
{
    public WinerySupply $winerySupply;

    public string $name = '';

    public string $commercial_name = '';

    public string $supply_type = '';

    public string $unit_of_measurement_id = '';

    public string $current_stock = '';

    public string $min_stock_alert = '';

    public string $expiry_date = '';

    public string $notes = '';

    public function mount(WinerySupply $winerySupply): void
    {
        $this->authorize('update', $winerySupply);
        $this->winerySupply = $winerySupply;
        $this->name = $winerySupply->name;
        $this->commercial_name = $winerySupply->commercial_name ?? '';
        $this->supply_type = $winerySupply->supply_type;
        $this->unit_of_measurement_id = (string) ($winerySupply->unit_of_measurement_id ?? '');
        $this->current_stock = $winerySupply->current_stock !== null ? (string) $winerySupply->current_stock : '';
        $this->min_stock_alert = $winerySupply->min_stock_alert !== null ? (string) $winerySupply->min_stock_alert : '';
        $this->expiry_date = $winerySupply->expiry_date?->toDateString() ?? '';
        $this->notes = $winerySupply->notes ?? '';
    }

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

    protected function performUpdate(): void
    {
        $this->winerySupply->update([
            'name' => $this->name,
            'commercial_name' => $this->commercial_name ?: null,
            'supply_type' => $this->supply_type,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'current_stock' => $this->current_stock !== '' ? $this->current_stock : null,
            'min_stock_alert' => $this->min_stock_alert !== '' ? $this->min_stock_alert : null,
            'expiry_date' => $this->expiry_date ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Insumo actualizado correctamente.');
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
