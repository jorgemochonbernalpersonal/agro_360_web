<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\FermentationControlResource;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FermentationControlController extends BaseApiController
{
    // ─── GET /winery/fermentation-controls ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = $this->resolvePerPage($request, 30, 100);
        $controls = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )
            ->with(['wine', 'container'])
            ->orderByDesc('control_date')
            ->paginate($perPage);

        return $this->paginated($controls, FermentationControlResource::collection($controls->items()));
    }

    // ─── POST /winery/fermentation-controls ───────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

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

        return $this->created(new FermentationControlResource($control));
    }

    // ─── GET /winery/fermentation-controls/{id} ──────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $control = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->with(['wine', 'container'])->findOrFail($id);

        return $this->success(new FermentationControlResource($control));
    }

    // ─── PUT /winery/fermentation-controls/{id} ──────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

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

        return $this->success(new FermentationControlResource($control));
    }

    // ─── DELETE /winery/fermentation-controls/{id} ────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $control = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $control->delete();

        return $this->deleted(__('Control eliminado correctamente.'));
    }
}
