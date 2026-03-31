<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WineProcessStepResource;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineProcessStepController extends Controller
{
    // ─── GET /winery/wines/{id}/process ──────────────────────────────────────

    public function index(Request $request, int $wineId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($wineId);

        $steps = WineProcessDetail::where('wine_id', $wine->id)
            ->with(['container', 'containers', 'oenologist', 'unitOfMeasurement'])
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => WineProcessStepResource::collection($steps),
            'meta' => [
                'total'          => $steps->count(),
                'process_types'  => WineProcessDetail::PROCESS_TYPES,
            ],
        ]);
    }

    // ─── POST /winery/wines/{id}/process ─────────────────────────────────────

    public function store(Request $request, int $wineId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($wineId);

        $validated = $request->validate([
            'process_type'           => 'required|string|in:' . implode(',', array_keys(WineProcessDetail::PROCESS_TYPES)),
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'container_id'           => 'nullable|integer|exists:containers,id',
            'oenologist_id'          => 'nullable|integer|exists:oenologists,id',
            'quantity'               => 'nullable|numeric|min:0',
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'observations'           => 'nullable|string|max:2000',
            // Contenedores adicionales (blending, trasvases múltiples, etc.)
            'extra_containers'                        => 'nullable|array|max:10',
            'extra_containers.*.container_id'         => 'required|integer|exists:containers,id',
            'extra_containers.*.quantity'             => 'nullable|numeric|min:0',
            'extra_containers.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
        ]);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }

        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $extraContainers = $validated['extra_containers'] ?? [];
        unset($validated['extra_containers']);

        $step = WineProcessDetail::create([
            ...$validated,
            'wine_id'    => $wine->id,
            'created_by' => $user->id,
        ]);

        if (!empty($extraContainers)) {
            $pivot = [];
            foreach ($extraContainers as $ec) {
                $pivot[$ec['container_id']] = [
                    'quantity'               => $ec['quantity'] ?? null,
                    'unit_of_measurement_id' => $ec['unit_of_measurement_id'] ?? null,
                ];
            }
            $step->containers()->sync($pivot);
        }

        $step->load(['container', 'containers', 'oenologist', 'unitOfMeasurement']);

        return response()->json([
            'data'    => new WineProcessStepResource($step),
            'message' => 'Paso de proceso registrado correctamente.',
        ], 201);
    }

    // ─── PUT /winery/process/{id} ─────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $step = WineProcessDetail::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'process_type'           => 'sometimes|string|in:' . implode(',', array_keys(WineProcessDetail::PROCESS_TYPES)),
            'start_date'             => 'sometimes|date',
            'end_date'               => 'sometimes|nullable|date',
            'container_id'           => 'sometimes|nullable|integer|exists:containers,id',
            'oenologist_id'          => 'sometimes|nullable|integer|exists:oenologists,id',
            'quantity'               => 'sometimes|nullable|numeric|min:0',
            'unit_of_measurement_id' => 'sometimes|nullable|integer|exists:unit_of_measurements,id',
            'observations'           => 'sometimes|nullable|string|max:2000',
            'extra_containers'                          => 'sometimes|nullable|array|max:10',
            'extra_containers.*.container_id'           => 'required|integer|exists:containers,id',
            'extra_containers.*.quantity'               => 'nullable|numeric|min:0',
            'extra_containers.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
        ]);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }

        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $extraContainers = $validated['extra_containers'] ?? null;
        unset($validated['extra_containers']);

        $step->update($validated);

        if ($extraContainers !== null) {
            $pivot = [];
            foreach ($extraContainers as $ec) {
                $pivot[$ec['container_id']] = [
                    'quantity'               => $ec['quantity'] ?? null,
                    'unit_of_measurement_id' => $ec['unit_of_measurement_id'] ?? null,
                ];
            }
            $step->containers()->sync($pivot);
        }

        $step->load(['container', 'containers', 'oenologist', 'unitOfMeasurement']);

        return response()->json(['data' => new WineProcessStepResource($step)]);
    }

    // ─── DELETE /winery/process/{id} ─────────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $step = WineProcessDetail::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $step->containers()->detach();
        $step->delete();

        return response()->json(['message' => 'Paso de proceso eliminado correctamente.']);
    }

    // ─── POST /winery/process/{id}/complete ──────────────────────────────────

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $step = WineProcessDetail::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'end_date' => 'nullable|date|after_or_equal:' . $step->start_date->toDateString(),
        ]);

        $step->update(['end_date' => $validated['end_date'] ?? now()->toDateString()]);
        $step->load(['container', 'containers', 'oenologist', 'unitOfMeasurement']);

        return response()->json([
            'data'    => new WineProcessStepResource($step),
            'message' => 'Paso marcado como completado.',
        ]);
    }
}
