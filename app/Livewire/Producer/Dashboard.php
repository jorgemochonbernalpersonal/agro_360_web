<?php

namespace App\Livewire\Producer;

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
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use App\Models\WineTransfer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $userId = $user->id;

        // ── CAMPO (viticulturist side) ───────────────────────────────────────
        $plots = Plot::forUser($user)->select(['id', 'area', 'active'])->get();
        $totalPlots = $plots->count();
        $totalArea = round((float) $plots->sum('area'), 2);

        $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $userId)
            ->whereYear('activity_date', now()->year)
            ->whereMonth('activity_date', now()->month)
            ->count();

        $recentActivities = AgriculturalActivity::where('viticulturist_id', $userId)
            ->with('plot:id,name')
            ->orderByDesc('activity_date')
            ->take(4)
            ->get();

        // Kg entregados (vendimia manual) este año
        $userPlotIds = $plots->pluck('id');
        $deliveredKg = (float) HarvestDelivery::whereHas('plotPlanting', fn ($q) => $q->whereIn('plot_id', $userPlotIds))
            ->where('disqualified', false)
            ->whereYear('delivery_date', now()->year)
            ->sum('delivered_kg');

        // ── BODEGA (winery side) ─────────────────────────────────────────────
        $activeCampaign = Campaign::forViticulturist($userId)->where('active', true)->first();
        $vintageYear = $activeCampaign?->year ?? now()->year;

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
        $todayKg = (float) ($todayStats->kg ?? 0);
        $todayCount = (int) ($todayStats->count ?? 0);

        $linkedViticulturists = WineryViticulturist::where('winery_id', $userId)
            ->whereHas('viticulturist', fn ($q) => $q->where('can_login', true))
            ->count();
        $pendingViticulturists = WineryViticulturist::where('winery_id', $userId)
            ->whereHas('viticulturist', fn ($q) => $q->where('can_login', false))
            ->count();
        $wineLotCount = ProductLot::where('user_id', $userId)->count();

        // Vinos activos y fermentaciones
        $activeWines = Wine::where('user_id', $userId)->where('status', 'active')->count();
        $activeFermentations = WineFermentationControl::whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->where(fn ($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2))
            ->whereDate('control_date', '>=', now()->subDays(3))
            ->distinct('wine_id')
            ->count('wine_id');

        $recentFermentations = WineFermentationControl::with(['wine', 'container'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('control_date')
            ->take(4)
            ->get();

        $recentReceptions = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->with(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'batch.viticulturist'])
            ->orderByDesc('harvest_start_date')
            ->take(4)
            ->get();

        // ── Contenedores ─────────────────────────────────────────────────
        $containerBase = Container::where('user_id', $userId)->where('archived', false);
        $containerUsage = [
            'total' => (float) (clone $containerBase)->sum('capacity'),
            'used' => (float) (clone $containerBase)->selectRaw('SUM(used_capacity + wine_volume_liters)')->value('SUM(used_capacity + wine_volume_liters)'),
        ];
        $containerUsage['pct'] = $containerUsage['total'] > 0
            ? min(($containerUsage['used'] / $containerUsage['total']) * 100, 100)
            : 0;

        $maintenanceOverdue = ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $userId))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('scheduled_date', '<', today())
            ->count();

        // ── Alertas de rendimiento ───────────────────────────────────────
        $forecasts = WineryYieldForecast::where('winery_id', $userId)
            ->where('vintage_year', $vintageYear)
            ->where('status', 'confirmed')
            ->get()
            ->keyBy('plot_planting_id');

        $alertsExceeded = 0;
        $alertsAtRisk = 0;
        foreach ($activeBatches as $batch) {
            $forecast = $forecasts->get($batch->plot_planting_id);
            if (! $forecast) {
                continue;
            }
            $pct = $forecast->estimated_kg > 0
                ? ($batch->total_weight_kg / $forecast->estimated_kg) * 100
                : 0;
            if ($pct > 100) {
                $alertsExceeded++;
            } elseif ($pct >= 80) {
                $alertsAtRisk++;
            }
        }

        // ── Trasvases recientes ──────────────────────────────────────────
        $recentTransfers = WineTransfer::with(['wine', 'fromContainer', 'toContainer'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('transfer_date')
            ->take(3)
            ->get();

        // ── Viticultores vinculados ──────────────────────────────────────
        $recentViticulturists = WineryViticulturist::where('winery_id', $userId)
            ->with('viticulturist:id,name,email')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.producer.dashboard', [
            // Campo
            'totalPlots' => $totalPlots,
            'totalArea' => $totalArea,
            'activitiesThisMonth' => $activitiesThisMonth,
            'deliveredKg' => $deliveredKg,
            'recentActivities' => $recentActivities,
            // Bodega
            'vintageYear' => $vintageYear,
            'totalKgCampaign' => $totalKgCampaign,
            'totalReceptions' => $totalReceptions,
            'todayKg' => $todayKg,
            'todayCount' => $todayCount,
            'linkedViticulturists' => $linkedViticulturists,
            'pendingViticulturists' => $pendingViticulturists,
            'wineLotCount' => $wineLotCount,
            'activeWines' => $activeWines,
            'activeFermentations' => $activeFermentations,
            'recentFermentations' => $recentFermentations,
            'recentReceptions' => $recentReceptions,
            'containerUsage' => $containerUsage,
            'maintenanceOverdue' => $maintenanceOverdue,
            'alertsExceeded' => $alertsExceeded,
            'alertsAtRisk' => $alertsAtRisk,
            'recentTransfers' => $recentTransfers,
            'recentViticulturists' => $recentViticulturists,
        ])->layout('layouts.app', [
            'title' => __('Dashboard Productor - Agro365'),
            'description' => __('Panel de control combinado campo y bodega'),
        ]);
    }
}
