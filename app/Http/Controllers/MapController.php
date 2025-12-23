<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\MultipartPlotSigpac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MapController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Mostrar mapa (detecta automáticamente Plot o SigpacCode)
     */
    public function show($id)
    {
        // Intentar primero como Plot
        $plot = Plot::find($id);
        
        if ($plot) {
            $this->authorize('view', $plot);
            return $this->renderMap($plot);
        }
        
        // Si no es Plot, intentar como SigpacCode
        $sigpacCode = SigpacCode::find($id);
        
        if ($sigpacCode) {
            return $this->showSigpac($sigpacCode);
        }
        
        // Si no es ninguno, error 404
        abort(404, 'No se encontró el mapa solicitado.');
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
            ->with('plot')
            ->first();

        if (!$multipart || !$multipart->plot) {
            return redirect()->back()
                ->with('error', 'Este código SIGPAC no tiene una parcela asociada con mapa generado.');
        }

        $plot = $multipart->plot;
        $this->authorize('view', $plot);

        return $this->renderMap($plot, $sigpacCode->id);
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
            ->filter(fn($rel) => $rel->plotGeometry)
            ->map(function($rel, $index) use ($highlightSigpacId) {
                $wkt = $rel->plotGeometry->getWktCoordinates();
                
                if (!$wkt) {
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
                ->with('error', 'Esta parcela no tiene recintos generados. Genera el mapa primero.');
        }

        return view('map', compact('plot', 'plotGeometries', 'highlightSigpacId'));
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
