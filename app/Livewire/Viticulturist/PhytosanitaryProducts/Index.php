<?php

namespace App\Livewire\Viticulturist\PhytosanitaryProducts;

use App\Models\PhytosanitaryProduct;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive'
    public $search     = '';
    public $typeFilter = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (! Auth::user()->hasViticulturistAccess()) {
            abort(403, 'No tienes permiso para ver productos fitosanitarios.');
        }
    }

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function toggleActive($productId): void
    {
        $product = PhytosanitaryProduct::where('user_id', Auth::id())->findOrFail($productId);

        $newActive = ! $product->active;
        $product->update(['active' => $newActive]);

        if ($newActive) {
            $this->toastSuccess('Producto activado exitosamente.');
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Producto desactivado exitosamente.');
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function render()
    {
        $query = PhytosanitaryProduct::forUser(Auth::id())->orderBy('name');

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } else {
            $query->where('active', false);
        }

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(active_ingredient) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(registration_number) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(manufacturer) LIKE ?', [$search]);
            });
        }

        if ($this->typeFilter) {
            $query->whereRaw('LOWER(type) = ?', [strtolower($this->typeFilter)]);
        }

        $products = $query->paginate(12);

        $types = PhytosanitaryProduct::forUser(Auth::id())
            ->select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $base  = PhytosanitaryProduct::forUser(Auth::id());
        $stats = [
            'active'   => (clone $base)->where('active', true)->count(),
            'inactive' => (clone $base)->where('active', false)->count(),
        ];

        return view('livewire.viticulturist.phytosanitary-products.index', [
            'products' => $products,
            'types'    => $types,
            'stats'    => $stats,
        ])->layout('layouts.app', [
            'title'       => 'Productos Fitosanitarios - Agro365',
            'description' => 'Catálogo completo de productos fitosanitarios.',
        ]);
    }
}
