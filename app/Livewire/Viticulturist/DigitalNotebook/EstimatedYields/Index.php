<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Models\EstimatedYield;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive', 'statistics'
    public $selectedCampaign = '';
    public $filterStatus = '';
    public $search = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'selectedCampaign' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function mount()
    {
        $user = Auth::user();
        
        // Si no hay campaña seleccionada, usar la activa
        if (empty($this->selectedCampaign)) {
            $activeCampaign = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->first();
            
            if ($activeCampaign) {
                $this->selectedCampaign = $activeCampaign->id;
            }
        }
        
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($yieldId)
    {
        $user = Auth::user();
        $yield = EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })->findOrFail($yieldId);
        
        $wasActive = $yield->active;
        $newActiveState = !$wasActive;
        
        $yield->update([
            'active' => $newActiveState
        ]);

        if ($newActiveState) {
            $this->toastSuccess('Estimación activada exitosamente.');
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Estimación desactivada exitosamente.');
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCampaign()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        
        // Obtener campañas del usuario
        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Construir query de rendimientos estimados
        $query = EstimatedYield::query()
            ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign', 'estimator'])
            ->whereHas('plotPlanting.plot', function($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            });

        // Filtro por campaña
        if ($this->selectedCampaign) {
            $query->where('campaign_id', $this->selectedCampaign);
        }

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('active', true); // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false); // Inactivos
        }
        // Si es 'statistics', no filtrar por activo/inactivo

        // Filtro por estado (solo si no estamos en statistics)
        if ($this->filterStatus && $this->currentTab !== 'statistics') {
            $query->where('status', $this->filterStatus);
        }

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('plotPlanting.plot', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('plotPlanting.grapeVariety', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $estimatedYields = $query->orderBy('estimation_date', 'desc')
            ->paginate(15);

        // Estadísticas básicas
        $statsQuery = EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        });

        if ($this->selectedCampaign && $this->currentTab !== 'statistics') {
            $statsQuery->where('campaign_id', $this->selectedCampaign);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('active', true)->count(),
            'inactive' => (clone $statsQuery)->where('active', false)->count(),
            'confirmed' => (clone $statsQuery)->where('status', 'confirmed')->count(),
            'draft' => (clone $statsQuery)->where('status', 'draft')->count(),
            'with_actual' => (clone $statsQuery)->whereNotNull('actual_total_yield')->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics($user);
        }

        return view('livewire.viticulturist.digital-notebook.estimated-yields.index', [
            'estimatedYields' => $estimatedYields,
            'campaigns' => $campaigns,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Rendimientos Estimados - Agro365',
            'description' => 'Gestiona las estimaciones de producción de tus viñedos. Compara rendimientos estimados vs reales y optimiza tu planificación.',
        ]);
    }

    private function getAdvancedStatistics($user)
    {
        $year = $this->yearFilter;
        $allYields = EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
        ->whereYear('estimation_date', $year)
        ->get();
        
        // Distribución por estado
        $statusStats = $allYields->groupBy('status')->map(function ($group) {
            return $group->count();
        });
        
        // Distribución por método de estimación
        $methodStats = $allYields->groupBy('estimation_method')->map(function ($group) {
            return $group->count();
        });
        
        // Estimaciones con/sin rendimiento real
        $withActual = $allYields->filter(fn($y) => $y->hasActualYield())->count();
        $withoutActual = $allYields->count() - $withActual;
        
        // Total estimado vs real
        $totalEstimated = $allYields->sum('estimated_total_yield');
        $totalActual = $allYields->sum('actual_total_yield') ?? 0;
        $averageVariance = $allYields->filter(fn($y) => $y->variance_percentage !== null)
            ->avg('variance_percentage') ?? 0;
        
        // Top 10 estimaciones con mayor diferencia (positiva o negativa)
        $topVariances = $allYields
            ->filter(fn($y) => $y->variance_percentage !== null)
            ->sortByDesc(fn($y) => abs($y->variance_percentage))
            ->take(10)
            ->map(function($yield) {
                return [
                    'id' => $yield->id,
                    'plot_name' => $yield->plotPlanting->plot->name ?? 'Sin parcela',
                    'variety' => $yield->plotPlanting->grapeVariety->name ?? 'Sin variedad',
                    'estimated' => $yield->estimated_total_yield,
                    'actual' => $yield->actual_total_yield,
                    'variance' => $yield->variance_percentage,
                ];
            });
        
        // Estimaciones por mes (últimos 12 meses)
        $yieldsByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
                    $q->where('viticulturist_id', $user->id);
                })
                ->whereYear('estimation_date', $date->year)
                ->whereMonth('estimation_date', $date->month)
                ->count(),
            ];
        });
        
        // Distribución por campaña
        $campaignStats = $allYields->groupBy('campaign_id')->map(function ($group) {
            $campaign = $group->first()->campaign;
            return [
                'name' => $campaign->name ?? 'Sin campaña',
                'count' => $group->count(),
                'total_estimated' => $group->sum('estimated_total_yield'),
                'total_actual' => $group->sum('actual_total_yield') ?? 0,
            ];
        })->sortByDesc('count');
        
        return [
            'statusStats' => $statusStats,
            'methodStats' => $methodStats,
            'withActual' => $withActual,
            'withoutActual' => $withoutActual,
            'totalEstimated' => $totalEstimated,
            'totalActual' => $totalActual,
            'averageVariance' => $averageVariance,
            'topVariances' => $topVariances,
            'yieldsByMonth' => $yieldsByMonth,
            'campaignStats' => $campaignStats,
        ];
    }
}

