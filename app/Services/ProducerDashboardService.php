<?php

namespace App\Services;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\ProductLot;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Cache;

class ProducerDashboardService
{
    /**
     * All aggregate stats shared between the API controller and the Livewire dashboard.
     *
     * @return array{field: array, winery: array, containers: array, alerts: array}
     */
    public function stats(User $user): array
    {
        // El endpoint API ya cachea 60s en su capa (DashboardController), pero
        // el componente Livewire llamaba a este método sin caché alguna en
        // cada render. Cachearlo aquí cubre ambos consumidores.
        return Cache::remember("producer-dashboard-{$user->id}", now()->addMinutes(3), fn () => $this->buildStats($user));
    }

    /**
     * @return array{field: array, winery: array, containers: array, alerts: array}
     */
    private function buildStats(User $user): array
    {
        $userId = $user->id;
        $now = now();

        // ── Campo ────────────────────────────────────────────────────────────
        $plots = Plot::forUser($user)->select(['id', 'area', 'active'])->get();
        $plotIds = $plots->pluck('id');

        $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $userId)
            ->whereBetween('activity_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $deliveredKg = (float) HarvestDelivery::whereHas('plotPlanting', fn ($q) => $q->whereIn('plot_id', $plotIds))
            ->where('disqualified', false)
            ->whereBetween('delivery_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
            ->sum('delivered_kg');

        // ── Bodega ───────────────────────────────────────────────────────────
        $activeCampaign = Campaign::forViticulturist($userId)->where('active', true)->first();
        $vintageYear = $activeCampaign->year ?? $now->year;

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
            ->whereBetween('harvest_start_date', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_weight), 0) as kg')
            ->first();

        // ── Contenedores ─────────────────────────────────────────────────────
        $containerStats = Container::where('user_id', $userId)
            ->where('archived', false)
            ->selectRaw('COALESCE(SUM(capacity), 0) as total, COALESCE(SUM(used_capacity + wine_volume_liters), 0) as used')
            ->first();
        $capTotal = (float) ($containerStats->total ?? 0);
        $capUsed = (float) ($containerStats->used ?? 0);

        $maintenanceOverdue = ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $userId))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('scheduled_date', '<', $now->copy()->startOfDay())
            ->count();

        // ── Alertas de rendimiento ───────────────────────────────────────────
        $forecasts = WineryYieldForecast::where('winery_id', $userId)
            ->where('vintage_year', $vintageYear)
            ->where('status', 'confirmed')
            ->get()
            ->keyBy('plot_planting_id');

        $alertsExceeded = 0;
        $alertsAtRisk = 0;
        foreach ($activeBatches as $batch) {
            $fc = $forecasts->get($batch->plot_planting_id);
            if (! $fc || $fc->estimated_kg <= 0) {
                continue;
            }
            $pct = ($batch->total_weight_kg / $fc->estimated_kg) * 100;
            if ($pct > 100) {
                $alertsExceeded++;
            } elseif ($pct >= 80) {
                $alertsAtRisk++;
            }
        }

        return [
            'field' => [
                'plots' => $plots,
                'total_plots' => $plots->count(),
                'total_area' => round((float) $plots->sum('area'), 2),
                'activities_this_month' => $activitiesThisMonth,
                'delivered_kg' => $deliveredKg,
            ],
            'winery' => [
                'vintage_year' => $vintageYear,
                'total_kg_campaign' => $totalKgCampaign,
                'total_receptions' => $totalReceptions,
                'today_kg' => (float) ($todayStats->kg ?? 0),
                'today_count' => (int) ($todayStats->count ?? 0),
                'active_wines' => Wine::where('user_id', $userId)->where('status', 'active')->count(),
                'active_fermentations' => WineFermentationControl::whereHas('wine', fn ($q) => $q->where('user_id', $userId))
                    ->where(fn ($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2))
                    ->where('control_date', '>=', $now->copy()->subDays(3))
                    ->distinct('wine_id')->count('wine_id'),
                'product_lots' => ProductLot::where('user_id', $userId)->count(),
                'linked_viticulturists' => WineryViticulturist::where('winery_id', $userId)
                    ->whereHas('viticulturist', fn ($q) => $q->where('can_login', true))->count(),
                'pending_viticulturists' => WineryViticulturist::where('winery_id', $userId)
                    ->whereHas('viticulturist', fn ($q) => $q->where('can_login', false))->count(),
            ],
            'containers' => [
                'total_capacity' => $capTotal,
                'used_capacity' => $capUsed,
                'usage_pct' => $capTotal > 0 ? round(min($capUsed / $capTotal * 100, 100), 1) : 0,
                'maintenance_overdue' => $maintenanceOverdue,
            ],
            'alerts' => [
                'exceeded' => $alertsExceeded,
                'at_risk' => $alertsAtRisk,
            ],
        ];
    }
}
