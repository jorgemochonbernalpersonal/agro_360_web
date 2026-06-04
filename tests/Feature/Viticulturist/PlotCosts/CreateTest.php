<?php

namespace Tests\Feature\Viticulturist\PlotCosts;

use App\Livewire\Viticulturist\PlotCosts\Create;
use App\Models\Campaign;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_create_cost(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('description', 'Vendimia manual')
            ->set('category', 'labor')
            ->set('amount', '350.00')
            ->set('cost_date', now()->toDateString())
            ->call('save')
            ->assertRedirect(route('viticulturist.plot-costs.index'));

        $this->assertDatabaseHas('plot_costs', [
            'viticulturist_id' => $viticulturist->id,
            'description' => 'Vendimia manual',
            'category' => 'labor',
        ]);
    }

    public function test_description_is_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('description', '')
            ->set('category', 'labor')
            ->set('amount', '100.00')
            ->set('cost_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['description']);
    }

    public function test_amount_is_required_and_positive(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('description', 'Test')
            ->set('category', 'labor')
            ->set('amount', '0')
            ->set('cost_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    public function test_category_must_be_valid(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('description', 'Test')
            ->set('category', 'invalid_category')
            ->set('amount', '100.00')
            ->set('cost_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['category']);
    }

    public function test_cost_date_is_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('description', 'Test')
            ->set('category', 'labor')
            ->set('amount', '100.00')
            ->set('cost_date', '')
            ->call('save')
            ->assertHasErrors(['cost_date']);
    }

    public function test_can_associate_with_plot_and_campaign(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('description', 'Poda')
            ->set('category', 'labor')
            ->set('amount', '120.00')
            ->set('cost_date', now()->toDateString())
            ->set('plot_id', (string) $plot->id)
            ->set('campaign_id', (string) $campaign->id)
            ->call('save');

        $this->assertDatabaseHas('plot_costs', [
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
        ]);
    }

    public function test_mount_preselects_active_campaign(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('campaign_id', (string) $campaign->id);
    }
}
