<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Livewire\Concerns\WithListing;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithListing, WithToastNotifications;

    public $selectedCampaign = '';

    public $filterStatus = '';

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (empty($this->selectedCampaign)) {
            $active = Campaign::where('viticulturist_id', Auth::id())
                ->where('active', true)
                ->first();
            if ($active) {
                $this->selectedCampaign = $active->id;
            }
        }
    }

    public function updatingSelectedCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedCampaign = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function toggleActive($yieldId): void
    {
        $user = Auth::user();
        $yield = EstimatedYield::whereHas('plotPlanting.plot', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->findOrFail($yieldId);

        $newActive = ! $yield->active;
        $yield->update(['active' => $newActive]);

        if ($newActive) {
            $this->toastSuccess(__('Estimación activada exitosamente.'));
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess(__('Estimación desactivada exitosamente.'));
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function render()
    {
        $user = Auth::user();

        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        $query = EstimatedYield::query()
            ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
            ->whereHas('plotPlanting.plot', fn ($q) => $q->where('viticulturist_id', $user->id));

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } else {
            $query->where('active', false);
        }

        if ($this->selectedCampaign) {
            $query->where('campaign_id', $this->selectedCampaign);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('notes', 'like', '%'.$this->search.'%')
                    ->orWhereHas('plotPlanting.plot', fn ($s) => $s->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('plotPlanting.grapeVariety', fn ($s) => $s->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        $estimatedYields = $query->orderBy('estimation_date', 'desc')->paginate(15);

        $statsBase = EstimatedYield::whereHas('plotPlanting.plot', fn ($q) => $q->where('viticulturist_id', $user->id));

        $stats = [
            'active' => (clone $statsBase)->where('active', true)->count(),
            'inactive' => (clone $statsBase)->where('active', false)->count(),
        ];

        return view('livewire.viticulturist.digital-notebook.estimated-yields.index', [
            'estimatedYields' => $estimatedYields,
            'campaigns' => $campaigns,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => __('Rendimientos Estimados - Agro365'),
            'description' => __('Gestiona las estimaciones de producción de tus viñedos.'),
        ]);
    }
}
