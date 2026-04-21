<?php

namespace Tests\Feature\Supervisor\Campaigns;

use App\Livewire\Supervisor\Campaigns\Index;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class CampaignsDataTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForCampaigns(User $supervisor, User $winery): User
    {
        $vit = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by'      => $supervisor->id,
        ]);

        return $vit;
    }

    private function makeCampaign(User $viticulturist, array $attrs = []): void
    {
        DB::table('campaigns')->insert(array_merge([
            'name'             => 'Campaña ' . ($attrs['year'] ?? now()->year),
            'year'             => now()->year,
            'viticulturist_id' => $viticulturist->id,
            'active'           => false,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $attrs));
    }

    private function makeHarvest(User $winery, array $attrs = []): void
    {
        $activityId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $winery->id,
            'activity_type'    => 'observation',
            'activity_date'    => now()->format('Y-m-d'),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('harvests')->insert(array_merge([
            'activity_id'        => $activityId,
            'winery_id'          => $winery->id,
            'harvest_start_date' => now()->format('Y-m-d'),
            'total_weight'       => 1000,
            'total_value'        => 500.00,
            'vintage'            => now()->year,
            'status'             => 'active',
            'created_at'         => now(),
            'updated_at'         => now(),
        ], $attrs));
    }

    // ── setTab ────────────────────────────────────────────────────────────────

    public function test_set_tab_switches_active_tab(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('activeTab', 'resumen')
            ->call('setTab', 'viticultores')
            ->assertSet('activeTab', 'viticultores');
    }

    // ── summaryRows ───────────────────────────────────────────────────────────

    public function test_summary_rows_merges_campaigns_and_harvests_by_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForCampaigns($supervisor, $winery);

        $this->makeCampaign($vit, ['year' => 2025]);
        $this->makeHarvest($winery, ['vintage' => 2025, 'total_weight' => 800]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2025);
                return $row !== null
                    && $row->campaign_count === 1
                    && $row->total_kg == 800;
            });
    }

    public function test_summary_rows_includes_year_with_only_campaigns(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForCampaigns($supervisor, $winery);

        $this->makeCampaign($vit, ['year' => 2024]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2024);
                return $row !== null
                    && $row->campaign_count === 1
                    && $row->total_kg == 0
                    && $row->reception_count === 0;
            });
    }

    public function test_summary_rows_includes_year_with_only_harvests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['vintage' => 2023, 'total_weight' => 600]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2023);
                return $row !== null
                    && $row->campaign_count === 0
                    && $row->total_kg == 600;
            });
    }

    public function test_summary_rows_isolated_from_other_supervisor(): void
    {
        [$supervisor]                    = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $otherVit = $this->makeViticulturistForCampaigns($otherSupervisor, $otherWinery);
        $this->makeCampaign($otherVit, ['year' => 2025]);
        $this->makeHarvest($otherWinery, ['vintage' => 2025, 'total_weight' => 999]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', fn ($rows) => $rows->isEmpty());
    }

    // ── campaignsByYear ───────────────────────────────────────────────────────

    public function test_campaigns_by_year_counts_multiple_viticulturists(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit1 = $this->makeViticulturistForCampaigns($supervisor, $winery);
        $vit2 = $this->makeViticulturistForCampaigns($supervisor, $winery);

        $this->makeCampaign($vit1, ['year' => 2025]);
        $this->makeCampaign($vit2, ['year' => 2025]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2025);
                return $row?->campaign_count === 2;
            });
    }

    public function test_active_count_counts_only_active_campaigns(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForCampaigns($supervisor, $winery);

        $this->makeCampaign($vit, ['year' => 2025, 'active' => true]);
        $this->makeCampaign($vit, ['year' => 2025, 'active' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2025);
                return $row?->campaign_count === 2
                    && $row?->active_count == 1;
            });
    }

    // ── harvestsByVintage ─────────────────────────────────────────────────────

    public function test_harvests_by_vintage_sums_kg_per_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['vintage' => 2025, 'total_weight' => 400]);
        $this->makeHarvest($winery, ['vintage' => 2025, 'total_weight' => 600]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2025);
                return $row?->total_kg == 1000;
            });
    }

    public function test_harvests_by_vintage_counts_receptions(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['vintage' => 2025]);
        $this->makeHarvest($winery, ['vintage' => 2025]);
        $this->makeHarvest($winery, ['vintage' => 2025]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('summaryRows', function ($rows) {
                $row = $rows->firstWhere('year', 2025);
                return $row?->reception_count === 3;
            });
    }

    // ── viticulturistList ─────────────────────────────────────────────────────

    public function test_viticulturist_list_shows_only_supervisor_viticulturists(): void
    {
        [$supervisor, $winery]          = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $mine  = $this->makeViticulturistForCampaigns($supervisor, $winery);
        $other = $this->makeViticulturistForCampaigns($otherSupervisor, $otherWinery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('viticulturistList', function ($list) use ($mine, $other) {
                $ids = $list->pluck('id');
                return $ids->contains($mine->id) && !$ids->contains($other->id);
            });
    }

    // ── tabs ──────────────────────────────────────────────────────────────────

    public function test_tabs_counts_reflect_years_and_viticulturists(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForCampaigns($supervisor, $winery);

        $this->makeCampaign($vit, ['year' => 2024]);
        $this->makeCampaign($vit, ['year' => 2025]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('tabs', function ($tabs) {
                return $tabs['resumen']['count'] === 2
                    && $tabs['viticultores']['count'] === 1;
            });
    }
}
