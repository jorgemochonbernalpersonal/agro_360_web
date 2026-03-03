<?php

namespace Tests\Feature\Viticulturist\EnergyUsages;

use App\Livewire\Viticulturist\EnergyUsages\Create;
use App\Models\Campaign;
use App\Models\EnergyUsage;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_energy_usage(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        // mount() auto-creates campaign and sets campaign_id
        Livewire::test(Create::class)
            ->set('date', '2024-06-15')
            ->set('energy_type', 'diesel')
            ->set('unit', 'liters')
            ->set('quantity', '100')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.energy-usages.index'));

        $this->assertDatabaseHas('energy_usages', [
            'viticulturist_id' => $viticulturist->id,
            'energy_type'      => 'diesel',
            'unit'             => 'liters',
            'active'           => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('quantity', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'quantity']);
    }

    public function test_quantity_must_be_positive(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('quantity', '0')
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    public function test_co2_recalculates_on_quantity_change(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        // diesel CO2 factor = 2.640 → 100 * 2.640 = 264
        Livewire::test(Create::class)
            ->set('energy_type', 'diesel')
            ->set('quantity', '100')
            ->assertSet('co2_kg_equivalent', '264');
    }

    public function test_unit_changes_with_energy_type(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('energy_type', 'electricity')
            ->assertSet('unit', 'kwh');
    }

    public function test_total_cost_recalculates(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        // 10 * 1.5 = 15
        Livewire::test(Create::class)
            ->set('quantity', '10')
            ->set('cost_per_unit', '1.5')
            ->assertSet('total_cost', '15');
    }
}
