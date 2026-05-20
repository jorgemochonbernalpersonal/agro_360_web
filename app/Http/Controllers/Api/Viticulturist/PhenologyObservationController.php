<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PhenologyObservationResource;
use App\Models\Campaign;
use App\Models\PhenologyObservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhenologyObservationController extends Controller
{
    // ─── GET /viticulturist/phenology-observations ───────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'event'       => 'nullable|string',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = PhenologyObservation::where('viticulturist_id', $user->id)
            ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
            ->orderByDesc('obs_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $query->where('event', 'like', "%{$request->search}%");
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => PhenologyObservationResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'plot_planting_id'        => 'required|integer|exists:plot_plantings,id',
            'campaign_id'             => 'nullable|integer|exists:campaigns,id',
            'event'                   => 'required|in:budbreak,shoot_growth,flowering,fruit_set,veraison,pre_harvest,harvest',
            'obs_date'                => 'required|date',
            'source'                  => 'nullable|string|in:manual,sensor,model,auto',
            'confidence'              => 'nullable|integer|min:0|max:100',
            'degree_days_accumulated' => 'nullable|numeric|min:0',
            'bbch_code'               => 'nullable|integer|min:0|max:99',
            'notes'                   => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->value('id');
        }

        $record = PhenologyObservation::updateOrCreate(
            [
                'plot_planting_id' => $validated['plot_planting_id'],
                'campaign_id'      => $validated['campaign_id'],
                'event'            => $validated['event'],
            ],
            [...$validated, 'viticulturist_id' => $user->id, 'active' => true]
        );
        $record->load(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign']);

        return response()->json([
            'data'    => new PhenologyObservationResource($record),
            'message' => 'Observación fenológica registrada correctamente.',
        ], 201);
    }
}
