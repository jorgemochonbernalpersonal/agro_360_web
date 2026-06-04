<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\BottlingAuthorization;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BottlingAuthorizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = BottlingAuthorization::forUser($user->id)
            ->with('wine')
            ->orderByDesc('valid_from');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($a) => $this->format($a)),
            'meta' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'types' => BottlingAuthorization::authorizationTypeOptions(),
                'statuses' => BottlingAuthorization::statusOptions(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $authorization = BottlingAuthorization::forUser($user->id)->with('wine')->findOrFail($id);

        return response()->json(['data' => $this->format($authorization)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'authorization_number' => 'required|string|max:100',
            'authorization_type' => 'required|string|in:'.implode(',', array_keys(BottlingAuthorization::AUTHORIZATION_TYPES)),
            'wine_id' => 'nullable|integer|exists:wines,id',
            'authorized_volume_liters' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'issuing_authority' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:'.implode(',', array_keys(BottlingAuthorization::STATUSES)),
            'conditions' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['wine_id'])) {
            Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        }

        $validated['status'] ??= 'active';
        $validated['user_id'] = $user->id;

        $authorization = BottlingAuthorization::create($validated);
        $authorization->load('wine');

        return response()->json([
            'data' => $this->format($authorization),
            'message' => __('Autorización de embotellado creada correctamente.'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $authorization = BottlingAuthorization::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'authorization_number' => 'sometimes|string|max:100',
            'authorization_type' => 'sometimes|string|in:'.implode(',', array_keys(BottlingAuthorization::AUTHORIZATION_TYPES)),
            'wine_id' => 'sometimes|nullable|integer|exists:wines,id',
            'authorized_volume_liters' => 'sometimes|nullable|numeric|min:0',
            'valid_from' => 'sometimes|date',
            'valid_until' => 'sometimes|nullable|date',
            'issuing_authority' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|string|in:'.implode(',', array_keys(BottlingAuthorization::STATUSES)),
            'conditions' => 'sometimes|nullable|string|max:2000',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $authorization->update($validated);
        $authorization->load('wine');

        return response()->json(['data' => $this->format($authorization)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $authorization = BottlingAuthorization::forUser($user->id)->findOrFail($id);
        $authorization->delete();

        return response()->json(['message' => __('Autorización de embotellado eliminada correctamente.')]);
    }

    private function format(BottlingAuthorization $a): array
    {
        return [
            'id' => $a->id,
            'authorization_number' => $a->authorization_number,
            'authorization_type' => $a->authorization_type,
            'type_label' => $a->type_label,
            'wine_id' => $a->wine_id,
            'wine_name' => $a->wine?->name,
            'authorized_volume_liters' => $a->authorized_volume_liters !== null ? (float) $a->authorized_volume_liters : null,
            'valid_from' => $a->valid_from?->toDateString(),
            'valid_until' => $a->valid_until?->toDateString(),
            'issuing_authority' => $a->issuing_authority,
            'status' => $a->status,
            'status_label' => $a->status_label,
            'expiring_soon' => $a->isExpiringSoon(),
            'conditions' => $a->conditions,
            'notes' => $a->notes,
            'created_at' => $a->created_at->toIso8601String(),
        ];
    }
}
