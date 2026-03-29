<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FermentationControlResource;
use App\Http\Resources\Api\WineResource;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineController extends Controller
{
    // ─── GET /winery/wines ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'status'   => 'nullable|string|in:' . implode(',', array_keys(Wine::STATUSES)),
            'vintage'  => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $base = Wine::forUser($user->id);

        if ($request->filled('status')) {
            $base->where('status', $request->status);
        }
        if ($request->filled('vintage')) {
            $base->where('vintage', (int) $request->vintage);
        }

        // Status counts over full filtered set (single aggregation query)
        $counts = (clone $base)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'aged'        THEN 1 ELSE 0 END) as aged,
                SUM(CASE WHEN status = 'bottled'     THEN 1 ELSE 0 END) as bottled
            ")
            ->first();

        $perPage = min((int) $request->query('per_page', 12), 100);

        $wines = (clone $base)
            ->with(['fermentationControls' => fn ($q) => $q->latest('control_date')->take(1)])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => WineResource::collection($wines),
            'meta' => [
                'total'        => (int) $counts->total,
                'per_page'     => $wines->perPage(),
                'current_page' => $wines->currentPage(),
                'last_page'    => $wines->lastPage(),
                'in_progress'  => (int) $counts->in_progress,
                'aged'         => (int) $counts->aged,
                'bottled'      => (int) $counts->bottled,
            ],
        ]);
    }

    // ─── GET /winery/wines/{id} ───────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)
            ->with(['fermentationControls' => fn ($q) => $q->latest('control_date')->take(10)])
            ->findOrFail($id);

        return response()->json(['data' => new WineResource($wine)]);
    }

    // ─── POST /winery/wines ───────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'vintage'             => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'wine_type'           => 'required|string|in:' . implode(',', array_keys(Wine::WINE_TYPES)),
            'aging_type'          => 'nullable|string|in:' . implode(',', array_keys(Wine::AGING_TYPES)),
            'category'            => 'nullable|string|in:' . implode(',', array_keys(Wine::CATEGORIES)),
            'variety'             => 'nullable|string|max:255',
            'volume_liters'       => 'nullable|numeric|min:0',
            'initial_quantity_kg' => 'nullable|numeric|min:0',
            'internal_code'       => 'nullable|string|max:100',
            'is_must'             => 'nullable|boolean',
            'is_organic'          => 'nullable|boolean',
            'notes'               => 'nullable|string|max:2000',
        ]);

        $wine = Wine::create(array_merge($validated, [
            'user_id' => $user->id,
            'status'  => 'in_progress',
        ]));

        return response()->json(['data' => new WineResource($wine)], 201);
    }

    // ─── PUT /winery/wines/{id} ───────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'vintage'       => 'sometimes|nullable|integer|min:1900|max:' . (now()->year + 2),
            'wine_type'     => 'sometimes|string|in:' . implode(',', array_keys(Wine::WINE_TYPES)),
            'aging_type'    => 'sometimes|nullable|string|in:' . implode(',', array_keys(Wine::AGING_TYPES)),
            'category'      => 'sometimes|nullable|string|in:' . implode(',', array_keys(Wine::CATEGORIES)),
            'status'        => 'sometimes|string|in:' . implode(',', array_keys(Wine::STATUSES)),
            'variety'       => 'sometimes|nullable|string|max:255',
            'volume_liters' => 'sometimes|nullable|numeric|min:0',
            'internal_code' => 'sometimes|nullable|string|max:100',
            'is_must'       => 'sometimes|nullable|boolean',
            'is_organic'    => 'sometimes|nullable|boolean',
            'notes'         => 'sometimes|nullable|string|max:2000',
        ]);

        $wine->update($validated);

        return response()->json(['data' => new WineResource($wine)]);
    }

    // ─── DELETE /winery/wines/{id} ────────────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($id);
        $wine->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Vino cancelado correctamente.']);
    }

    // ─── GET /winery/wines/{id}/fermentation-controls ─────────────────────────

    public function fermentationControls(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($id);

        $controls = WineFermentationControl::where('wine_id', $wine->id)
            ->with(['container'])
            ->orderByDesc('control_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => FermentationControlResource::collection($controls->items()),
            'meta' => [
                'total'        => $controls->total(),
                'current_page' => $controls->currentPage(),
                'last_page'    => $controls->lastPage(),
            ],
        ]);
    }
}
