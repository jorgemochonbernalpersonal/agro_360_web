<?php

namespace Tests\Feature\Viticulturist\MarketedHarvests;

use App\Livewire\Viticulturist\MarketedHarvests\Index;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\MarketedHarvest;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeMarketedHarvest(int $viticulturistId, string $buyerName = 'Bodega Test'): MarketedHarvest
    {
        // Use same campaign as Index mount() to ensure it appears in filtered view
        $campaign = Campaign::getOrCreateActiveForYear($viticulturistId);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => '2024-09-15',
        ]);

        $harvest = Harvest::create([
            'activity_id'        => $activity->id,
            'harvest_start_date' => '2024-09-15',
            'total_weight'       => 5000,
            'status'             => 'active',
        ]);

        return MarketedHarvest::create([
            'viticulturist_id' => $viticulturistId,
            'harvest_id'       => $harvest->id,
            'campaign_id'      => $campaign->id,
            'delivery_date'    => '2024-09-20',
            'quantity_kg'      => 1000,
            'destination_type' => 'cooperative',
            'buyer_name'       => $buyerName,
            'active'           => true,
        ]);
    }

    public function test_index_shows_entries(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        // delivery_date '2024-09-20' renders as '20/09/2024' in the table
        Livewire::test(Index::class)
            ->assertSee('20/09/2024');
    }

    public function test_delete_removes_entry(): void
    {
        $viticulturist   = $this->makeViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $marketedHarvest->id);

        $this->assertDatabaseMissing('marketed_harvests', ['id' => $marketedHarvest->id]);
    }

    public function test_generate_invoice_redirects(): void
    {
        $viticulturist   = $this->makeViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('generateInvoice', $marketedHarvest->id)
            ->assertRedirect();
    }

    public function test_cannot_delete_other_viticulturists_entry(): void
    {
        $viticulturist   = $this->makeViticulturist();
        $other           = $this->makeOtherViticulturist();
        $marketedHarvest = $this->makeMarketedHarvest($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $marketedHarvest->id);
    }
}
