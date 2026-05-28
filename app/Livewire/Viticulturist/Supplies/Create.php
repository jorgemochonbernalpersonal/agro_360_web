<?php

namespace App\Livewire\Viticulturist\Supplies;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Supply;
use App\Models\Unit;
use App\Models\Warehouse;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public string $name                = '';
    public string $commercial_name     = '';
    public string $registration_number = '';
    public string $supply_type         = 'fertilizer';
    public string $unit_of_measurement = 'L';
    public mixed  $initial_stock       = 0;
    public string $min_stock_alert     = '';
    public string $expiry_date         = '';
    public string $notes               = '';
    public ?int   $warehouse_id        = null;

    public function mount(): void
    {
        if (!Auth::user()->hasViticulturistAccess()) {
            abort(403);
        }
    }

    protected function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'supply_type'         => 'required|in:' . implode(',', array_keys(Supply::SUPPLY_TYPES)),
            'unit_of_measurement' => 'required|exists:units,symbol',
            'initial_stock'       => 'nullable|numeric|min:0',
            'min_stock_alert'     => 'nullable|numeric|min:0',
            'expiry_date'         => 'nullable|date',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        Supply::create([
            'viticulturist_id'    => Auth::id(),
            'warehouse_id'        => $this->warehouse_id,
            'name'                => $this->name,
            'commercial_name'     => $this->commercial_name ?: null,
            'registration_number' => $this->registration_number ?: null,
            'supply_type'         => $this->supply_type,
            'unit_of_measurement' => $this->unit_of_measurement,
            'initial_stock'       => $this->initial_stock ?: 0,
            'current_stock'       => $this->initial_stock ?: 0,
            'min_stock_alert'     => $this->min_stock_alert ?: null,
            'expiry_date'         => $this->expiry_date ?: null,
            'notes'               => $this->notes ?: null,
            'active'              => true,
        ]);

        $this->toastSuccess(__('Insumo añadido al almacén.'));

        return $this->viticulturistRoleRedirect('warehouse.index', ['tab' => 'insumos']);
    }

    public function render()
    {
        return view('livewire.viticulturist.supplies.create', [
            'supplyTypes' => Supply::supplyTypeOptions(),
            'units'       => Unit::active()->orderBy('category')->orderBy('name')->get(),
            'warehouses'  => Warehouse::select(['id', 'name'])
                ->where('user_id', Auth::id())
                ->where('active', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app', ['title' => __('Nuevo Insumo - Agro365')]);
    }
}
