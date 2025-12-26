<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\Plot;
use App\Models\MultipartPlotSigpac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MunicipalityMapController extends Controller
{
    /**
     * Mostrar todos los mapas de un municipio
     */
    public function show($municipalityId)
    {
        $user = Auth::user();
        
        // Buscar el municipio
        $municipality = Municipality::findOrFail($municipalityId);

        // Obtener IDs de parcelas que el usuario puede ver
        $plotIds = Plot::forUser($user)->pluck('id');

        if ($plotIds->isEmpty()) {
            return redirect()
                ->route('sigpac.codes')
                ->with('error', 'No tienes parcelas registradas.');
        }

        // Obtener todas las geometrías del municipio con caché
        $cacheKey = "municipality_geometries_{$municipality->id}_user_{$user->id}";
        
        $plotGeometries = \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addHours(24),
            function () use ($municipality, $plotIds) {
                // Cargar relaciones con eager loading
                $relations = MultipartPlotSigpac::with(['sigpacCode', 'plotGeometry', 'plot'])
                    ->whereHas('plot', function ($query) use ($plotIds, $municipality) {
                        $query->whereIn('plots.id', $plotIds)
                              ->where('municipality_id', $municipality->id);
                    })
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
                $wktData = DB::select(
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
                            'wkt' => $wkt,
                            'sigpac_code' => $rel->sigpacCode->code ?? 'Sin código',
                            'sigpac_formatted' => $rel->sigpacCode->formatted_code ?? 'Sin código',
                            'plot_name' => $rel->plot->name ?? 'Sin parcela',
                            'color' => [
                                'fill' => "hsla({$hue}, 70%, 50%, 0.3)",
                                'line' => "hsl({$hue}, 70%, 40%)",
                            ],
                        ];
                    })
                    ->filter()
                    ->values();
            }
        );

        if ($plotGeometries->isEmpty()) {
            return redirect()
                ->route('sigpac.codes')
                ->with('warning', "No hay mapas generados para {$municipality->name}. Usa el botón 'Generar Todos los Mapas' primero.");
        }

        return view('sigpac.municipality-map', compact('municipality', 'plotGeometries'));
    }
}
