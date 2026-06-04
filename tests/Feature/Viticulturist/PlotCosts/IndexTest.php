<?php

namespace Tests\Feature\Viticulturist\PlotCosts;

use App\Livewire\Viticulturist\PlotCosts\Index;
use App\Models\Campaign;
use App\Models\PlotCost;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_view_index(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_index_shows_own_costs(): void
    {
        $viticulturist = $this->makeViticulturist();
        $cost = PlotCost::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'description' => 'Tratamiento mildiu',
            'amount' => 150.00,
            'cost_date' => now()->toDateString(),
            'category' => 'phytosanitary',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Tratamiento mildiu');
    }

    public function test_index_does_not_show_other_viticulturist_costs(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        PlotCost::factory()->create([
            'viticulturist_id' => $other->id,
            'description' => 'Coste ajeno',
            'amount' => 500.00,
            'cost_date' => now()->toDateString(),
            'category' => 'labor',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertDontSee('Coste ajeno');
    }

    public function test_delete_own_cost(): void
    {
        $viticulturist = $this->makeViticulturist();
        $cost = PlotCost::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'description' => 'Poda manual',
            'amount' => 200.00,
            'cost_date' => now()->toDateString(),
            'category' => 'labor',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $cost->id);

        $this->assertDatabaseMissing('plot_costs', ['id' => $cost->id]);
    }

    public function test_cannot_delete_other_viticulturist_cost(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $cost = PlotCost::factory()->create([
            'viticulturist_id' => $other->id,
            'description' => 'Coste ajeno',
            'amount' => 100.00,
            'cost_date' => now()->toDateString(),
            'category' => 'other',
        ]);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $cost->id);
    }

    public function test_filter_by_campaign(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        PlotCost::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'description' => 'Con campaña',
            'amount' => 100.00,
            'cost_date' => now()->toDateString(),
            'category' => 'labor',
        ]);
        PlotCost::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => null,
            'description' => 'Sin campaña',
            'amount' => 200.00,
            'cost_date' => now()->toDateString(),
            'category' => 'other',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filter_campaign_id', (string) $campaign->id)
            ->assertSee('Con campaña')
            ->assertDontSee('Sin campaña');
    }
}
