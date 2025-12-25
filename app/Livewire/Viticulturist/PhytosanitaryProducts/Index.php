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

    public $currentTab = 'active'; // 'active', 'inactive', 'statistics'
    public $search = '';
    public $typeFilter = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function mount(): void
    {
        // Solo viticultores pueden gestionar el catálogo en esta vista
        if (! Auth::user()->isViticulturist()) {
            abort(403, 'No tienes permiso para ver productos fitosanitarios.');
        }
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($productId)
    {
        $product = PhytosanitaryProduct::findOrFail($productId);
        
        $wasActive = $product->active;
        $newActiveState = !$wasActive;
        
        $product->update([
            'active' => $newActiveState
        ]);

        if ($newActiveState) {
            $this->toastSuccess('Producto activado exitosamente.');
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Producto desactivado exitosamente.');
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function render()
    {
        $query = PhytosanitaryProduct::query()
            ->orderBy('name');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(active_ingredient) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(registration_number) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(manufacturer) LIKE ?', [$search]);
            });
        }

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('active', true); // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false); // Inactivos
        }
        // Si es 'statistics', no filtrar por activo/inactivo

        if ($this->typeFilter && $this->currentTab !== 'statistics') {
            $query->whereRaw('LOWER(type) = ?', [strtolower($this->typeFilter)]);
        }

        $products = $query->paginate(10);

        // Tipos únicos para el filtro
        $types = PhytosanitaryProduct::select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // Estadísticas básicas
        $baseQuery = PhytosanitaryProduct::query();
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics();
        }

        return view('livewire.viticulturist.phytosanitary-products.index', [
            'products' => $products,
            'types' => $types,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Productos Fitosanitarios - Agro365',
            'description' => 'Catálogo completo de productos fitosanitarios. Gestiona tratamientos, plazos de seguridad y cumplimiento normativo para tu viñedo.',
        ]);
    }

    private function getAdvancedStatistics()
    {
        $year = $this->yearFilter;
        $allProducts = PhytosanitaryProduct::withCount('treatments')->get();
        
        // Distribución por tipo
        $typeStats = $allProducts->groupBy('type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'active' => $group->where('active', true)->count(),
                'inactive' => $group->where('active', false)->count(),
            ];
        })->sortByDesc('count');
        
        // Distribución por clase de toxicidad
        $toxicityStats = $allProducts->groupBy('toxicity_class')->map(function ($group) {
            return $group->count();
        });
        
        // Productos con/sin registro válido
        $withValidRegistration = $allProducts->filter(fn($p) => $p->isRegistrationValid())->count();
        $withoutValidRegistration = $allProducts->count() - $withValidRegistration;
        
        // Productos con/sin plazo de seguridad
        $withWithdrawalPeriod = $allProducts->filter(fn($p) => $p->withdrawal_period_days > 0)->count();
        $withoutWithdrawalPeriod = $allProducts->count() - $withWithdrawalPeriod;
        
        // Productos más usados (Top 10)
        $mostUsed = $allProducts
            ->sortByDesc('treatments_count')
            ->take(10)
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'type' => $product->type,
                    'treatments_count' => $product->treatments_count,
                ];
            });
        
        // Nuevos productos por mes (últimos 12 meses)
        $newProductsByMonth = collect(range(11, 0))->map(function($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => PhytosanitaryProduct::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });
        
        // Distribución por estado de registro
        $registrationStatusStats = $allProducts->groupBy('registration_status')->map(function ($group) {
            return $group->count();
        });
        
        // Plazo de seguridad promedio
        $avgWithdrawalPeriod = $allProducts->where('withdrawal_period_days', '>', 0)
            ->avg('withdrawal_period_days') ?? 0;
        
        return [
            'typeStats' => $typeStats,
            'toxicityStats' => $toxicityStats,
            'withValidRegistration' => $withValidRegistration,
            'withoutValidRegistration' => $withoutValidRegistration,
            'withWithdrawalPeriod' => $withWithdrawalPeriod,
            'withoutWithdrawalPeriod' => $withoutWithdrawalPeriod,
            'mostUsed' => $mostUsed,
            'newProductsByMonth' => $newProductsByMonth,
            'registrationStatusStats' => $registrationStatusStats,
            'avgWithdrawalPeriod' => $avgWithdrawalPeriod,
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->resetPage();
    }
}


