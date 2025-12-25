<?php

namespace App\Livewire\Plots\Plantings;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive', 'statistics'
    public $search = '';
    public $status = '';
    public $year = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'year' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingYear()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($plantingId)
    {
        $user = Auth::user();
        $visiblePlotIds = Plot::forUser($user)->pluck('id');
        $planting = PlotPlanting::whereIn('plot_id', $visiblePlotIds)->findOrFail($plantingId);
        
        $wasActive = $planting->active;
        $newActiveState = !$wasActive;
        
        $planting->update([
            'active' => $newActiveState
        ]);

        if ($newActiveState) {
            $this->toastSuccess('Plantación activada exitosamente.');
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Plantación desactivada exitosamente.');
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->year = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Parcelas visibles para el usuario, reutilizando el scope existente
        $visiblePlotIds = Plot::forUser($user)->pluck('id');

        $query = PlotPlanting::with(['plot.viticulturist', 'plot.municipality', 'grapeVariety'])
            ->whereIn('plot_id', $visiblePlotIds);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereHas('plot', function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(name) LIKE ?', [$search]);
                    })->orWhereHas('grapeVariety', function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(name) LIKE ?', [$search])
                            ->orWhereRaw('LOWER(code) LIKE ?', [$search]);
                    });
            });
        }

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('active', true); // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false); // Inactivos
        }
        // Si es 'statistics', no filtrar por activo/inactivo

        if ($this->status !== '' && $this->currentTab !== 'statistics') {
            $query->where('status', $this->status);
        }

        if ($this->year !== '') {
            $query->where('planting_year', $this->year);
        }

        $plantings = $query->orderByDesc('created_at')->paginate(10);

        $years = PlotPlanting::whereNotNull('planting_year')
            ->distinct()
            ->orderByDesc('planting_year')
            ->pluck('planting_year');

        // Estadísticas básicas
        $baseQuery = PlotPlanting::whereIn('plot_id', $visiblePlotIds);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics($user, $visiblePlotIds);
        }

        return view('livewire.plots.plantings.index', [
            'plantings' => $plantings,
            'years' => $years,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Plantaciones - Agro365',
            'description' => 'Gestiona las plantaciones de tus parcelas. Variedades de uva, años de plantación, hectáreas y estado de cada viñedo.',
        ]);
    }

    private function getAdvancedStatistics($user, $visiblePlotIds)
    {
        $year = $this->yearFilter;
        $allPlantings = PlotPlanting::whereIn('plot_id', $visiblePlotIds)
            ->with(['plot', 'grapeVariety'])
            ->get();
        
        // Superficie total plantada
        $totalSurface = $allPlantings->sum('area_planted');
        $activeSurface = $allPlantings->where('active', true)->sum('area_planted');
        $inactiveSurface = $allPlantings->where('active', false)->sum('area_planted');
        
        // Distribución por estado
        $statusStats = $allPlantings->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->count(),
                'surface' => $group->sum('area_planted'),
                'active' => $group->where('active', true)->count(),
                'inactive' => $group->where('active', false)->count(),
            ];
        });
        
        // Distribución por variedad (Top 10)
        $varietyStats = $allPlantings->groupBy('grape_variety_id')->map(function ($group) {
            $variety = $group->first()->grapeVariety;
            return [
                'name' => $variety->name ?? 'Sin variedad',
                'code' => $variety->code ?? '',
                'count' => $group->count(),
                'surface' => $group->sum('area_planted'),
            ];
        })->sortByDesc('surface')->take(10);
        
        // Plantaciones con/sin riego
        $irrigated = $allPlantings->where('irrigated', true)->count();
        $nonIrrigated = $allPlantings->where('irrigated', false)->count();
        
        // Distribución por año de plantación
        $yearStats = $allPlantings->groupBy('planting_year')->map(function ($group) {
            return [
                'year' => $group->first()->planting_year ?? 'Sin año',
                'count' => $group->count(),
                'surface' => $group->sum('area_planted'),
            ];
        })->sortByDesc('year')->take(10);
        
        // Nuevas plantaciones por mes (últimos 12 meses)
        $newPlantingsByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($visiblePlotIds) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => PlotPlanting::whereIn('plot_id', $visiblePlotIds)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });
        
        // Superficie media por plantación
        $avgSurfacePerPlanting = $allPlantings->count() > 0 ? $allPlantings->avg('area_planted') : 0;
        
        // Plantaciones con/sin autorización
        $withAuthorization = $allPlantings->filter(fn($p) => !empty($p->planting_authorization))->count();
        $withoutAuthorization = $allPlantings->count() - $withAuthorization;
        
        return [
            'totalSurface' => $totalSurface,
            'activeSurface' => $activeSurface,
            'inactiveSurface' => $inactiveSurface,
            'statusStats' => $statusStats,
            'varietyStats' => $varietyStats,
            'irrigated' => $irrigated,
            'nonIrrigated' => $nonIrrigated,
            'yearStats' => $yearStats,
            'newPlantingsByMonth' => $newPlantingsByMonth,
            'avgSurfacePerPlanting' => $avgSurfacePerPlanting,
            'withAuthorization' => $withAuthorization,
            'withoutAuthorization' => $withoutAuthorization,
        ];
    }
}


