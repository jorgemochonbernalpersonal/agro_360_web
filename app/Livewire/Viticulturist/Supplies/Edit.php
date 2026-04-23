<?php

namespace App\Livewire\Viticulturist\Supplies;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Supply;
use App\Models\Unit;
use App\Models\Warehouse;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public Supply $supply;

    public string $name                = '';
    public string $commercial_name     = '';
    public string $registration_number = '';
    public string $supply_type         = 'fertilizer';
    public string $unit_of_measurement = 'L';
    public mixed  $current_stock       = 0;
    public string $min_stock_alert     = '';
    public string $expiry_date         = '';
    public string $notes               = '';
    public ?int   $warehouse_id        = null;

    public function mount(Supply $supply): void
    {
        if ($supply->viticulturist_id !== Auth::id()) {
            abort(403);
        }

        $this->supply              = $supply;
        $this->name                = $supply->name;
        $this->commercial_name     = $supply->commercial_name ?? '';
        $this->registration_number = $supply->registration_number ?? '';
        $this->supply_type         = $supply->supply_type;
        $this->unit_of_measurement = $supply->unit_of_measurement;
        $this->current_stock       = $supply->current_stock;
        $this->min_stock_alert     = $supply->min_stock_alert ?? '';
        $this->expiry_date         = $supply->expiry_date?->format('Y-m-d') ?? '';
        $this->notes               = $supply->notes ?? '';
        $this->warehouse_id        = $supply->warehouse_id;
    }

    protected function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'supply_type'         => 'required|in:' . implode(',', array_keys(Supply::SUPPLY_TYPES)),
            'unit_of_measurement' => 'required|exists:units,symbol',
            'current_stock'       => 'nullable|numeric|min:0',
            'min_stock_alert'     => 'nullable|numeric|min:0',
            'expiry_date'         => 'nullable|date',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        $this->supply->update([
            'warehouse_id'        => $this->warehouse_id,
            'name'                => $this->name,
            'commercial_name'     => $this->commercial_name ?: null,
            'registration_number' => $this->registration_number ?: null,
            'supply_type'         => $this->supply_type,
            'unit_of_measurement' => $this->unit_of_measurement,
            'current_stock'       => $this->current_stock ?: 0,
            'min_stock_alert'     => $this->min_stock_alert ?: null,
            'expiry_date'         => $this->expiry_date ?: null,
            'notes'               => $this->notes ?: null,
        ]);

        $this->toastSuccess('Insumo actualizado correctamente.');

        return $this->viticulturistRoleRedirect('warehouse.index', ['tab' => 'insumos']);
    }

    public function render()
    {
        return view('livewire.viticulturist.supplies.edit', [
            'supplyTypes' => Supply::SUPPLY_TYPES,
            'units'       => Unit::active()->orderBy('category')->orderBy('name')->get(),
            'warehouses'  => Warehouse::select(['id', 'name'])
                ->where('user_id', Auth::id())
                ->where('active', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app', ['title' => 'Editar Insumo - Agro365']);
    }
}
