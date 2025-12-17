<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $yearFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'yearFilter' => ['except' => ''],
    ];

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
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
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
            session()->flash('error', 'No tienes permiso para activar esta campaña.');
            return;
        }

        try {
            $campaign->activate();
            session()->flash('message', 'Campaña activada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al activar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $campaignId,
                'user_id' => Auth::id(),
            ]);

            session()->flash('error', 'Error al activar la campaña. Por favor, intenta de nuevo.');
        }
    }

    public function delete($campaignId)
    {
        $campaign = Campaign::withCount('activities')->findOrFail($campaignId);

        if (!Auth::user()->can('delete', $campaign)) {
            session()->flash('error', 'No tienes permiso para eliminar esta campaña.');
            return;
        }

        // Validar que no tenga actividades
        if ($campaign->activities_count > 0) {
            session()->flash('error', 'No se puede eliminar una campaña que tiene actividades registradas.');
            return;
        }

        try {
            $campaign->delete();
            session()->flash('message', 'Campaña eliminada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $campaignId,
                'user_id' => Auth::id(),
            ]);

            session()->flash('error', 'Error al eliminar la campaña. Por favor, intenta de nuevo.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->yearFilter = '';
        $this->resetPage();
    }
}
