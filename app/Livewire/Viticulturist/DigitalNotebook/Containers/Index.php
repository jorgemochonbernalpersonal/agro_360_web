<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\Container;
use App\Models\Harvest;
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
    public $selectedHarvest = '';
    public $filterType = '';
    public $filterAvailability = ''; // 'available', 'assigned', o ''
    public $search = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'selectedCampaign' => ['except' => ''],
        'selectedHarvest' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterAvailability' => ['except' => ''],
        'search' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function mount()
    {
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCampaign()
    {
        $this->resetPage();
        $this->selectedHarvest = '';
    }

    public function updatingSelectedHarvest()
    {
        $this->resetPage();
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function toggleActive($containerId)
    {
        $user = Auth::user();
        $container = Container::where('user_id', $user->id)->findOrFail($containerId);
        
        $wasArchived = $container->archived;
        $newArchivedState = !$wasArchived;
        
        // Actualizar directamente en la base de datos
        $container->archived = $newArchivedState;
        $container->save();

        if ($newArchivedState) {
            $this->toastSuccess('Contenedor desactivado exitosamente.');
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        } else {
            $this->toastSuccess('Contenedor activado exitosamente.');
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        // Obtener campañas del usuario
        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Construir query de contenedores
        // Mostrar todos los contenedores del usuario (con o sin cosecha)
        $query = Container::query()
            ->where('user_id', $user->id)
            ->where(function($q) use ($user) {
                // Contenedores sin cosecha (disponibles)
                $q->whereDoesntHave('harvests')
                  // O contenedores con cosecha del usuario
                  ->orWhereHas('harvests.activity', function($subQ) use ($user) {
                      $subQ->where('viticulturist_id', $user->id);
                  });
            });

        // Filtro por disponibilidad
        if ($this->filterAvailability === 'available') {
            $query->whereDoesntHave('harvests');
        } elseif ($this->filterAvailability === 'assigned') {
            $query->whereHas('harvests.activity', function($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            });
        }

        // Filtro por campaña (solo para contenedores asignados)
        if ($this->selectedCampaign) {
            $query->whereHas('harvests.activity', function($q) {
                $q->where('campaign_id', $this->selectedCampaign);
            });
        }

        // Filtro por cosecha
        if ($this->selectedHarvest) {
            $query->whereHas('harvests', function($q) {
                $q->where('id', $this->selectedHarvest);
            });
        }

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('archived', false); // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('archived', true); // Inactivos
        }
        // Si es 'statistics', no filtrar por activo/inactivo

        // Filtro por tipo (usando type_id)
        if ($this->filterType) {
            $query->where('type_id', $this->filterType);
        }

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('harvests.activity.plot', function($subQ) {
                      $subQ->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $containers = $query
            ->with(['harvests.activity.plot', 'harvests.plotPlanting.grapeVariety', 'harvests.activity.campaign', 'currentState'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Obtener cosechas para el filtro
        $harvests = collect();
        if ($this->selectedCampaign) {
            $harvests = Harvest::whereHas('activity', function($q) {
                $q->where('campaign_id', $this->selectedCampaign)
                  ->where('viticulturist_id', Auth::id());
            })
            ->with(['plotPlanting.grapeVariety', 'activity.plot'])
            ->orderBy('harvest_start_date', 'desc')
            ->get();
        }

        // Estadísticas
        $baseQuery = Container::where('user_id', $user->id)
            ->where(function($q) use ($user) {
                $q->whereDoesntHave('harvests')
                  ->orWhereHas('harvests.activity', function($subQ) use ($user) {
                      $subQ->where('viticulturist_id', $user->id);
                      if ($this->selectedCampaign) {
                          $subQ->where('campaign_id', $this->selectedCampaign);
                      }
                  });
            });

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'total_capacity' => (clone $baseQuery)->sum('capacity'),
            'total_used' => (clone $baseQuery)->sum('used_capacity'),
            'active' => (clone $baseQuery)->where('archived', false)->count(),
            'inactive' => (clone $baseQuery)->where('archived', true)->count(),
            'available' => (clone $baseQuery)->whereDoesntHave('harvests')->where('archived', false)->count(),
            'assigned' => (clone $baseQuery)->whereHas('harvests')->where('archived', false)->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics($user);
        }

        return view('livewire.viticulturist.digital-notebook.containers.index', [
            'containers' => $containers,
            'campaigns' => $campaigns,
            'harvests' => $harvests,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Contenedores de Cosecha - Agro365',
            'description' => 'Gestiona tus contenedores de cosecha. Control de peso, ubicación y asignación a vendimias. Trazabilidad completa de la uva.',
        ]);
    }

    private function getAdvancedStatistics($user)
    {
        $year = $this->yearFilter;
        $allContainers = Container::where('user_id', $user->id)
            ->where(function($q) use ($user) {
                $q->whereDoesntHave('harvests')
                  ->orWhereHas('harvests.activity', function($subQ) use ($user) {
                      $subQ->where('viticulturist_id', $user->id);
                  });
            })
            ->with(['harvests.activity.campaign'])
            ->get();
        
        // Capacidad total
        $totalCapacity = $allContainers->sum('capacity');
        $totalUsed = $allContainers->sum('used_capacity');
        $totalAvailable = $totalCapacity - $totalUsed;
        $occupancyPercentage = $totalCapacity > 0 ? ($totalUsed / $totalCapacity) * 100 : 0;
        
        // Distribución por estado (vacío, parcial, lleno)
        $emptyContainers = $allContainers->filter(fn($c) => $c->isEmpty())->count();
        $partialContainers = $allContainers->filter(fn($c) => !$c->isEmpty() && !$c->isFull())->count();
        $fullContainers = $allContainers->filter(fn($c) => $c->isFull())->count();
        
        // Contenedores disponibles vs asignados
        $availableContainers = $allContainers->filter(fn($c) => $c->harvests->isEmpty() && !$c->archived)->count();
        $assignedContainers = $allContainers->filter(fn($c) => $c->harvests->isNotEmpty() && !$c->archived)->count();
        
        // Distribución por campaña
        $campaignStats = $allContainers
            ->flatMap(fn($c) => $c->harvests->map(fn($h) => $h->activity->campaign_id ?? null))
            ->filter()
            ->groupBy(fn($id) => $id)
            ->map(function($group, $campaignId) use ($user) {
                $campaign = Campaign::find($campaignId);
                return [
                    'campaign_id' => $campaignId,
                    'campaign_year' => $campaign->year ?? 'N/A',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10);
        
        // Nuevos contenedores por mes (últimos 12 meses)
        $newContainersByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => Container::where('user_id', $user->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });
        
        // Capacidad media por contenedor
        $avgCapacityPerContainer = $allContainers->count() > 0 ? $allContainers->avg('capacity') : 0;
        
        // Top 10 contenedores por capacidad
        $topContainers = $allContainers
            ->sortByDesc('capacity')
            ->take(10)
            ->map(function($container) {
                return [
                    'id' => $container->id,
                    'name' => $container->name,
                    'capacity' => $container->capacity,
                    'used' => $container->used_capacity,
                    'percentage' => $container->getOccupancyPercentage(),
                ];
            });
        
        // Contenedores activos vs inactivos
        $activeContainers = $allContainers->where('archived', false)->count();
        $inactiveContainers = $allContainers->where('archived', true)->count();
        
        return [
            'totalCapacity' => $totalCapacity,
            'totalUsed' => $totalUsed,
            'totalAvailable' => $totalAvailable,
            'occupancyPercentage' => $occupancyPercentage,
            'emptyContainers' => $emptyContainers,
            'partialContainers' => $partialContainers,
            'fullContainers' => $fullContainers,
            'availableContainers' => $availableContainers,
            'assignedContainers' => $assignedContainers,
            'campaignStats' => $campaignStats,
            'newContainersByMonth' => $newContainersByMonth,
            'avgCapacityPerContainer' => $avgCapacityPerContainer,
            'topContainers' => $topContainers,
            'activeContainers' => $activeContainers,
            'inactiveContainers' => $inactiveContainers,
        ];
    }
}

