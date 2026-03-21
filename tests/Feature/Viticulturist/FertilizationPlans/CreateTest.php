<?php

namespace Tests\Feature\Viticulturist\FertilizationPlans;

use App\Livewire\Viticulturist\FertilizationPlans\Create;
use App\Models\Campaign;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_fertilization_plan(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plan_year', '2024')
            ->set('prepared_by', 'Ing. Agrónomo Test')
            ->set('total_n_kg_ha', '30.000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.fertilization-plans.index'));

        $this->assertDatabaseHas('fertilization_plans', [
            'viticulturist_id' => $v->id,
            'plan_year'        => 2024,
            'prepared_by'      => 'Ing. Agrónomo Test',
            'status'           => 'draft',
            'active'           => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('plan_year', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'plan_year']);
    }

    public function test_plan_year_must_be_valid_integer(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plan_year', 'not_a_year')
            ->call('save')
            ->assertHasErrors(['plan_year']);
    }

    public function test_new_plan_defaults_to_draft_status(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plan_year', '2024')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fertilization_plans', [
            'viticulturist_id' => $v->id,
            'status'           => 'draft',
        ]);
    }

    public function test_mount_auto_fills_campaign(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('campaign_id', (string) $c->id);
    }

    public function test_nitrate_zone_flag_is_saved(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plan_year', '2024')
            ->set('nitrate_zone', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fertilization_plans', [
            'viticulturist_id' => $v->id,
            'nitrate_zone'     => true,
        ]);
    }

    public function test_add_and_remove_lines(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        $component = Livewire::test(Create::class);

        // mount() adds 1 line by default
        $component->assertSet('lines', fn($lines) => count($lines) === 1);

        $component->call('addLine')
            ->assertSet('lines', fn($lines) => count($lines) === 2);

        $component->call('removeLine', 0)
            ->assertSet('lines', fn($lines) => count($lines) === 1);
    }
}
