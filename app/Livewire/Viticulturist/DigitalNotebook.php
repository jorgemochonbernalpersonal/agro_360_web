<?php

namespace App\Livewire\Viticulturist;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\Campaign;
use App\Models\Crew;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class DigitalNotebook extends Component
{
    use WithPagination, WithToastNotifications;

    public $selectedCampaign = null;
    public $selectedPlot = null;
    public $activityType = null;
    public $search = '';
    public $dateFrom = null;
    public $dateTo = null;
    public $productFilter = null;
    public $showAuditHistory = false;
    public $selectedActivityId = null;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'selectedPlot' => ['except' => ''],
        'activityType' => ['except' => ''],
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'productFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Validar autorización para ver actividades
        if (!Auth::user()->can('viewAny', \App\Models\AgriculturalActivity::class)) {
            abort(403, 'No tienes permiso para ver actividades agrícolas.');
        }
        
        // Obtener o crear campaña activa del año actual
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        
        if (!$campaign) {
            // Si no se pudo obtener/crear campaña, redirigir
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return redirect()->route('viticulturist.campaign.index');
        }
        
        $this->selectedCampaign = $campaign->id;
    }

    public function render()
    {
        $user = Auth::user();
        
        // ✅ OPTIMIZACIÓN: Cargar solo campos necesarios
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->select(['id', 'name', 'area'])
            ->orderBy('name')
            ->get();

        // Obtener o crear campaña activa si no hay seleccionada
        if (!$this->selectedCampaign) {
            $campaign = Campaign::getOrCreateActiveForYear($user->id);
            
            if (!$campaign) {
                // Si no se pudo obtener/crear campaña, mostrar mensaje
                $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
                // Continuar renderizando pero sin campaña seleccionada
            } else {
                $this->selectedCampaign = $campaign->id;
            }
        }

        // Obtener actividades
        $query = AgriculturalActivity::forViticulturist($user->id)
            ->with(['plot', 'plotPlanting.grapeVariety', 'crew', 'crewMember.viticulturist', 'campaign'])
            ->orderBy('activity_date', 'desc');

        // Filtrar por campaña (siempre)
        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }

        if ($this->selectedPlot) {
            $query->forPlot($this->selectedPlot);
        }

        if ($this->activityType) {
            $query->ofType($this->activityType);
        }

        if ($this->dateFrom) {
            $query->where('activity_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('activity_date', '<=', $this->dateTo);
        }

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(notes) LIKE ?', [$search])
                  ->orWhereHas('plot', function($plotQuery) use ($search) {
                      $plotQuery->whereRaw('LOWER(name) LIKE ?', [$search]);
                  })
                  ->orWhereHas('phytosanitaryTreatment.product', function($productQuery) use ($search) {
                      $productQuery->whereRaw('LOWER(name) LIKE ?', [$search]);
                  })
                  ->orWhereHas('fertilization', function($fertQuery) use ($search) {
                      $fertQuery->whereRaw('LOWER(fertilizer_name) LIKE ?', [$search]);
                  });
            });
        }

        if ($this->productFilter && $this->activityType === 'phytosanitary') {
            $query->whereHas('phytosanitaryTreatment', function($treatmentQuery) {
                $treatmentQuery->where('product_id', $this->productFilter);
            });
        }

        // ✅ OPTIMIZACIÓN: Obtener todas las estadísticas en una sola query
        $baseQuery = AgriculturalActivity::forViticulturist($user->id)
            ->forCampaign($this->selectedCampaign);
        
        if ($this->selectedPlot) {
            $baseQuery->forPlot($this->selectedPlot);
        }
        if ($this->dateFrom) {
            $baseQuery->where('activity_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $baseQuery->where('activity_date', '<=', $this->dateTo);
        }

        // ✅ OPTIMIZADO: Una sola query con agregaciones en lugar de 4 queries separadas
        $stats = $baseQuery
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as phytosanitary,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as fertilization,
                SUM(CASE WHEN activity_type = ? THEN 1 ELSE 0 END) as irrigation
            ', ['phytosanitary', 'fertilization', 'irrigation'])
            ->first();
        
        $totalActivities = $stats->total ?? 0;
        $phytosanitaryCount = $stats->phytosanitary ?? 0;
        $fertilizationCount = $stats->fertilization ?? 0;
        $irrigationCount = $stats->irrigation ?? 0;

        $activities = $query->paginate(10);

        // ✅ OPTIMIZACIÓN: Cargar solo campos necesarios para el filtro
        $products = $this->activityType === 'phytosanitary' 
            ? PhytosanitaryProduct::select(['id', 'name'])
                ->where('active', true)
                ->orderBy('name')
                ->get() 
            : collect();

        // Obtener todas las campañas del viticultor
        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Campaña seleccionada
        $currentCampaign = Campaign::find($this->selectedCampaign);

        return view('livewire.viticulturist.digital-notebook', [
            'plots' => $plots,
            'activities' => $activities,
            'products' => $products,
            'campaigns' => $campaigns,
            'currentCampaign' => $currentCampaign,
            'stats' => [
                'total' => $totalActivities,
                'phytosanitary' => $phytosanitaryCount,
                'fertilization' => $fertilizationCount,
                'irrigation' => $irrigationCount,
            ],
        ]);
    }

    public function clearFilters()
    {
        // No limpiar selectedCampaign, mantener la campaña activa
        $this->selectedPlot = null;
        $this->activityType = null;
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->productFilter = null;
        $this->resetPage();
    }

    public function updatedSelectedCampaign()
    {
        // Resetear página cuando cambia la campaña
        $this->resetPage();
    }

    public function updatedActivityType()
    {
        // Limpiar filtro de producto si cambia el tipo de actividad
        $this->productFilter = null;
        $this->resetPage();
    }

    public function deleteActivity($activityId)
    {
        $activity = AgriculturalActivity::findOrFail($activityId);
        
        // Validar autorización
        if (!Auth::user()->can('delete', $activity)) {
            $this->toastError('No tienes permiso para eliminar esta actividad.');
            return;
        }

        // Verificar si está bloqueada
        if ($activity->isLocked()) {
            $this->toastError('🔒 No se puede eliminar una actividad bloqueada. Las actividades se bloquean automáticamente después de ' . config('activities.lock_days', 7) . ' días para cumplimiento PAC.');
            return;
        }

        try {
            $activity->delete();
            $this->toastSuccess('Actividad eliminada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar actividad', [
                'error' => $e->getMessage(),
                'activity_id' => $activityId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al eliminar la actividad. Por favor, intenta de nuevo.');
        }
    }
    
    public function getActivityAuditData($activityId)
    {
        $activity = AgriculturalActivity::with(['plot', 'auditLogs.user'])
            ->forUser(Auth::id())
            ->findOrFail($activityId);
        
        // Verificar autorización
        if (!Auth::user()->can('view', $activity)) {
            return null;
        }
        
        return $activity;
    }

    public function openAuditHistory($activityId)
    {
        $this->selectedActivityId = $activityId;
        $this->showAuditHistory = true;
    }

    public function closeAuditHistory()
    {
        $this->showAuditHistory = false;
        $this->selectedActivityId = null;
    }
}

