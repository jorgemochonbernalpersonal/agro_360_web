<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\SanitaryRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SanitaryRegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = SanitaryRegistration::forUser($user->id)
            ->orderByDesc('registration_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('registration_type', $request->input('type'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($r) => $this->format($r)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'types'        => SanitaryRegistration::REGISTRATION_TYPES,
                'statuses'     => SanitaryRegistration::STATUSES,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $registration = SanitaryRegistration::forUser($user->id)->findOrFail($id);

        return response()->json(['data' => $this->format($registration)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'registration_number'  => 'required|string|max:100',
            'registration_type'    => 'required|string|in:' . implode(',', array_keys(SanitaryRegistration::REGISTRATION_TYPES)),
            'activity_description' => 'nullable|string|max:500',
            'registration_date'    => 'required|date',
            'renewal_date'         => 'nullable|date|after:registration_date',
            'issuing_authority'    => 'nullable|string|max:255',
            'status'               => 'nullable|string|in:' . implode(',', array_keys(SanitaryRegistration::STATUSES)),
            'notes'                => 'nullable|string|max:1000',
        ]);

        $validated['status']  ??= 'active';
        $validated['user_id']   = $user->id;

        $registration = SanitaryRegistration::create($validated);

        return response()->json([
            'data'    => $this->format($registration),
            'message' => __('Registro sanitario creado correctamente.'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $registration = SanitaryRegistration::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'registration_number'  => 'sometimes|string|max:100',
            'registration_type'    => 'sometimes|string|in:' . implode(',', array_keys(SanitaryRegistration::REGISTRATION_TYPES)),
            'activity_description' => 'sometimes|nullable|string|max:500',
            'registration_date'    => 'sometimes|date',
            'renewal_date'         => 'sometimes|nullable|date',
            'issuing_authority'    => 'sometimes|nullable|string|max:255',
            'status'               => 'sometimes|string|in:' . implode(',', array_keys(SanitaryRegistration::STATUSES)),
            'notes'                => 'sometimes|nullable|string|max:1000',
        ]);

        $registration->update($validated);

        return response()->json(['data' => $this->format($registration)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $registration = SanitaryRegistration::forUser($user->id)->findOrFail($id);
        $registration->delete();

        return response()->json(['message' => __('Registro sanitario eliminado correctamente.')]);
    }

    private function format(SanitaryRegistration $r): array
    {
        return [
            'id'                   => $r->id,
            'registration_number'  => $r->registration_number,
            'registration_type'    => $r->registration_type,
            'type_label'           => $r->type_label,
            'activity_description' => $r->activity_description,
            'registration_date'    => $r->registration_date?->toDateString(),
            'renewal_date'         => $r->renewal_date?->toDateString(),
            'issuing_authority'    => $r->issuing_authority,
            'status'               => $r->status,
            'status_label'         => $r->status_label,
            'expiring_soon'        => $r->isExpiringSoon(),
            'notes'                => $r->notes,
            'created_at'           => $r->created_at->toIso8601String(),
        ];
    }
}
