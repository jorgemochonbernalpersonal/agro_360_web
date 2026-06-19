<?php

namespace Tests\Feature\Producer;

use App\Livewire\Producer\IntegratedCampaign\Index;
use App\Models\Campaign;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

class IntegratedCampaignTest extends ProducerTestCase
{
    public function test_renders_for_producer(): void
    {
        $this->actingAs($this->makeProducer())
            ->get(route('producer.integrated-campaign'))
            ->assertOk();
    }

    public function test_viticulturist_cannot_access(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.integrated-campaign'))
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

    public function test_campaigns_scoped_to_own_producer(): void
    {
        $producer = $this->makeProducer();
        $other = $this->makeOtherProducer();

        Campaign::factory()->create(['viticulturist_id' => $other->id, 'active' => true]);
        $ownCampaign = Campaign::factory()->create(['viticulturist_id' => $producer->id, 'active' => true]);

        $component = Livewire::actingAs($producer)->test(Index::class);
        $campaigns = collect($component->viewData('campaigns'));

        $this->assertTrue($campaigns->contains('id', $ownCampaign->id));
        $this->assertFalse($campaigns->contains('viticulturist_id', $other->id));
    }
}
