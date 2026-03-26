<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityResource;
use App\Http\Resources\Api\CampaignResource;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    // ─── GET /viticulturist/campaigns ─────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $campaigns = Campaign::forViticulturist($user->id)
            ->orderByDesc('year')
            ->get();

        return response()->json(['data' => CampaignResource::collection($campaigns)]);
    }

    // ─── GET /viticulturist/campaigns/active ──────────────────────────────────

    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $campaign = Campaign::forViticulturist($user->id)->active()->first()
            ?? Campaign::getOrCreateActiveForYear($user->id);

        if (! $campaign) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => new CampaignResource($campaign)]);
    }

    // ─── GET /viticulturist/campaigns/{id}/activities ─────────────────────────

    public function activities(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $campaign = Campaign::forViticulturist($user->id)->findOrFail($id);

        $activities = AgriculturalActivity::forCampaign($campaign->id)
            ->with(['plot'])
            ->orderByDesc('activity_date')
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
}
