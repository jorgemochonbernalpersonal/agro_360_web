<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlotResource;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    // ─── GET /viticulturist/plots ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $plots = Plot::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->with(['province', 'municipality', 'plantings.grapeVariety'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => PlotResource::collection($plots),
            'meta' => [
                'total'        => $plots->count(),
                'total_area'   => round($plots->sum(fn ($p) => (float) $p->area), 2),
                'organic_area' => round($plots->where('is_organic', true)->sum(fn ($p) => (float) $p->area), 2),
            ],
        ]);
    }

    // ─── GET /viticulturist/plots/{id} ────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $plot = Plot::where('viticulturist_id', $user->id)
            ->with(['province', 'municipality', 'plantings.grapeVariety'])
            ->findOrFail($id);

        return response()->json(['data' => new PlotResource($plot)]);
    }
}
