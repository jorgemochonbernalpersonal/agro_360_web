<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FertilizationPlanResource;
use App\Models\FertilizationPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FertilizationPlanController extends Controller
{
    // ─── GET /viticulturist/fertilization-plans ───────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|min:1',
            'status'      => 'nullable|string|in:draft,active,archived',
            'per_page'    => 'nullable|integer|min:1|max:100',
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

        return response()->json([
            'data' => FertilizationPlanResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
