<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\FertilizationPlanResource;
use App\Models\FertilizationPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FertilizationPlanController extends BaseApiController
{
    // ─── GET /viticulturist/fertilization-plans ───────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:draft,active,archived',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = FertilizationPlan::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->with('campaign')
            ->orderByDesc('plan_year');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, FertilizationPlanResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'plan_year' => 'required|integer|min:2000|max:2100',
            'nitrate_zone' => 'nullable|boolean',
            'total_surface_ha' => 'nullable|numeric|min:0',
            'total_n_kg_ha' => 'nullable|numeric|min:0',
            'total_p_kg_ha' => 'nullable|numeric|min:0',
            'total_k_kg_ha' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = \App\Models\Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)->value('id');
        }

        $record = \App\Models\FertilizationPlan::create([...$validated, 'viticulturist_id' => $user->id, 'status' => $validated['status'] ?? 'draft', 'active' => true]);

        return response()->json([
            'data' => new \App\Http\Resources\Api\FertilizationPlanResource($record),
            'message' => __('Plan de fertilización registrado correctamente.'),
        ], 201);
    }
}
