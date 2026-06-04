<?php

namespace Tests\Feature\Viticulturist\MarketedHarvests;

use App\Livewire\Viticulturist\MarketedHarvests\Edit;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\MarketedHarvest;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['marketedHarvest' => $marketedHarvest])
            ->assertSet('destination_type', 'cooperative')
            ->assertSet('delivery_date', '2024-09-20')
            ->assertSet('quantity_kg', '1000.000');
    }

    public function test_can_update_marketed_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['marketedHarvest' => $marketedHarvest])
            ->set('destination_type', 'own_winery')
            ->set('buyer_name', 'Bodega Propia SA')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.marketed-harvests.index'));

        $this->assertDatabaseHas('marketed_harvests', [
            'id' => $marketedHarvest->id,
            'destination_type' => 'own_winery',
            'buyer_name' => 'Bodega Propia SA',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['marketedHarvest' => $marketedHarvest])
            ->set('quantity_kg', '')
            ->set('delivery_date', '')
            ->call('save')
            ->assertHasErrors(['quantity_kg', 'delivery_date']);
    }

    public function test_harvest_ownership_enforced_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);
        $otherHarvest = $this->makeHarvest($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['marketedHarvest' => $marketedHarvest])
            ->set('harvest_id', (string) $otherHarvest->id)
            ->call('save');
    }

    public function test_cannot_edit_other_viticulturists_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.marketed-harvests.edit', $marketedHarvest))
            ->assertStatus(403);
    }

    private function makeHarvest(int $viticulturistId): Harvest
    {
        $campaign = Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year' => 2024,
            'name' => 'Campaña 2024',
        ]);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => '2024-09-15',
        ]);

        return Harvest::create([
            'activity_id' => $activity->id,
            'harvest_start_date' => '2024-09-15',
            'total_weight' => 5000,
            'status' => 'active',
        ]);
    }

    private function makeMarketedHarvest(int $viticulturistId): MarketedHarvest
    {
        $harvest = $this->makeHarvest($viticulturistId);

        return MarketedHarvest::create([
            'viticulturist_id' => $viticulturistId,
            'harvest_id' => $harvest->id,
            'campaign_id' => $harvest->activity->campaign_id,
            'delivery_date' => '2024-09-20',
            'quantity_kg' => 1000,
            'destination_type' => 'cooperative',
            'active' => true,
        ]);
    }
}
