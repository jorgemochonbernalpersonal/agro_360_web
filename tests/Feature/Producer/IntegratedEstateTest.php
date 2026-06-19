<?php

namespace Tests\Feature\Producer;

use App\Livewire\Producer\IntegratedEstate\Index;
use App\Models\Campaign;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

class IntegratedEstateTest extends ProducerTestCase
{
    public function test_renders_for_producer(): void
    {
        $this->actingAs($this->makeProducer())
            ->get(route('producer.integrated-estate'))
            ->assertOk();
    }

    public function test_viticulturist_cannot_access(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.integrated-estate'))
            ->assertForbidden();
    }

    public function test_without_campaign_data_is_null(): void
    {
        $producer = $this->makeProducer();

        Livewire::actingAs($producer)
            ->test(Index::class)
            ->set('filterCampaign', '')
            ->assertStatus(200);
    }

    public function test_with_campaign_builds_dashboard(): void
    {
        $producer = $this->makeProducer();
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $producer->id,
            'active' => true,
        ]);

        Livewire::actingAs($producer)
            ->test(Index::class)
            ->set('filterCampaign', (string) $campaign->id)
            ->assertStatus(200);
    }

    public function test_plots_and_campaigns_scoped_to_own_producer(): void
    {
        $producer = $this->makeProducer();
        $other = $this->makeOtherProducer();

        $ownCampaign = Campaign::factory()->create(['viticulturist_id' => $producer->id]);
        Campaign::factory()->create(['viticulturist_id' => $other->id]);

        $ownPlot = Plot::factory()->create(['viticulturist_id' => $producer->id]);
        Plot::factory()->create(['viticulturist_id' => $other->id]);

        $component = Livewire::actingAs($producer)->test(Index::class);

        $campaigns = collect($component->campaigns);
        $plots = collect($component->plots);

        $this->assertTrue($campaigns->contains('id', $ownCampaign->id));
        $this->assertFalse($campaigns->contains('viticulturist_id', $other->id));

        $this->assertTrue($plots->contains('id', $ownPlot->id));
        $this->assertFalse($plots->contains('viticulturist_id', $other->id));
    }
}
