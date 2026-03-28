<?php

namespace App\Livewire\Winery;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserPreferences;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\Plot;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VisualDashboard extends Component
{
    use WithToastNotifications, WithUserPreferences;

    public string $activeTab            = 'plots';
    public ?int   $selectedPlotId       = null;
    public ?int   $selectedContainerId  = null;
    public string $containerSearch      = '';
    public string $containerTypeFilter  = '';

    public function mount(): void
    {
        $this->activeTab = $this->getPreference('winery_visual_tab', 'plots');
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab           = $tab;
        $this->selectedPlotId      = null;
        $this->selectedContainerId = null;
        $this->savePreference('winery_visual_tab', $tab);
    }

    public function selectPlot(int $id): void
    {
        $this->selectedPlotId = ($this->selectedPlotId === $id) ? null : $id;
    }

    public function selectContainer(int $id): void
    {
        $this->selectedContainerId = ($this->selectedContainerId === $id) ? null : $id;
    }

    public function emptyWine(int $containerId): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        app(WineContainerStockService::class)->emptyWineContent($container);
        $this->toastSuccess("Contenedor «{$container->name}» vaciado de vino.");
    }

    public function archiveContainer(int $containerId): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => true]);
        $this->toastSuccess("Contenedor «{$container->name}» desactivado.");
        if ($this->selectedContainerId === $containerId) {
            $this->selectedContainerId = null;
        }
    }

    public function unarchiveContainer(int $containerId): void
    {
        $container = Container::where('user_id', Auth::id())->findOrFail($containerId);
        $container->update(['archived' => false]);
        $this->toastSuccess("Contenedor «{$container->name}» activado.");
    }

    public function render()
    {
        $userId = Auth::id();
        $user   = Auth::user();

        // ── Parcelas (tab mapa) ─────────────────────────────────────────
        $mapPlots = Plot::forUser($user)
            ->where('active', true)
            ->with(['municipality:id,name,lat,lng', 'province:id,name', 'viticulturist:id,name', 'sigpacCodes:id'])
            ->select(['id', 'name', 'area', 'active', 'municipality_id', 'province_id', 'viticulturist_id'])
            ->get()
            ->filter(fn($p) => $p->municipality?->lat && $p->municipality?->lng)
            ->map(fn($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'area'          => $p->area,
                'lat'           => (float) $p->municipality->lat,
                'lng'           => (float) $p->municipality->lng,
                'municipality'  => $p->municipality?->name,
                'province'      => $p->province?->name,
                'viticulturist' => $p->viticulturist?->name,
                'has_sigpac'    => $p->sigpacCodes->isNotEmpty(),
            ])
            ->values()
            ->toArray();

        // Detalle de la parcela seleccionada
        $selectedPlot = $this->selectedPlotId
            ? Plot::forUser($user)
                ->with([
                    'municipality:id,name',
                    'province:id,name',
                    'viticulturist:id,name',
                    'sigpacCodes:id,code,plot_id',
                    'multiplePlotSigpacs.plotGeometry',
                ])
                ->find($this->selectedPlotId)
            : null;

        // ── Contenedores (tab bodega) ───────────────────────────────────
        $containersQuery = Container::where('user_id', $userId)
            ->where('archived', false)
            ->with(['containerType', 'containerRoom']);

        if ($this->containerSearch) {
            $term = '%' . mb_strtolower($this->containerSearch) . '%';
            $containersQuery->where(fn($q) => $q
                ->whereRaw('LOWER(name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(IFNULL(description, \'\')) LIKE ?', [$term])
            );
        }

        if ($this->containerTypeFilter) {
            $containersQuery->where('type_id', $this->containerTypeFilter);
        }

        $containers     = $containersQuery->orderBy('name')->get();
        $containerTypes = ContainerType::orderBy('name')->get();

        // Detalle del contenedor seleccionado
        $selectedContainer = $this->selectedContainerId
            ? Container::where('user_id', $userId)
                ->with([
                    'containerType',
                    'containerMaterial',
                    'containerRoom',
                    'currentState.wine',
                    'currentState.harvest.batch.grapeVariety',
                ])
                ->find($this->selectedContainerId)
            : null;

        // Stats rápidas de contenedores
        $allContainers  = Container::where('user_id', $userId)->where('archived', false)->get(['id', 'capacity', 'used_capacity', 'wine_volume_liters']);
        $containerStats = [
            'total'    => $allContainers->count(),
            'full'     => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() >= 100)->count(),
            'critical' => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() >= 85 && $c->getOccupancyPercentage() < 100)->count(),
            'empty'    => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() == 0)->count(),
        ];

        return view('livewire.winery.visual-dashboard', [
            'mapPlots'          => $mapPlots,
            'selectedPlot'      => $selectedPlot,
            'containers'        => $containers,
            'containerTypes'    => $containerTypes,
            'selectedContainer' => $selectedContainer,
            'containerStats'    => $containerStats,
        ])->layout('layouts.app', ['title' => 'Vista Visual — Agro365']);
    }
}
