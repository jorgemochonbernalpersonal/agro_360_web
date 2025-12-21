<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Models\EstimatedYield;
use App\Models\Campaign;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $selectedCampaign = '';
    public $filterStatus = '';
    public $search = '';

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
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

        // Filtro por estado
        if ($this->filterStatus) {
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

        // Estadísticas
        $statsQuery = EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        });

        if ($this->selectedCampaign) {
            $statsQuery->where('campaign_id', $this->selectedCampaign);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'confirmed' => (clone $statsQuery)->where('status', 'confirmed')->count(),
            'with_actual' => (clone $statsQuery)->whereNotNull('actual_total_yield')->count(),
        ];

        return view('livewire.viticulturist.digital-notebook.estimated-yields.index', [
            'estimatedYields' => $estimatedYields,
            'campaigns' => $campaigns,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Rendimientos Estimados - Agro365',
            'description' => 'Gestiona las estimaciones de producción de tus viñedos. Compara rendimientos estimados vs reales y optimiza tu planificación.',
        ]);
    }
}

