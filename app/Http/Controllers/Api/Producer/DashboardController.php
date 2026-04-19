<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\ProductLot;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use App\Models\WineTransfer;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user   = $request->user();
        $userId = $user->id;

        $data = Cache::remember("producer_dashboard:{$userId}", 60, function () use ($user, $userId) {

            // ── CAMPO ────────────────────────────────────────────────────────
            $plots     = Plot::forUser($user)->select(['id', 'area', 'active'])->get();
            $plotIds   = $plots->pluck('id');
            $totalArea = round((float) $plots->sum('area'), 2);

            $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $userId)
                ->whereYear('activity_date', now()->year)
                ->whereMonth('activity_date', now()->month)
                ->count();

            $deliveredKg = (float) HarvestDelivery::whereHas('plotPlanting', fn ($q) => $q->whereIn('plot_id', $plotIds))
                ->where('disqualified', false)
                ->whereYear('delivery_date', now()->year)
                ->sum('delivered_kg');

            // ── BODEGA ───────────────────────────────────────────────────────
            $activeCampaign = Campaign::forViticulturist($userId)->where('active', true)->first();
            $vintageYear    = $activeCampaign?->year ?? now()->year;

            $activeBatches = GrapeReceptionBatch::where('winery_id', $userId)
                ->where('vintage_year', $vintageYear)
                ->get();

            $totalKgCampaign = (float) $activeBatches->sum('total_weight_kg');
            $totalReceptions = Harvest::where('winery_id', $userId)
                ->where('status', 'active')
                ->whereHas('batch', fn ($q) => $q->where('vintage_year', $vintageYear))
                ->count();

            $todayStats = Harvest::where('winery_id', $userId)
                ->where('status', 'active')
                ->whereDate('harvest_start_date', today())
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_weight), 0) as kg')
                ->first();

            // Contenedores
            $containerStats = Container::where('user_id', $userId)
                ->where('archived', false)
                ->selectRaw('COALESCE(SUM(capacity), 0) as total, COALESCE(SUM(used_capacity + wine_volume_liters), 0) as used')
                ->first();
            $capTotal = (float) ($containerStats->total ?? 0);
            $capUsed  = (float) ($containerStats->used ?? 0);

            $maintenanceOverdue = ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $userId))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('scheduled_date', '<', today())
                ->count();

            // Alertas rendimiento
            $forecasts = WineryYieldForecast::where('winery_id', $userId)
                ->where('vintage_year', $vintageYear)
                ->where('status', 'confirmed')
                ->get()->keyBy('plot_planting_id');

            $alertsExceeded = 0;
            $alertsAtRisk   = 0;
            foreach ($activeBatches as $batch) {
                $fc = $forecasts->get($batch->plot_planting_id);
                if (! $fc || $fc->estimated_kg <= 0) continue;
                $pct = ($batch->total_weight_kg / $fc->estimated_kg) * 100;
                if ($pct > 100) $alertsExceeded++;
                elseif ($pct >= 80) $alertsAtRisk++;
            }

            return [
                'field' => [
                    'total_plots'           => $plots->count(),
                    'total_area'            => $totalArea,
                    'activities_this_month' => $activitiesThisMonth,
                    'delivered_kg'          => $deliveredKg,
                ],
                'winery' => [
                    'vintage_year'           => $vintageYear,
                    'total_kg_campaign'      => $totalKgCampaign,
                    'total_receptions'       => $totalReceptions,
                    'today_kg'               => (float) ($todayStats->kg ?? 0),
                    'today_count'            => (int) ($todayStats->count ?? 0),
                    'active_wines'           => Wine::where('user_id', $userId)->where('status', 'active')->count(),
                    'active_fermentations'   => WineFermentationControl::whereHas('wine', fn ($q) => $q->where('user_id', $userId))
                        ->where(fn ($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2))
                        ->whereDate('control_date', '>=', now()->subDays(3))
                        ->distinct('wine_id')->count('wine_id'),
                    'product_lots'           => ProductLot::where('user_id', $userId)->count(),
                    'linked_viticulturists'  => WineryViticulturist::where('winery_id', $userId)
                        ->whereHas('viticulturist', fn ($q) => $q->where('can_login', true))->count(),
                    'pending_viticulturists' => WineryViticulturist::where('winery_id', $userId)
                        ->whereHas('viticulturist', fn ($q) => $q->where('can_login', false))->count(),
                ],
                'containers' => [
                    'total_capacity'      => $capTotal,
                    'used_capacity'       => $capUsed,
                    'usage_pct'           => $capTotal > 0 ? round(min($capUsed / $capTotal * 100, 100), 1) : 0,
                    'maintenance_overdue' => $maintenanceOverdue,
                ],
                'alerts' => [
                    'exceeded' => $alertsExceeded,
                    'at_risk'  => $alertsAtRisk,
                ],
            ];
        });

        return response()->json($data)
            ->header('Cache-Control', 'private, max-age=120');
    }
}
