<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\MultipartPlotSigpac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlotMapController extends Controller
{
    public function show(Plot $plot, Request $request)
    {
        $this->authorize('view', $plot);

        // ✅ Iniciar medición de performance
        $startTime = microtime(true);
        
        // Verificar si se debe mostrar todo el municipio
        $municipalityId = $request->query('municipality');
        $showMunicipality = $municipalityId !== null;

        // Usar caché diferente según el contexto
        $cacheKey = $showMunicipality 
            ? "municipality_geometries_{$municipalityId}_user_" . auth()->id()
            : "plot_geometries_{$plot->id}";
        $fromCache = \Illuminate\Support\Facades\Cache::has($cacheKey);
        
        $plotGeometries = \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addHours(24),
            function () use ($plot, $showMunicipality, $municipalityId) {
                if ($showMunicipality) {
                    return $this->loadMunicipalityGeometries($municipalityId);
                }
                return $this->loadPlotGeometries($plot);
            }
        );

        // ✅ Calcular tiempo de carga
        $loadTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        // ✅ Log de performance para monitoreo
        Log::info('Map performance metrics', [
            'plot_id' => $plot->id,
            'plot_name' => $plot->name,
            'municipality_mode' => $showMunicipality,
            'municipality_id' => $municipalityId,
            'geometries_count' => $plotGeometries->count(),
            'load_time_ms' => $loadTimeMs,
            'from_cache' => $fromCache,
            'cache_key' => $cacheKey,
            'user_id' => auth()->id(),
        ]);

        if ($plotGeometries->isEmpty()) {
            return redirect()
                ->route('plots.show', $plot)
                ->with('error', 'Esta parcela no tiene recintos generados. Genera el mapa primero.');
        }

        return view('map', compact('plot', 'plotGeometries', 'showMunicipality'));
    }

    /**
     * Cargar geometrías de la parcela optimizando queries (sin N+1)
     */
    private function loadPlotGeometries(Plot $plot)
    {
        // Cargar relaciones con eager loading
        $relations = MultipartPlotSigpac::with(['sigpacCode'])
            ->where('plot_id', $plot->id)
            ->whereNotNull('plot_geometry_id')
            ->get();

        if ($relations->isEmpty()) {
            return collect([]);
        }

        // Obtener todos los IDs de geometrías
        $geometryIds = $relations->pluck('plot_geometry_id')->unique()->filter();

        if ($geometryIds->isEmpty()) {
            return collect([]);
        }

        // ✅ OPTIMIZACIÓN: Cargar todos los WKT en una sola query batch
        $wktData = \Illuminate\Support\Facades\DB::select(
            'SELECT id, ST_AsText(coordinates) as wkt 
             FROM plot_geometry 
             WHERE id IN (' . $geometryIds->implode(',') . ')'
        );

        // Crear mapa id => wkt para acceso O(1)
        $wktMap = collect($wktData)->pluck('wkt', 'id');

        // Mapear relaciones a formato de vista
        return $relations
            ->map(function ($rel, $index) use ($wktMap) {
                $wkt = $wktMap[$rel->plot_geometry_id] ?? null;

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
    }

    /**
     * Cargar geometrías de todo un municipio
     */
    private function loadMunicipalityGeometries($municipalityId)
    {
        $user = auth()->user();
        
        // Obtener IDs de parcelas que el usuario puede ver en este municipio
        $plotIds = Plot::forUser($user)
            ->where('municipality_id', $municipalityId)
            ->pluck('id');

        if ($plotIds->isEmpty()) {
            return collect([]);
        }

        // Cargar relaciones con eager loading
        $relations = MultipartPlotSigpac::with(['sigpacCode', 'plot'])
            ->whereIn('plot_id', $plotIds)
            ->whereNotNull('plot_geometry_id')
            ->get();

        if ($relations->isEmpty()) {
            return collect([]);
        }

        // Obtener todos los IDs de geometrías
        $geometryIds = $relations->pluck('plot_geometry_id')->unique()->filter();

        if ($geometryIds->isEmpty()) {
            return collect([]);
        }

        // Cargar todos los WKT en una sola query batch
        $wktData = \Illuminate\Support\Facades\DB::select(
            'SELECT id, ST_AsText(coordinates) as wkt 
             FROM plot_geometry 
             WHERE id IN (' . $geometryIds->implode(',') . ')'
        );

        // Crear mapa id => wkt para acceso O(1)
        $wktMap = collect($wktData)->pluck('wkt', 'id');

        // Mapear relaciones a formato de vista
        return $relations
            ->map(function ($rel, $index) use ($wktMap) {
                $wkt = $wktMap[$rel->plot_geometry_id] ?? null;

                if (!$wkt) {
                    return null;
                }

                // Generar color único para cada recinto
                $hue = ($index * 137.5) % 360;
                
                return [
                    'id' => $rel->id,
                    'index' => $index + 1,
                    'sigpac_code' => $rel->sigpacCode->code ?? 'Sin código',
                    'sigpac_formatted' => $rel->sigpacCode->formatted_code ?? 'Sin código',
                    'plot_name' => $rel->plot->name ?? 'Sin parcela',
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
