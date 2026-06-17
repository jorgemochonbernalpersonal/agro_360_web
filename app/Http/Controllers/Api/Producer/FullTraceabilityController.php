<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\ProductLot;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FullTraceabilityController extends BaseApiController
{
    public function __invoke(Request $request, int $campaignId): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $campaign = Campaign::forViticulturist($userId)->findOrFail($campaignId);
        $year = $campaign->year;

        // ── Step 1: Campo ────────────────────────────────────────────────────
        $fieldStats = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $userId))
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('COUNT(*) as entries, COALESCE(SUM(total_weight), 0) as total_kg')
            ->first();

        $plotIds = Plot::forUser($user)->pluck('id');
        $plotsCount = $plotIds->count();

        // ── Step 2: Recepción en bodega ──────────────────────────────────────
        $receptionStats = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('COUNT(*) as entries, COALESCE(SUM(total_weight), 0) as total_kg, COUNT(DISTINCT batch_id) as batches_count')
            ->first();

        // ── Step 3: Vinos ────────────────────────────────────────────────────
        $wines = Wine::where('user_id', $userId)
            ->where('vintage', $year)
            ->withCount(['harvests', 'transfers', 'bottlings'])
            ->get();

        $wineStats = [
            'total_wines' => $wines->count(),
            'total_volume' => round((float) $wines->sum('volume_liters'), 2),
            'by_type' => $wines->groupBy('wine_type')->map->count(),
        ];

        // ── Step 4: Productos ────────────────────────────────────────────────
        $productStats = ProductLot::where('user_id', $userId)
            ->where('vintage', $year)
            ->selectRaw('
                COUNT(*) as total_lots,
                COALESCE(SUM(quantity), 0) as total_units,
                COALESCE(SUM(sold_quantity), 0) as sold_units,
                COALESCE(SUM(sold_quantity * price_per_unit), 0) as revenue
            ')
            ->first();

        // ── Flujo por parcela ────────────────────────────────────────────────
        $flowByPlot = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $userId))
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->with(['plotPlanting.plot:id,name', 'plotPlanting.grapeVariety:id,name'])
            ->get()
            ->groupBy(fn ($h) => $h->plotPlanting?->plot_id)
            ->map(function ($group, $plotId) {
                $first = $group->first();

                return [
                    'plot_id' => $plotId,
                    'plot_name' => $first->plotPlanting?->plot->name ?? '—',
                    'variety' => $first->plotPlanting?->grapeVariety->name ?? '—',
                    'total_kg' => round((float) $group->sum('total_weight'), 2),
                    'entries' => $group->count(),
                ];
            })
            ->values();

        return response()->json([
            'campaign' => ['id' => $campaign->id, 'name' => $campaign->name, 'year' => $year],
            'field' => [
                'entries' => (int) ($fieldStats->entries ?? 0),
                'total_kg' => (float) ($fieldStats->total_kg ?? 0),
                'plots_count' => $plotsCount,
            ],
            'reception' => [
                'entries' => (int) ($receptionStats->entries ?? 0),
                'total_kg' => (float) ($receptionStats->total_kg ?? 0),
                'batches_count' => (int) ($receptionStats->batches_count ?? 0),
            ],
            'wines' => [
                'stats' => $wineStats,
                'items' => $wines->map(fn ($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'wine_type' => $w->wine_type,
                    'volume_liters' => (float) $w->volume_liters,
                    'status' => $w->status,
                    'harvests_count' => $w->harvests_count,
                    'transfers_count' => $w->transfers_count,
                    'bottlings_count' => $w->bottlings_count,
                ]),
            ],
            'products' => [
                'total_lots' => (int) ($productStats->total_lots ?? 0),
                'total_units' => (float) ($productStats->total_units ?? 0),
                'sold_units' => (float) ($productStats->sold_units ?? 0),
                'revenue' => (float) ($productStats->revenue ?? 0),
            ],
            'flow_by_plot' => $flowByPlot,
        ])->header('Cache-Control', 'private, max-age=300');
    }
}
