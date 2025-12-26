<?php

namespace App\Livewire\Sigpac;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use App\Models\PlotGeometry;
use App\Models\SigpacCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class CodesIndex extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $filterAutonomousCommunity = '';
    public $filterProvince = '';
    public $filterMunicipality = '';

    protected $queryString = [
        'search',
        'filterAutonomousCommunity' => ['as' => 'ca'],
        'filterProvince' => ['as' => 'prov'],
        'filterMunicipality' => ['as' => 'mun'],
    ];

    public function render()
    {
        $user = Auth::user();

        // Obtener IDs de parcelas que el usuario puede ver
        $plotIds = Plot::forUser($user)->pluck('id');

        $codes = SigpacCode::query()
            ->whereHas('plots', function ($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            })
            ->when($this->search, function ($query) {
                $search = '%' . strtolower($this->search) . '%';
                $query->whereRaw('LOWER(code) LIKE ?', [$search]);
            })
            // ✅ Filtro por Comunidad Autónoma
            ->when($this->filterAutonomousCommunity, function ($query) use ($plotIds) {
                $query->whereHas('plots', function ($q) use ($plotIds) {
                    $q->whereIn('plots.id', $plotIds)
                      ->where('autonomous_community_id', $this->filterAutonomousCommunity);
                });
            })
            // ✅ Filtro por Provincia
            ->when($this->filterProvince, function ($query) use ($plotIds) {
                $query->whereHas('plots', function ($q) use ($plotIds) {
                    $q->whereIn('plots.id', $plotIds)
                      ->where('province_id', $this->filterProvince);
                });
            })
            // ✅ Filtro por Municipio
            ->when($this->filterMunicipality, function ($query) use ($plotIds) {
                $query->whereHas('plots', function ($q) use ($plotIds) {
                    $q->whereIn('plots.id', $plotIds)
                      ->where('municipality_id', $this->filterMunicipality);
                });
            })
            ->with(['plots' => function ($query) use ($plotIds) {
                $query
                    ->whereIn('plots.id', $plotIds)
                    ->with(['autonomousCommunity', 'province', 'municipality'])
                    ->limit(1);
            }])
            ->withCount(['plots' => function ($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            }])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.sigpac.codes-index', [
            'codes' => $codes,
        ])->layout('layouts.app', [
            'title' => 'Códigos SIGPAC - Agro365',
            'description' => 'Gestiona los códigos de identificación SIGPAC de tus parcelas. Integración completa con el sistema SIGPAC para cumplimiento normativo.',
        ]);
    }

    public function generateMap($sigpacCodeId, $plotId)
    {
        $plot = Plot::findOrFail($plotId);
        $sigpacCode = SigpacCode::findOrFail($sigpacCodeId);

        if (!Auth::user()->can('update', $plot)) {
            $this->toastError('No tienes permiso para modificar esta parcela.');
            return;
        }

        $sigpacCodes = $plot->sigpacCodes->where('id', $sigpacCodeId);

        if ($sigpacCodes->isEmpty()) {
            $this->toastError('Este código SIGPAC no está asociado a esta parcela.');
            return;
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            $wkt = $this->fetchCoordinatesFromSigpacApi($sigpacCode);

            if (!$wkt) {
                $this->toastError("No se pudieron obtener coordenadas para el código {$sigpacCode->code}");
                return;
            }

            if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                $this->toastError("Formato de coordenadas inválido para el código {$sigpacCode->code}");
                return;
            }

            // Insertar geometría directamente con ST_GeomFromText
            DB::statement(
                'INSERT INTO plot_geometry (coordinates, centroid, created_at, updated_at) 
                 VALUES (ST_GeomFromText(?, 4326), ST_Centroid(ST_GeomFromText(?, 4326)), ?, ?)',
                [$wkt, $wkt, now(), now()]
            );
            
            $geometryId = DB::getPdo()->lastInsertId();

            $mps = MultipartPlotSigpac::where('plot_id', $plotId)
                ->where('sigpac_code_id', $sigpacCodeId)
                ->first();

            if ($mps) {
                $mps->plot_geometry_id = $geometryId;
                $mps->updated_at = now();
                $mps->save();
            } else {
                MultipartPlotSigpac::create([
                    'plot_id' => $plotId,
                    'sigpac_code_id' => $sigpacCodeId,
                    'plot_geometry_id' => $geometryId,
                ]);
            }

            DB::commit();
            $this->toastSuccess('Mapa generado correctamente.');
            // Forzar recarga de la vista
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating map from SIGPAC', [
                'plot_id' => $plotId,
                'sigpac_code_id' => $sigpacCodeId,
                'error' => $e->getMessage(),
            ]);
            $this->toastError('Error al generar el mapa. Por favor, intenta de nuevo.');
        }
    }

    private function fetchCoordinatesFromSigpacApi(SigpacCode $sigpacCode): ?string
    {
        try {
            $url = sprintf(
                'https://sigpac-hubcloud.es/servicioconsultassigpac/query/recinfo/%s/%s/%s/%s/%s/%s/%s.json',
                $sigpacCode->code_province,
                $sigpacCode->code_municipality,
                $sigpacCode->code_aggregate ?? '0',
                $sigpacCode->code_zone,
                $sigpacCode->code_polygon,
                $sigpacCode->code_plot,
                $sigpacCode->code_enclosure
            );

            $httpClient = Http::timeout(10);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($url);

            if ($response->status() !== 200) {
                return null;
            }

            $data = $response->json();

            if (!is_array($data) || empty($data) || !isset($data[0]['wkt'])) {
                return null;
            }

            return $data[0]['wkt'];
        } catch (\Exception $e) {
            Log::warning('Error fetching SIGPAC coordinates', [
                'sigpac_code_id' => $sigpacCode->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener opciones de Comunidades Autónomas (con caché)
     */
    public function getAutonomousCommunitiesProperty()
    {
        return \Illuminate\Support\Facades\Cache::remember('filter_autonomous_communities', now()->addHours(24), function () {
            $user = Auth::user();
            $plotIds = Plot::forUser($user)->pluck('id');
            
            return \App\Models\AutonomousCommunity::whereHas('plots', function ($query) use ($plotIds) {
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

        $cacheKey = "filter_provinces_{$this->filterAutonomousCommunity}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function () {
            $user = Auth::user();
            $plotIds = Plot::forUser($user)->pluck('id');
            
            return \App\Models\Province::where('autonomous_community_id', $this->filterAutonomousCommunity)
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

        $cacheKey = "filter_municipalities_{$this->filterProvince}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function () {
            $user = Auth::user();
            $plotIds = Plot::forUser($user)->pluck('id');
            
            return \App\Models\Municipality::where('province_id', $this->filterProvince)
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
        $plotIds = Plot::forUser($user)->pluck('id');
        
        return SigpacCode::whereHas('plots', function ($query) use ($plotIds) {
            $query->whereIn('plots.id', $plotIds)
                  ->where('municipality_id', $this->filterMunicipality);
        })->exists();
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

    /**
     * Limpiar todos los filtros
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->filterAutonomousCommunity = '';
        $this->filterProvince = '';
        $this->filterMunicipality = '';
        $this->resetPage();
    }

    /**
     * Generar todos los mapas para el municipio seleccionado
     */
    public function generateAllMapsForMunicipality()
    {
        if (!$this->filterMunicipality) {
            $this->toastError('Debes seleccionar un municipio primero.');
            return;
        }

        $user = Auth::user();
        $plotIds = Plot::forUser($user)->pluck('id');

        // Obtener todos los códigos SIGPAC del municipio que no tienen geometría
        $codesWithoutGeometry = SigpacCode::query()
            ->whereHas('plots', function ($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds)
                      ->where('municipality_id', $this->filterMunicipality);
            })
            ->with(['plots' => function ($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds)
                      ->where('municipality_id', $this->filterMunicipality)
                      ->limit(1);
            }])
            ->get()
            ->filter(function ($code) {
                $plot = $code->plots->first();
                if (!$plot) return false;
                
                // Verificar si ya tiene geometría
                $hasGeometry = MultipartPlotSigpac::where('plot_id', $plot->id)
                    ->where('sigpac_code_id', $code->id)
                    ->whereNotNull('plot_geometry_id')
                    ->exists();
                
                return !$hasGeometry;
            });

        if ($codesWithoutGeometry->isEmpty()) {
            $this->toastInfo('Todos los códigos de este municipio ya tienen mapas generados.');
            return;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($codesWithoutGeometry as $code) {
            $plot = $code->plots->first();
            if (!$plot) continue;

            try {
                DB::beginTransaction();

                $wkt = $this->fetchCoordinatesFromSigpacApi($code);

                if (!$wkt) {
                    $errorCount++;
                    DB::rollBack();
                    continue;
                }

                if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                    $errorCount++;
                    DB::rollBack();
                    continue;
                }

                // Insertar geometría directamente con ST_GeomFromText
                DB::statement(
                    'INSERT INTO plot_geometry (coordinates, centroid, created_at, updated_at) 
                     VALUES (ST_GeomFromText(?, 4326), ST_Centroid(ST_GeomFromText(?, 4326)), ?, ?)',
                    [$wkt, $wkt, now(), now()]
                );
                
                $geometryId = DB::getPdo()->lastInsertId();

                $mps = MultipartPlotSigpac::where('plot_id', $plot->id)
                    ->where('sigpac_code_id', $code->id)
                    ->first();

                if ($mps) {
                    $mps->plot_geometry_id = $geometryId;
                    $mps->updated_at = now();
                    $mps->save();
                } else {
                    MultipartPlotSigpac::create([
                        'plot_id' => $plot->id,
                        'sigpac_code_id' => $code->id,
                        'plot_geometry_id' => $geometryId,
                    ]);
                }

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                Log::error('Error generating bulk map', [
                    'sigpac_code_id' => $code->id,
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
