<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlotResource;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlotController extends Controller
{
    // ─── GET /winery/plots ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'per_page'       => 'nullable|integer|min:1|max:100',
            'viticulturist'  => 'nullable|integer|min:1',
            'search'         => 'nullable|string|max:100',
        ]);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id')
            ->all();

        $base = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true);

        if ($request->filled('viticulturist')) {
            $base->where('viticulturist_id', (int) $request->viticulturist);
        }
        if ($request->filled('search')) {
            $base->where('name', 'like', '%' . $request->search . '%');
        }

        // Area stats over full filtered set (single aggregation query)
        $areaStats = (clone $base)
            ->selectRaw('SUM(area) as total_area, SUM(CASE WHEN is_organic = 1 THEN area ELSE 0 END) as organic_area')
            ->first();

        $perPage = $this->resolvePerPage($request, 12, 100);

        $plots = (clone $base)
            ->with(['province', 'municipality', 'plantings.grapeVariety'])
            ->orderBy('name')
            ->paginate($perPage);

        $centroids = $this->batchCentroids($plots->pluck('id')->all());

        $plots->each(function ($plot) use ($centroids) {
            $plot->centroid_data = $centroids[$plot->id] ?? null;
            $plot->has_geometry  = isset($centroids[$plot->id]);
        });

        return response()->json([
            'data' => PlotResource::collection($plots),
            'meta' => [
                'total'        => $plots->total(),
                'per_page'     => $plots->perPage(),
                'current_page' => $plots->currentPage(),
                'last_page'    => $plots->lastPage(),
                'total_area'   => round((float) $areaStats->total_area, 2),
                'organic_area' => round((float) $areaStats->organic_area, 2),
            ],
        ]);
    }

    // ─── GET /winery/plots/geometries ────────────────────────────────────────

    public function allGeometries(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'bbox' => ['nullable', 'string', 'regex:/^-?\d{1,3}(\.\d+)?,-?\d{1,3}(\.\d+)?,-?\d{1,3}(\.\d+)?,-?\d{1,3}(\.\d+)?$/'],
        ]);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id')
            ->all();

        $plots = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->with('viticulturist:id,name')
            ->orderBy('name')
            ->limit(500) // Cap: evitar DoS de memoria con bodegas muy grandes
            ->get(['id', 'name', 'area', 'viticulturist_id']);

        $plotIds = $plots->pluck('id')->all();

        if (empty($plotIds)) {
            return response()->json(['plots' => []]);
        }

        $placeholders = implode(',', array_fill(0, count($plotIds), '?'));
        $params       = $plotIds;

        $sql = "SELECT mps.plot_id, sc.code,
                        ST_AsText(pg.coordinates) AS coordinates_wkt,
                        ST_AsText(pg.centroid) AS centroid_wkt
                 FROM   multipart_plot_sigpac mps
                 JOIN   sigpac_code   sc ON mps.sigpac_code_id  = sc.id
                 JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
                 WHERE  mps.plot_id IN ($placeholders)
                 AND    pg.coordinates IS NOT NULL";

        if ($request->filled('bbox')) {
            $parts = array_map('floatval', explode(',', $request->query('bbox')));
            if (count($parts) === 4) {
                [$south, $west, $north, $east] = $parts;
                // Validar rangos geográficos: lat [-90,90], lng [-180,180]
                if ($south >= -90 && $north <= 90 && $south < $north &&
                    $west >= -180 && $east <= 180 && $west < $east) {
                    $bboxWkt  = "POLYGON(($west $south,$east $south,$east $north,$west $north,$west $south))";
                    $sql     .= ' AND ST_Intersects(pg.coordinates, ST_GeomFromText(?, 4326))';
                    $params[] = $bboxWkt;
                }
            }
        }

        $rows             = DB::select($sql, $params);
        $geometriesByPlot = collect($rows)->groupBy('plot_id');

        $result = $plots->map(function ($plot) use ($geometriesByPlot) {
            $geos       = $geometriesByPlot->get($plot->id, collect());
            $geometries = $geos->map(fn ($row) => [
                'sigpac_code' => $row->code,
                'centroid'    => $this->parseCentroidWkt($row->centroid_wkt),
                'coordinates' => $this->parsePolygonWkt($row->coordinates_wkt),
            ])->values();

            return [
                'plot_id'            => $plot->id,
                'plot_name'          => $plot->name,
                'viticulturist_name' => $plot->viticulturist?->name,
                'area'               => (float) $plot->area,
                'has_geometry'       => $geometries->isNotEmpty(),
                'geometries'         => $geometries,
            ];
        })->filter(fn ($p) => $p['has_geometry'])->values();

        return response()->json(['plots' => $result]);
    }

    // ─── GET /winery/plots/centroids ─────────────────────────────────────────

    public function centroids(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id')->all();

        $plots = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->with(['viticulturist:id,name', 'municipality:id,lat,lng'])
            ->orderBy('name')
            ->get(['id', 'name', 'area', 'viticulturist_id', 'municipality_id']);

        $centroids = $this->batchCentroids($plots->pluck('id')->all());

        $result = $plots
            ->filter(fn ($p) =>
                isset($centroids[$p->id]) ||
                ($p->municipality?->lat && $p->municipality?->lng)
            )
            ->map(fn ($p) => [
                'plot_id'            => $p->id,
                'plot_name'          => $p->name,
                'viticulturist_name' => $p->viticulturist?->name,
                'area'               => (float) $p->area,
                'has_geometry'       => isset($centroids[$p->id]),
                'centroid'           => $centroids[$p->id] ?? [
                    'lat' => (float) $p->municipality->lat,
                    'lng' => (float) $p->municipality->lng,
                ],
            ])->values();

        return response()->json(['plots' => $result]);
    }

    // ─── GET /winery/plots/{id} ───────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $plot = Plot::whereIn('viticulturist_id', $viticulturistIds)
            ->with(['province', 'municipality', 'plantings.grapeVariety'])
            ->findOrFail($id);

        $centroids = $this->batchCentroids([$plot->id]);
        $plot->centroid_data = $centroids[$plot->id] ?? null;
        $plot->has_geometry  = isset($centroids[$plot->id]);

        return response()->json(['data' => new PlotResource($plot)]);
    }

    // ─── GET /winery/plots/{id}/geometries ────────────────────────────────────

    public function geometries(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        Plot::whereIn('viticulturist_id', $viticulturistIds)->findOrFail($id);

        $rows = DB::select(
            'SELECT sc.code,
                    ST_AsText(pg.coordinates) AS coordinates_wkt,
                    ST_AsText(pg.centroid)    AS centroid_wkt
             FROM   multipart_plot_sigpac mps
             JOIN   sigpac_code   sc ON mps.sigpac_code_id  = sc.id
             JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
             WHERE  mps.plot_id = ?
             AND    pg.coordinates IS NOT NULL',
            [$id]
        );

        $geometries = collect($rows)->map(fn ($row) => [
            'sigpac_code' => $row->code,
            'centroid'    => $this->parseCentroidWkt($row->centroid_wkt),
            'coordinates' => $this->parsePolygonWkt($row->coordinates_wkt),
        ]);

        return response()->json([
            'plot_id'      => $id,
            'has_geometry' => $geometries->isNotEmpty(),
            'geometries'   => $geometries,
        ]);
    }

    // ─── GET /winery/plots/{id}/plantings ──────────────────────────────────────

    public function plantings(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $plot = Plot::whereIn('viticulturist_id', $viticulturistIds)->findOrFail($id);

        $plantings = $plot->plantings()
            ->with(['grapeVariety', 'trainingSystem'])
            ->where('active', true)
            ->orderBy('planting_year', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'grape_variety'   => $p->grapeVariety?->name,
                'planted_area'    => (float) $p->planted_area,
                'planting_year'   => $p->planting_year,
                'vine_count'      => $p->vine_count,
                'row_spacing'     => $p->row_spacing ? (float) $p->row_spacing : null,
                'vine_spacing'    => $p->vine_spacing ? (float) $p->vine_spacing : null,
                'rootstock'       => $p->rootstock,
                'training_system' => $p->trainingSystem?->name,
                'status'          => $p->status,
                'irrigated'       => (bool) $p->irrigated,
            ]);

        return response()->json(['data' => $plantings]);
    }

    // ─── GET /winery/plots/{id}/harvest-quality ───────────────────────────────

    public function harvestQuality(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        Plot::whereIn('viticulturist_id', $viticulturistIds)->findOrFail($id);

        $activities = AgriculturalActivity::where('plot_id', $id)
            ->where('activity_type', 'harvest')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->with(['harvest', 'plotPlanting.grapeVariety'])
            ->orderByDesc('activity_date')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $activities->map(fn ($a) => [
                'id'                => $a->id,
                'activity_date'     => $a->activity_date?->format('Y-m-d'),
                'grape_variety'     => $a->plotPlanting?->grapeVariety?->name,
                'baume_degree'      => $a->harvest?->baume_degree,
                'brix_degree'       => $a->harvest?->brix_degree,
                'ph_level'          => $a->harvest?->ph_level,
                'acidity_level'     => $a->harvest?->acidity_level,
                'potential_alcohol' => $a->harvest?->potential_alcohol,
                'total_weight'      => $a->harvest?->total_weight,
                'notes'             => $a->notes ?? $a->harvest?->notes,
            ]),
            'meta' => [
                'total'        => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/plots/{id}/notebook ─────────────────────────────────────

    public function notebook(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        Plot::whereIn('viticulturist_id', $viticulturistIds)->findOrFail($id);

        $activities = AgriculturalActivity::where('plot_id', $id)
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->orderByDesc('activity_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $activities->map(fn ($a) => [
                'id'            => $a->id,
                'activity_type' => $a->activity_type,
                'activity_date' => $a->activity_date?->format('Y-m-d'),
                'notes'         => $a->notes,
            ]),
            'meta' => [
                'total'        => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
            ],
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function batchCentroids(array $plotIds): array
    {
        if (empty($plotIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($plotIds), '?'));

        $rows = DB::select(
            "SELECT   mps.plot_id,
                      MIN(ST_AsText(pg.centroid)) AS centroid_wkt
             FROM     multipart_plot_sigpac mps
             JOIN     plot_geometry pg ON mps.plot_geometry_id = pg.id
             WHERE    mps.plot_id IN ($placeholders)
             AND      pg.centroid IS NOT NULL
             GROUP BY mps.plot_id",
            $plotIds
        );

        $result = [];
        foreach ($rows as $row) {
            $centroid = $this->parseCentroidWkt($row->centroid_wkt);
            if ($centroid) {
                $result[$row->plot_id] = $centroid;
            }
        }

        return $result;
    }

    private function parseCentroidWkt(?string $wkt): ?array
    {
        if (!$wkt) return null;
        preg_match('/POINT\(([^)]+)\)/', $wkt, $m);
        if (!isset($m[1])) return null;
        $parts = explode(' ', trim($m[1]));
        return count($parts) >= 2
            ? ['lat' => (float) $parts[1], 'lng' => (float) $parts[0]]
            : null;
    }

    private function parsePolygonWkt(?string $wkt): array
    {
        if (!$wkt) return [];
        preg_match('/POLYGON\(\(([^)]+)\)\)/', $wkt, $m);
        if (!isset($m[1])) return [];

        $points = [];
        foreach (explode(',', $m[1]) as $coord) {
            $parts = explode(' ', trim($coord));
            if (count($parts) >= 2) {
                $points[] = ['lat' => (float) $parts[1], 'lng' => (float) $parts[0]];
            }
        }
        return $points;
    }
}
