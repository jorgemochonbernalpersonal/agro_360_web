<?php

namespace App\Livewire\Producer;

use App\Models\AgriculturalActivity;
use App\Models\Harvest;
use App\Models\WineFermentationControl;
use App\Models\WineryViticulturist;
use App\Models\WineTransfer;
use App\Services\ProducerDashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $userId = $user->id;

        $stats = app(ProducerDashboardService::class)->stats($user);

        // ── Registros recientes (sólo Livewire, no la API) ───────────────────
        $recentActivities = AgriculturalActivity::where('viticulturist_id', $userId)
            ->with('plot:id,name')
            ->orderByDesc('activity_date')
            ->take(4)
            ->get();

        $recentReceptions = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->with(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'batch.viticulturist'])
            ->orderByDesc('harvest_start_date')
            ->take(4)
            ->get();

        $recentFermentations = WineFermentationControl::with(['wine', 'container'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('control_date')
            ->take(4)
            ->get();

        $recentTransfers = WineTransfer::with(['wine', 'fromContainer', 'toContainer'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('transfer_date')
            ->take(3)
            ->get();

        $recentViticulturists = WineryViticulturist::where('winery_id', $userId)
            ->with('viticulturist:id,name,email')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.producer.dashboard', [
            // Agregados del servicio
            'totalPlots'             => $stats['field']['total_plots'],
            'totalArea'              => $stats['field']['total_area'],
            'activitiesThisMonth'    => $stats['field']['activities_this_month'],
            'deliveredKg'            => $stats['field']['delivered_kg'],
            'vintageYear'            => $stats['winery']['vintage_year'],
            'totalKgCampaign'        => $stats['winery']['total_kg_campaign'],
            'totalReceptions'        => $stats['winery']['total_receptions'],
            'todayKg'                => $stats['winery']['today_kg'],
            'todayCount'             => $stats['winery']['today_count'],
            'activeWines'            => $stats['winery']['active_wines'],
            'activeFermentations'    => $stats['winery']['active_fermentations'],
            'wineLotCount'           => $stats['winery']['product_lots'],
            'linkedViticulturists'   => $stats['winery']['linked_viticulturists'],
            'pendingViticulturists'  => $stats['winery']['pending_viticulturists'],
            'containerUsage'         => [
                'total' => $stats['containers']['total_capacity'],
                'used'  => $stats['containers']['used_capacity'],
                'pct'   => $stats['containers']['usage_pct'],
            ],
            'maintenanceOverdue'     => $stats['containers']['maintenance_overdue'],
            'alertsExceeded'         => $stats['alerts']['exceeded'],
            'alertsAtRisk'           => $stats['alerts']['at_risk'],
            // Recientes (UI-only)
            'recentActivities'       => $recentActivities,
            'recentReceptions'       => $recentReceptions,
            'recentFermentations'    => $recentFermentations,
            'recentTransfers'        => $recentTransfers,
            'recentViticulturists'   => $recentViticulturists,
        ])->layout('layouts.app', [
            'title'       => __('Dashboard Productor - Agro365'),
            'description' => __('Panel de control combinado campo y bodega'),
        ]);
    }
}
