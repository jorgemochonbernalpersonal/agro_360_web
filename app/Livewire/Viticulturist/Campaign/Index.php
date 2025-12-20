<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $yearFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'yearFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingYearFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Validar autorización
        if (!Auth::user()->can('viewAny', Campaign::class)) {
            abort(403, 'No tienes permiso para ver campañas.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Campaign::forViticulturist($user->id)
            ->withCount('activities')
            ->orderBy('year', 'desc');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
            });
        }

        if ($this->yearFilter) {
            $query->forYear($this->yearFilter);
        }

        $campaigns = $query->paginate(10);

        // Obtener años únicos para el filtro
        $years = Campaign::forViticulturist($user->id)
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('livewire.viticulturist.campaign.index', [
            'campaigns' => $campaigns,
            'years' => $years,
        ])->layout('layouts.app');
    }

    public function activate($campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        if (!Auth::user()->can('activate', $campaign)) {
            $this->toastError('No tienes permiso para activar esta campaña.');
            return;
        }

        try {
            $campaign->activate();
            $this->toastSuccess('Campaña activada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al activar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $campaignId,
                'user_id' => Auth::id(),
            ]);

            $this->toastError('Error al activar la campaña. Por favor, intenta de nuevo.');
        }
    }

    public function delete($campaignId)
    {
        $campaign = Campaign::withCount('activities')->findOrFail($campaignId);

        if (!Auth::user()->can('delete', $campaign)) {
            $this->toastError('No tienes permiso para eliminar esta campaña.');
            return;
        }

        // Validar que no tenga actividades
        if ($campaign->activities_count > 0) {
            $this->toastError('No se puede eliminar una campaña que tiene actividades registradas.');
            return;
        }

        try {
            $campaign->delete();
            $this->toastSuccess('Campaña eliminada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $campaignId,
                'user_id' => Auth::id(),
            ]);

            $this->toastError('Error al eliminar la campaña. Por favor, intenta de nuevo.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->yearFilter = '';
        $this->resetPage();
    }
}
