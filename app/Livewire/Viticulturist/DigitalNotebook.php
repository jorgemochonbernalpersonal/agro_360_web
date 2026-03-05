<?php

namespace App\Livewire\Viticulturist;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\PhytosanitaryProduct;
use App\Models\Plot;
use App\Repositories\AgriculturalActivityRepository;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class DigitalNotebook extends Component
{
    use WithPagination, WithToastNotifications;

    // Campaña
    public $selectedCampaign = null;

    // Filtros (equivalente a ActivityFilters)
    public $selectedPlot    = null;
    public $activityType    = null;
    public $search          = '';
    public $dateFrom        = null;
    public $dateTo          = null;
    public $productFilter   = null;

    // Auditoría (equivalente a ActivityList)
    public $showAuditHistory  = false;
    public $selectedActivityId = null;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'selectedPlot'     => ['except' => ''],
        'activityType'     => ['except' => ''],
        'search'           => ['except' => ''],
        'dateFrom'         => ['except' => ''],
        'dateTo'           => ['except' => ''],
        'productFilter'    => ['except' => ''],
    ];

    public function mount()
    {
        if (!Auth::user()->can('viewAny', AgriculturalActivity::class)) {
            abort(403, 'No tienes permiso para ver actividades agrícolas.');
        }

        // Si viene selectedCampaign por queryString, verificar que pertenece al usuario
        if ($this->selectedCampaign) {
            $valid = Campaign::forViticulturist(Auth::id())
                ->where('id', $this->selectedCampaign)
                ->exists();
            if (!$valid) {
                $this->selectedCampaign = null;
            }
        }

        // Si no hay campaña seleccionada (o era inválida), usar la activa
        if (!$this->selectedCampaign) {
            $campaign = Campaign::getOrCreateActiveForYear(Auth::id());

            if (!$campaign) {
                $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
                return redirect()->route('viticulturist.campaign.index');
            }

            $this->selectedCampaign = $campaign->id;
        }
    }

    public function updatedSelectedCampaign(): void
    {
        $this->resetFilters();
        $this->resetPage();
    }

    public function updatedActivityType(): void
    {
        $this->productFilter = null;
        $this->resetPage();
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingSelectedPlot(): void  { $this->resetPage(); }
    public function updatingDateFrom(): void  { $this->resetPage(); }
    public function updatingDateTo(): void    { $this->resetPage(); }
    public function updatingProductFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->resetFilters();
        $this->resetPage();
    }

    private function resetFilters(): void
    {
        $this->selectedPlot  = null;
        $this->activityType  = null;
        $this->search        = '';
        $this->dateFrom      = null;
        $this->dateTo        = null;
        $this->productFilter = null;
    }

    public function deleteActivity($activityId): void
    {
        $activity = AgriculturalActivity::findOrFail($activityId);

        if (!Auth::user()->can('delete', $activity)) {
            $this->toastError('No tienes permiso para eliminar esta actividad.');
            return;
        }

        if ($activity->isLocked()) {
            $this->toastError('No se puede eliminar una actividad bloqueada. Las actividades se bloquean automáticamente después de ' . config('activities.lock_days', 7) . ' días para cumplimiento PAC.');
            return;
        }

        try {
            $activity->delete();
            $this->toastSuccess('Actividad eliminada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar actividad', [
                'error'       => $e->getMessage(),
                'activity_id' => $activityId,
                'user_id'     => Auth::id(),
            ]);
            $this->toastError('Error al eliminar la actividad. Por favor, intenta de nuevo.');
        }
    }

    public function openAuditHistory($activityId): void
    {
        $this->selectedActivityId = $activityId;
        $this->showAuditHistory   = true;
    }

    public function closeAuditHistory(): void
    {
        $this->showAuditHistory   = false;
        $this->selectedActivityId = null;
    }

    #[Layout('layouts.app', [
        'title'       => 'Cuaderno Digital - Agro365',
        'description' => 'Registro completo de todas tus actividades agrícolas.',
    ])]
    public function render()
    {
        $user = Auth::user();
        $repository = app(AgriculturalActivityRepository::class);

        // Campañas para el selector
        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        $currentCampaign = Campaign::find($this->selectedCampaign);

        // Estadísticas de la campaña actual
        $stats = $currentCampaign
            ? $repository->getStatsForCampaign($user->id, $currentCampaign->id, $this->getFilters())
            : ['total' => 0, 'phytosanitary' => 0, 'fertilization' => 0, 'irrigation' => 0, 'cultural' => 0, 'observation' => 0, 'harvest' => 0];

        // Parcelas activas para el filtro
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        // Productos fitosanitarios (solo si el filtro de tipo es phytosanitary)
        $products = $this->activityType === 'phytosanitary'
            ? PhytosanitaryProduct::forUser($user->id)
                ->select(['id', 'name'])
                ->where('active', true)
                ->orderBy('name')
                ->get()
            : collect();

        // Actividades paginadas con todos los filtros
        $activities = $repository->findForViticulturist($user, $this->getFilters(), 10);

        return view('livewire.viticulturist.digital-notebook', [
            'campaigns'      => $campaigns,
            'currentCampaign'=> $currentCampaign,
            'stats'          => $stats,
            'plots'          => $plots,
            'products'       => $products,
            'activities'     => $activities,
        ]);
    }

    private function getFilters(): array
    {
        return array_filter([
            'campaign_id'    => $this->selectedCampaign,
            'plot_id'        => $this->selectedPlot    ?: null,
            'activity_type'  => $this->activityType    ?: null,
            'search'         => $this->search          ?: null,
            'date_from'      => $this->dateFrom        ?: null,
            'date_to'        => $this->dateTo          ?: null,
            'product_filter' => $this->productFilter   ?: null,
        ]);
    }
}
