<?php

namespace Tests\Feature\Viticulturist\PlotCosts;

use App\Livewire\Viticulturist\PlotCosts\Edit;
use App\Models\PlotCost;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_edit_own_cost(): void
    {
        $viticulturist = $this->makeViticulturist();
        $cost = PlotCost::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'description' => 'Original',
            'amount' => 100.00,
            'cost_date' => now()->toDateString(),
            'category' => 'labor',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cost' => $cost])
            ->set('description', 'Actualizado')
            ->set('amount', '200.00')
            ->call('update')
            ->assertRedirect(route('viticulturist.plot-costs.index'));

        $this->assertDatabaseHas('plot_costs', [
            'id' => $cost->id,
            'description' => 'Actualizado',
        ]);
    }

    public function test_cannot_edit_other_viticulturist_cost(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        $cost = PlotCost::factory()->create([
            'viticulturist_id' => $other->id,
            'description' => 'Ajeno',
            'amount' => 100.00,
            'cost_date' => now()->toDateString(),
            'category' => 'labor',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cost' => $cost])
            ->assertStatus(403);
    }

    public function test_mount_populates_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $cost = PlotCost::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'description' => 'Abono foliar',
            'amount' => 75.50,
            'cost_date' => '2026-03-01',
            'category' => 'fertilizer',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cost' => $cost])
            ->assertSet('description', 'Abono foliar')
            ->assertSet('category', 'fertilizer')
            ->assertSet('cost_date', '2026-03-01');
    }
}
