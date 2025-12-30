<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Models\ProductStock;
use App\Models\PhytosanitaryProduct;
use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreateStock extends Component
{
    use WithToastNotifications;

    public $product_id = '';
    public $warehouse_id = '';
    public $batch_number = '';
    public $expiry_date = '';
    public $manufacturing_date = '';
    public $quantity = '';
    public $unit = 'L';
    public $unit_price = '';
    public $supplier = '';
    public $invoice_number = '';
    public $notes = '';

    public function mount()
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }
    }

    public function save()
    {
        $this->validate([
            'product_id' => 'required|exists:phytosanitary_products,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ], [
            'product_id.required' => 'Debes seleccionar un producto',
            'quantity.required' => 'La cantidad es obligatoria',
            'quantity.min' => 'La cantidad debe ser mayor a 0',
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
            'supplier' => $this->supplier,
            'unit_price' => $this->unit_price ?: null,
        ]);

        // Agregar stock
        $stock->addStock((float) $this->quantity, [
            'unit_price' => $this->unit_price ?: null,
            'invoice_number' => $this->invoice_number,
            'notes' => $this->notes,
        ]);

        $this->toastSuccess('Stock registrado correctamente');
        return redirect()->route('viticulturist.inventory.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.inventory.create-stock', [
            // ✅ OPTIMIZACIÓN: Solo campos necesarios para selects
            'products' => PhytosanitaryProduct::select(['id', 'name'])
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'warehouses' => Warehouse::select(['id', 'name', 'user_id'])
                ->where('user_id', Auth::id())
                ->where('active', true)
                ->get(),
        ])->layout('layouts.app', [
            'title' => 'Registrar Stock - Agro365',
        ]);
    }
}
