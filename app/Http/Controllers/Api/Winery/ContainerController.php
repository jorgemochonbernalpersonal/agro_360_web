<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\IndexContainerRequest;
use App\Http\Requests\Api\Winery\StoreContainerRequest;
use App\Http\Requests\Api\Winery\UpdateContainerRequest;
use App\Http\Requests\Api\Winery\WineryApiRequest;
use App\Http\Resources\Api\ContainerResource;
use App\Models\Container;
use Illuminate\Http\JsonResponse;

class ContainerController extends BaseApiController
{
    // ─── GET /winery/containers ───────────────────────────────────────────────

    public function index(IndexContainerRequest $request): JsonResponse
    {
        $base = Container::where('user_id', $request->user()->id)
            ->where('archived', false);

        if ($request->filled('room_id')) {
            $base->where('container_room_id', (int) $request->room_id);
        }
        if ($request->filled('unit')) {
            $base->where('unit', $request->unit);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'empty' => $base->scopes(['empty']),
                'full' => $base->scopes(['full']),
                'critical' => $base->whereRaw('(used_capacity / NULLIF(capacity, 0)) >= 0.85'),
                default => null,
            };
        }

        // Capacity totals over full filtered set (single aggregation query)
        $totals = (clone $base)
            ->selectRaw('COUNT(*) as total, SUM(capacity) as total_capacity, SUM(used_capacity) as total_used')
            ->first();

        $perPageRaw = $request->query('per_page', '30');
        $all = $perPageRaw === 'all';
        $perPage = $all ? (int) $totals->total ?: 1000 : min((int) $perPageRaw, 500);

        $containers = (clone $base)
            ->with(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine'])
            ->orderBy('name')
            ->paginate($perPage);

        return $this->paginated($containers, ContainerResource::collection($containers), [
            'total_capacity' => round((float) $totals->total_capacity, 2),
            'total_used' => round((float) $totals->total_used, 2),
        ]);
    }

    // ─── GET /winery/containers/{id} ──────────────────────────────────────────

    public function show(WineryApiRequest $request, int $id): JsonResponse
    {
        $container = Container::where('user_id', $request->user()->id)
            ->with(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine'])
            ->findOrFail($id);

        return $this->success(new ContainerResource($container));
    }

    // ─── POST /winery/containers ─────────────────────────────────────────────

    public function store(StoreContainerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Strip nulls so DB column defaults (type_id, material_id) kick in
        $filtered = array_filter($validated, fn ($v) => ! is_null($v));

        $container = Container::create(array_merge($filtered, [
            'user_id' => $request->user()->id,
            'used_capacity' => 0,
            'archived' => false,
        ]));

        $container->load(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine']);

        return $this->created(new ContainerResource($container));
    }

    // ─── PUT /winery/containers/{id} ──────────────────────────────────────────

    public function update(UpdateContainerRequest $request, int $id): JsonResponse
    {
        $container = Container::where('user_id', $request->user()->id)->findOrFail($id);

        $container->update($request->validated());

        $container->load(['containerType', 'containerMaterial', 'containerRoom', 'currentStates.wine']);

        return $this->success(new ContainerResource($container));
    }

    // ─── DELETE /winery/containers/{id} — archive ─────────────────────────────

    public function destroy(WineryApiRequest $request, int $id): JsonResponse
    {
        $container = Container::where('user_id', $request->user()->id)->findOrFail($id);
        $container->update(['archived' => true]);

        return $this->deleted(__('Depósito archivado correctamente.'));
    }
}
