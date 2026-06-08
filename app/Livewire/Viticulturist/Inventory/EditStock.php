<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\PhytosanitaryProduct;
use App\Models\ProductStock;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditStock extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public ProductStock $stock;

    public $product_id;

    public $warehouse_id;

    public $batch_number;

    public $expiry_date;

    public $manufacturing_date;

    public $quantity;

    public $minimum_stock;

    public $unit;

    public $unit_price;

    public $supplier;

    public $invoice_number;

    public $notes;

    protected $rules = [
        'quantity' => 'required|numeric|min:0',
        'minimum_stock' => 'nullable|numeric|min:0',
        'unit' => 'required|exists:units,symbol',
        'unit_price' => 'nullable|numeric|min:0',
        'invoice_number' => 'nullable|string|max:100',
        'expiry_date' => 'nullable|date|after:today',
        'warehouse_id' => 'nullable|exists:warehouses,id',
    ];

    protected $messages = [
        'quantity.required' => 'La cantidad es obligatoria',
        'quantity.min' => 'La cantidad debe ser mayor o igual a 0',
        'minimum_stock.min' => 'El stock mínimo debe ser mayor o igual a 0',
        'unit_price.min' => 'El precio debe ser mayor o igual a 0',
        'expiry_date.after' => 'La fecha de caducidad debe ser posterior a hoy',
    ];

    public function mount(ProductStock $stock): void
    {
        if (! Auth::user()->hasViticulturistAccess()) {
            abort(403);
        }

        if ($stock->user_id !== Auth::id()) {
            abort(403);
        }

        $this->stock = $stock;

        // Cargar datos actuales
        $this->product_id = $this->stock->product_id;
        $this->warehouse_id = $this->stock->warehouse_id;
        $this->batch_number = $this->stock->batch_number;
        $this->expiry_date = $this->stock->expiry_date?->format('Y-m-d');
        $this->manufacturing_date = $this->stock->manufacturing_date?->format('Y-m-d');
        $this->quantity = $this->stock->quantity;
        $this->minimum_stock = $this->stock->minimum_stock;
        $this->unit = $this->stock->unit;
        $this->unit_price = $this->stock->unit_price;
        $this->supplier = $this->stock->supplier;
        $this->invoice_number = $this->stock->invoice_number;
        $this->notes = $this->stock->notes;
    }

    public function save()
    {
        $this->validate();

        $changes = [];
        $quantityBefore = $this->stock->quantity;

        // Detectar cambios importantes
        if ($this->quantity != $quantityBefore) {
            $changes['quantity'] = [
                'from' => $quantityBefore,
                'to' => $this->quantity,
            ];
        }

        // Actualizar
        $this->stock->update([
            'warehouse_id' => $this->warehouse_id,
            'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date,
            'manufacturing_date' => $this->manufacturing_date,
            'quantity' => $this->quantity,
            'minimum_stock' => $this->minimum_stock,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'supplier' => $this->supplier,
            'invoice_number' => $this->invoice_number ?: null,
            'notes' => $this->notes,
        ]);

        // Log de cambios si hubo ajuste de cantidad
        if (! empty($changes)) {
            $quantityChange = $this->quantity - $quantityBefore;
            $this->stock->movements()->create([
                'user_id' => Auth::id(),
                'movement_type' => $quantityChange >= 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $this->quantity,
                'notes' => __('Ajuste manual de stock'),
            ]);
        }

        $this->toastSuccess(__('Stock actualizado correctamente'));

        return $this->viticulturistRoleRedirect('warehouse.index', ['tab' => 'fitosanitarios']);
    }

    public function render()
    {
        return view('livewire.viticulturist.inventory.edit-stock', [
            'product' => PhytosanitaryProduct::find($this->product_id),
            'units' => Unit::active()->orderBy('category')->orderBy('name')->get(),
            'warehouses' => Warehouse::where('user_id', Auth::id())
                ->where('active', true)
                ->get(),
        ])->layout('layouts.app', [
            'title' => __('Editar Stock - Agro365'),
        ]);
    }
}
