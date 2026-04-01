<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\EcoCertification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcoCertificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = EcoCertification::forUser($user->id)
            ->orderByDesc('valid_from');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('certification_type', $request->input('type'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($c) => $this->format($c)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'types'        => EcoCertification::CERTIFICATION_TYPES,
                'statuses'     => EcoCertification::STATUSES,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $certification = EcoCertification::forUser($user->id)->findOrFail($id);

        return response()->json(['data' => $this->format($certification)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'certification_type' => 'required|string|in:' . implode(',', array_keys(EcoCertification::CERTIFICATION_TYPES)),
            'certifying_body'    => 'nullable|string|max:255',
            'certificate_number' => 'nullable|string|max:100',
            'valid_from'         => 'required|date',
            'valid_until'        => 'nullable|date|after:valid_from',
            'status'             => 'nullable|string|in:' . implode(',', array_keys(EcoCertification::STATUSES)),
            'notes'              => 'nullable|string|max:1000',
        ]);

        $validated['status']  ??= 'active';
        $validated['user_id']   = $user->id;

        $certification = EcoCertification::create($validated);

        return response()->json([
            'data'    => $this->format($certification),
            'message' => 'Certificación ecológica creada correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $certification = EcoCertification::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'certification_type' => 'sometimes|string|in:' . implode(',', array_keys(EcoCertification::CERTIFICATION_TYPES)),
            'certifying_body'    => 'sometimes|nullable|string|max:255',
            'certificate_number' => 'sometimes|nullable|string|max:100',
            'valid_from'         => 'sometimes|date',
            'valid_until'        => 'sometimes|nullable|date',
            'status'             => 'sometimes|string|in:' . implode(',', array_keys(EcoCertification::STATUSES)),
            'notes'              => 'sometimes|nullable|string|max:1000',
        ]);

        $certification->update($validated);

        return response()->json(['data' => $this->format($certification)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $certification = EcoCertification::forUser($user->id)->findOrFail($id);
        $certification->delete();

        return response()->json(['message' => 'Certificación eliminada correctamente.']);
    }

    private function format(EcoCertification $c): array
    {
        return [
            'id'                 => $c->id,
            'name'               => $c->name,
            'certification_type' => $c->certification_type,
            'type_label'         => $c->type_label,
            'certifying_body'    => $c->certifying_body,
            'certificate_number' => $c->certificate_number,
            'valid_from'         => $c->valid_from?->toDateString(),
            'valid_until'        => $c->valid_until?->toDateString(),
            'status'             => $c->status,
            'status_label'       => $c->status_label,
            'expiring_soon'      => $c->isExpiringSoon(),
            'notes'              => $c->notes,
            'created_at'         => $c->created_at->toIso8601String(),
        ];
    }
}
