<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\PlotResource;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlotController extends BaseApiController
{
    // ─── GET /viticulturist/plots ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = min((int) $request->query('per_page', 30), 100);
        $search = $request->query('search');

        $base = Plot::where('viticulturist_id', $user->id)
            ->where('active', true);

        if ($search) {
            $base->where('name', 'like', '%'.$search.'%');
        }

        // Area stats over full filtered set (single aggregation query)
        $areaStats = (clone $base)
            ->selectRaw('SUM(area) as total_area, SUM(CASE WHEN is_organic = 1 THEN area ELSE 0 END) as organic_area')
            ->first();

        // Paginated data
        $plots = (clone $base)
            ->with(['province', 'municipality', 'plantings.grapeVariety'])
            ->orderBy('name')
            ->paginate($perPage);

        // Batch-fetch one centroid per plot (single query, no N+1)
        $centroids = $this->batchCentroids($plots->pluck('id')->all());

        $plots->each(function ($plot) use ($centroids) {
            $plot->centroid_data = $centroids[$plot->id] ?? null;
            $plot->has_geometry = isset($centroids[$plot->id]);
        });

        return $this->paginated($plots, PlotResource::collection($plots), [
            'total_area' => round((float) $areaStats->total_area, 2),
            'organic_area' => round((float) $areaStats->organic_area, 2),
        ]);
    }

    // ─── GET /viticulturist/plots/centroids ──────────────────────────────────

    public function centroids(Request $request): JsonResponse
    {
        $user = $request->user();

        $plots = Plot::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->with('municipality:id,lat,lng')
            ->orderBy('name')
            ->get(['id', 'name', 'area', 'municipality_id']);

        $centroids = $this->batchCentroids($plots->pluck('id')->all());

        $result = $plots
            ->filter(fn ($p) => isset($centroids[$p->id]) ||
                ($p->municipality->lat && $p->municipality->lng)
            )
            ->map(fn ($p) => [
                'plot_id' => $p->id,
                'plot_name' => $p->name,
                'area' => (float) $p->area,
                'has_geometry' => isset($centroids[$p->id]),
                'centroid' => $centroids[$p->id] ?? [
                    'lat' => (float) $p->municipality->lat,
                    'lng' => (float) $p->municipality->lng,
                ],
            ])->values();

        return response()->json(['plots' => $result]);
    }

    // ─── GET /viticulturist/plots/geometries ─────────────────────────────────

    public function allGeometries(Request $request): JsonResponse
    {
        $user = $request->user();

        // Sólo los IDs del viticulturist (consulta ligera, sin cargar todos los campos)
        $plotIds = Plot::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->pluck('id')
            ->all();

        if (empty($plotIds)) {
            return response()->json(['plots' => []]);
        }

        $placeholders = implode(',', array_fill(0, count($plotIds), '?'));
        $params = $plotIds;

        $sql = "SELECT mps.plot_id, sc.code,
                        ST_AsText(pg.coordinates) AS coordinates_wkt,
                        ST_AsText(pg.centroid)    AS centroid_wkt
                 FROM   multipart_plot_sigpac mps
                 JOIN   sigpac_code   sc ON mps.sigpac_code_id  = sc.id
                 JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
                 WHERE  mps.plot_id IN ($placeholders)
                 AND    pg.coordinates IS NOT NULL";

        if ($request->filled('bbox')) {
            $parts = array_map('floatval', explode(',', $request->query('bbox')));
            if (count($parts) === 4) {
                [$south, $west, $north, $east] = $parts;
                $bboxWkt = "POLYGON(($west $south,$east $south,$east $north,$west $north,$west $south))";
                $sql .= ' AND ST_Intersects(pg.coordinates, ST_GeomFromText(?, 4326))';
                $params[] = $bboxWkt;
            }
        }

        $rows = DB::select($sql, $params);
        $geometriesByPlot = collect($rows)->groupBy('plot_id');

        if ($geometriesByPlot->isEmpty()) {
            return response()->json(['plots' => []]);
        }

        // Cargar metadatos sólo para parcelas con geometría — evita traer datos de
        // parcelas sin geometría que el filtro PHP descartaría después
        $relevantIds = $geometriesByPlot->keys()->all();
        $plotMeta = Plot::whereIn('id', $relevantIds)
            ->orderBy('name')
            ->get(['id', 'name', 'area'])
            ->keyBy('id');

        $result = $geometriesByPlot->map(function ($geos, $plotId) use ($plotMeta) {
            $plot = $plotMeta->get($plotId);
            if (! $plot) {
                return null;
            }

            return [
                'plot_id' => (int) $plotId,
                'plot_name' => $plot->name,
                'area' => (float) $plot->area,
                'has_geometry' => true,
                'geometries' => $geos->map(fn ($row) => [
                    'sigpac_code' => $row->code,
                    'centroid' => $this->parseCentroidWkt($row->centroid_wkt),
                    'coordinates' => $this->parsePolygonWkt($row->coordinates_wkt),
                ])->values(),
            ];
        })->filter()->sortBy('plot_name')->values();

        return response()->json(['plots' => $result]);
    }

    // ─── GET /viticulturist/plots/{id} ────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $plot = Plot::where('viticulturist_id', $user->id)
            ->with(['province', 'municipality', 'plantings.grapeVariety'])
            ->findOrFail($id);

        // Query directa para un único plot — evita el overhead de batchCentroids con GROUP BY
        $centroid = $this->singleCentroid($plot->id);
        $plot->centroid_data = $centroid;
        $plot->has_geometry = $centroid !== null;

        return $this->success(new PlotResource($plot));
    }

    // ─── GET /viticulturist/plots/{id}/geometries ─────────────────────────────

    public function geometries(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $plot = Plot::where('viticulturist_id', $user->id)->findOrFail($id);

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
            'centroid' => $this->parseCentroidWkt($row->centroid_wkt),
            'coordinates' => $this->parsePolygonWkt($row->coordinates_wkt),
        ]);

        return response()->json([
            'plot_id' => $id,
            'has_geometry' => $geometries->isNotEmpty(),
            'geometries' => $geometries,
        ]);
    }

    // ─── GET /viticulturist/plots/{id}/plantings ────────────────────────────────

    public function plantings(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $plot = Plot::where('viticulturist_id', $user->id)->findOrFail($id);

        $plantings = $plot->plantings()
            ->with(['grapeVariety', 'trainingSystem'])
            ->where('active', true)
            ->orderBy('planting_year', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'grape_variety' => $p->grapeVariety?->name,
                'planted_area' => (float) $p->planted_area,
                'planting_year' => $p->planting_year,
                'vine_count' => $p->vine_count,
                'row_spacing' => $p->row_spacing ? (float) $p->row_spacing : null,
                'vine_spacing' => $p->vine_spacing ? (float) $p->vine_spacing : null,
                'rootstock' => $p->rootstock,
                'training_system' => $p->trainingSystem?->name,
                'status' => $p->status,
                'irrigated' => (bool) $p->irrigated,
            ]);

        return $this->success($plantings);
    }

    // ─── PUT /viticulturist/plots/{id} ────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $plot = Plot::where('viticulturist_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_organic' => 'sometimes|boolean',
            'cultivation_type' => 'sometimes|string|max:100',
        ]);

        $plot->update($validated);
        $plot->load(['province', 'municipality', 'plantings.grapeVariety']);

        return $this->success(new PlotResource($plot));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Fetch one representative centroid per plot in a single query.
     * Returns [ plot_id => ['lat' => x, 'lng' => y] ]
     */
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

    /**
     * Centroid de una única parcela — query directa sin GROUP BY overhead.
     */
    private function singleCentroid(int $plotId): ?array
    {
        $row = DB::selectOne(
            'SELECT ST_AsText(pg.centroid) AS centroid_wkt
             FROM   multipart_plot_sigpac mps
             JOIN   plot_geometry pg ON mps.plot_geometry_id = pg.id
             WHERE  mps.plot_id = ?
             AND    pg.centroid IS NOT NULL
             LIMIT  1',
            [$plotId]
        );

        return $row ? $this->parseCentroidWkt($row->centroid_wkt) : null;
    }

    private function parseCentroidWkt(?string $wkt): ?array
    {
        if (! $wkt) {
            return null;
        }
        preg_match('/POINT\(([^)]+)\)/', $wkt, $m);
        if (! isset($m[1])) {
            return null;
        }
        $parts = explode(' ', trim($m[1]));

        return count($parts) >= 2
            ? ['lat' => (float) $parts[1], 'lng' => (float) $parts[0]]
            : null;
    }

    private function parsePolygonWkt(?string $wkt): array
    {
        if (! $wkt) {
            return [];
        }
        preg_match('/POLYGON\(\(([^)]+)\)\)/', $wkt, $m);
        if (! isset($m[1])) {
            return [];
        }

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
