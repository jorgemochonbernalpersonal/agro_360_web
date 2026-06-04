<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FermentationControlResource;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FermentationControlController extends Controller
{
    // ─── GET /winery/fermentation-controls ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $perPage = $this->resolvePerPage($request, 30, 100);
        $controls = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )
            ->with(['wine', 'container'])
            ->orderByDesc('control_date')
            ->paginate($perPage);

        return response()->json([
            'data' => FermentationControlResource::collection($controls->items()),
            'meta' => [
                'total' => $controls->total(),
                'per_page' => $controls->perPage(),
                'current_page' => $controls->currentPage(),
                'last_page' => $controls->lastPage(),
            ],
        ]);
    }

    // ─── POST /winery/fermentation-controls ───────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id' => 'required|integer|exists:wines,id',
            'container_id' => 'nullable|integer',
            'control_date' => 'required|date',
            'temperature' => 'nullable|numeric|between:-10,60',
            'brix_degree' => 'nullable|numeric|between:0,40',
            'baume_degree' => 'nullable|numeric|between:0,25',
            'density' => 'nullable|numeric|between:0.900,1.200',
            'ph' => 'nullable|numeric|between:2,5',
            'volatile_acidity' => 'nullable|numeric|between:0,5',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify ownership
        $wine = Wine::forUser($user->id)->findOrFail($validated['wine_id']);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }

        $control = WineFermentationControl::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        $control->load(['wine', 'container']);

        return response()->json(['data' => new FermentationControlResource($control)], 201);
    }

    // ─── GET /winery/fermentation-controls/{id} ──────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $control = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->with(['wine', 'container'])->findOrFail($id);

        return response()->json(['data' => new FermentationControlResource($control)]);
    }

    // ─── PUT /winery/fermentation-controls/{id} ──────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $control = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'container_id' => 'nullable|integer',
            'control_date' => 'nullable|date',
            'temperature' => 'nullable|numeric|between:-10,60',
            'brix_degree' => 'nullable|numeric|between:0,40',
            'baume_degree' => 'nullable|numeric|between:0,25',
            'density' => 'nullable|numeric|between:0.900,1.200',
            'ph' => 'nullable|numeric|between:2,5',
            'volatile_acidity' => 'nullable|numeric|between:0,5',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }

        $control->update($validated);
        $control->load(['wine', 'container']);

        return response()->json(['data' => new FermentationControlResource($control)]);
    }

    // ─── DELETE /winery/fermentation-controls/{id} ────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $control = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $control->delete();

        return response()->json(['message' => __('Control eliminado correctamente.')]);
    }
}
