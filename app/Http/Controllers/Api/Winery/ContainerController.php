<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContainerResource;
use App\Models\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    // ─── GET /winery/containers ───────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = Container::where('user_id', $user->id)
            ->where('archived', false)
            ->with(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine']);

        // Filters
        if ($request->filled('room_id')) {
            $query->where('container_room_id', $request->room_id);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'empty'    => $query->scopes(['empty']),
                'full'     => $query->scopes(['full']),
                'critical' => $query->whereRaw('(used_capacity / NULLIF(capacity, 0)) >= 0.85'),
                default    => null,
            };
        }

        $containers = $query->orderBy('name')->get();

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total'          => $containers->count(),
                'total_capacity' => $containers->sum(fn ($c) => (float) $c->capacity),
                'total_used'     => $containers->sum(fn ($c) => (float) $c->used_capacity),
            ],
        ]);
    }

    // ─── GET /winery/containers/{id} ──────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $container = Container::where('user_id', $user->id)
            ->with(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine'])
            ->findOrFail($id);

        return response()->json(['data' => new ContainerResource($container)]);
    }

    // ─── PUT /winery/containers/{id} ──────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $container = Container::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'notes'              => 'nullable|string|max:1000',
            'container_room_id'  => 'nullable|integer|exists:container_rooms,id',
        ]);

        $container->update($validated);

        $container->load(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine']);

        return response()->json(['data' => new ContainerResource($container)]);
    }
}
