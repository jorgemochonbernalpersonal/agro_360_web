<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignComparisonController extends BaseApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'campaign_a' => 'required|integer',
            'campaign_b' => 'required|integer',
            'plot_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $userId = $user->id;

        $campaignA = Campaign::forViticulturist($userId)->findOrFail($request->input('campaign_a'));
        $campaignB = Campaign::forViticulturist($userId)->findOrFail($request->input('campaign_b'));
        $plotId = $request->input('plot_id');

        $plotIds = $plotId
            ? collect([$plotId])
            : Plot::forUser($user)->pluck('id');

        return response()->json([
            'campaign_a' => $this->campaignData($userId, $campaignA, $plotIds),
            'campaign_b' => $this->campaignData($userId, $campaignB, $plotIds),
        ])->header('Cache-Control', 'private, max-age=600');
    }

    private function campaignData(int $userId, Campaign $campaign, $plotIds): array
    {
        $year = $campaign->year;

        $harvestStats = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $userId)->whereIn('plot_id', $plotIds))
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('
                COUNT(*) as entries,
                COALESCE(SUM(total_weight), 0) as total_kg,
                AVG(baume_degree) as avg_baume,
                AVG(brix_degree) as avg_brix,
                AVG(ph_level) as avg_ph,
                AVG(price_per_kg) as avg_price
            ')
            ->first();

        $activityCounts = AgriculturalActivity::where('viticulturist_id', $userId)
            ->whereIn('plot_id', $plotIds)
            ->whereYear('activity_date', $year)
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type');

        $byPlot = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $userId)->whereIn('plot_id', $plotIds))
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->with('plotPlanting.plot:id,name')
            ->get()
            ->groupBy(fn ($h) => $h->plotPlanting?->plot_id)
            ->map(function ($group, $plotId) {
                $first = $group->first();

                return [
                    'plot_id' => $plotId,
                    'plot_name' => $first->plotPlanting?->plot->name ?? '—',
                    'total_kg' => round((float) $group->sum('total_weight'), 2),
                    'avg_baume' => $group->avg('baume_degree') !== null ? round($group->avg('baume_degree'), 2) : null,
                    'entries' => $group->count(),
                ];
            })
            ->values();

        return [
            'campaign' => ['id' => $campaign->id, 'name' => $campaign->name, 'year' => $year],
            'harvest' => [
                'entries' => (int) ($harvestStats->entries ?? 0),
                'total_kg' => (float) ($harvestStats->total_kg ?? 0),
                'avg_baume' => $harvestStats->avg_baume ? round((float) $harvestStats->avg_baume, 2) : null,
                'avg_brix' => $harvestStats->avg_brix ? round((float) $harvestStats->avg_brix, 2) : null,
                'avg_ph' => $harvestStats->avg_ph ? round((float) $harvestStats->avg_ph, 2) : null,
                'avg_price' => $harvestStats->avg_price ? round((float) $harvestStats->avg_price, 4) : null,
            ],
            'activity_counts' => $activityCounts,
            'by_plot' => $byPlot,
        ];
    }
}
