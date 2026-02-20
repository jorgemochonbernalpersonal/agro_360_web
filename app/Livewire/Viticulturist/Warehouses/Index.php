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

    public $currentTab = 'active'; // 'active', 'inactive'
    public $search     = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($warehouseId)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())->findOrFail($warehouseId);
        $warehouse->update(['active' => !$warehouse->active]);

        $this->toastSuccess($warehouse->active
            ? 'Almacén activado exitosamente.'
            : 'Almacén desactivado exitosamente.'
        );

        if ($warehouse->active && $this->currentTab === 'inactive') {
            $this->currentTab = 'active';
        } elseif (!$warehouse->active && $this->currentTab === 'active') {
            $this->currentTab = 'inactive';
        }
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

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $user  = Auth::user();
        $query = Warehouse::where('user_id', $user->id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } else {
            $query->where('active', false);
        }

        $warehouses = $query->withCount('stocks')->orderBy('name')->paginate(12);

        $baseQuery = Warehouse::where('user_id', $user->id);
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'active'   => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
        ];

        return view('livewire.viticulturist.warehouses.index', compact('warehouses', 'stats'))
            ->layout('layouts.app', [
                'title' => 'Gestionar Almacenes - Agro365',
            ]);
    }
}
