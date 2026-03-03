<?php

namespace Tests\Feature\Viticulturist\EnergyUsages;

use App\Livewire\Viticulturist\EnergyUsages\Edit;
use App\Models\Campaign;
use App\Models\EnergyUsage;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    private function makeCampaign(int $viticulturistId): Campaign
    {
        return Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year'             => 2024,
            'name'             => 'Campaña 2024',
        ]);
    }

    private function makeEnergyUsage(int $viticulturistId, int $campaignId): EnergyUsage
    {
        return EnergyUsage::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaignId,
            'date'             => '2024-06-15',
            'energy_type'      => 'diesel',
            'unit'             => 'liters',
            'quantity'         => 100,
            'active'           => true,
        ]);
    }

    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['energyUsage' => $usage])
            ->assertSet('energy_type', 'diesel')
            ->assertSet('unit', 'liters')
            ->assertSet('date', '2024-06-15');
    }

    public function test_can_update_energy_usage(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['energyUsage' => $usage])
            ->set('energy_type', 'electricity')
            ->set('unit', 'kwh')
            ->set('quantity', '500')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.energy-usages.index'));

        $this->assertDatabaseHas('energy_usages', [
            'id'          => $usage->id,
            'energy_type' => 'electricity',
            'unit'        => 'kwh',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['energyUsage' => $usage])
            ->set('quantity', '')
            ->set('date', '')
            ->call('save')
            ->assertHasErrors(['quantity', 'date']);
    }

    public function test_recalculate_still_works_on_edit(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($viticulturist);

        // 50 * 1.5 = 75
        Livewire::test(Edit::class, ['energyUsage' => $usage])
            ->set('quantity', '50')
            ->set('cost_per_unit', '1.5')
            ->assertSet('total_cost', '75');
    }

    public function test_cannot_edit_other_viticulturists_usage(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($other)
            ->get(route('viticulturist.energy-usages.edit', $usage))
            ->assertStatus(403);
    }
}
