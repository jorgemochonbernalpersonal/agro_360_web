<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\ProductLot;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegratedCampaignController extends Controller
{
    public function __invoke(Request $request, int $campaignId): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $campaign = Campaign::forViticulturist($userId)->findOrFail($campaignId);
        $year = $campaign->year;

        $plotIds = Plot::forUser($user)->pluck('id');

        // ── VIÑEDO ───────────────────────────────────────────────────────────
        $activityCounts = AgriculturalActivity::where('viticulturist_id', $userId)
            ->whereIn('plot_id', $plotIds)
            ->whereYear('activity_date', $year)
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type');

        $fieldHarvest = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $userId))
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('
                COUNT(*) as entries,
                COALESCE(SUM(total_weight), 0) as total_kg,
                AVG(baume_degree) as avg_baume,
                AVG(brix_degree) as avg_brix,
                AVG(ph_level) as avg_ph,
                AVG(price_per_kg) as avg_price_per_kg
            ')
            ->first();

        // ── BODEGA ───────────────────────────────────────────────────────────
        $receptionStats = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('
                COUNT(*) as entries,
                COALESCE(SUM(total_weight), 0) as total_kg,
                AVG(baume_degree) as avg_baume,
                AVG(brix_degree) as avg_brix
            ')
            ->first();

        $wines = Wine::where('user_id', $userId)
            ->where('vintage', $year)
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(volume_liters), 0) as total_volume,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress
            ')
            ->first();

        $winesByType = Wine::where('user_id', $userId)
            ->where('vintage', $year)
            ->selectRaw('wine_type, COUNT(*) as count')
            ->groupBy('wine_type')
            ->pluck('count', 'wine_type');

        // Contenedores
        $containerStats = Container::where('user_id', $userId)
            ->where('archived', false)
            ->selectRaw('COALESCE(SUM(capacity), 0) as total, COALESCE(SUM(used_capacity + wine_volume_liters), 0) as used')
            ->first();
        $capTotal = (float) ($containerStats->total ?? 0);
        $capUsed = (float) ($containerStats->used ?? 0);

        // Productos
        $productStats = ProductLot::where('user_id', $userId)
            ->where('vintage', $year)
            ->selectRaw('
                COUNT(*) as total_lots,
                COALESCE(SUM(quantity), 0) as total_units,
                COALESCE(SUM(sold_quantity), 0) as sold_units,
                COALESCE(SUM(sold_quantity * price_per_unit), 0) as revenue
            ')
            ->first();

        // ── MÉTRICAS CRUZADAS ────────────────────────────────────────────────
        $fieldKg = (float) ($fieldHarvest->total_kg ?? 0);
        $receptionKg = (float) ($receptionStats->total_kg ?? 0);
        $yieldLoss = $fieldKg > 0 ? round((1 - $receptionKg / $fieldKg) * 100, 1) : 0;

        $fieldValue = $fieldKg * ((float) ($fieldHarvest->avg_price_per_kg ?? 0));
        $revenue = (float) ($productStats->revenue ?? 0);
        $valueMult = $fieldValue > 0 ? round($revenue / $fieldValue, 2) : 0;

        return response()->json([
            'campaign' => ['id' => $campaign->id, 'name' => $campaign->name, 'year' => $year],
            'field' => [
                'activity_counts' => $activityCounts,
                'harvest' => [
                    'entries' => (int) ($fieldHarvest->entries ?? 0),
                    'total_kg' => $fieldKg,
                    'avg_baume' => $fieldHarvest->avg_baume ? round((float) $fieldHarvest->avg_baume, 2) : null,
                    'avg_brix' => $fieldHarvest->avg_brix ? round((float) $fieldHarvest->avg_brix, 2) : null,
                    'avg_ph' => $fieldHarvest->avg_ph ? round((float) $fieldHarvest->avg_ph, 2) : null,
                ],
            ],
            'winery' => [
                'receptions' => [
                    'entries' => (int) ($receptionStats->entries ?? 0),
                    'total_kg' => $receptionKg,
                    'avg_baume' => $receptionStats->avg_baume ? round((float) $receptionStats->avg_baume, 2) : null,
                    'avg_brix' => $receptionStats->avg_brix ? round((float) $receptionStats->avg_brix, 2) : null,
                ],
                'wines' => [
                    'total' => (int) ($wines->total ?? 0),
                    'volume' => (float) ($wines->total_volume ?? 0),
                    'active' => (int) ($wines->active ?? 0),
                    'in_progress' => (int) ($wines->in_progress ?? 0),
                    'by_type' => $winesByType,
                ],
                'containers' => [
                    'total_capacity' => $capTotal,
                    'used_capacity' => $capUsed,
                    'usage_pct' => $capTotal > 0 ? round(min($capUsed / $capTotal * 100, 100), 1) : 0,
                ],
                'products' => [
                    'total_lots' => (int) ($productStats->total_lots ?? 0),
                    'total_units' => (float) ($productStats->total_units ?? 0),
                    'sold_units' => (float) ($productStats->sold_units ?? 0),
                    'revenue' => $revenue,
                ],
            ],
            'cross_metrics' => [
                'yield_loss_pct' => $yieldLoss,
                'value_multiplier' => $valueMult,
            ],
        ])->header('Cache-Control', 'private, max-age=300');
    }
}
