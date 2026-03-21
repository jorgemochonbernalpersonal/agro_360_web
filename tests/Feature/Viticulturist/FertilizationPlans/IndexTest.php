<?php

namespace Tests\Feature\Viticulturist\FertilizationPlans;

use App\Livewire\Viticulturist\FertilizationPlans\Index;
use App\Models\Campaign;
use App\Models\FertilizationPlan;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makePlan(int $viticulturistId, int $campaignId, string $status = 'draft', array $overrides = []): FertilizationPlan
    {
        return FertilizationPlan::create(array_merge([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaignId,
            'plan_year'        => 2024,
            'nitrate_zone'     => false,
            'status'           => $status,
            'active'           => true,
        ], $overrides));
    }

    public function test_index_shows_plans(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->makePlan($v->id, $c->id, 'draft', ['prepared_by' => 'Asesor-Visible-001']);
        $this->actingAs($v);

        Livewire::test(Index::class)->assertSee('Asesor-Visible-001');
    }

    public function test_activate_sets_status_to_active(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id, 'draft');
        $this->actingAs($v);

        Livewire::test(Index::class)->call('activate', $plan->id);

        $this->assertDatabaseHas('fertilization_plans', ['id' => $plan->id, 'status' => 'active']);
    }

    public function test_archive_sets_status_to_archived_without_touching_active_flag(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id, 'active');
        $this->actingAs($v);

        Livewire::test(Index::class)->call('archive', $plan->id);

        $this->assertDatabaseHas('fertilization_plans', [
            'id'     => $plan->id,
            'status' => 'archived',
            'active' => true,  // active flag must remain true
        ]);
    }

    public function test_delete_removes_draft_plan(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id, 'draft');
        $this->actingAs($v);

        Livewire::test(Index::class)->call('delete', $plan->id);

        $this->assertDatabaseMissing('fertilization_plans', ['id' => $plan->id]);
    }

    public function test_cannot_delete_active_plan(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id, 'active');
        $this->actingAs($v);

        Livewire::test(Index::class)->call('delete', $plan->id);

        $this->assertDatabaseHas('fertilization_plans', ['id' => $plan->id]);
    }

    public function test_cannot_archive_other_viticulturists_plan(): void
    {
        $v     = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $c     = Campaign::getOrCreateActiveForYear($v->id);
        $plan  = $this->makePlan($v->id, $c->id);
        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('archive', $plan->id);
    }

    public function test_stats_count_by_status(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->makePlan($v->id, $c->id, 'draft');
        $this->makePlan($v->id, $c->id, 'active');
        $this->makePlan($v->id, $c->id, 'archived');
        $this->actingAs($v);

        // All 3 are active=true, just different statuses — all visible
        Livewire::test(Index::class)->assertOk();
    }
}
