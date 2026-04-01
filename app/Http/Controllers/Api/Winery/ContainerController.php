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

        $request->validate([
            'room_id'  => 'nullable|integer|min:1',
            'status'   => 'nullable|string|in:empty,full,critical',
            'per_page' => 'nullable|string|max:10',
        ]);

        $base = Container::where('user_id', $user->id)
            ->where('archived', false);

        if ($request->filled('room_id')) {
            $base->where('container_room_id', (int) $request->room_id);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'empty'    => $base->scopes(['empty']),
                'full'     => $base->scopes(['full']),
                'critical' => $base->whereRaw('(used_capacity / NULLIF(capacity, 0)) >= 0.85'),
                default    => null,
            };
        }

        // Capacity totals over full filtered set (single aggregation query)
        $totals = (clone $base)
            ->selectRaw('COUNT(*) as total, SUM(capacity) as total_capacity, SUM(used_capacity) as total_used')
            ->first();

        $perPageRaw = $request->query('per_page', '30');
        $all        = $perPageRaw === 'all';
        $perPage    = $all ? (int) $totals->total ?: 1000 : min((int) $perPageRaw, 500);

        $containers = (clone $base)
            ->with(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine'])
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total'          => (int) $totals->total,
                'per_page'       => $containers->perPage(),
                'current_page'   => $containers->currentPage(),
                'last_page'      => $containers->lastPage(),
                'has_more'       => $containers->hasMorePages(),
                'total_capacity' => round((float) $totals->total_capacity, 2),
                'total_used'     => round((float) $totals->total_used, 2),
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

    // ─── POST /winery/containers ─────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'capacity'           => 'required|numeric|min:0.1',
            'type_id'            => 'nullable|integer|exists:container_types,id',
            'material_id'        => 'nullable|integer|exists:container_materials,id',
            'container_room_id'  => 'nullable|integer|exists:container_rooms,id',
            'serial_number'      => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $container = Container::create(array_merge($validated, [
            'user_id'       => $user->id,
            'used_capacity' => 0,
            'archived'      => false,
        ]));

        $container->load(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine']);

        return response()->json(['data' => new ContainerResource($container)], 201);
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

    // ─── DELETE /winery/containers/{id} — archive ─────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $container = Container::where('user_id', $user->id)->findOrFail($id);
        $container->update(['archived' => true]);

        return response()->json(['message' => 'Depósito archivado correctamente.']);
    }
}
