<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HarvestByproductResource;
use App\Models\Campaign;
use App\Models\HarvestByproduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HarvestByproductController extends Controller
{
    // ─── GET /viticulturist/harvest-byproducts ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'    => 'nullable|integer',
            'byproduct_type' => 'nullable|string',
            'search'         => 'nullable|string|max:100',
            'per_page'       => 'nullable|integer|min:1|max:100',
        ]);

        $query = HarvestByproduct::where('viticulturist_id', $user->id)
            ->with(['campaign'])
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('byproduct_type')) {
            $query->where('byproduct_type', $request->byproduct_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('destination_name', 'like', "%{$term}%")
                  ->orWhere('document_reference', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => HarvestByproductResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/harvest-byproducts ────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $this->normalizeCampaignId($request);

        $validated = $request->validate([
            'campaign_id'        => 'nullable|integer|exists:campaigns,id',
            'date'               => 'required|date',
            'byproduct_type'     => 'required|string|in:pomace,stem,lees,other',
            'quantity_kg'        => 'required|numeric|min:0.001',
            'destination_type'   => 'required|string|in:cooperative,winery,distillery,composting,authorized_landfill,other',
            'destination_name'   => 'required|string|max:255',
            'document_reference' => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->value('id');
        }

        $record = HarvestByproduct::create([...$validated, 'viticulturist_id' => $user->id]);
        $record->load(['campaign']);

        return response()->json([
            'data'    => new HarvestByproductResource($record),
            'message' => 'Subproducto registrado correctamente.',
        ], 201);
    }
}
