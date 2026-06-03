<?php

namespace App\Livewire\Winery;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserPreferences;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use App\Models\WineTransfer;
use App\Models\WineryViticulturist;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class VisualDashboard extends Component
{
    use WithOwnershipRules, WithToastNotifications, WithUserPreferences;

    public string $activeTab            = 'plots';
    public ?int   $selectedPlotId       = null;
    public ?int   $selectedContainerId  = null;
    public string $containerSearch      = '';
    public string $containerTypeFilter  = '';
    public string $containerSort        = 'name';
    public string $mapTileMode          = 'satellite';
    public bool   $mapShowList          = false;

    // ── Modal: Recibir uva ─────────────────────────────────────────────────────
    public bool   $modalGrapeReception   = false;
    public string $gr_viticulturistId    = '';
    public string $gr_plotId             = '';
    public string $gr_plantingId         = '';
    public string $gr_containerId        = '';
    public string $gr_harvestDate        = '';
    public string $gr_totalWeight        = '';
    public int    $gr_vintageYear        = 0;
    public array  $gr_availablePlots     = [];
    public array  $gr_availablePlantings = [];

    // ── Modal: Control de fermentación ────────────────────────────────────────
    public bool   $modalFermentation = false;
    public string $fc_wineId         = '';
    public string $fc_containerId    = '';
    public string $fc_controlDate    = '';
    public string $fc_temperature    = '';
    public string $fc_brix           = '';

    // ── Modal: Trasvase de vino ───────────────────────────────────────────────
    public bool   $modalTransfer      = false;
    public string $tr_wineId          = '';
    public string $tr_fromContainerId = '';
    public string $tr_toContainerId   = '';
    public string $tr_quantity        = '';
    public string $tr_unitId          = '';
    public string $tr_transferType    = 'racking';
    public string $tr_transferDate    = '';

    // ── Modal: Nuevo vino ─────────────────────────────────────────────────────
    public bool   $modalWine  = false;
    public string $wine_name  = '';
    public string $wine_type  = '';

    // ── Modal: Nuevo contenedor ───────────────────────────────────────────────
    public bool   $modalContainer = false;
    public string $cont_name      = '';
    public string $cont_typeId    = '';
    public string $cont_capacity  = '';
    public string $cont_unit      = 'litros';

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mapTileMode    = $this->getPreference('winery_map_tile', 'satellite');
        $this->mapShowList    = (bool) $this->getPreference('winery_map_show_list', false);
        $this->gr_vintageYear = now()->year;
        $this->gr_harvestDate = now()->toDateString();
        $this->fc_controlDate = now()->format('Y-m-d\TH:i');
        $this->tr_transferDate = now()->toDateString();
    }

    // ── Preferencias ──────────────────────────────────────────────────────────

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

    // ── Navegación ────────────────────────────────────────────────────────────

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

    // ── Acciones contenedor ───────────────────────────────────────────────────

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

    // ══════════════════════════════════════════════════════════════════════════
    //  QUICK-ACTION MODALS
    // ══════════════════════════════════════════════════════════════════════════

    // ── Modal: Recibir uva ────────────────────────────────────────────────────

    public function openModalGrapeReception(?int $viticulturistId = null): void
    {
        $this->resetGrapeReceptionForm();
        $this->gr_harvestDate = now()->toDateString();
        $this->gr_vintageYear = now()->year;
        if ($viticulturistId) {
            $this->gr_viticulturistId = (string) $viticulturistId;
            $this->updatedGrViticulturistId();
        }
        $this->modalGrapeReception = true;
    }

    public function updatedGrViticulturistId(): void
    {
        $this->gr_plotId             = '';
        $this->gr_plantingId         = '';
        $this->gr_availablePlots     = [];
        $this->gr_availablePlantings = [];

        if (!$this->gr_viticulturistId) return;

        $userId   = Auth::id();
        $isSelf   = Auth::user()->isProducer() && (int)$this->gr_viticulturistId === $userId;
        $isLinked = $isSelf || WineryViticulturist::where('winery_id', $userId)
            ->where('viticulturist_id', $this->gr_viticulturistId)
            ->exists();

        if (!$isLinked) { $this->gr_viticulturistId = ''; return; }

        $this->gr_availablePlots = Plot::where('viticulturist_id', $this->gr_viticulturistId)
            ->where('active', true)
            ->whereHas('plantings', fn($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function updatedGrPlotId(): void
    {
        $this->gr_plantingId         = '';
        $this->gr_availablePlantings = [];

        if (!$this->gr_plotId) return;

        $this->gr_availablePlantings = PlotPlanting::where('plot_id', $this->gr_plotId)
            ->where('status', 'active')
            ->with('grapeVariety:id,name')
            ->get()
            ->map(fn($p) => [
                'id'    => $p->id,
                'label' => ($p->grapeVariety?->name ?? $p->name ?? 'Sin variedad')
                    . ($p->area_planted ? ' — ' . number_format($p->area_planted, 2) . ' ha' : ''),
            ])
            ->toArray();

        if (count($this->gr_availablePlantings) === 1) {
            $this->gr_plantingId = (string) $this->gr_availablePlantings[0]['id'];
        }
    }

    public function saveGrapeReception(): void
    {
        $this->validate([
            'gr_viticulturistId' => $this->linkedViticulturistRule(),
            'gr_plotId'          => $this->linkedPlotRule(),
            'gr_plantingId'      => $this->linkedPlotPlantingRule(true),
            'gr_harvestDate'     => ['required', 'date'],
            'gr_totalWeight'     => ['required', 'numeric', 'min:0.01'],
            'gr_vintageYear'     => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'gr_containerId'     => ['required', Rule::exists('containers', 'id')
                ->where('user_id', Auth::id())->where('unit', 'kg')],
        ], [
            'gr_viticulturistId.required' => __('Selecciona un viticultor.'),
            'gr_plotId.required'          => __('Selecciona una parcela.'),
            'gr_plantingId.required'      => __('Selecciona una plantación.'),
            'gr_harvestDate.required'     => __('La fecha es obligatoria.'),
            'gr_totalWeight.required'     => __('El peso es obligatorio.'),
            'gr_totalWeight.min'          => __('El peso debe ser mayor que 0.'),
            'gr_containerId.required'     => __('Selecciona un depósito de destino.'),
        ]);

        $userId    = Auth::id();
        $isSelf    = Auth::user()->isProducer() && (int)$this->gr_viticulturistId === $userId;
        $isLinked  = $isSelf || WineryViticulturist::where('winery_id', $userId)
            ->where('viticulturist_id', $this->gr_viticulturistId)
            ->exists();

        if (!$isLinked) { $this->addError('gr_viticulturistId', __('Viticultor no vinculado.')); return; }

        $plot      = Plot::where('viticulturist_id', $this->gr_viticulturistId)->find($this->gr_plotId);
        $planting  = $plot ? PlotPlanting::where('plot_id', $plot->id)->find($this->gr_plantingId) : null;
        $container = Container::where('user_id', $userId)->find((int) $this->gr_containerId);

        if (!$plot || !$planting || !$container) {
            $this->toastError(__('Datos no válidos. Revisa la selección.')); return;
        }

        $weight = (float) $this->gr_totalWeight;
        if (!$container->hasAvailableCapacity($weight)) {
            $this->addError('gr_containerId', __('Sin capacidad suficiente. Disponible: :available kg.', ['available' => number_format($container->getAvailableCapacity(), 0)]));
            return;
        }

        $campaign = Campaign::getOrCreateActiveForYear($userId, $this->gr_vintageYear);
        if (!$campaign) { $this->toastError(__('No se pudo obtener la campaña.')); return; }

        $existingBatch = GrapeReceptionBatch::where('winery_id', $userId)
            ->where('plot_planting_id', $planting->id)
            ->where('campaign_id', $campaign->id)
            ->first();
        if ($existingBatch && $existingBatch->status === 'closed') {
            $this->toastError(__('El lote de esta plantación está cerrado.')); return;
        }

        try {
            DB::transaction(function () use ($userId, $campaign, $planting, $container, $weight) {
                $batch = GrapeReceptionBatch::firstOrCreate(
                    ['winery_id' => $userId, 'plot_planting_id' => $planting->id, 'campaign_id' => $campaign->id],
                    [
                        'viticulturist_id'      => (int) $this->gr_viticulturistId,
                        'vintage_year'          => $this->gr_vintageYear,
                        'total_weight_kg'       => 0,
                        'designation_of_origin' => $planting->designation_of_origin,
                        'status'                => 'open',
                    ]
                );

                Harvest::create([
                    'winery_id'          => $userId,
                    'batch_id'           => $batch->id,
                    'plot_planting_id'   => $planting->id,
                    'container_id'       => $container->id,
                    'harvest_start_date' => $this->gr_harvestDate,
                    'vintage'            => $this->gr_vintageYear,
                    'total_weight'       => $weight,
                    'destination_type'   => 'winery',
                    'status'             => 'active',
                    'disqualified'       => false,
                ]);

                $batch->increment('total_weight_kg', $weight);
            });

            $this->toastSuccess("Recepción de {$weight} kg registrada correctamente.");
            $this->resetGrapeReceptionForm();
            $this->modalGrapeReception = false;

        } catch (\Exception $e) {
            \Log::error('VisualDashboard::saveGrapeReception', ['error' => $e->getMessage()]);
            $this->toastError(__('Error al guardar. Inténtalo de nuevo.'));
        }
    }

    private function resetGrapeReceptionForm(): void
    {
        $this->gr_viticulturistId    = '';
        $this->gr_plotId             = '';
        $this->gr_plantingId         = '';
        $this->gr_containerId        = '';
        $this->gr_totalWeight        = '';
        $this->gr_availablePlots     = [];
        $this->gr_availablePlantings = [];
    }

    // ── Modal: Control de fermentación ────────────────────────────────────────

    public function openModalFermentation(?int $containerId = null, ?int $wineId = null): void
    {
        $this->fc_wineId      = $wineId ? (string) $wineId : '';
        $this->fc_containerId = $containerId ? (string) $containerId : '';
        $this->fc_controlDate = now()->format('Y-m-d\TH:i');
        $this->fc_temperature = '';
        $this->fc_brix        = '';
        $this->modalFermentation = true;
    }

    public function saveFermentationControl(): void
    {
        $this->validate([
            'fc_wineId'      => $this->ownedWineRule(),
            'fc_containerId' => $this->ownedContainerRule(),
            'fc_controlDate' => ['required', 'date'],
            'fc_temperature' => ['nullable', 'numeric', 'min:-20', 'max:60'],
            'fc_brix'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'fc_wineId.required'      => __('Selecciona un vino.'),
            'fc_containerId.required' => __('Selecciona un contenedor.'),
            'fc_controlDate.required' => __('La fecha es obligatoria.'),
        ]);

        WineFermentationControl::create([
            'wine_id'      => $this->fc_wineId,
            'container_id' => $this->fc_containerId,
            'control_date' => $this->fc_controlDate,
            'temperature'  => $this->fc_temperature !== '' ? $this->fc_temperature : null,
            'brix_degree'  => $this->fc_brix !== '' ? $this->fc_brix : null,
            'created_by'   => Auth::id(),
        ]);

        $this->toastSuccess(__('Control de fermentación registrado.'));
        $this->modalFermentation = false;
    }

    // ── Modal: Trasvase de vino ───────────────────────────────────────────────

    public function openModalTransfer(?int $fromContainerId = null, ?int $wineId = null): void
    {
        $this->tr_wineId          = $wineId ? (string) $wineId : '';
        $this->tr_fromContainerId = $fromContainerId ? (string) $fromContainerId : '';
        $this->tr_toContainerId   = '';
        $this->tr_quantity        = '';
        $this->tr_unitId          = (string) (UnitOfMeasurement::where('symbol', 'L')->value('id') ?? '');
        $this->tr_transferType    = 'racking';
        $this->tr_transferDate    = now()->toDateString();
        $this->modalTransfer = true;
    }

    public function saveTransfer(): void
    {
        $this->validate([
            'tr_wineId'        => $this->ownedWineRule(),
            'tr_toContainerId' => $this->ownedContainerRule(),
            'tr_quantity'      => ['required', 'numeric', 'min:0.001'],
            'tr_unitId'        => ['required', 'exists:units_of_measurement,id'],
            'tr_transferType'  => ['required', 'in:' . implode(',', array_keys(WineTransfer::TRANSFER_TYPES))],
            'tr_transferDate'  => ['required', 'date'],
        ], [
            'tr_wineId.required'        => __('Selecciona un vino.'),
            'tr_toContainerId.required' => __('Selecciona contenedor destino.'),
            'tr_quantity.required'      => __('La cantidad es obligatoria.'),
            'tr_quantity.min'           => __('La cantidad debe ser mayor que 0.'),
        ]);

        $dest = Container::where('user_id', Auth::id())->find($this->tr_toContainerId);
        if ($dest && $dest->getAvailableCapacity() < (float) $this->tr_quantity) {
            $this->addError('tr_quantity', __('Sin capacidad suficiente. Disponible: :available kg.', ['available' => number_format($dest->getAvailableCapacity(), 1)]));
            return;
        }

        $transfer = WineTransfer::create([
            'wine_id'                => $this->tr_wineId,
            'from_container_id'      => $this->tr_fromContainerId ?: null,
            'to_container_id'        => $this->tr_toContainerId,
            'quantity'               => $this->tr_quantity,
            'unit_of_measurement_id' => $this->tr_unitId,
            'transfer_type'          => $this->tr_transferType,
            'transfer_date'          => $this->tr_transferDate,
            'created_by'             => Auth::id(),
        ]);

        app(WineContainerStockService::class)->recordTransfer($transfer);

        $this->toastSuccess(__('Trasvase registrado correctamente.'));
        $this->modalTransfer = false;
    }

    // ── Modal: Nuevo vino ─────────────────────────────────────────────────────

    public function openModalWine(): void
    {
        $this->wine_name = '';
        $this->wine_type = '';
        $this->modalWine = true;
    }

    public function saveWine(): void
    {
        $this->validate([
            'wine_name' => ['required', 'string', 'max:200'],
            'wine_type' => ['required', 'in:' . implode(',', array_keys(Wine::WINE_TYPES))],
        ], [
            'wine_name.required' => __('El nombre es obligatorio.'),
            'wine_type.required' => __('Selecciona el tipo de vino.'),
        ]);

        Wine::create([
            'user_id'   => Auth::id(),
            'name'      => $this->wine_name,
            'wine_type' => $this->wine_type,
            'status'    => 'in_progress',
        ]);

        $this->toastSuccess("Vino «{$this->wine_name}» creado.");
        $this->modalWine = false;
    }

    // ── Modal: Nuevo contenedor ───────────────────────────────────────────────

    public function openModalContainer(): void
    {
        $this->cont_name     = '';
        $this->cont_typeId   = '';
        $this->cont_capacity = '';
        $this->cont_unit     = 'litros';
        $this->modalContainer = true;
    }

    public function saveContainer(): void
    {
        $this->validate([
            'cont_name'     => ['required', 'string', 'max:100'],
            'cont_typeId'   => ['required', 'exists:container_types,id'],
            'cont_capacity' => ['required', 'numeric', 'min:1'],
            'cont_unit'     => ['required', 'in:kg,litros'],
        ], [
            'cont_name.required'     => __('El nombre es obligatorio.'),
            'cont_typeId.required'   => __('Selecciona el tipo.'),
            'cont_capacity.required' => __('La capacidad es obligatoria.'),
            'cont_capacity.min'      => __('La capacidad debe ser al menos 1.'),
        ]);

        Container::create([
            'user_id'    => Auth::id(),
            'name'       => $this->cont_name,
            'type_id'    => $this->cont_typeId,
            'capacity'   => $this->cont_capacity,
            'unit'       => $this->cont_unit,
            'archived'   => false,
        ]);

        $this->toastSuccess("Contenedor «{$this->cont_name}» creado.");
        $this->modalContainer = false;
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  RENDER
    // ══════════════════════════════════════════════════════════════════════════

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

        $needsFallback = $allPlots
            ->filter(fn($p) => !($p->municipality?->lat && $p->municipality?->lng))
            ->pluck('id')
            ->all();

        $sigpacCentroids = [];
        if (!empty($needsFallback)) {
            $placeholders = implode(',', array_fill(0, count($needsFallback), '?'));
            $rows = DB::select(
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
                    return null;
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
            $polyRows = DB::select(
                // LEFT JOIN: las geometrías catastrales/manuales no tienen
                // sigpac_code_id; sc.code sale NULL pero la parcela se pinta igual.
                "SELECT mps.plot_id, sc.code AS sigpac_code,
                        ST_AsText(pg.coordinates) AS wkt
                 FROM   multipart_plot_sigpac mps
                 LEFT JOIN sigpac_code sc ON mps.sigpac_code_id  = sc.id
                 JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
                 WHERE  mps.plot_id IN ($placeholders)
                 AND    pg.coordinates IS NOT NULL",
                $allPlotIds
            );
            foreach ($polyRows as $row) {
                // Un MULTIPOLYGON produce varios anillos → una entrada por anillo,
                // todas con el mismo plot_id (el color en el front es por variedad/plot).
                foreach ($this->parseWktRings($row->wkt) as $ring) {
                    $mapPolygons[] = [
                        'plot_id'     => $row->plot_id,
                        'sigpac_code' => $row->sigpac_code,
                        'coords'      => $ring,
                    ];
                }
            }
        }

        // ── Opciones de filtro ─────────────────────────────────────────────
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

        $selectedPlotLastActivity = $this->selectedPlotId
            ? AgriculturalActivity::where('plot_id', $this->selectedPlotId)
                ->latest('activity_date')
                ->first(['id', 'activity_type', 'activity_date'])
            : null;

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

        // Si el plot seleccionado no existe (datos obsoletos en el cliente), limpiar selección
        if ($this->selectedPlotId && !$selectedPlot) {
            $this->selectedPlotId = null;
        }

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

        $selectedContainer = $this->selectedContainerId
            ? Container::where('user_id', $userId)
                ->with(['containerType', 'containerMaterial', 'containerRoom', 'currentState.wine', 'currentState.harvest.batch.grapeVariety'])
                ->find($this->selectedContainerId)
            : null;

        $allContainers   = Container::where('user_id', $userId)->where('archived', false)->get(['id', 'capacity', 'used_capacity', 'wine_volume_liters']);
        $totalCapacityKg = (float) $allContainers->sum('capacity');
        $totalUsedKg     = (float) $allContainers->sum('used_capacity');
        $totalWineLiters = (float) $allContainers->sum('wine_volume_liters');
        $containerStats  = [
            'total'             => $allContainers->count(),
            'full'              => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() >= 100)->count(),
            'critical'          => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() >= 85 && $c->getOccupancyPercentage() < 100)->count(),
            'empty'             => $allContainers->filter(fn($c) => $c->getOccupancyPercentage() == 0)->count(),
            'total_capacity_kg' => $totalCapacityKg,
            'total_used_kg'     => $totalUsedKg,
            'total_wine_liters' => $totalWineLiters,
            'used_pct'          => $totalCapacityKg > 0 ? round($totalUsedKg / $totalCapacityKg * 100) : 0,
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

        // ── Datos para modales (solo cuando están abiertos) ────────────
        $modalViticulturists = $this->modalGrapeReception
            ? User::whereIn('id', WineryViticulturist::where('winery_id', $userId)->pluck('viticulturist_id'))
                ->orderBy('name')->get(['id', 'name'])
            : collect();

        $modalContainersKg = $this->modalGrapeReception
            ? Container::where('user_id', $userId)->where('unit', 'kg')->where('archived', false)
                ->orderBy('name')->get(['id', 'name', 'capacity', 'used_capacity'])
            : collect();

        $modalWines = ($this->modalFermentation || $this->modalTransfer)
            ? Wine::where('user_id', $userId)->orderBy('name')->get(['id', 'name', 'wine_type'])
            : collect();

        $modalContainersAll = ($this->modalFermentation || $this->modalTransfer)
            ? Container::where('user_id', $userId)->where('archived', false)
                ->orderBy('name')->get(['id', 'name', 'unit', 'capacity', 'used_capacity', 'wine_volume_liters'])
            : collect();

        $modalUnits = $this->modalTransfer
            ? UnitOfMeasurement::orderBy('name')->get(['id', 'name', 'symbol'])
            : collect();

        return view('livewire.winery.visual-dashboard', [
            'mapPlots'                 => $mapPlots,
            'selectedPlotLastActivity' => $selectedPlotLastActivity,
            'mapPolygons'              => $mapPolygons,
            'filterOptions'            => $filterOptions,
            'selectedPlot'             => $selectedPlot,
            'containers'               => $containers,
            'containerTypes'           => $containerTypes,
            'selectedContainer'        => $selectedContainer,
            'containerStats'           => $containerStats,
            'dashboardStats'           => $dashboardStats,
            'criticalContainers'       => $criticalContainers,
            'recentControls'           => $recentControls,
            // Modal data
            'modalViticulturists'      => $modalViticulturists,
            'modalContainersKg'        => $modalContainersKg,
            'modalWines'               => $modalWines,
            'modalContainersAll'       => $modalContainersAll,
            'modalUnits'               => $modalUnits,
            'wineTypes'                => Wine::wineTypeOptions(),
            'transferTypes'            => WineTransfer::transferTypeOptions(),
        ])->layout('layouts.app', ['title' => __('Vista Visual — Agro365')]);
    }

    /**
     * Parsea un WKT (POLYGON o MULTIPOLYGON) y devuelve el anillo EXTERIOR de
     * cada polígono como lista de puntos [lat, lng].
     *
     * El regex `\(\(([^()]+)\)` captura el contenido que sigue a cada `((`
     * (inicio de un polígono), por lo que de forma natural:
     *   - obtiene un anillo por polígono en un MULTIPOLYGON,
     *   - ignora los agujeros (holes), precedidos por un solo `(`.
     * Los holes no aportan en un mapa de conjunto y romperían el contrato
     * plano que consume el front (L.polygon(coords)).
     *
     * @return array<int, array<int, array{0: float, 1: float}>> lista de anillos
     */
    private function parseWktRings(?string $wkt): array
    {
        if (!$wkt) return [];

        $rings = [];
        if (preg_match_all('/\(\(([^()]+)\)/', $wkt, $matches)) {
            foreach ($matches[1] as $ringStr) {
                $ring = [];
                foreach (explode(',', $ringStr) as $coord) {
                    $parts = preg_split('/\s+/', trim($coord));
                    if (count($parts) >= 2) {
                        $lng = (float) $parts[0];
                        $lat = (float) $parts[1];
                        if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                            $ring[] = [$lat, $lng];
                        }
                    }
                }
                if (count($ring) >= 3) $rings[] = $ring;
            }
        }

        return $rings;
    }
}
