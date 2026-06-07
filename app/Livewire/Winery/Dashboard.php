<?php

namespace App\Livewire\Winery;

use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\ProductLot;
use App\Models\Wine;
use App\Models\WineCost;
use App\Models\WineFermentationControl;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use App\Models\WineTransfer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    // ── Campaña ───────────────────────────────────────────────────────────────

    #[Computed]
    public function activeCampaign()
    {
        return Campaign::forViticulturist(Auth::id())->where('active', true)->first();
    }

    #[Computed]
    public function vintageYear(): int
    {
        return $this->activeCampaign?->year ?? now()->year;
    }

    // ── Viticultores ──────────────────────────────────────────────────────────

    #[Computed]
    public function viticulturistCount(): int
    {
        $count = WineryViticulturist::where('winery_id', Auth::id())
            ->whereHas('viticulturist', fn ($q) => $q->where('can_login', true))
            ->count();

        // El producer cuenta como su propio viticultor vinculado
        return Auth::user()->isProducer() ? $count + 1 : $count;
    }

    #[Computed]
    public function pendingViticulturistCount(): int
    {
        return WineryViticulturist::where('winery_id', Auth::id())
            ->whereHas('viticulturist', fn ($q) => $q->where('can_login', false))
            ->count();
    }

    // ── Batches de la campaña ─────────────────────────────────────────────────

    #[Computed]
    public function activeBatches()
    {
        return GrapeReceptionBatch::where('winery_id', Auth::id())
            ->where('vintage_year', $this->vintageYear)
            ->with(['viticulturist:id,name', 'plotPlanting.grapeVariety'])
            ->get();
    }

    #[Computed]
    public function totalKgCampaign(): float
    {
        return (float) $this->activeBatches->sum('total_weight_kg');
    }

    #[Computed]
    public function openBatchCount(): int
    {
        return $this->activeBatches->where('status', 'open')->count();
    }

    #[Computed]
    public function closedBatchCount(): int
    {
        return $this->activeBatches->where('status', 'closed')->count();
    }

    #[Computed]
    public function totalReceptions(): int
    {
        return Harvest::where('winery_id', Auth::id())
            ->where('status', 'active')
            ->whereHas('batch', fn ($q) => $q->where('vintage_year', $this->vintageYear))
            ->count();
    }

    // ── Stats de hoy ──────────────────────────────────────────────────────────

    #[Computed]
    public function todayStats(): object
    {
        return Harvest::where('winery_id', Auth::id())
            ->where('status', 'active')
            ->whereDate('harvest_start_date', today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_weight), 0) as kg')
            ->first();
    }

    #[Computed]
    public function todayKg(): float
    {
        return (float) $this->todayStats->kg;
    }

    #[Computed]
    public function todayCount(): int
    {
        return (int) $this->todayStats->count;
    }

    // ── Alertas de rendimiento ────────────────────────────────────────────────

    #[Computed]
    public function yieldAlerts(): array
    {
        $forecasts = WineryYieldForecast::where('winery_id', Auth::id())
            ->where('vintage_year', $this->vintageYear)
            ->where('status', 'confirmed')
            ->get()
            ->keyBy('plot_planting_id');

        $exceeded = 0;
        $atRisk = 0;

        foreach ($this->activeBatches as $batch) {
            $forecast = $forecasts->get($batch->plot_planting_id);
            if (! $forecast || $forecast->estimated_kg <= 0) {
                continue;
            }
            $pct = ($batch->total_weight_kg / $forecast->estimated_kg) * 100;
            if ($pct > 100) {
                $exceeded++;
            } elseif ($pct >= 80) {
                $atRisk++;
            }
        }

        return ['exceeded' => $exceeded, 'at_risk' => $atRisk];
    }

    #[Computed]
    public function alertsExceeded(): int
    {
        return $this->yieldAlerts['exceeded'];
    }

    #[Computed]
    public function alertsAtRisk(): int
    {
        return $this->yieldAlerts['at_risk'];
    }

    // ── Bodega: vinos, lotes, fermentaciones ──────────────────────────────────

    #[Computed]
    public function wineLotCount(): int
    {
        return ProductLot::where('user_id', Auth::id())->count();
    }

    #[Computed]
    public function activeWines(): int
    {
        return Wine::where('user_id', Auth::id())->where('status', 'in_progress')->count();
    }

    #[Computed]
    public function activeFermentations(): int
    {
        return WineFermentationControl::whereHas('wine', fn ($q) => $q->where('user_id', Auth::id()))
            ->where(fn ($q) => $q->where('density', '>', 1.000)->orWhere('brix_degree', '>', 2))
            ->whereDate('control_date', '>=', now()->subDays(3))
            ->distinct('wine_id')
            ->count('wine_id');
    }

    // ── Contenedores ──────────────────────────────────────────────────────────

    #[Computed]
    public function containerUsage(): array
    {
        $stats = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->selectRaw('COALESCE(SUM(capacity), 0) as total, COALESCE(SUM(used_capacity + wine_volume_liters), 0) as used')
            ->first();

        $total = (float) ($stats->total ?? 0);
        $used = (float) ($stats->used ?? 0);

        return [
            'total' => $total,
            'used'  => $used,
            'pct'   => $total > 0 ? min(($used / $total) * 100, 100) : 0,
        ];
    }

    #[Computed]
    public function maintenanceOverdue(): int
    {
        return ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', Auth::id()))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('scheduled_date', '<', today())
            ->count();
    }

    // ── Costes ────────────────────────────────────────────────────────────────

    #[Computed]
    public function totalCostsYear(): float
    {
        return (float) WineCost::where('user_id', Auth::id())
            ->whereYear('cost_date', $this->vintageYear)
            ->sum('amount');
    }

    // ── Recientes ────────────────────────────────────────────────────────────

    #[Computed]
    public function recentTransfers()
    {
        return WineTransfer::with(['wine', 'fromContainer', 'toContainer'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', Auth::id()))
            ->orderByDesc('transfer_date')
            ->take(3)
            ->get();
    }

    #[Computed]
    public function recentFermentations()
    {
        return WineFermentationControl::with(['wine', 'container'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', Auth::id()))
            ->orderByDesc('control_date')
            ->take(3)
            ->get();
    }

    #[Computed]
    public function recentReceptions()
    {
        return Harvest::where('winery_id', Auth::id())
            ->where('status', 'active')
            ->with(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'batch.viticulturist'])
            ->orderByDesc('harvest_start_date')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentViticulturists()
    {
        return WineryViticulturist::where('winery_id', Auth::id())
            ->with('viticulturist:id,name,email')
            ->latest()
            ->take(5)
            ->get();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.winery.dashboard', [
            'activeCampaign'            => $this->activeCampaign,
            'vintageYear'               => $this->vintageYear,
            'viticulturistCount'        => $this->viticulturistCount,
            'pendingViticulturistCount' => $this->pendingViticulturistCount,
            'activeBatches'             => $this->activeBatches,
            'totalKgCampaign'           => $this->totalKgCampaign,
            'openBatchCount'            => $this->openBatchCount,
            'closedBatchCount'          => $this->closedBatchCount,
            'totalReceptions'           => $this->totalReceptions,
            'todayKg'                   => $this->todayKg,
            'todayCount'                => $this->todayCount,
            'alertsExceeded'            => $this->alertsExceeded,
            'alertsAtRisk'              => $this->alertsAtRisk,
            'wineLotCount'              => $this->wineLotCount,
            'activeWines'               => $this->activeWines,
            'activeFermentations'       => $this->activeFermentations,
            'containerUsage'            => $this->containerUsage,
            'maintenanceOverdue'        => $this->maintenanceOverdue,
            'totalCostsYear'            => $this->totalCostsYear,
            'recentTransfers'           => $this->recentTransfers,
            'recentFermentations'       => $this->recentFermentations,
            'recentReceptions'          => $this->recentReceptions,
            'recentViticulturists'      => $this->recentViticulturists,
        ])->layout('layouts.app');
    }
}
