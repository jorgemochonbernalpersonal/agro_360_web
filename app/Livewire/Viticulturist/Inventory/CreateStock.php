<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Models\ProductStock;
use App\Models\PhytosanitaryProduct;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreateStock extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public $product_id = '';
    public $warehouse_id = '';
    public $batch_number = '';
    public $expiry_date = '';
    public $manufacturing_date = '';
    public $quantity = '';
    public $unit = 'L';
    public $minimum_stock = '';
    public $unit_price = '';
    public $supplier = '';
    public $invoice_number = '';
    public $notes = '';

    public function mount()
    {
        if (!Auth::user()->hasViticulturistAccess()) {
            abort(403);
        }
    }

    public function save()
    {
        $validSymbols = Unit::active()->pluck('symbol')->implode(',');

        $this->validate([
            'product_id' => 'required|exists:phytosanitary_products,id',
            'quantity' => 'required|numeric|min:0.001',
            'minimum_stock' => 'nullable|numeric|min:0',
            'unit' => "required|in:{$validSymbols}",
            'unit_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ], [
            'product_id.required' => __('Debes seleccionar un producto'),
            'quantity.required' => __('La cantidad es obligatoria'),
            'quantity.min' => __('La cantidad debe ser mayor a 0'),
        ]);

        $user = Auth::user();

        // Buscar o crear stock para este producto/lote/almacén
        $stock = ProductStock::firstOrCreate([
            'product_id' => $this->product_id,
            'user_id' => $user->id,
            'batch_number' => $this->batch_number ?: null,
            'warehouse_id' => $this->warehouse_id ?: null,
        ], [
            'expiry_date' => $this->expiry_date ?: null,
            'manufacturing_date' => $this->manufacturing_date ?: null,
            'unit' => $this->unit,
            'minimum_stock' => $this->minimum_stock ?: null,
            'supplier' => $this->supplier,
            'unit_price' => $this->unit_price ?: null,
            'invoice_number' => $this->invoice_number ?: null,
        ]);

        // Agregar stock
        $stock->addStock((float) $this->quantity, [
            'unit_price' => $this->unit_price ?: null,
            'invoice_number' => $this->invoice_number,
            'notes' => $this->notes,
        ]);

        $this->toastSuccess(__('Stock registrado correctamente'));
        return $this->viticulturistRoleRedirect('warehouse.index', ['tab' => 'fitosanitarios']);
    }

    public function render()
    {
        return view('livewire.viticulturist.inventory.create-stock', [
            // ✅ OPTIMIZACIÓN: Solo campos necesarios para selects
            'products' => PhytosanitaryProduct::forUser(Auth::id())
                ->select(['id', 'name'])
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'units' => Unit::active()->orderBy('category')->orderBy('name')->get(),
            'warehouses' => Warehouse::select(['id', 'name', 'user_id'])
                ->where('user_id', Auth::id())
                ->where('active', true)
                ->get(),
        ])->layout('layouts.app', [
            'title' => __('Registrar Stock - Agro365'),
        ]);
    }
}
