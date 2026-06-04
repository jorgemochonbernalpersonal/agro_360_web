<?php

namespace App\Livewire\Viticulturist\CampaignComparison;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $campaignA = '';

    public string $campaignB = '';

    public string $filterPlot = '';

    public function mount(): void
    {
        $campaigns = $this->getUserCampaigns();
        if ($campaigns->count() >= 2) {
            $this->campaignA = (string) $campaigns->first()->id;
            $this->campaignB = (string) $campaigns->skip(1)->first()->id;
        } elseif ($campaigns->count() === 1) {
            $this->campaignA = (string) $campaigns->first()->id;
        }
    }

    public function render()
    {
        $userId = Auth::id();
        $campaigns = $this->getUserCampaigns();
        $plots = Plot::where('viticulturist_id', $userId)->orderBy('name')->get(['id', 'name']);

        $dataA = $this->campaignA ? $this->buildCampaignData((int) $this->campaignA, $userId) : null;
        $dataB = $this->campaignB ? $this->buildCampaignData((int) $this->campaignB, $userId) : null;

        $campaignAModel = $this->campaignA ? $campaigns->find($this->campaignA) : null;
        $campaignBModel = $this->campaignB ? $campaigns->find($this->campaignB) : null;

        return view('livewire.viticulturist.campaign-comparison.index', [
            'campaigns' => $campaigns,
            'plots' => $plots,
            'dataA' => $dataA,
            'dataB' => $dataB,
            'campaignAModel' => $campaignAModel,
            'campaignBModel' => $campaignBModel,
        ]);
    }

    private function getUserCampaigns()
    {
        return Campaign::where('viticulturist_id', Auth::id())
            ->orderByDesc('year')
            ->get(['id', 'name', 'year', 'start_date', 'end_date']);
    }

    private function buildCampaignData(int $campaignId, int $userId): array
    {
        $plotFilter = $this->filterPlot ? (int) $this->filterPlot : null;

        // Harvests
        $harvestQuery = Harvest::query()
            ->join('agricultural_activities as aa', 'harvests.activity_id', '=', 'aa.id')
            ->where('aa.viticulturist_id', $userId)
            ->where('aa.campaign_id', $campaignId)
            ->where('harvests.status', '!=', 'cancelled');

        if ($plotFilter) {
            $harvestQuery->where('aa.plot_id', $plotFilter);
        }

        $harvestStats = (clone $harvestQuery)->selectRaw('
            COUNT(*) as total_harvests,
            COALESCE(SUM(harvests.total_weight), 0) as total_kg,
            COALESCE(AVG(harvests.yield_per_hectare), 0) as avg_yield,
            COALESCE(AVG(harvests.baume_degree), 0) as avg_baume,
            COALESCE(AVG(harvests.brix_degree), 0) as avg_brix,
            COALESCE(AVG(harvests.ph_level), 0) as avg_ph,
            COALESCE(SUM(harvests.total_value), 0) as total_value,
            COALESCE(AVG(harvests.price_per_kg), 0) as avg_price_kg
        ')->first();

        // Activity counts by type
        $activityQuery = AgriculturalActivity::where('viticulturist_id', $userId)
            ->where('campaign_id', $campaignId);

        if ($plotFilter) {
            $activityQuery->where('plot_id', $plotFilter);
        }

        $activityCounts = (clone $activityQuery)
            ->select('activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('activity_type')
            ->pluck('count', 'activity_type')
            ->toArray();

        $totalActivities = array_sum($activityCounts);

        // Per-plot breakdown
        $perPlot = (clone $harvestQuery)
            ->join('plots as p', 'aa.plot_id', '=', 'p.id')
            ->select(
                'p.id as plot_id',
                'p.name as plot_name',
                DB::raw('SUM(harvests.total_weight) as total_kg'),
                DB::raw('AVG(harvests.yield_per_hectare) as avg_yield'),
                DB::raw('AVG(harvests.baume_degree) as avg_baume'),
                DB::raw('SUM(harvests.total_value) as total_value'),
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_kg')
            ->get();

        return [
            'harvest' => $harvestStats,
            'activityCounts' => $activityCounts,
            'totalActivities' => $totalActivities,
            'perPlot' => $perPlot,
        ];
    }
}
