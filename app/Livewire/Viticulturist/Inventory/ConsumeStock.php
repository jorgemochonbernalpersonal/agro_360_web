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
        if (!Auth::user()->hasViticulturistAccess()) {
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

        // Para dar de baja producto caducado usamos la cantidad total, no la disponible (que es 0)
        $limit = $this->reason === 'expired'
            ? (float) $this->stock->quantity
            : $this->stock->getAvailableQuantity();

        if ($this->quantity > $limit) {
            $this->toastError("Cantidad superior a la existente: {$limit} {$this->stock->unit}");
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
        return $this->redirect(route('viticulturist.almacen.index', ['tab' => 'fitosanitarios']), navigate: true);
    }

    public function render()
    {
        $maxQuantity = $this->reason === 'expired'
            ? (float) $this->stock->quantity
            : $this->stock->getAvailableQuantity();

        return view('livewire.viticulturist.inventory.consume-stock', [
            'availableQuantity' => $this->stock->getAvailableQuantity(),
            'maxQuantity'       => $maxQuantity,
        ])->layout('layouts.app', [
            'title' => 'Registrar Consumo - Agro365',
        ]);
    }
}
