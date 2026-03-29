<?php

namespace App\Livewire\Winery;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserPreferences;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\Harvest;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\Wine;
use App\Models\WineFermentationControl;
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
    public string $containerSort        = 'name'; // name | pct_desc | pct_asc
    public string $mapTileMode          = 'satellite'; // satellite | street
    public bool   $mapShowList          = false;

    public function mount(): void
    {
        // Tab no se persiste: siempre arranca en mapa de parcelas.
        $this->mapTileMode = $this->getPreference('winery_map_tile', 'satellite');
        $this->mapShowList = (bool) $this->getPreference('winery_map_show_list', false);
    }

    public function saveTileMode(string $mode): void
    {
        $this->mapTileMode = $mode;
        $this->savePreference('winery_map_tile', $mode);
    }

    public function saveShowList(bool $show): void
    {
        $this->mapShowList = $show;
        $this->savePreference('winery_map_show_list', $show);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab           = $tab;
        $this->selectedPlotId      = null;
        $this->selectedContainerId = null;
    }

    public function selectPlot(int $id): void
    {
        $this->selectedPlotId = ($this->selectedPlotId === $id) ? null : $id;
    }

    public function selectContainer(int $id): void
    {
        $this->selectedContainerId = ($this->selectedContainerId === $id) ? null : $id;
    }

    public function openContainer(int $id): void
    {
        $this->activeTab           = 'containers';
        $this->selectedContainerId = $id;
        $this->dispatch('set-active-tab', tab: 'containers');
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
        $allPlots = Plot::forUser($user)
            ->where('active', true)
            ->with(['municipality:id,name,lat,lng', 'province:id,name', 'viticulturist:id,name', 'sigpacCodes:id', 'autonomousCommunity:id,name', 'plantings.grapeVariety:id,name'])
            ->select(['id', 'name', 'area', 'active', 'municipality_id', 'province_id', 'autonomous_community_id', 'viticulturist_id'])
            ->get();

        // Parcelas sin municipio con coordenadas → fallback al centroide SIGPAC
        $needsFallback = $allPlots
            ->filter(fn($p) => !($p->municipality?->lat && $p->municipality?->lng))
            ->pluck('id')
            ->all();

        $sigpacCentroids = [];
        if (!empty($needsFallback)) {
            $placeholders = implode(',', array_fill(0, count($needsFallback), '?'));
            // Centroide de la unión de TODOS los polígonos SIGPAC del plot
            // (un plot puede tener varios multipart_plot_sigpac, cada uno con su geometry)
            $rows = \Illuminate\Support\Facades\DB::select(
                "SELECT mps.plot_id,
                        ST_AsText(ST_Centroid(ST_Union(pg.coordinates))) AS centroid_wkt
                 FROM   multipart_plot_sigpac mps
                 JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
                 WHERE  mps.plot_id IN ($placeholders)
                 AND    pg.coordinates IS NOT NULL
                 GROUP BY mps.plot_id",
                $needsFallback
            );
            foreach ($rows as $row) {
                if ($row->centroid_wkt && preg_match('/POINT\(([^)]+)\)/', $row->centroid_wkt, $m)) {
                    $parts = explode(' ', trim($m[1]));
                    if (count($parts) >= 2) {
                        $sigpacCentroids[$row->plot_id] = ['lat' => (float) $parts[1], 'lng' => (float) $parts[0]];
                    }
                }
            }
        }

        $mapPlots = $allPlots
            ->map(function ($p) use ($sigpacCentroids) {
                if ($p->municipality?->lat && $p->municipality?->lng) {
                    $lat    = (float) $p->municipality->lat;
                    $lng    = (float) $p->municipality->lng;
                    $source = 'municipality';
                } elseif (isset($sigpacCentroids[$p->id])) {
                    $lat    = $sigpacCentroids[$p->id]['lat'];
                    $lng    = $sigpacCentroids[$p->id]['lng'];
                    $source = 'sigpac';
                } else {
                    return null; // sin coordenadas de ningún tipo
                }
                $primaryVariety = $p->plantings->first()?->grapeVariety;
                return [
                    'id'                      => $p->id,
                    'name'                    => $p->name,
                    'area'                    => $p->area,
                    'lat'                     => $lat,
                    'lng'                     => $lng,
                    'source'                  => $source,
                    'municipality'            => $p->municipality?->name,
                    'municipality_id'         => $p->municipality_id,
                    'province'                => $p->province?->name,
                    'province_id'             => $p->province_id,
                    'community'               => $p->autonomousCommunity?->name,
                    'autonomous_community_id' => $p->autonomous_community_id,
                    'viticulturist'           => $p->viticulturist?->name,
                    'has_sigpac'              => $p->sigpacCodes->isNotEmpty(),
                    'variety_id'              => $primaryVariety?->id,
                    'variety_name'            => $primaryVariety?->name,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        // ── Polígonos SIGPAC ───────────────────────────────────────────────
        $allPlotIds  = $allPlots->pluck('id')->all();
        $mapPolygons = [];
        if (!empty($allPlotIds)) {
            $placeholders = implode(',', array_fill(0, count($allPlotIds), '?'));
            $polyRows = \Illuminate\Support\Facades\DB::select(
                "SELECT mps.plot_id, sc.code AS sigpac_code,
                        ST_AsText(pg.coordinates) AS wkt
                 FROM   multipart_plot_sigpac mps
                 JOIN   sigpac_code sc ON mps.sigpac_code_id  = sc.id
                 JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
                 WHERE  mps.plot_id IN ($placeholders)
                 AND    pg.coordinates IS NOT NULL",
                $allPlotIds
            );
            foreach ($polyRows as $row) {
                $coords = $this->parseWktToLatLng($row->wkt);
                if (!empty($coords)) {
                    $mapPolygons[] = [
                        'plot_id'     => $row->plot_id,
                        'sigpac_code' => $row->sigpac_code,
                        'coords'      => $coords,
                    ];
                }
            }
        }

        // ── Opciones de filtro (CCAA / Provincia / Municipio) ──────────────
        $communityIds    = $allPlots->pluck('autonomous_community_id')->filter()->unique()->values()->all();
        $provinceIds     = $allPlots->pluck('province_id')->filter()->unique()->values()->all();
        $municipalityIds = $allPlots->pluck('municipality_id')->filter()->unique()->values()->all();

        $filterOptions = ['communities' => [], 'provinces' => [], 'municipalities' => []];
        if (!empty($communityIds)) {
            $filterOptions['communities'] = AutonomousCommunity::whereIn('id', $communityIds)
                ->orderBy('name')->get(['id', 'name'])
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
                ->values()->toArray();
        }
        if (!empty($provinceIds)) {
            $filterOptions['provinces'] = Province::whereIn('id', $provinceIds)
                ->orderBy('name')->get(['id', 'name', 'autonomous_community_id'])
                ->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'community_id' => $p->autonomous_community_id])
                ->values()->toArray();
        }
        if (!empty($municipalityIds)) {
            $filterOptions['municipalities'] = Municipality::whereIn('id', $municipalityIds)
                ->orderBy('name')->get(['id', 'name', 'province_id'])
                ->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'province_id' => $m->province_id])
                ->values()->toArray();
        }

        // Última actividad de la parcela seleccionada
        $selectedPlotLastActivity = $this->selectedPlotId
            ? AgriculturalActivity::where('plot_id', $this->selectedPlotId)
                ->latest('activity_date')
                ->first(['id', 'activity_type', 'activity_date'])
            : null;

        // Detalle de la parcela seleccionada
        $selectedPlot = $this->selectedPlotId
            ? Plot::forUser($user)
                ->with([
                    'municipality:id,name',
                    'province:id,name',
                    'viticulturist:id,name',
                    'sigpacCodes:id,code',
                    'multiplePlotSigpacs.plotGeometry',
                    'plantings.grapeVariety:id,name',
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

        $containers = $containersQuery->orderBy('name')->get();
        if ($this->containerSort === 'pct_desc') {
            $containers = $containers->sortByDesc(fn($c) => $c->getOccupancyPercentage())->values();
        } elseif ($this->containerSort === 'pct_asc') {
            $containers = $containers->sortBy(fn($c) => $c->getOccupancyPercentage())->values();
        }
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
        $totalCapacityKg = (float) $allContainers->sum('capacity');
        $totalUsedKg     = (float) $allContainers->sum('used_capacity');
        $totalWineLiters = (float) $allContainers->sum('wine_volume_liters');
        $containerStats = [
            'total'              => $allContainers->count(),
            'full'               => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() >= 100)->count(),
            'critical'           => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() >= 85 && $c->getOccupancyPercentage() < 100)->count(),
            'empty'              => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() == 0)->count(),
            'total_capacity_kg'  => $totalCapacityKg,
            'total_used_kg'      => $totalUsedKg,
            'total_wine_liters'  => $totalWineLiters,
            'used_pct'           => $totalCapacityKg > 0 ? round($totalUsedKg / $totalCapacityKg * 100) : 0,
        ];

        // ── Dashboard (tab resumen) ─────────────────────────────────────
        $currentYear = (int) date('Y');

        $kgReceived = Harvest::where('winery_id', $userId)
            ->whereYear('created_at', $currentYear)
            ->sum('total_weight');

        $winesInProgress = Wine::where('user_id', $userId)
            ->where('status', 'in_progress')
            ->count();

        $activeFermentations = WineFermentationControl::whereHas(
                'wine', fn($q) => $q->where('user_id', $userId)
            )
            ->where('control_date', '>=', now()->subDays(7))
            ->where('brix_degree', '>', 2)
            ->distinct('wine_id')
            ->count('wine_id');

        $criticalContainerIds = $allContainers
            ->filter(fn($c) => $c->getOccupancyPercentage() >= 85)
            ->sortByDesc(fn($c) => $c->getOccupancyPercentage())
            ->take(8)
            ->pluck('id');

        $criticalContainers = $criticalContainerIds->isNotEmpty()
            ? Container::where('user_id', $userId)
                ->whereIn('id', $criticalContainerIds)
                ->where('archived', false)
                ->with(['containerType'])
                ->get()
                ->sortByDesc(fn($c) => $c->getOccupancyPercentage())
                ->values()
            : collect();

        $recentControls = WineFermentationControl::whereHas(
                'wine', fn($q) => $q->where('user_id', $userId)
            )
            ->with(['wine:id,name', 'container:id,name'])
            ->orderByDesc('control_date')
            ->take(8)
            ->get();

        $dashboardStats = [
            'kg_received'          => (float) $kgReceived,
            'wines_in_progress'    => $winesInProgress,
            'active_fermentations' => $activeFermentations,
            'campaign_year'        => $currentYear,
            'containers_total'     => $containerStats['total'],
            'containers_critical'  => $containerStats['critical'] + $containerStats['full'],
        ];

        return view('livewire.winery.visual-dashboard', [
            'mapPlots'                  => $mapPlots,
            'selectedPlotLastActivity'  => $selectedPlotLastActivity,
            'mapPolygons'         => $mapPolygons,
            'filterOptions'       => $filterOptions,
            'selectedPlot'        => $selectedPlot,
            'containers'          => $containers,
            'containerTypes'      => $containerTypes,
            'selectedContainer'   => $selectedContainer,
            'containerStats'      => $containerStats,
            'dashboardStats'      => $dashboardStats,
            'criticalContainers'  => $criticalContainers,
            'recentControls'      => $recentControls,
        ])->layout('layouts.app', ['title' => 'Vista Visual — Agro365']);
    }

    private function parseWktToLatLng(?string $wkt): array
    {
        if (!$wkt) return [];
        if (!preg_match('/POLYGON\(\(([^)]+)\)\)/', $wkt, $m)) return [];

        $points = [];
        foreach (explode(',', $m[1]) as $coord) {
            $parts = explode(' ', trim($coord));
            if (count($parts) >= 2) {
                $points[] = [(float) $parts[1], (float) $parts[0]]; // [lat, lng] for Leaflet
            }
        }
        return $points;
    }
}
