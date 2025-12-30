<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Models\ProductStock;
use App\Models\PhytosanitaryProduct;
use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EditStock extends Component
{
    use WithToastNotifications;

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
    public $notes;

    protected $rules = [
        'quantity' => 'required|numeric|min:0',
        'minimum_stock' => 'nullable|numeric|min:0',
        'unit_price' => 'nullable|numeric|min:0',
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

    public function mount($stock)
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }

        $this->stock = ProductStock::findOrFail($stock);
        
        // Verificar propiedad
        if ($this->stock->user_id !== Auth::id()) {
            abort(403);
        }

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
                'to' => $this->quantity
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
            'unit_price' => $this->unit_price,
            'supplier' => $this->supplier,
            'notes' => $this->notes,
        ]);

        // Log de cambios si hubo ajuste de cantidad
        if (!empty($changes)) {
            $quantityChange = $this->quantity - $quantityBefore;
            $this->stock->movements()->create([
                'user_id' => Auth::id(),
                'movement_type' => 'adjustment',
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $this->quantity,
                'notes' => 'Ajuste manual de stock',
            ]);
        }

        $this->toastSuccess('Stock actualizado correctamente');
        return redirect()->route('viticulturist.inventory.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.inventory.edit-stock', [
            'product' => PhytosanitaryProduct::find($this->product_id),
            'warehouses' => Warehouse::where('user_id', Auth::id())
                ->where('active', true)
                ->get(),
        ])->layout('layouts.app', [
            'title' => 'Editar Stock - Agro365',
        ]);
    }
}
