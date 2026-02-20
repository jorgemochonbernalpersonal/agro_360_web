<?php

namespace App\Livewire\Viticulturist\DigitalNotebook\Containers;

use App\Models\Container;
use App\Models\Harvest;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab         = 'active'; // 'active', 'inactive'
    public $search             = '';
    public $selectedCampaign   = '';
    public $selectedHarvest    = '';
    public $filterAvailability = '';

    protected $queryString = [
        'currentTab'         => ['as' => 'tab', 'except' => 'active'],
        'search'             => ['except' => ''],
        'selectedCampaign'   => ['except' => ''],
        'selectedHarvest'    => ['except' => ''],
        'filterAvailability' => ['except' => ''],
    ];

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingSelectedCampaign(): void { $this->selectedHarvest = ''; $this->resetPage(); }
    public function updatingSelectedHarvest(): void  { $this->resetPage(); }
    public function updatingFilterAvailability(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search             = '';
        $this->selectedCampaign   = '';
        $this->selectedHarvest    = '';
        $this->filterAvailability = '';
        $this->resetPage();
    }

    public function toggleActive($containerId): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $newArchived = !$container->archived;
        $container->archived = $newArchived;
        $container->save();

        if ($newArchived) {
            $this->toastSuccess('Contenedor desactivado exitosamente.');
            if ($this->currentTab === 'active') $this->currentTab = 'inactive';
        } else {
            $this->toastSuccess('Contenedor activado exitosamente.');
            if ($this->currentTab === 'inactive') $this->currentTab = 'active';
        }
    }

    public function render()
    {
        $user = Auth::user();

        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')
            ->get();

        $query = Container::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('harvests')
                  ->orWhereHas('harvests.activity', fn ($s) => $s->where('viticulturist_id', $user->id));
            });

        if ($this->currentTab === 'active') {
            $query->where('archived', false);
        } else {
            $query->where('archived', true);
        }

        if ($this->filterAvailability === 'available') {
            $query->whereDoesntHave('harvests');
        } elseif ($this->filterAvailability === 'assigned') {
            $query->whereHas('harvests.activity', fn ($q) => $q->where('viticulturist_id', $user->id));
        }

        if ($this->selectedCampaign) {
            $query->whereHas('harvests.activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign));
        }

        if ($this->selectedHarvest) {
            $query->whereHas('harvests', fn ($q) => $q->where('id', $this->selectedHarvest));
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('harvests.activity.plot', fn ($s) => $s->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        $containers = $query
            ->with(['harvests.activity.plot', 'harvests.plotPlanting.grapeVariety', 'currentState'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $harvests = collect();
        if ($this->selectedCampaign) {
            $harvests = Harvest::whereHas('activity', function ($q) {
                $q->where('campaign_id', $this->selectedCampaign)
                  ->where('viticulturist_id', Auth::id());
            })
            ->with(['plotPlanting.grapeVariety', 'activity.plot'])
            ->orderBy('harvest_start_date', 'desc')
            ->get();
        }

        $base = Container::where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('harvests')
                  ->orWhereHas('harvests.activity', fn ($s) => $s->where('viticulturist_id', $user->id));
            });

        $stats = [
            'active'         => (clone $base)->where('archived', false)->count(),
            'inactive'       => (clone $base)->where('archived', true)->count(),
            'total_capacity' => (clone $base)->sum('capacity'),
            'total_used'     => (clone $base)->sum('used_capacity'),
        ];

        return view('livewire.viticulturist.digital-notebook.containers.index', [
            'containers' => $containers,
            'campaigns'  => $campaigns,
            'harvests'   => $harvests,
            'stats'      => $stats,
        ])->layout('layouts.app', [
            'title'       => 'Contenedores de Cosecha - Agro365',
            'description' => 'Gestiona tus contenedores de cosecha.',
        ]);
    }
}
