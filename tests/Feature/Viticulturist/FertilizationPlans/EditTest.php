<?php

namespace Tests\Feature\Viticulturist\FertilizationPlans;

use App\Livewire\Viticulturist\FertilizationPlans\Edit;
use App\Models\Campaign;
use App\Models\FertilizationPlan;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    private function makePlan(int $viticulturistId, int $campaignId, string $status = 'draft'): FertilizationPlan
    {
        return FertilizationPlan::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaignId,
            'plan_year'        => 2024,
            'nitrate_zone'     => false,
            'status'           => $status,
            'active'           => true,
        ]);
    }

    public function test_can_edit_fertilization_plan(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['plan' => $plan])
            ->set('prepared_by', 'Nuevo Asesor')
            ->set('plan_year', '2025')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.fertilization-plans.index'));

        $this->assertDatabaseHas('fertilization_plans', [
            'id'          => $plan->id,
            'prepared_by' => 'Nuevo Asesor',
            'plan_year'   => 2025,
        ]);
    }

    public function test_mount_fills_properties_from_model(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id, 'active');
        $this->actingAs($v);

        Livewire::test(Edit::class, ['plan' => $plan])
            ->assertSet('plan_year', '2024')
            ->assertSet('status', 'active');
    }

    public function test_cannot_edit_other_viticulturists_plan(): void
    {
        $v     = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $c     = Campaign::getOrCreateActiveForYear($v->id);
        $plan  = $this->makePlan($v->id, $c->id);
        $this->actingAs($other);

        Livewire::test(Edit::class, ['plan' => $plan])
            ->assertStatus(403);
    }

    public function test_editing_archived_plan_does_not_set_active_false(): void
    {
        $v    = $this->makeViticulturist();
        $c    = Campaign::getOrCreateActiveForYear($v->id);
        $plan = $this->makePlan($v->id, $c->id, 'active');
        $this->actingAs($v);

        Livewire::test(Edit::class, ['plan' => $plan])
            ->set('status', 'archived')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fertilization_plans', [
            'id'     => $plan->id,
            'status' => 'archived',
            'active' => true,  // must remain true
        ]);
    }
}
