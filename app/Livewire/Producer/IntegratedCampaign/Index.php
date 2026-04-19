<?php

namespace App\Livewire\Producer\IntegratedCampaign;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $filterCampaign = '';

    public function mount(): void
    {
        $active = Campaign::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->orderByDesc('year')
            ->first();

        $this->filterCampaign = (string) ($active?->id ?? '');
    }

    public function render()
    {
        $userId    = Auth::id();
        $campaigns = Campaign::where('viticulturist_id', $userId)
            ->orderByDesc('year')
            ->get(['id', 'name', 'year', 'active']);

        $data = $this->filterCampaign
            ? $this->buildDashboard($userId, (int) $this->filterCampaign)
            : null;

        $campaign = $this->filterCampaign
            ? $campaigns->find($this->filterCampaign)
            : null;

        return view('livewire.producer.integrated-campaign.index', [
            'campaigns' => $campaigns,
            'campaign'  => $campaign,
            'data'      => $data,
        ])->layout('layouts.app', [
            'title'       => 'Dashboard Integrado de Campaña',
            'description' => 'Visión completa viñedo + bodega por campaña',
        ]);
    }

    private function buildDashboard(int $userId, int $campaignId): array
    {
        $campaign = Campaign::find($campaignId);
        $year     = $campaign?->year;

        // ═══════════════════════════════════════════════════════════
        // VIÑEDO
        // ═══════════════════════════════════════════════════════════

        // Actividades del cuaderno
        $activityCounts = AgriculturalActivity::where('viticulturist_id', $userId)
            ->where('campaign_id', $campaignId)
            ->select('activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type')
            ->toArray();

        $totalActivities = array_sum($activityCounts);

        // Vendimia de campo
        $fieldHarvestQuery = Harvest::query()
            ->join('agricultural_activities as aa', 'harvests.activity_id', '=', 'aa.id')
            ->where('aa.viticulturist_id', $userId)
            ->where('aa.campaign_id', $campaignId)
            ->where('harvests.status', '!=', 'cancelled');

        $fieldHarvest = (clone $fieldHarvestQuery)->selectRaw('
            COUNT(*) as entries,
            COALESCE(SUM(harvests.total_weight), 0) as total_kg,
            COALESCE(AVG(harvests.yield_per_hectare), 0) as avg_yield,
            COALESCE(AVG(harvests.baume_degree), 0) as avg_baume,
            COALESCE(AVG(harvests.brix_degree), 0) as avg_brix,
            COALESCE(AVG(harvests.ph_level), 0) as avg_ph,
            COALESCE(SUM(harvests.total_value), 0) as field_value
        ')->first();

        // Desglose por parcela
        $perPlot = (clone $fieldHarvestQuery)
            ->join('plots as p', 'aa.plot_id', '=', 'p.id')
            ->select(
                'p.id as plot_id',
                'p.name as plot_name',
                DB::raw('SUM(harvests.total_weight) as total_kg'),
                DB::raw('AVG(harvests.yield_per_hectare) as avg_yield'),
                DB::raw('AVG(harvests.baume_degree) as avg_baume'),
                DB::raw('COUNT(*) as harvest_count'),
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_kg')
            ->get();

        // Costes de parcela (si hay datos)
        $plotCosts = DB::table('plot_costs')
            ->where('viticulturist_id', $userId)
            ->where('campaign_id', $campaignId)
            ->sum('amount') ?? 0;

        // ═══════════════════════════════════════════════════════════
        // BODEGA
        // ═══════════════════════════════════════════════════════════

        // Recepciones
        $receptionStats = Harvest::where('winery_id', $userId)
            ->whereNotNull('batch_id')
            ->whereHas('batch', fn ($q) => $q->where('vintage_year', $year))
            ->selectRaw('
                COUNT(*) as entries,
                COALESCE(SUM(total_weight), 0) as total_kg,
                COALESCE(AVG(baume_degree), 0) as avg_baume,
                COALESCE(AVG(brix_degree), 0) as avg_brix
            ')->first();

        // Vinos
        $wines = Wine::where('user_id', $userId)
            ->where('vintage', $year)
            ->get(['id', 'name', 'wine_type', 'status']);

        $wineVolume = $wines->sum('current_volume');

        $winesByType = $wines->groupBy('wine_type')->map->count()->toArray();
        $winesByStatus = $wines->groupBy('status')->map->count()->toArray();

        // Contenedores
        $containerBase = Container::where('user_id', $userId)->where('archived', false);
        $containerCapacity = (clone $containerBase)->sum('capacity');
        $containerUsed     = (clone $containerBase)->selectRaw('SUM(used_capacity + wine_volume_liters) as used')->value('used') ?? 0;
        $containerPct      = $containerCapacity > 0 ? round($containerUsed / $containerCapacity * 100, 1) : 0;

        // Productos / lotes
        $wineIds = $wines->pluck('id');
        $productStats = DB::table('product_lots')
            ->whereIn('wine_id', $wineIds)
            ->selectRaw('
                COUNT(*) as lots,
                COALESCE(SUM(quantity), 0) as units,
                COALESCE(SUM(sold_qty), 0) as sold,
                COALESCE(SUM(sold_qty * price), 0) as revenue
            ')->first();

        // ═══════════════════════════════════════════════════════════
        // CRUCE VIÑEDO ↔ BODEGA
        // ═══════════════════════════════════════════════════════════

        $fieldKg     = $fieldHarvest->total_kg ?? 0;
        $receptionKg = $receptionStats->total_kg ?? 0;
        $yieldLoss   = $fieldKg > 0 ? round(($fieldKg - $receptionKg) / $fieldKg * 100, 1) : 0;

        $fieldValue   = $fieldHarvest->field_value ?? 0;
        $wineryRevenue = $productStats->revenue ?? 0;
        $valueMultiplier = $fieldValue > 0 ? round($wineryRevenue / $fieldValue, 2) : 0;

        return [
            // Viñedo
            'activityCounts'   => $activityCounts,
            'totalActivities'  => $totalActivities,
            'fieldHarvest'     => $fieldHarvest,
            'perPlot'          => $perPlot,
            'plotCosts'        => $plotCosts,
            // Bodega
            'receptionStats'   => $receptionStats,
            'wines'            => $wines,
            'wineVolume'       => $wineVolume,
            'winesByType'      => $winesByType,
            'winesByStatus'    => $winesByStatus,
            'containerCapacity' => $containerCapacity,
            'containerUsed'    => $containerUsed,
            'containerPct'     => $containerPct,
            'productStats'     => $productStats,
            // Cruce
            'yieldLoss'        => $yieldLoss,
            'valueMultiplier'  => $valueMultiplier,
        ];
    }
}
