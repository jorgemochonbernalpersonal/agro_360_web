<?php

namespace Tests\Feature\Supervisor\Finance;

use App\Livewire\Supervisor\Finance\Index;
use App\Models\Subscription;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class FinanceDataTest extends SupervisorTestCase
{
    // ── setTab ────────────────────────────────────────────────────────────────

    public function test_set_tab_switches_active_tab(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('activeTab', 'subscriptions')
            ->call('setTab', 'wineries')
            ->assertSet('activeTab', 'wineries');
    }

    // ── activeSubscriptions ───────────────────────────────────────────────────

    public function test_active_subscriptions_counts_non_expired_active(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForFinance($supervisor, $winery);

        $this->makeSubscription($vit);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activeSubscriptions', 1);
    }

    public function test_active_subscriptions_ignores_expired(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForFinance($supervisor, $winery);

        $this->makeSubscription($vit, [
            'ends_at' => now()->subDay(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activeSubscriptions', 0);
    }

    public function test_active_subscriptions_ignores_cancelled_status(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForFinance($supervisor, $winery);

        $this->makeSubscription($vit, [
            'status' => Subscription::STATUS_CANCELLED,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activeSubscriptions', 0);
    }

    public function test_active_subscriptions_isolated_from_other_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $otherVit = $this->makeViticulturistForFinance($otherSupervisor, $otherWinery);
        $this->makeSubscription($otherVit);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activeSubscriptions', 0);
    }

    // ── revenue aggregates ────────────────────────────────────────────────────

    public function test_monthly_revenue_sums_active_monthly_subscriptions(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit1 = $this->makeViticulturistForFinance($supervisor, $winery);
        $vit2 = $this->makeViticulturistForFinance($supervisor, $winery);

        $this->makeSubscription($vit1, ['plan_type' => Subscription::PLAN_MONTHLY, 'amount' => 9.00]);
        $this->makeSubscription($vit2, ['plan_type' => Subscription::PLAN_MONTHLY, 'amount' => 9.00]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('monthlyRevenue', fn ($v) => (float) $v === 18.0);
    }

    public function test_yearly_revenue_sums_active_yearly_subscriptions(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForFinance($supervisor, $winery);

        $this->makeSubscription($vit, ['plan_type' => Subscription::PLAN_YEARLY, 'amount' => 85.00]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('yearlyRevenue', fn ($v) => (float) $v === 85.0)
            ->assertViewHas('monthlyRevenue', fn ($v) => (float) $v === 0.0);
    }

    public function test_revenue_only_counts_active_non_expired_plans(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForFinance($supervisor, $winery);

        $this->makeSubscription($vit, ['plan_type' => Subscription::PLAN_MONTHLY, 'amount' => 9.00, 'ends_at' => now()->subDay()]);
        $this->makeSubscription($vit, ['plan_type' => Subscription::PLAN_MONTHLY, 'status' => Subscription::STATUS_CANCELLED, 'amount' => 9.00]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('monthlyRevenue', fn ($v) => (float) $v === 0.0);
    }

    // ── harvestValueByWinery ──────────────────────────────────────────────────

    public function test_harvest_value_by_winery_sums_current_year_active_harvests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['total_value' => 300.00, 'total_weight' => 600]);
        $this->makeHarvest($winery, ['total_value' => 200.00, 'total_weight' => 400]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('harvestValueByWinery', function ($collection) {
                return $collection->first()?->total_value == 500.00;
            });
    }

    public function test_harvest_value_excludes_other_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['vintage' => now()->year - 1, 'total_value' => 999.00]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('harvestValueByWinery', fn ($col) => $col->isEmpty());
    }

    public function test_harvest_value_excludes_other_supervisor_wineries(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($otherWinery, ['total_value' => 999.00]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('harvestValueByWinery', fn ($col) => $col->isEmpty());
    }

    // ── tabs ──────────────────────────────────────────────────────────────────

    public function test_tabs_counts_reflect_supervisor_pools(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForFinance($supervisor, $winery);
        $this->makeViticulturistForFinance($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('tabs', function ($tabs) {
                return $tabs['subscriptions']['count'] === 2
                    && $tabs['wineries']['count'] === 1;
            });
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForFinance(User $supervisor, User $winery): User
    {
        $vit = User::factory()->create(['role' => 'viticulturist']);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $vit->id,
            'assigned_by' => $supervisor->id,
        ]);

        WineryViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'viticulturist_id' => $vit->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $supervisor->id,
        ]);

        return $vit;
    }

    private function makeSubscription(User $viticulturist, array $attrs = []): Subscription
    {
        return Subscription::create(array_merge([
            'user_id' => $viticulturist->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => 9.00,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
        ], $attrs));
    }

    private function makeHarvest(User $winery, array $attrs = []): void
    {
        $activityId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $winery->id,
            'activity_type' => 'observation',
            'activity_date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('harvests')->insert(array_merge([
            'activity_id' => $activityId,
            'winery_id' => $winery->id,
            'harvest_start_date' => now()->format('Y-m-d'),
            'total_weight' => 1000,
            'total_value' => 500.00,
            'vintage' => now()->year,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }
}
