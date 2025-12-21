<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\HarvestContainer;
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
        $query = HarvestContainer::query()
            ->with(['harvest.activity.plot', 'harvest.plotPlanting.grapeVariety', 'harvest.activity.campaign'])
            ->where(function($q) use ($user) {
                // Contenedores sin cosecha (disponibles)
                $q->whereNull('harvest_id')
                  // O contenedores con cosecha del usuario
                  ->orWhereHas('harvest.activity', function($subQ) use ($user) {
                      $subQ->where('viticulturist_id', $user->id);
                  });
            });

        // Filtro por disponibilidad
        if ($this->filterAvailability === 'available') {
            $query->whereNull('harvest_id');
        } elseif ($this->filterAvailability === 'assigned') {
            $query->whereNotNull('harvest_id')
                  ->whereHas('harvest.activity', function($q) use ($user) {
                      $q->where('viticulturist_id', $user->id);
                  });
        }

        // Filtro por campaña (solo para contenedores asignados)
        if ($this->selectedCampaign) {
            $query->whereHas('harvest.activity', function($q) {
                $q->where('campaign_id', $this->selectedCampaign);
            });
        }

        // Filtro por cosecha
        if ($this->selectedHarvest) {
            $query->where('harvest_id', $this->selectedHarvest);
        }

        // Filtro por estado
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Filtro por tipo
        if ($this->filterType) {
            $query->where('container_type', $this->filterType);
        }

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('container_number', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('harvest.activity.plot', function($subQ) {
                      $subQ->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $containers = $query->orderBy('created_at', 'desc')
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
        $baseQuery = HarvestContainer::where(function($q) use ($user) {
            $q->whereNull('harvest_id')
              ->orWhereHas('harvest.activity', function($subQ) use ($user) {
                  $subQ->where('viticulturist_id', $user->id);
                  if ($this->selectedCampaign) {
                      $subQ->where('campaign_id', $this->selectedCampaign);
                  }
              });
        });

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'total_weight' => (clone $baseQuery)->sum('weight'),
            'available' => (clone $baseQuery)->whereNull('harvest_id')->count(),
            'assigned' => (clone $baseQuery)->whereNotNull('harvest_id')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
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

