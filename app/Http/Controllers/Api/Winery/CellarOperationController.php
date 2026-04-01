<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\CellarOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CellarOperationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = CellarOperation::forUser($user->id)
            ->with(['sourceContainer', 'targetContainer'])
            ->orderByDesc('operation_date');

        if ($request->filled('type')) {
            $query->where('operation_type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('container_id')) {
            $cid = $request->integer('container_id');
            $query->where(function ($q) use ($cid) {
                $q->where('source_container_id', $cid)
                  ->orWhere('target_container_id', $cid);
            });
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($op) => $this->format($op)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'types'        => CellarOperation::OPERATION_TYPES,
                'statuses'     => CellarOperation::STATUSES,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $operation = CellarOperation::forUser($user->id)
            ->with(['sourceContainer', 'targetContainer'])
            ->findOrFail($id);

        return response()->json(['data' => $this->format($operation)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'operation_type'      => 'required|string|in:' . implode(',', array_keys(CellarOperation::OPERATION_TYPES)),
            'operation_date'      => 'required|date',
            'source_container_id' => 'nullable|integer|exists:containers,id',
            'target_container_id' => 'nullable|integer|exists:containers,id',
            'volume_liters'       => 'nullable|numeric|min:0',
            'responsible_person'  => 'nullable|string|max:150',
            'status'              => 'nullable|string|in:' . implode(',', array_keys(CellarOperation::STATUSES)),
            'notes'               => 'nullable|string|max:1000',
        ]);

        $validated['status']  ??= 'planned';
        $validated['user_id']   = $user->id;

        $operation = CellarOperation::create($validated);
        $operation->load(['sourceContainer', 'targetContainer']);

        return response()->json([
            'data'    => $this->format($operation),
            'message' => 'Operación de bodega registrada correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $operation = CellarOperation::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'operation_type'      => 'sometimes|string|in:' . implode(',', array_keys(CellarOperation::OPERATION_TYPES)),
            'operation_date'      => 'sometimes|date',
            'source_container_id' => 'sometimes|nullable|integer|exists:containers,id',
            'target_container_id' => 'sometimes|nullable|integer|exists:containers,id',
            'volume_liters'       => 'sometimes|nullable|numeric|min:0',
            'responsible_person'  => 'sometimes|nullable|string|max:150',
            'status'              => 'sometimes|string|in:' . implode(',', array_keys(CellarOperation::STATUSES)),
            'notes'               => 'sometimes|nullable|string|max:1000',
        ]);

        $operation->update($validated);
        $operation->load(['sourceContainer', 'targetContainer']);

        return response()->json(['data' => $this->format($operation)]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $operation = CellarOperation::forUser($user->id)->findOrFail($id);

        abort_if(
            $operation->status === 'completed',
            422,
            'La operación ya está completada.'
        );

        $operation->update(['status' => 'completed']);
        $operation->load(['sourceContainer', 'targetContainer']);

        return response()->json([
            'data'    => $this->format($operation),
            'message' => 'Operación completada.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $operation = CellarOperation::forUser($user->id)->findOrFail($id);
        $operation->delete();

        return response()->json(['message' => 'Operación eliminada correctamente.']);
    }

    private function format(CellarOperation $op): array
    {
        return [
            'id'                  => $op->id,
            'operation_type'      => $op->operation_type,
            'type_label'          => $op->type_label,
            'operation_date'      => $op->operation_date?->toDateString(),
            'source_container'    => $op->sourceContainer ? ['id' => $op->sourceContainer->id, 'name' => $op->sourceContainer->name] : null,
            'target_container'    => $op->targetContainer ? ['id' => $op->targetContainer->id, 'name' => $op->targetContainer->name] : null,
            'volume_liters'       => $op->volume_liters !== null ? (float) $op->volume_liters : null,
            'responsible_person'  => $op->responsible_person,
            'status'              => $op->status,
            'status_label'        => $op->status_label,
            'notes'               => $op->notes,
            'created_at'          => $op->created_at->toIso8601String(),
        ];
    }
}
