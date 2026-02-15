<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Repositories\AgriculturalActivityRepository;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ActivityList extends Component
{
    use WithPagination, WithToastNotifications;

    public $selectedCampaign;
    public $filters = [];
    public $showAuditHistory = false;
    public $selectedActivityId = null;

    protected $listeners = [
        'filters-updated' => 'updateFilters',
        'filters-cleared' => 'clearFilters',
    ];

    public function mount($selectedCampaign, $filters = [])
    {
        $this->selectedCampaign = $selectedCampaign;
        $this->filters = $filters;
    }

    public function render()
    {
        $repository = app(AgriculturalActivityRepository::class);
        $user = Auth::user();

        // Asegurar que campaign_id está en los filtros
        $this->filters['campaign_id'] = $this->selectedCampaign;

        $activities = $repository->findForViticulturist($user, $this->filters, 10);

        return view('livewire.viticulturist.digital-notebook.activity-list', [
            'activities' => $activities,
        ]);
    }

    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->filters = ['campaign_id' => $this->selectedCampaign];
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
            $this->dispatch('activity-deleted');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar actividad', [
                'error' => $e->getMessage(),
                'activity_id' => $activityId,
                'user_id' => Auth::id(),
            ]);

            $this->toastError('Error al eliminar la actividad. Por favor, intenta de nuevo.');
        }
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
}
