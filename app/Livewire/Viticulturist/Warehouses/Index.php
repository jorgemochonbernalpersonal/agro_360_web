<?php

namespace App\Livewire\Viticulturist\Warehouses;

use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }
    }

    public function toggleActive($warehouseId)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())
            ->findOrFail($warehouseId);
        
        $warehouse->update([
            'active' => !$warehouse->active
        ]);

        $this->toastSuccess($warehouse->active ? 'Almacén activado exitosamente.' : 'Almacén desactivado exitosamente.');
    }

    public function delete($warehouseId)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())
            ->withCount('stocks')
            ->findOrFail($warehouseId);

        if ($warehouse->stocks_count > 0) {
            $this->toastError('No se puede eliminar el almacén porque tiene productos en stock asociados.');
            return;
        }

        $warehouse->delete();
        $this->toastSuccess('Almacén eliminado exitosamente.');
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = Warehouse::where('user_id', $user->id);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        $warehouses = $query->withCount('stocks')
            ->orderBy('active', 'desc')
            ->orderBy('name')
            ->paginate(15);

        $stats = [
            'total' => Warehouse::where('user_id', $user->id)->count(),
            'active' => Warehouse::where('user_id', $user->id)->where('active', true)->count(),
            'inactive' => Warehouse::where('user_id', $user->id)->where('active', false)->count(),
        ];

        return view('livewire.viticulturist.warehouses.index', [
            'warehouses' => $warehouses,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Gestionar Almacenes - Agro365',
        ]);
    }
}
