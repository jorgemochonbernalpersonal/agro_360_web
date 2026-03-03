<?php

namespace Tests\Feature\Viticulturist\MarketedHarvests;

use App\Livewire\Viticulturist\MarketedHarvests\Create;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\MarketedHarvest;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    private function makeHarvest(int $viticulturistId): Harvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($viticulturistId);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => '2024-09-15',
        ]);

        return Harvest::create([
            'activity_id'        => $activity->id,
            'harvest_start_date' => '2024-09-15',
            'total_weight'       => 5000,
            'status'             => 'active',
        ]);
    }

    public function test_can_create_marketed_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $harvest       = $this->makeHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('harvest_id', (string) $harvest->id)
            ->set('delivery_date', '2024-09-20')
            ->set('quantity_kg', '1000')
            ->set('destination_type', 'cooperative')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.marketed-harvests.index'));

        $this->assertDatabaseHas('marketed_harvests', [
            'viticulturist_id' => $viticulturist->id,
            'harvest_id'       => $harvest->id,
            'destination_type' => 'cooperative',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('harvest_id', '')
            ->set('quantity_kg', '')
            ->set('destination_type', '')
            ->call('save')
            ->assertHasErrors(['harvest_id', 'quantity_kg', 'destination_type']);
    }

    public function test_harvest_must_belong_to_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $otherHarvest  = $this->makeHarvest($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Create::class)
            ->set('harvest_id', (string) $otherHarvest->id)
            ->set('delivery_date', '2024-09-20')
            ->set('quantity_kg', '1000')
            ->set('destination_type', 'cooperative')
            ->call('save');
    }

    public function test_total_value_recalculates(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        // 500 kg * 0.50 €/kg = 250
        Livewire::test(Create::class)
            ->set('price_per_kg', '0.50')
            ->set('quantity_kg', '500')
            ->assertSet('total_value', '250');
    }

    public function test_all_destination_types_accepted(): void
    {
        $viticulturist = $this->makeViticulturist();
        $harvest       = $this->makeHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        foreach (array_keys(MarketedHarvest::DESTINATION_TYPES) as $type) {
            Livewire::test(Create::class)
                ->set('harvest_id', (string) $harvest->id)
                ->set('delivery_date', '2024-09-20')
                ->set('quantity_kg', '100')
                ->set('destination_type', $type)
                ->call('save')
                ->assertHasNoErrors(['destination_type']);
        }
    }
}
