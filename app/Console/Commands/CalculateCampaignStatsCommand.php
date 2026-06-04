<?php

namespace App\Console\Commands;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CalculateCampaignStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:calculate-stats {campaign? : Campaign ID to calculate stats for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and cache campaign statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $campaignId = $this->argument('campaign');

        if ($campaignId) {
            $campaigns = Campaign::where('id', $campaignId)->get();

            if ($campaigns->isEmpty()) {
                $this->error("Campaign with ID {$campaignId} not found.");

                return self::FAILURE;
            }
        } else {
            $campaigns = Campaign::all();
        }

        $this->info("Calculating statistics for {$campaigns->count()} campaign(s)...");

        $this->withProgressBar($campaigns, function ($campaign) {
            $this->calculateCampaignStats($campaign);
        });

        $this->newLine(2);
        $this->info('✅ Campaign statistics calculated and cached successfully.');

        return self::SUCCESS;
    }

    /**
     * Calculate statistics for a campaign
     */
    protected function calculateCampaignStats(Campaign $campaign): void
    {
        $stats = [
            'total_activities' => AgriculturalActivity::where('campaign_id', $campaign->id)->count(),
            'activities_by_type' => AgriculturalActivity::where('campaign_id', $campaign->id)
                ->selectRaw('activity_type, COUNT(*) as count')
                ->groupBy('activity_type')
                ->pluck('count', 'activity_type')
                ->toArray(),
            'total_plots' => AgriculturalActivity::where('campaign_id', $campaign->id)
                ->distinct('plot_id')
                ->count('plot_id'),
            'calculated_at' => now()->toIso8601String(),
        ];

        // Calcular estadísticas específicas por tipo
        if (isset($stats['activities_by_type']['phytosanitary'])) {
            $stats['phytosanitary_stats'] = $this->calculatePhytosanitaryStats($campaign);
        }

        if (isset($stats['activities_by_type']['harvest'])) {
            $stats['harvest_stats'] = $this->calculateHarvestStats($campaign);
        }

        // Cachear por 24 horas
        Cache::put("campaign_{$campaign->id}_stats", $stats, now()->addDay());
    }

    /**
     * Calculate phytosanitary treatment statistics
     */
    protected function calculatePhytosanitaryStats(Campaign $campaign): array
    {
        $treatments = AgriculturalActivity::where('campaign_id', $campaign->id)
            ->where('activity_type', 'phytosanitary')
            ->with('phytosanitaryTreatment.product')
            ->get();

        $totalArea = $treatments->sum(fn ($a) => $a->phytosanitaryTreatment?->area_treated ?? 0);

        return [
            'total_treatments' => $treatments->count(),
            'total_area_treated' => round($totalArea, 2),
            'unique_products' => $treatments->pluck('phytosanitaryTreatment.product_id')->unique()->count(),
        ];
    }

    /**
     * Calculate harvest statistics
     */
    protected function calculateHarvestStats(Campaign $campaign): array
    {
        $harvests = AgriculturalActivity::where('campaign_id', $campaign->id)
            ->where('activity_type', 'harvest')
            ->with('harvest')
            ->get();

        $totalKg = $harvests->sum(fn ($a) => $a->harvest?->quantity_kg ?? 0);

        return [
            'total_harvests' => $harvests->count(),
            'total_kg' => round($totalKg, 2),
            'total_tons' => round($totalKg / 1000, 2),
        ];
    }
}
