<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive'
    public $search = '';
    public $yearFilter = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
        'yearFilter' => ['except' => ''],
    ];

    public function mount()
    {
        if (!Auth::user()->can('viewAny', Campaign::class)) {
            abort(403, 'No tienes permiso para ver campañas.');
        }
    }

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingYearFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->yearFilter = '';
        $this->resetPage();
    }

    public function toggleActive($campaignId): void
    {
        $campaign = Campaign::forViticulturist(Auth::id())->findOrFail($campaignId);

        if (!Auth::user()->can('update', $campaign)) {
            $this->toastError('No tienes permiso para modificar esta campaña.');
            return;
        }

        if ($campaign->active) {
            $campaign->update(['active' => false]);
            $this->toastSuccess("Campaña {$campaign->year} desactivada.");
        } else {
            $campaign->activate();
            $this->toastSuccess("Campaña {$campaign->year} activada como campaña actual.");
            $this->currentTab = 'active';
        }
    }

    public function delete($campaignId): void
    {
        $campaign = Campaign::forViticulturist(Auth::id())->withCount('activities')->findOrFail($campaignId);

        if (!Auth::user()->can('delete', $campaign)) {
            $this->toastError('No tienes permiso para eliminar esta campaña.');
            return;
        }

        if ($campaign->activities_count > 0) {
            $this->toastError('No se puede eliminar una campaña que tiene actividades registradas.');
            return;
        }

        try {
            $campaign->delete();
            $this->toastSuccess('Campaña eliminada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar campaña', [
                'error'       => $e->getMessage(),
                'campaign_id' => $campaignId,
                'user_id'     => Auth::id(),
            ]);
            $this->toastError('Error al eliminar la campaña. Por favor, intenta de nuevo.');
        }
    }

    #[Layout('layouts.app', [
        'title'       => 'Campañas Agrícolas - Agro365',
        'description' => 'Gestiona tus campañas agrícolas por año. Organiza y controla todas las actividades de cada temporada vitivinícola.',
    ])]
    public function render()
    {
        $user = Auth::user();

        $query = Campaign::forViticulturist($user->id)
            ->withCount('activities')
            ->orderBy('year', 'desc');

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } else {
            $query->where('active', false);
        }

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
            });
        }

        if ($this->yearFilter) {
            $query->forYear($this->yearFilter);
        }

        $campaigns = $query->paginate(12);

        $stats = [
            'active'   => Campaign::forViticulturist($user->id)->where('active', true)->count(),
            'inactive' => Campaign::forViticulturist($user->id)->where('active', false)->count(),
        ];
        $years = Campaign::forViticulturist($user->id)
            ->orderByDesc('year')
            ->distinct()
            ->pluck('year');

        return view('livewire.viticulturist.campaign.index', [
            'campaigns' => $campaigns,
            'stats'     => $stats,
            'years'     => $years,
        ]);
    }
}
