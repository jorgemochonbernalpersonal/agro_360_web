<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\CertificationResource;
use App\Models\Certification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificationController extends BaseApiController
{
    // ─── GET /viticulturist/certifications ───────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'certification_type' => 'nullable|string',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Certification::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->orderByDesc('issue_date');

        if ($request->filled('certification_type')) {
            $query->where('certification_type', $request->certification_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('certifying_body', 'like', "%{$term}%")
                    ->orWhere('certificate_number', 'like', "%{$term}%")
                    ->orWhere('scope', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, CertificationResource::collection($items->items()));
    }

    // ─── POST /viticulturist/certifications ─────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'certification_type' => 'required|string|in:ecologico,produccion_integrada,globalgap,rainforest,denominacion_origen,indicacion_geografica,otro',
            'certifying_body' => 'required|string|max:255',
            'certificate_number' => 'nullable|string|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'scope' => 'nullable|string|max:500',
            'audit_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $record = Certification::create([...$validated, 'viticulturist_id' => $user->id]);

        return response()->json([
            'data' => new CertificationResource($record),
            'message' => __('Certificación registrada correctamente.'),
        ], 201);
    }
}
