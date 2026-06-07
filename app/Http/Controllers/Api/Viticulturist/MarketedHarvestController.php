<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\MarketedHarvestResource;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\MarketedHarvest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketedHarvestController extends BaseApiController
{
    // ─── GET /viticulturist/harvests/picker ─────────────────────────────────

    public function picker(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $harvests = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->with(['activity.plot', 'plotPlanting.grapeVariety'])
            ->orderByDesc('harvest_start_date')
            ->get();

        return $this->success($harvests->map(fn ($h) => [
            'id' => $h->id,
            'harvest_start_date' => $h->harvest_start_date?->toDateString(),
            'plot_name' => $h->activity?->plot?->name,
            'variety' => $h->plotPlanting?->grapeVariety?->name,
            'total_weight' => $h->total_weight ? (float) $h->total_weight : null,
        ]));
    }

    // ─── GET /viticulturist/marketed-harvests ────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'destination_type' => 'nullable|string',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = MarketedHarvest::where('viticulturist_id', $user->id)
            ->with(['campaign'])
            ->orderByDesc('delivery_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('destination_type')) {
            $query->where('destination_type', $request->destination_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('buyer_name', 'like', "%{$term}%")
                    ->orWhere('transport_document', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, MarketedHarvestResource::collection($items->items()));
    }

    // ─── POST /viticulturist/marketed-harvests ──────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'harvest_id' => 'nullable|integer|exists:harvests,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'delivery_date' => 'required|date',
            'quantity_kg' => 'required|numeric|min:0.001',
            'destination_type' => 'required|string|in:own_winery,cooperative,third_party,other',
            'buyer_name' => 'nullable|string|max:255',
            'buyer_rega_code' => 'nullable|string|max:30',
            'transport_document' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:15',
            'price_per_kg' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->value('id');
        }

        $record = MarketedHarvest::create([...$validated, 'viticulturist_id' => $user->id]);
        $record->load(['campaign']);

        return response()->json([
            'data' => new MarketedHarvestResource($record),
            'message' => __('Cosecha comercializada registrada correctamente.'),
        ], 201);
    }
}
