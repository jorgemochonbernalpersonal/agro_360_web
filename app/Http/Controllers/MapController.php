<?php

namespace App\Http\Controllers;

use App\Models\MultipartPlotSigpac;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\SigpacCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mostrar mapa (detecta automáticamente Plot o SigpacCode)
     *
     * @param mixed $id
     */
    public function show($id, Request $request)
    {
        // Verificar si se debe mostrar todo el municipio
        $municipalityId = $request->query('municipality');

        // Check if a specific sigpac parcel (MultipartPlotSigpac) is requested
        $plotSigpacId = $request->query('recinto'); // URL param kept as 'recinto' for backwards compat

        // Check if it is an individual SIGPAC code lookup
        $isSigpac = $request->query('sigpac');

        if ($plotSigpacId) {
            return $this->showRecinto($plotSigpacId);
        }

        // Si es código SIGPAC, intentar primero como SigpacCode
        if ($isSigpac) {
            $sigpacCode = SigpacCode::find($id);
            if ($sigpacCode) {
                return $this->showSigpac($sigpacCode);
            }
        }

        // Intentar primero como Plot
        $plot = Plot::find($id);

        if ($plot) {
            $this->authorize('view', $plot);

            // Si hay parámetro municipality, mostrar todos los recintos del municipio
            if ($municipalityId) {
                return $this->renderMunicipalityMap($plot, $municipalityId);
            }

            return $this->renderMap($plot);
        }

        // Si no es Plot, intentar como SigpacCode
        $sigpacCode = SigpacCode::find($id);

        if ($sigpacCode) {
            return $this->showSigpac($sigpacCode);
        }

        // Si no es ninguno, error 404
        abort(404, __('No se encontró el mapa solicitado.'));
    }

    /**
     * Mapa de todas las parcelas del usuario en un municipio (sin plot_id)
     */
    public function showMunicipality(int $municipalityId)
    {
        $user = auth()->user();
        $municipality = Municipality::with('province.autonomousCommunity')->findOrFail($municipalityId);

        $plotIds = Plot::forUser($user)
            ->where('municipality_id', $municipalityId)
            ->pluck('id');

        if ($plotIds->isEmpty()) {
            return redirect()->route('plots.map')
                ->with('error', __('No tienes parcelas en este municipio.'));
        }

        $plotGeometries = $this->buildMunicipalityGeometries($plotIds);

        if ($plotGeometries->isEmpty()) {
            return redirect()->route('plots.map')
                ->with('error', __('No hay mapas generados para :municipality. Genera los mapas primero desde la lista de parcelas.', ['municipality' => $municipality->name]));
        }

        Log::info('Municipality map loaded', [
            'municipality_id' => $municipalityId,
            'geometries_count' => $plotGeometries->count(),
            'user_id' => $user->id,
        ]);

        $showMunicipality = true;

        return view('map', compact('municipality', 'plotGeometries', 'showMunicipality'));
    }

    /**
     * Mostrar mapa de una parcela
     */
    public function showPlot(Plot $plot)
    {
        $this->authorize('view', $plot);

        return $this->renderMap($plot);
    }

    /**
     * Mostrar mapa de un código SIGPAC
     */
    public function showSigpac(SigpacCode $sigpacCode)
    {
        // Obtener la parcela asociada al código SIGPAC
        $multipart = MultipartPlotSigpac::where('sigpac_code_id', $sigpacCode->id)
            ->whereNotNull('plot_geometry_id')
            ->with(['plot', 'plotGeometry'])
            ->first();

        if (! $multipart || ! $multipart->plot) {
            return redirect()->back()
                ->with('error', __('Este código SIGPAC no tiene una parcela asociada con mapa generado.'));
        }

        $plot = $multipart->plot;
        $this->authorize('view', $plot);

        // Renderizar SOLO este recinto, no todos los de la parcela
        return $this->renderSingleSigpacMap($multipart, $plot);
    }

    /**
     * Mostrar mapa de un recinto específico (MultipartPlotSigpac)
     *
     * @param mixed $multipartId
     */
    public function showRecinto($multipartId)
    {
        // Buscar el multipart
        $multipart = MultipartPlotSigpac::with(['plot', 'plotGeometry', 'sigpacCode'])
            ->find($multipartId);

        if (! $multipart || ! $multipart->plot || ! $multipart->plotGeometry) {
            return redirect()->back()
                ->with('error', __('No se encontró el recinto solicitado o no tiene mapa generado.'));
        }

        $plot = $multipart->plot;
        $this->authorize('view', $plot);

        // Renderizar SOLO este recinto
        return $this->renderSingleSigpacMap($multipart, $plot);
    }

    /**
     * Renderizar vista de mapa para un único recinto SIGPAC
     */
    protected function renderSingleSigpacMap(MultipartPlotSigpac $multipart, Plot $plot)
    {
        $wkt = $multipart->plotGeometry->getWktCoordinates();

        if (! $wkt) {
            Log::warning('No WKT for geometry for single sigpac map', [
                'multipart_plot_sigpac_id' => $multipart->id,
                'plot_geometry_id' => $multipart->plot_geometry_id,
            ]);

            return redirect()->back()
                ->with('error', __('No se pudo obtener la geometría para este recinto SIGPAC.'));
        }

        $plotGeometries = collect([[
            'id' => $multipart->id,
            'index' => 1,
            'sigpac_code' => $multipart->sigpacCode->code ?? 'N/A',
            'sigpac_formatted' => $multipart->sigpacCode->formatted_code ?? 'Sin código',
            'sigpac_id' => $multipart->sigpac_code_id,
            'wkt' => $wkt,
            'color' => $this->getColorForIndex(0), // Use the first color for a single item
            'highlight' => true, // Always highlight the single item
        ]])->values();

        Log::info('Single Sigpac map geometry loaded', [
            'plot_id' => $plot->id,
            'sigpac_code_id' => $multipart->sigpac_code_id,
            'geometries_count' => $plotGeometries->count(),
        ]);

        $highlightSigpacId = $multipart->sigpac_code_id; // This will be used to center the map

        return view('map', compact('plot', 'plotGeometries', 'highlightSigpacId'));
    }

    /**
     * Renderizar vista de mapa
     */
    protected function renderMap(Plot $plot, ?int $highlightSigpacId = null)
    {
        $plotGeometries = MultipartPlotSigpac::with(['plotGeometry', 'sigpacCode'])
            ->where('plot_id', $plot->id)
            ->whereNotNull('plot_geometry_id')
            ->get()
            ->filter(fn ($rel) => $rel->plotGeometry)
            ->map(function ($rel, $index) use ($highlightSigpacId) {
                $wkt = $rel->plotGeometry->getWktCoordinates();

                if (! $wkt) {
                    Log::warning('No WKT for geometry', [
                        'multipart_plot_sigpac_id' => $rel->id,
                        'plot_geometry_id' => $rel->plot_geometry_id,
                    ]);

                    return null;
                }

                return [
                    'id' => $rel->id,
                    'index' => $index + 1,
                    'sigpac_code' => $rel->sigpacCode?->code ?? 'N/A',
                    'sigpac_formatted' => $rel->sigpacCode?->formatted_code ?? 'Sin código',
                    'sigpac_id' => $rel->sigpac_code_id,
                    'polygon' => $rel->sigpacCode?->code_polygon ?? '',
                    'enclosure' => $rel->sigpacCode?->code_enclosure ?? '',
                    'wkt' => $wkt,
                    'color' => $this->getColorForIndex($index),
                    'highlight' => $highlightSigpacId && $rel->sigpac_code_id == $highlightSigpacId,
                ];
            })
            ->filter()
            ->values();

        Log::info('Map geometries loaded', [
            'plot_id' => $plot->id,
            'geometries_count' => $plotGeometries->count(),
            'highlight_sigpac_id' => $highlightSigpacId,
        ]);

        if ($plotGeometries->isEmpty()) {
            return redirect()->back()
                ->with('error', __('Esta parcela no tiene recintos generados. Genera el mapa primero.'));
        }

        return view('map', compact('plot', 'plotGeometries', 'highlightSigpacId'));
    }

    /**
     * Renderizar vista de mapa con todos los recintos de un municipio (ruta legacy con plot_id)
     *
     * @param mixed $municipalityId
     */
    protected function renderMunicipalityMap(Plot $plot, $municipalityId)
    {
        $user = auth()->user();
        $plotIds = Plot::forUser($user)->where('municipality_id', $municipalityId)->pluck('id');

        if ($plotIds->isEmpty()) {
            return redirect()->back()->with('error', __('No tienes parcelas en este municipio.'));
        }

        $plotGeometries = $this->buildMunicipalityGeometries($plotIds);

        if ($plotGeometries->isEmpty()) {
            return redirect()->back()->with('error', __('No hay mapas generados para este municipio.'));
        }

        Log::info('Municipality map loaded (legacy)', [
            'municipality_id' => $municipalityId,
            'plot_id' => $plot->id,
            'geometries_count' => $plotGeometries->count(),
        ]);

        $highlightSigpacId = null;
        $showMunicipality = true;

        return view('map', compact('plot', 'plotGeometries', 'highlightSigpacId', 'showMunicipality'));
    }

    /**
     * Carga y formatea geometrías de un conjunto de plot_ids para vista de municipio
     */
    private function buildMunicipalityGeometries(\Illuminate\Support\Collection $plotIds): \Illuminate\Support\Collection
    {
        return MultipartPlotSigpac::with(['plotGeometry', 'sigpacCode', 'plot'])
            ->whereIn('plot_id', $plotIds)
            ->whereNotNull('plot_geometry_id')
            ->get()
            ->filter(fn ($rel) => $rel->plotGeometry)
            ->map(function ($rel, $index) {
                $wkt = $rel->plotGeometry->getWktCoordinates();
                if (! $wkt) {
                    return null;
                }
                $hue = ($index * 137.5) % 360;

                return [
                    'id' => $rel->id,
                    'index' => $index + 1,
                    'source' => $rel->source ?? 'sigpac',
                    'sigpac_code' => $rel->sigpacCode?->code ?? null,
                    'sigpac_formatted' => $rel->sigpacCode?->formatted_code ?? null,
                    'polygon' => $rel->sigpacCode?->code_polygon ?? null,
                    'enclosure' => $rel->sigpacCode?->code_enclosure ?? null,
                    'plot_name' => $rel->plot?->name ?? '',
                    'wkt' => $wkt,
                    'color' => [
                        'fill' => "hsla({$hue}, 70%, 50%, 0.3)",
                        'line' => "hsl({$hue}, 70%, 40%)",
                    ],
                ];
            })
            ->filter()
            ->values();
    }

    private function getColorForIndex(int $index): array
    {
        $colors = [
            ['line' => '#10b981', 'fill' => '#86efac'], // Verde
            ['line' => '#3b82f6', 'fill' => '#93c5fd'], // Azul
            ['line' => '#f59e0b', 'fill' => '#fcd34d'], // Naranja
            ['line' => '#ef4444', 'fill' => '#fca5a5'], // Rojo
            ['line' => '#8b5cf6', 'fill' => '#c4b5fd'], // Morado
            ['line' => '#06b6d4', 'fill' => '#67e8f9'], // Cyan
            ['line' => '#ec4899', 'fill' => '#f9a8d4'], // Pink
        ];

        return $colors[$index % count($colors)];
    }
}
