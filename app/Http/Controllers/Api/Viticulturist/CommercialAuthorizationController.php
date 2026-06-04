<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CommercialAuthorizationResource;
use App\Models\CommercialAuthorization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommercialAuthorizationController extends Controller
{
    // ─── GET /viticulturist/commercial-authorizations ────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'authorization_type' => 'nullable|string',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = CommercialAuthorization::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->orderByDesc('issue_date');

        if ($request->filled('authorization_type')) {
            $query->where('authorization_type', $request->authorization_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('authorization_code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('issuing_body', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => CommercialAuthorizationResource::collection($items->items()),
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/commercial-authorizations ──────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'authorization_type' => 'required|string|in:do_registration,organic_certification,planting_right,replanting_right,integrated_production,other',
            'authorization_code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'issuing_body' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $record = CommercialAuthorization::create([...$validated, 'viticulturist_id' => $user->id]);

        return response()->json([
            'data' => new CommercialAuthorizationResource($record),
            'message' => __('Autorización registrada correctamente.'),
        ], 201);
    }
}
