<?php

namespace App\Livewire\Viticulturist\Machinery;

use App\Models\Machinery;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

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

    public function mount()
    {
        // Validar autorización
        if (!Auth::user()->can('viewAny', Machinery::class)) {
            abort(403, 'No tienes permiso para ver maquinaria.');
        }
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($machineryId)
    {
        $user = Auth::user();
        $machinery = Machinery::forViticulturist($user->id)->findOrFail($machineryId);
        
        if (!$user->can('update', $machinery)) {
            abort(403);
        }
        
        $wasActive = $machinery->active;
        $newActiveState = !$wasActive;
        
        $machinery->update([
            'active' => $newActiveState
        ]);

        if ($newActiveState) {
            $this->toastSuccess('Maquinaria activada exitosamente.');
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Maquinaria desactivada exitosamente.');
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Machinery::forViticulturist($user->id)
            ->withCount('activities')
            ->with('viticulturist')
            ->orderBy('name');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(brand) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(model) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(serial_number) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(roma_registration) LIKE ?', [$search]);
            });
        }

        if ($this->typeFilter) {
            $query->ofType($this->typeFilter);
        }

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('active', true); // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false); // Inactivos
        }
        // Si es 'statistics', no filtrar por activo/inactivo

        $machinery = $query->paginate(10);

        // Obtener tipos únicos para el filtro
        $types = Machinery::forViticulturist($user->id)
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // Estadísticas básicas
        $baseQuery = Machinery::forViticulturist($user->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics($user);
        }

        return view('livewire.viticulturist.machinery.index', [
            'machinery' => $machinery,
            'types' => $types,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Maquinaria Agrícola - Agro365',
            'description' => 'Gestiona tu flota de maquinaria agrícola. Control de equipos, mantenimiento y registro de uso en actividades del viñedo.',
        ]);
    }

    private function getAdvancedStatistics($user)
    {
        $year = $this->yearFilter;
        $allMachinery = Machinery::forViticulturist($user->id)
            ->withCount('activities')
            ->with(['activities' => function($q) use ($year) {
                $q->whereYear('activity_date', $year);
            }])
            ->get();
        
        // Distribución por tipo
        $typeStats = $allMachinery->groupBy('type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'active' => $group->where('active', true)->count(),
                'inactive' => $group->where('active', false)->count(),
            ];
        })->sortByDesc('count');
        
        // Maquinaria alquilada vs propia
        $rentedCount = $allMachinery->where('is_rented', true)->count();
        $ownedCount = $allMachinery->where('is_rented', false)->count();
        
        // Maquinaria con/sin actividades este año
        $withActivities = $allMachinery->filter(fn($m) => $m->activities_count > 0)->count();
        $withoutActivities = $allMachinery->count() - $withActivities;
        
        // Total de actividades este año
        $totalActivities = $allMachinery->sum('activities_count');
        
        // Maquinaria más usada (Top 10)
        $mostUsed = $allMachinery
            ->sortByDesc('activities_count')
            ->take(10)
            ->map(function($machinery) {
                return [
                    'id' => $machinery->id,
                    'name' => $machinery->name,
                    'type' => $machinery->type,
                    'activities_count' => $machinery->activities_count,
                ];
            });
        
        // Nuevas maquinarias por mes (últimos 12 meses)
        $newMachineryByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => Machinery::forViticulturist($user->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });
        
        // Maquinaria con registro ROMA
        $withRoma = $allMachinery->filter(fn($m) => !empty($m->roma_registration))->count();
        $withoutRoma = $allMachinery->count() - $withRoma;
        
        return [
            'typeStats' => $typeStats,
            'rentedCount' => $rentedCount,
            'ownedCount' => $ownedCount,
            'withActivities' => $withActivities,
            'withoutActivities' => $withoutActivities,
            'totalActivities' => $totalActivities,
            'mostUsed' => $mostUsed,
            'newMachineryByMonth' => $newMachineryByMonth,
            'withRoma' => $withRoma,
            'withoutRoma' => $withoutRoma,
        ];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->resetPage();
    }
}
