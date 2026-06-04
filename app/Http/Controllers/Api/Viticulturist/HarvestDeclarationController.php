<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HarvestDeclarationResource;
use App\Models\Campaign;
use App\Models\HarvestDeclaration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HarvestDeclarationController extends Controller
{
    // ─── GET /viticulturist/harvest-declarations ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,submitted,accepted,rejected',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = HarvestDeclaration::forViticulturist($user->id)
            ->active()
            ->with(['campaign'])
            ->orderByDesc('declaration_year');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => HarvestDeclarationResource::collection($items->items()),
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'declaration_year' => 'required|integer|min:2000|max:2100',
            'declaration_date' => 'required|date',
            'authority' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'total_surface_ha' => 'nullable|numeric|min:0',
            'total_kg' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->value('id');
        }

        $record = \App\Models\HarvestDeclaration::create([...$validated, 'viticulturist_id' => $user->id, 'status' => $validated['status'] ?? 'draft', 'active' => true]);

        return response()->json([
            'data' => new \App\Http\Resources\Api\HarvestDeclarationResource($record),
            'message' => __('Declaración de cosecha registrada correctamente.'),
        ], 201);
    }
}
