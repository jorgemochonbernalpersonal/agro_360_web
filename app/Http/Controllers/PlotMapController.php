<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\MultipartPlotSigpac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlotMapController extends Controller
{
    public function show(Plot $plot)
    {
        $this->authorize('view', $plot);

        $plotGeometries = MultipartPlotSigpac::with(['plotGeometry', 'sigpacCode'])
            ->where('plot_id', $plot->id)
            ->whereNotNull('plot_geometry_id')
            ->get()
            ->filter(fn($rel) => $rel->plotGeometry)
            ->map(function($rel, $index) {
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
                    'wkt' => $wkt,
                    'color' => $this->getColorForIndex($index),
                ];
            })
            ->filter()
            ->values();

        Log::info('Plot map geometries loaded', [
            'plot_id' => $plot->id,
            'geometries_count' => $plotGeometries->count(),
        ]);

        if ($plotGeometries->isEmpty()) {
            return redirect()
                ->route('plots.show', $plot)
                ->with('error', 'Esta parcela no tiene recintos generados. Genera el mapa primero.');
        }

        return view('plots.map', compact('plot', 'plotGeometries'));
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
