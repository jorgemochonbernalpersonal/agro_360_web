<?php

namespace App\Livewire\Producer;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\ProductLot;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user   = Auth::user();
        $userId = $user->id;

        // ── CAMPO (viticulturist side) ───────────────────────────────────────
        $plots      = Plot::forUser($user)->select(['id', 'area', 'active'])->get();
        $totalPlots = $plots->count();
        $totalArea  = round((float) $plots->sum('area'), 2);

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
        $userPlotIds  = $plots->pluck('id');
        $deliveredKg  = (float) HarvestDelivery::whereHas('plotPlanting', fn($q) => $q->whereIn('plot_id', $userPlotIds))
            ->where('disqualified', false)
            ->whereYear('delivery_date', now()->year)
            ->sum('delivered_kg');

        // ── BODEGA (winery side) ─────────────────────────────────────────────
        $activeCampaign = Campaign::forViticulturist($userId)->where('active', true)->first();
        $vintageYear    = $activeCampaign?->year ?? now()->year;

        $activeBatches = GrapeReceptionBatch::where('winery_id', $userId)
            ->where('vintage_year', $vintageYear)
            ->get();

        $totalKgCampaign = (float) $activeBatches->sum('total_weight_kg');
        $totalReceptions = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->whereHas('batch', fn($q) => $q->where('vintage_year', $vintageYear))
            ->count();

        $todayStats  = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->whereDate('harvest_start_date', today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_weight), 0) as kg')
            ->first();
        $todayKg     = (float) ($todayStats->kg ?? 0);
        $todayCount  = (int) ($todayStats->count ?? 0);

        $linkedViticulturists = WineryViticulturist::where('winery_id', $userId)->count();
        $wineLotCount         = ProductLot::where('user_id', $userId)->count();

        // Vinos activos y fermentaciones
        $activeWines = Wine::where('user_id', $userId)->where('status', 'active')->count();
        $activeFermentations = WineFermentationControl::whereHas('wine', fn($q) => $q->where('user_id', $userId))
            ->where(fn($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2))
            ->whereDate('control_date', '>=', now()->subDays(3))
            ->distinct('wine_id')
            ->count('wine_id');

        $recentFermentations = WineFermentationControl::with(['wine', 'container'])
            ->whereHas('wine', fn($q) => $q->where('user_id', $userId))
            ->orderByDesc('control_date')
            ->take(4)
            ->get();

        $recentReceptions = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->with(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'batch.viticulturist'])
            ->orderByDesc('harvest_start_date')
            ->take(4)
            ->get();

        return view('livewire.producer.dashboard', [
            // Campo
            'totalPlots'          => $totalPlots,
            'totalArea'           => $totalArea,
            'activitiesThisMonth' => $activitiesThisMonth,
            'deliveredKg'         => $deliveredKg,
            'recentActivities'    => $recentActivities,
            // Bodega
            'vintageYear'         => $vintageYear,
            'totalKgCampaign'     => $totalKgCampaign,
            'totalReceptions'     => $totalReceptions,
            'todayKg'             => $todayKg,
            'todayCount'          => $todayCount,
            'linkedViticulturists'   => $linkedViticulturists,
            'wineLotCount'           => $wineLotCount,
            'activeWines'            => $activeWines,
            'activeFermentations'    => $activeFermentations,
            'recentFermentations'    => $recentFermentations,
            'recentReceptions'       => $recentReceptions,
        ])->layout('layouts.app', [
            'title'       => 'Dashboard Productor - Agro365',
            'description' => 'Panel de control combinado campo y bodega',
        ]);
    }
}
