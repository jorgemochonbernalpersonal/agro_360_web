<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\Container;
use App\Models\Harvest;
use App\Models\Campaign;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $selectedCampaign = '';
    public $selectedHarvest = '';
    public $filterStatus = '';
    public $filterType = '';
    public $filterAvailability = ''; // 'available', 'assigned', o ''
    public $search = '';

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'selectedHarvest' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterAvailability' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        // Ya no necesitamos pre-seleccionar campaña por defecto
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

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
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

        // Filtro por estado (usando archived en lugar de status)
        if ($this->filterStatus) {
            if ($this->filterStatus === 'archived') {
                $query->where('archived', true);
            } elseif ($this->filterStatus === 'active') {
                $query->where('archived', false);
            }
        }

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
            'available' => (clone $baseQuery)->whereDoesntHave('harvests')->count(),
            'assigned' => (clone $baseQuery)->whereHas('harvests')->count(),
            'archived' => (clone $baseQuery)->where('archived', true)->count(),
        ];

        return view('livewire.viticulturist.digital-notebook.containers.index', [
            'containers' => $containers,
            'campaigns' => $campaigns,
            'harvests' => $harvests,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Contenedores de Cosecha - Agro365',
            'description' => 'Gestiona tus contenedores de cosecha. Control de peso, ubicación y asignación a vendimias. Trazabilidad completa de la uva.',
        ]);
    }
}

