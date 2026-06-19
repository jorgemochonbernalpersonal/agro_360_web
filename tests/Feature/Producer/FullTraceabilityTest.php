<?php

namespace Tests\Feature\Producer;

use App\Livewire\Producer\FullTraceability\Index;
use App\Models\Campaign;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

class FullTraceabilityTest extends ProducerTestCase
{
    public function test_renders_for_producer(): void
    {
        $this->actingAs($this->makeProducer())
            ->get(route('producer.full-traceability'))
            ->assertOk();
    }

    public function test_viticulturist_cannot_access(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.full-traceability'))
            ->assertForbidden();
    }

    public function test_without_campaign_filter_trace_data_is_null(): void
    {
        $producer = $this->makeProducer();

        // No campaigns → mount sets filterCampaign = '' → traceData is null → no exception
        Livewire::actingAs($producer)
            ->test(Index::class)
            ->assertSet('filterCampaign', '')
            ->assertStatus(200);
    }

    public function test_with_campaign_filter_renders_trace_data(): void
    {
        $producer = $this->makeProducer();
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $producer->id,
            'active' => true,
        ]);

        Livewire::actingAs($producer)
            ->test(Index::class)
            ->set('filterCampaign', (string) $campaign->id)
            ->assertSet('filterCampaign', (string) $campaign->id)
            ->assertStatus(200);
    }

    public function test_campaign_filter_only_shows_own_campaigns(): void
    {
        $producer = $this->makeProducer();
        $other = $this->makeOtherProducer();

        Campaign::factory()->create(['viticulturist_id' => $other->id]);
        $ownCampaign = Campaign::factory()->create(['viticulturist_id' => $producer->id]);

        $component = Livewire::actingAs($producer)->test(Index::class);
        $campaigns = collect($component->viewData('campaigns'));

        $this->assertTrue($campaigns->contains('id', $ownCampaign->id));
        $this->assertFalse($campaigns->contains('viticulturist_id', $other->id));
    }
}
