<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityResource;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotebookController extends Controller
{
    // ─── GET /viticulturist/notebook ──────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $query = AgriculturalActivity::forViticulturist($user->id)
            ->with(['plot', 'campaign']);

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }
        if ($request->filled('plot_id')) {
            $query->forPlot($request->plot_id);
        }
        if ($request->filled('campaign_id')) {
            $query->forCampaign($request->campaign_id);
        }

        $activities = $query->orderByDesc('activity_date')
            ->paginate($request->integer('per_page', 30));

        return response()->json([
            'data' => ActivityResource::collection($activities->items()),
            'meta' => [
                'total'        => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
            ],
        ]);
    }

    // ─── POST /viticulturist/notebook ─────────────────────────────────────────
    // Registro unificado: el tipo determina la acción (tratamiento, riego, observación, cosecha, etc.)

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'activity_type'      => 'required|string|in:phytosanitary,fertilization,irrigation,cultural,observation,harvest,pruning,phenology,post_harvest',
            'plot_id'            => 'required|integer',
            'activity_date'      => 'required|date',
            'campaign_id'        => 'nullable|integer',
            'phenological_stage' => 'nullable|string|max:100',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature'        => 'nullable|numeric|between:-20,60',
            'notes'              => 'nullable|string|max:2000',
        ]);

        // Ownership check
        Plot::where('viticulturist_id', $user->id)->findOrFail($validated['plot_id']);

        // Auto-assign active campaign if not provided
        if (empty($validated['campaign_id'])) {
            $campaign = Campaign::getOrCreateActiveForYear($user->id);
            $validated['campaign_id'] = $campaign?->id;
        } else {
            Campaign::forViticulturist($user->id)->findOrFail($validated['campaign_id']);
        }

        $activity = AgriculturalActivity::create([
            ...$validated,
            'viticulturist_id' => $user->id,
        ]);

        $activity->load(['plot', 'campaign']);

        return response()->json(['data' => new ActivityResource($activity)], 201);
    }
}
