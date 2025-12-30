<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Models\ProductStock;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ConsumeStock extends Component
{
    use WithToastNotifications;

    public ProductStock $stock;
    
    public $quantity;
    public $reason = 'loss';
    public $notes = '';

    protected $rules = [
        'quantity' => 'required|numeric|min:0.001',
        'reason' => 'required|in:loss,expired,donation,adjustment,other',
        'notes' => 'required_if:reason,other|nullable|string|max:500',
    ];

    protected $messages = [
        'quantity.required' => 'La cantidad es obligatoria',
        'quantity.min' => 'La cantidad debe ser mayor a 0',
        'reason.required' => 'Debes seleccionar un motivo',
        'notes.required_if' => 'Debes especificar el motivo cuando seleccionas "Otro"',
    ];

    public function mount($stock)
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }

        $this->stock = ProductStock::findOrFail($stock);
        
        if ($this->stock->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function consume()
    {
        $this->validate();

        // Verificar stock disponible
        $available = $this->stock->getAvailableQuantity();
        if ($this->quantity > $available) {
            $this->toastError("Stock insuficiente. Disponible: {$available} {$this->stock->unit}");
            return;
        }

        // Determinar nota según motivo
        $reasonLabels = [
            'loss' => 'Pérdida/Derrame',
            'expired' => 'Producto caducado',
            'donation' => 'Donación',
            'adjustment' => 'Ajuste de inventario',
            'other' => $this->notes,
        ];

        $note = $reasonLabels[$this->reason];
        if ($this->notes && $this->reason !== 'other') {
            $note .= ' - ' . $this->notes;
        }

        // Consumir stock
        $this->stock->consume($this->quantity, null, $note);

        $this->toastSuccess('Consumo registrado correctamente');
        return redirect()->route('viticulturist.inventory.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.inventory.consume-stock', [
            'availableQuantity' => $this->stock->getAvailableQuantity(),
        ])->layout('layouts.app', [
            'title' => 'Registrar Consumo - Agro365',
        ]);
    }
}
