<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CampaignResource;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Forma plana que espera el cliente móvil (CampaignActivityDto): type/date/plot_name.
        // No se usa ActivityResource porque ese recurso (compartido con otros endpoints)
        // emite activity_type/activity_date/plot:{} y el móvil no los parsea.
        return response()->json([
            'data' => collect($activities->items())->map(fn ($a) => [
                'id'        => $a->id,
                'type'      => $a->activity_type,
                'date'      => $a->activity_date?->toDateString(),
                'plot_name' => $a->plot?->name,
                'notes'     => $a->notes,
            ]),
            'meta' => [
                'total'        => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'has_more'     => $activities->hasMorePages(),
            ],
        ]);
    }

    // ─── GET /viticulturist/campaigns/compare ────────────────────────────────

    public function compare(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $campaigns = Campaign::forViticulturist($user->id)
            ->orderByDesc('year')
            ->get();

        $data = $campaigns->map(function (Campaign $campaign) {
            $activities = AgriculturalActivity::forCampaign($campaign->id);

            return [
                'id'               => $campaign->id,
                'name'             => $campaign->name,
                'year'             => $campaign->year,
                'active'           => (bool) $campaign->active,
                'locked'           => $campaign->locked_at !== null,
                'start_date'       => $campaign->start_date?->toDateString(),
                'end_date'         => $campaign->end_date?->toDateString(),
                'total_activities' => (clone $activities)->count(),
                'treatments'       => (clone $activities)->where('activity_type', 'phytosanitary')->count(),
                'irrigations'      => (clone $activities)->where('activity_type', 'irrigation')->count(),
                'harvests'         => (clone $activities)->where('activity_type', 'harvest')->count(),
                'observations'     => (clone $activities)->where('activity_type', 'observation')->count(),
                'plots_used'       => (clone $activities)->distinct('plot_id')->count('plot_id'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    // ─── POST /viticulturist/campaigns/{id}/lock ─────────────────────────────

    public function lock(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $campaign = Campaign::forViticulturist($user->id)->findOrFail($id);

        if ($campaign->locked_at) {
            return response()->json(['message' => __('La campaña ya está cerrada.')], 422);
        }

        $campaign->update([
            'locked_at' => now(),
            'active'    => false,
        ]);

        return response()->json(['data' => new CampaignResource($campaign->fresh())]);
    }
}
