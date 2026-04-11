<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403, 'Acceso denegado.');

        $userId = $user->id;

        // ── Plots ─────────────────────────────────────────────────────────────
        $plotStats = Plot::where('viticulturist_id', $userId)
            ->where('active', true)
            ->selectRaw('COUNT(*) as total, SUM(area) as total_area, SUM(CASE WHEN is_organic = 1 THEN area ELSE 0 END) as organic_area')
            ->first();
        $totalArea   = (float) ($plotStats->total_area ?? 0);
        $organicArea = (float) ($plotStats->organic_area ?? 0);

        // ── Active campaign ────────────────────────────────────────────────────
        $activeCampaign = Campaign::forViticulturist($userId)->active()->first();

        // ── Recent activities ─────────────────────────────────────────────────
        $recentActivities = AgriculturalActivity::where('viticulturist_id', $userId)
            ->with(['plot'])
            ->orderByDesc('activity_date')
            ->take(5)
            ->get();

        // ── Harvests this year ────────────────────────────────────────────────
        $currentYear = now()->year;
        $harvestStats = Harvest::whereHas(
            'batch', fn ($q) => $q->where('viticulturist_id', $userId)
        )
        ->whereYear('harvest_start_date', $currentYear)
        ->where('status', 'active')
        ->selectRaw('COUNT(*) as count, SUM(total_weight) as total_kg')
        ->first();

        // ── Pending activities this week ──────────────────────────────────────
        $pendingActivities = AgriculturalActivity::where('viticulturist_id', $userId)
            ->whereBetween('activity_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return response()->json([
            'campaign_year' => $currentYear,
            'plots' => [
                'total'        => (int) ($plotStats->total ?? 0),
                'total_area'   => round($totalArea, 2),
                'organic_area' => round($organicArea, 2),
            ],
            'active_campaign' => $activeCampaign ? [
                'id'   => $activeCampaign->id,
                'name' => $activeCampaign->name,
                'year' => $activeCampaign->year,
            ] : null,
            'harvests' => [
                'count'    => (int) ($harvestStats->count ?? 0),
                'total_kg' => (float) ($harvestStats->total_kg ?? 0),
            ],
            'pending_activities_this_week' => $pendingActivities,
            'recent_activities' => $recentActivities->map(fn ($a) => [
                'id'            => $a->id,
                'type'          => $a->activity_type,
                'date'          => $a->activity_date?->toDateString(),
                'plot_name'     => $a->plot?->name,
                'notes'         => $a->notes,
            ]),
        ]);
    }
}
