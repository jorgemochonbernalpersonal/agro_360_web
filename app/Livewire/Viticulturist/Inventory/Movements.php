<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Models\ProductStock;
use App\Models\ProductStockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Movements extends Component
{
    use WithPagination;

    public $stockId;
    public $dateFrom = '';
    public $dateTo = '';

    public function mount($stock)
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }

        $stockModel = ProductStock::findOrFail($stock);
        
        // Verificar que el stock pertenece al usuario
        if ($stockModel->user_id !== Auth::id()) {
            abort(403);
        }

        $this->stockId = $stockModel->id;
    }

    public function render()
    {
        $query = ProductStockMovement::where('stock_id', $this->stockId)
            ->with(['treatment.activity.plot', 'user'])
            ->orderBy('created_at', 'desc');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $stock = ProductStock::with(['product', 'warehouse'])->findOrFail($this->stockId);

        return view('livewire.viticulturist.inventory.movements', [
            'movements' => $query->paginate(20),
            'stock' => $stock,
        ])->layout('layouts.app', [
            'title' => 'Historial de Movimientos - Agro365',
        ]);
    }
}
