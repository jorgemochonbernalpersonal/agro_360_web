<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Models\ProductStock;
use App\Models\PhytosanitaryProduct;
use App\Models\Warehouse;
use App\Models\Machinery;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $productFilter = '';
    public $warehouseFilter = '';
    public $statusFilter = 'all'; // all, low_stock, expiring, expired, ok

    protected $queryString = [
        'search' => ['except' => ''],
        'productFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = ProductStock::where('user_id', $user->id)
            ->where('active', true)
            ->with(['product', 'warehouse']);

        // Filtros
        if ($this->search) {
            $query->whereHas('product', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->productFilter) {
            $query->where('product_id', $this->productFilter);
        }

        if ($this->warehouseFilter) {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        // Filtro por estado
        if ($this->statusFilter === 'expiring') {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '>', now())
                  ->where('expiry_date', '<=', now()->addDays(30));
        } elseif ($this->statusFilter === 'expired') {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', now());
        } elseif ($this->statusFilter === 'low_stock') {
            // Stock bajo: usar minimum_stock personalizado o 5 por defecto
            $query->where(function($q) {
                $q->whereColumn('quantity', '<', DB::raw('COALESCE(minimum_stock, 5)'));
            });
        }

        $stocks = $query->orderBy('expiry_date', 'asc')
            ->orderBy('product_id')
            ->paginate(20);

        // Estadísticas
        $stats = [
            'total_products' => ProductStock::where('user_id', $user->id)
                ->where('active', true)
                ->distinct('product_id')
                ->count('product_id'),
            'total_value' => ProductStock::where('user_id', $user->id)
                ->where('active', true)
                ->sum(DB::raw('quantity * COALESCE(unit_price, 0)')),
            'low_stock_count' => ProductStock::where('user_id', $user->id)
                ->where('active', true)
                ->where(function($q) {
                    $q->whereColumn('quantity', '<', DB::raw('COALESCE(minimum_stock, 5)'));
                })
                ->count(),
            'expiring_soon_count' => ProductStock::where('user_id', $user->id)
                ->where('active', true)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '>', now())
                ->where('expiry_date', '<=', now()->addDays(30))
                ->count(),
        ];

        return view('livewire.viticulturist.inventory.index', [
            'stocks' => $stocks,
            'stats' => $stats,
            // ✅ OPTIMIZACIÓN: Solo campos necesarios para selects
            'products' => PhytosanitaryProduct::select(['id', 'name'])
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'warehouses' => Warehouse::select(['id', 'name', 'user_id'])
                ->where('user_id', $user->id)
                ->where('active', true)
                ->get(),
        ])->layout('layouts.app', [
            'title' => 'Inventario de Productos Fitosanitarios - Agro365',
        ]);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->productFilter = '';
        $this->warehouseFilter = '';
        $this->statusFilter = 'all';
        $this->resetPage();
    }
}
