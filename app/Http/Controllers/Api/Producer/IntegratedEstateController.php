<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegratedEstateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        $campaignId = $request->input('campaign_id');
        $campaign = $campaignId
            ? Campaign::forViticulturist($userId)->findOrFail($campaignId)
            : Campaign::forViticulturist($userId)->where('active', true)->first();

        $plots = Plot::forUser($user)
            ->with([
                'plantings.grapeVariety:id,name',
                'plantings' => fn ($q) => $q->select('id', 'plot_id', 'grape_variety_id', 'area', 'planting_year', 'plant_count'),
            ])
            ->select(['id', 'name', 'area', 'active'])
            ->get();

        $plotIds = $plots->pluck('id');

        // Actividades por tipo
        $activityQuery = AgriculturalActivity::where('viticulturist_id', $userId)
            ->whereIn('plot_id', $plotIds);
        if ($campaign) {
            $activityQuery->whereYear('activity_date', $campaign->year);
        }
        $activityCounts = (clone $activityQuery)
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type');

        // Rendimiento real vs estimado por parcela
        $yieldByPlot = [];
        if ($campaign) {
            $harvests = Harvest::whereIn('container_id', function ($q) use ($userId) {
                $q->select('id')->from('containers')->where('user_id', $userId);
            })
                ->where('status', 'active')
                ->whereYear('harvest_start_date', $campaign->year)
                ->with('plotPlanting:id,plot_id')
                ->get();

            foreach ($harvests->groupBy(fn ($h) => $h->plotPlanting?->plot_id) as $plotId => $group) {
                $plot = $plots->firstWhere('id', $plotId);
                if (! $plot) {
                    continue;
                }
                $yieldByPlot[] = [
                    'plot_id' => $plotId,
                    'plot_name' => $plot->name,
                    'total_kg' => round((float) $group->sum('total_weight'), 2),
                    'entries' => $group->count(),
                ];
            }
        }

        // Variedades
        $varietyCounts = [];
        foreach ($plots as $plot) {
            foreach ($plot->plantings as $p) {
                $name = $p->grapeVariety?->name ?? 'Desconocida';
                $varietyCounts[$name] = ($varietyCounts[$name] ?? 0) + 1;
            }
        }

        return response()->json([
            'campaign' => $campaign ? ['id' => $campaign->id, 'name' => $campaign->name, 'year' => $campaign->year] : null,
            'total_plots' => $plots->count(),
            'total_area' => round((float) $plots->sum('area'), 2),
            'total_plantings' => $plots->sum(fn ($p) => $p->plantings->count()),
            'activity_counts' => $activityCounts,
            'variety_counts' => $varietyCounts,
            'yield_by_plot' => $yieldByPlot,
            'plots' => $plots->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'area' => (float) $p->area,
                'active' => $p->active,
                'plantings' => $p->plantings->map(fn ($pl) => [
                    'id' => $pl->id,
                    'variety' => $pl->grapeVariety?->name,
                    'area' => (float) $pl->area,
                    'planting_year' => $pl->planting_year,
                    'plant_count' => $pl->plant_count,
                ]),
            ]),
        ])->header('Cache-Control', 'private, max-age=300');
    }
}
