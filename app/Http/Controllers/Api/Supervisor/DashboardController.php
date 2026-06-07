<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSupervisor(), 403, 'Acceso denegado.');

        $userId = $user->id;

        // ── Supervised entities ───────────────────────────────────────────────
        $wineryIds = SupervisorWinery::where('supervisor_id', $userId)->pluck('winery_id');
        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $userId)->pluck('viticulturist_id');

        // ── Harvest stats current year ────────────────────────────────────────
        $currentYear = now()->year;
        $harvestStats = Harvest::whereIn('winery_id', $wineryIds)
            ->whereYear('harvest_start_date', $currentYear)
            ->where('status', 'active')
            ->selectRaw('COUNT(*) as receptions, SUM(total_weight) as total_kg, COUNT(DISTINCT winery_id) as wineries')
            ->first();

        // ── Active campaigns under supervision ────────────────────────────────
        $activeCampaigns = Campaign::whereIn('viticulturist_id', $viticulturistIds)
            ->active()
            ->count();

        return response()->json([
            'campaign_year' => $currentYear,
            'supervised' => [
                'wineries' => $wineryIds->count(),
                'viticulturists' => $viticulturistIds->count(),
            ],
            'harvest_stats' => [
                'receptions' => (int) ($harvestStats->receptions ?? 0),
                'total_kg' => (float) ($harvestStats->total_kg ?? 0),
            ],
            'active_campaigns' => $activeCampaigns,
        ]);
    }
}
