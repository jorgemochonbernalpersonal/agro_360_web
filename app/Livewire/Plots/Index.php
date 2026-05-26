<?php

namespace App\Livewire\Plots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserPreferences;
use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\SigpacCode;
use App\Services\SigpacGeometryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications, WithUserPreferences;

    public $currentTab = 'active';  // 'active', 'inactive'
    public $search = '';
    public $filterAutonomousCommunity = '';
    public $filterProvince = '';
    public $filterMunicipality = '';
    public $auditPlotId = null;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search' => ['except' => ''],
        'filterAutonomousCommunity' => ['except' => ''],
        'filterProvince' => ['except' => ''],
        'filterMunicipality' => ['except' => ''],
    ];

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($plotId)
    {
        $user = Auth::user();
        $plot = Plot::forUser($user)->findOrFail($plotId);

        if (!$user->can('update', $plot)) {
            abort(403);
        }

        $wasActive = $plot->active;
        $newActiveState = !$wasActive;

        $plot->update([
            'active' => $newActiveState
        ]);

        if ($newActiveState) {
            $this->toastSuccess(__('Parcela activada exitosamente.'));
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess(__('Parcela desactivada exitosamente.'));
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function render()
    {
        $query = Plot::forUser(Auth::user())
            ->select([
                'id',
                'name',
                'description',
                'area',
                'active',
                'viticulturist_id',
                'autonomous_community_id',
                'province_id',
                'municipality_id',
                'code_parcel',
                'soil_type_id',
                'orientation_id',
                'topography_id',
                'slope',
                'created_at',
                'updated_at',
            ])
            ->withMin(['plantings as oldest_planting_year' => fn ($q) => $q->where('status', 'active')], 'planting_year')
            ->withSum(['plantings as total_vines' => fn ($q) => $q->where('status', 'active')], 'vine_count')
            ->withCount(['plantings as active_plantings_count' => fn ($q) => $q->where('status', 'active')])
            ->with([
                'plantings' => fn ($q) => $q->where('status', 'active')->with('grapeVariety:id,name')->select('id', 'plot_id', 'grape_variety_id'),
                'viticulturist:id,name',
                'autonomousCommunity:id,name',
                'province:id,name',
                'municipality:id,name,province_id',
                'municipality.province:id,name',
                'soilType:id,name',
                'orientation:id,name',
                'topography:id,name',
                'sigpacCodes:id,code,code_autonomous_community,code_province,code_municipality,code_aggregate,code_zone,code_polygon,code_plot,code_enclosure',
                'multiplePlotSigpacs' => function($q) {
                    $q->with([
                        'sigpacCode:id,code,code_autonomous_community,code_province,code_municipality,code_aggregate,code_zone,code_polygon,code_plot,code_enclosure',
                        'plotGeometry'
                    ])->orderBy('id');
                }
            ]);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$search]);
        }

        // Filtro por Comunidad Autónoma
        if ($this->filterAutonomousCommunity) {
            $query->where('autonomous_community_id', $this->filterAutonomousCommunity);
        }

        // Filtro por Provincia
        if ($this->filterProvince) {
            $query->where('province_id', $this->filterProvince);
        }

        // Filtro por Municipio
        if ($this->filterMunicipality) {
            $query->where('municipality_id', $this->filterMunicipality);
        }

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('active', true);  // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false);  // Inactivos
        }

        $plots = $query
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(12);

        // Estadísticas
        $baseQuery = Plot::forUser(Auth::user());
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'active'     => (clone $baseQuery)->where('active', true)->count(),
            'inactive'   => (clone $baseQuery)->where('active', false)->count(),
            'total_area' => (clone $baseQuery)->sum('area') ?? 0,
            'with_sigpac' => (clone $baseQuery)->whereHas('sigpacCodes')->count(),
        ];

        $firstPlotForMap = $this->filterMunicipality
            ? Plot::forUser(Auth::user())
                ->where('municipality_id', $this->filterMunicipality)
                ->select('id')
                ->first()
            : null;

        $auditPlot = $this->auditPlotId
            ? Plot::forUser(Auth::user())->find($this->auditPlotId)
            : null;

        return view('livewire.plots.index', [
            'plots'              => $plots,
            'stats'              => $stats,
            'autonomousCommunities' => $this->autonomousCommunities,
            'provinces'          => $this->provinces,
            'municipalities'     => $this->municipalities,
            'firstPlotForMap'    => $firstPlotForMap,
            'auditPlot'          => $auditPlot,
        ])->layout('layouts.app', [
            'title' => __('Gestión de Parcelas - Agro365'),
            'description' => __('Administra y visualiza todas tus parcelas agrícolas. Control total de viñedos con integración SIGPAC.'),
        ]);
    }

    /**
     * Obtener opciones de Comunidades Autónomas (con caché)
     */
    public function getAutonomousCommunitiesProperty()
    {
        return \Illuminate\Support\Facades\Cache::remember('filter_autonomous_communities_plots', now()->addHours(24), function () {
            $user = Auth::user();
            $plotIds = Plot::forUser($user)->pluck('id');

            return AutonomousCommunity::whereHas('plots', function ($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            })
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn($ca) => [$ca->id => $ca->name]);
        });
    }

    /**
     * Obtener opciones de Provincias (filtradas por CA seleccionada)
     */
    public function getProvincesProperty()
    {
        if (!$this->filterAutonomousCommunity) {
            return collect();
        }

        $cacheKey = "filter_provinces_plots_{$this->filterAutonomousCommunity}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function () {
            $user = Auth::user();
            $plotIds = Plot::forUser($user)->pluck('id');

            return Province::where('autonomous_community_id', $this->filterAutonomousCommunity)
                ->whereHas('plots', function ($query) use ($plotIds) {
                    $query->whereIn('plots.id', $plotIds);
                })
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn($prov) => [$prov->id => $prov->name]);
        });
    }

    /**
     * Obtener opciones de Municipios (filtradas por Provincia seleccionada)
     */
    public function getMunicipalitiesProperty()
    {
        if (!$this->filterProvince) {
            return collect();
        }

        $cacheKey = "filter_municipalities_plots_{$this->filterProvince}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function () {
            $user = Auth::user();
            $plotIds = Plot::forUser($user)->pluck('id');

            return Municipality::where('province_id', $this->filterProvince)
                ->whereHas('plots', function ($query) use ($plotIds) {
                    $query->whereIn('plots.id', $plotIds);
                })
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn($mun) => [$mun->id => $mun->name]);
        });
    }

    /**
     * Verificar si el municipio seleccionado tiene códigos SIGPAC
     */
    public function getMunicipalityHasSigpacCodesProperty()
    {
        if (!$this->filterMunicipality) {
            return false;
        }

        $user = Auth::user();
        
        return Plot::forUser($user)
            ->where('municipality_id', $this->filterMunicipality)
            ->whereHas('sigpacCodes')
            ->exists();
    }

    /**
     * Resetear filtros dependientes cuando cambia CA
     */
    public function updatedFilterAutonomousCommunity()
    {
        $this->filterProvince = '';
        $this->filterMunicipality = '';
        $this->resetPage();
    }

    /**
     * Resetear municipio cuando cambia Provincia
     */
    public function updatedFilterProvince()
    {
        $this->filterMunicipality = '';
        $this->resetPage();
    }

    /**
     * Resetear página cuando cambia Municipio
     */
    public function updatedFilterMunicipality()
    {
        $this->resetPage();
    }

    public function selectAuditPlot(int $plotId): void
    {
        $this->auditPlotId = $plotId;
        $this->dispatch('open-modal', 'plot-audit');
    }

    public function generateMap($sigpacCodeId, $plotId)
    {
        $plot = Plot::findOrFail($plotId);

        if (!Auth::user()->can('update', $plot)) {
            $this->toastError(__('No tienes permiso para modificar esta parcela.'));
            return;
        }

        if ($sigpacCodeId === null) {
            $sigpacCodes = $plot->sigpacCodes;
        } else {
            $sigpacCodes = $plot->sigpacCodes->where('id', $sigpacCodeId);
        }

        if ($sigpacCodes->isEmpty()) {
            $this->toastError(__('Esta parcela no tiene códigos SIGPAC asociados.'));
            return;
        }

        $service = app(SigpacGeometryService::class);
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($sigpacCodes as $sigpacCode) {
                try {
                    $wkt = $service->fetchWkt($sigpacCode);

                    if (!$wkt) {
                        $errorCount++;
                        $errors[] = "No se pudieron obtener coordenadas para el código {$sigpacCode->code}";
                        continue;
                    }

                    if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                        $errorCount++;
                        $errors[] = "Formato de coordenadas inválido para el código {$sigpacCode->code}";
                        continue;
                    }

                    $service->upsertGeometry($plotId, $sigpacCode, $wkt);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error procesando código {$sigpacCode->code}: " . $e->getMessage();
                    Log::error('Error generating map for sigpac code', [
                        'sigpac_code_id' => $sigpacCode->id,
                        'plot_id' => $plotId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            if ($successCount > 0) {
                $message = $successCount === 1
                    ? 'Mapa generado correctamente para 1 código SIGPAC.'
                    : "Mapas generados correctamente para {$successCount} códigos SIGPAC.";
                $this->toastSuccess($message);
                $this->dispatch('$refresh');
            }

            if ($errorCount > 0) {
                $errorMessage = "Error al generar {$errorCount} mapa(s). " . implode(' ', array_slice($errors, 0, 3));
                $this->toastError($errorMessage);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating maps from SIGPAC', [
                'plot_id' => $plotId,
                'sigpac_code_id' => $sigpacCodeId,
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Error al generar los mapas. Por favor, intenta de nuevo.'));
        }
    }

    public function generateAllMapsForMunicipality()
    {
        if (!$this->filterMunicipality) {
            $this->toastError(__('Debes seleccionar un municipio primero.'));
            return;
        }

        $user = Auth::user();
        $service = app(SigpacGeometryService::class);

        $plotsWithoutGeometry = Plot::forUser($user)
            ->where('municipality_id', $this->filterMunicipality)
            ->whereHas('sigpacCodes')
            ->whereDoesntHave('multiplePlotSigpacs', function($q) {
                $q->whereNotNull('plot_geometry_id');
            })
            ->with(['sigpacCodes'])
            ->get();

        if ($plotsWithoutGeometry->isEmpty()) {
            $this->toastInfo(__('Todas las parcelas de este municipio ya tienen mapas generados.'));
            return;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($plotsWithoutGeometry as $plot) {
            $sigpacCodes = $plot->sigpacCodes;

            if ($sigpacCodes->isEmpty()) {
                continue;
            }

            try {
                DB::beginTransaction();

                foreach ($sigpacCodes as $sigpacCode) {
                    try {
                        $wkt = $service->fetchWkt($sigpacCode);

                        if (!$wkt) {
                            $errorCount++;
                            continue;
                        }

                        if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                            $errorCount++;
                            continue;
                        }

                        $service->upsertGeometry($plot->id, $sigpacCode, $wkt);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        Log::error('Error generating bulk map for plot', [
                            'plot_id' => $plot->id,
                            'sigpac_code_id' => $sigpacCode->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                Log::error('Error generating bulk maps for plot', [
                    'plot_id' => $plot->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($successCount > 0) {
            $this->toastSuccess("Se generaron {$successCount} mapas correctamente.");
        }

        if ($errorCount > 0) {
            $this->toastWarning("No se pudieron generar {$errorCount} mapas.");
        }

        $this->dispatch('$refresh');
    }
}
