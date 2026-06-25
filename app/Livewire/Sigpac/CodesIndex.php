<?php

namespace App\Livewire\Sigpac;

use App\Livewire\Concerns\WithGeoFiltering;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use App\Models\SigpacCode;
use App\Services\SigpacGeometryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class CodesIndex extends Component
{
    use WithGeoFiltering, WithPagination, WithToastNotifications;

    // geoCachePrefix() usa el valor por defecto 'filter' del trait,
    // que coincide con las claves existentes en caché.

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
                $search = '%'.strtolower($this->search).'%';
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
            'title' => __('Códigos SIGPAC - Agro365'),
            'description' => __('Gestiona los códigos de identificación SIGPAC de tus parcelas. Integración completa con el sistema SIGPAC para cumplimiento normativo.'),
        ]);
    }

    public function generateMap($sigpacCodeId, $plotId)
    {
        $plot = Plot::findOrFail($plotId);
        $sigpacCode = SigpacCode::findOrFail($sigpacCodeId);

        if (! Auth::user()->can('update', $plot)) {
            $this->toastError(__('No tienes permiso para modificar esta parcela.'));

            return;
        }

        if (! $plot->sigpacCodes->contains('id', $sigpacCodeId)) {
            $this->toastError(__('Este código SIGPAC no está asociado a esta parcela.'));

            return;
        }

        try {
            $service = app(SigpacGeometryService::class);
            $wkt = $service->fetchWkt($sigpacCode);

            if (! $wkt) {
                $this->toastError("No se pudieron obtener coordenadas para el código {$sigpacCode->code}");

                return;
            }

            if (! preg_match(SigpacGeometryService::WKT_PATTERN, $wkt)) {
                $this->toastError("Formato de coordenadas inválido para el código {$sigpacCode->code}");

                return;
            }

            DB::transaction(fn () => $service->upsertGeometry($plotId, $sigpacCode, $wkt));

            $this->toastSuccess(__('Mapa generado correctamente.'));
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('Error generating map from SIGPAC', [
                'plot_id' => $plotId,
                'sigpac_code_id' => $sigpacCodeId,
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Error al generar el mapa. Por favor, intenta de nuevo.'));
        }
    }

    /**
    /**
     * Verificar si el municipio seleccionado tiene códigos SIGPAC
     */
    public function getMunicipalityHasSigpacCodesProperty()
    {
        if (! $this->filterMunicipality) {
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
        if (! $this->filterMunicipality) {
            $this->toastError(__('Debes seleccionar un municipio primero.'));

            return;
        }

        $user = Auth::user();
        $plotIds = Plot::forUser($user)->pluck('id');

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

                return $plot && ! MultipartPlotSigpac::where('plot_id', $plot->id)
                    ->where('sigpac_code_id', $code->id)
                    ->whereNotNull('plot_geometry_id')
                    ->exists();
            });

        if ($codesWithoutGeometry->isEmpty()) {
            $this->toastInfo(__('Todos los códigos de este municipio ya tienen mapas generados.'));

            return;
        }

        $service = app(SigpacGeometryService::class);
        $successCount = 0;
        $errorCount = 0;

        foreach ($codesWithoutGeometry as $code) {
            $plot = $code->plots->first();
            if (! $plot) {
                continue;
            }

            $wkt = $service->fetchWkt($code);

            if (! $wkt || ! preg_match(SigpacGeometryService::WKT_PATTERN, $wkt)) {
                $errorCount++;

                continue;
            }

            try {
                DB::transaction(fn () => $service->upsertGeometry($plot->id, $code, $wkt));
                $successCount++;
            } catch (\Exception $e) {
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
