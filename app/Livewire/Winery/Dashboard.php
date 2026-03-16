<?php

namespace App\Livewire\Winery;

use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\ProductLot;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $wineryId = Auth::id();

        // Campaña activa de la bodega (viticulturist_id almacena el ID del propietario, sea viticultor o bodega)
        $activeCampaign = Campaign::forViticulturist($wineryId)->where('active', true)->first();
        $vintageYear    = $activeCampaign?->year ?? now()->year;

        // Viticultores vinculados
        $viticulturistCount = WineryViticulturist::where('winery_id', $wineryId)->count();
        if (Auth::user()->isProducer()) {
            $viticulturistCount += 1; // producer counts as their own linked viticulturist
        }

        // Batches de la campaña activa
        $activeBatches = GrapeReceptionBatch::where('winery_id', $wineryId)
            ->where('vintage_year', $vintageYear)
            ->with(['viticulturist:id,name', 'plotPlanting.grapeVariety'])
            ->get();

        $totalKgCampaign  = (float) $activeBatches->sum('total_weight_kg');
        $openBatchCount   = $activeBatches->where('status', 'open')->count();
        $closedBatchCount = $activeBatches->where('status', 'closed')->count();
        $totalReceptions  = Harvest::where('winery_id', $wineryId)
            ->where('status', 'active')
            ->whereHas('batch', fn($q) => $q->where('vintage_year', $vintageYear))
            ->count();

        // Kg y entradas de hoy (una sola query)
        $todayStats = Harvest::where('winery_id', $wineryId)
            ->where('status', 'active')
            ->whereDate('harvest_start_date', today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_weight), 0) as kg')
            ->first();
        $todayKg    = (float) $todayStats->kg;
        $todayCount = (int) $todayStats->count;

        // Alertas: lotes superados o en riesgo (comparando con forecast confirmado)
        $forecasts = WineryYieldForecast::where('winery_id', $wineryId)
            ->where('vintage_year', $vintageYear)
            ->where('status', 'confirmed')
            ->get()
            ->keyBy('plot_planting_id');

        $alertsExceeded = 0;
        $alertsAtRisk   = 0;
        foreach ($activeBatches as $batch) {
            $forecast = $forecasts->get($batch->plot_planting_id);
            if (!$forecast) continue;
            $pct = $forecast->estimated_kg > 0
                ? ($batch->total_weight_kg / $forecast->estimated_kg) * 100
                : 0;
            if ($pct > 100) $alertsExceeded++;
            elseif ($pct >= 80) $alertsAtRisk++;
        }

        // Lotes de vino en bodega
        $wineLotCount = ProductLot::where('user_id', $wineryId)->count();

        // Últimas 5 recepciones
        $recentReceptions = Harvest::where('winery_id', $wineryId)
            ->where('status', 'active')
            ->with(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'batch.viticulturist'])
            ->orderByDesc('harvest_start_date')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Viticultores vinculados recientes
        $recentViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name,email')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.winery.dashboard', [
            'activeCampaign'       => $activeCampaign,
            'vintageYear'          => $vintageYear,
            'viticulturistCount'   => $viticulturistCount,
            'totalKgCampaign'      => $totalKgCampaign,
            'openBatchCount'       => $openBatchCount,
            'closedBatchCount'     => $closedBatchCount,
            'totalReceptions'      => $totalReceptions,
            'todayKg'              => $todayKg,
            'todayCount'           => $todayCount,
            'alertsExceeded'       => $alertsExceeded,
            'alertsAtRisk'         => $alertsAtRisk,
            'wineLotCount'         => $wineLotCount,
            'recentReceptions'     => $recentReceptions,
            'recentViticulturists' => $recentViticulturists,
        ])->layout('layouts.app');
    }
}
