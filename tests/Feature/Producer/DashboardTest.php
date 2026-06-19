<?php

namespace Tests\Feature\Producer;

use App\Livewire\Producer\Dashboard;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

class DashboardTest extends ProducerTestCase
{
    public function test_dashboard_renders_for_producer(): void
    {
        $this->actingAs($this->makeProducer())
            ->get(route('producer.dashboard'))
            ->assertOk();
    }

    public function test_viticulturist_cannot_access_dashboard(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.dashboard'))
            ->assertForbidden();
    }

    public function test_winery_cannot_access_dashboard(): void
    {
        $this->actingAs($this->makeWinery())
            ->get(route('producer.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_only_shows_own_activities(): void
    {
        $producer = $this->makeProducer();
        $other = $this->makeOtherProducer();

        $plot = Plot::factory()->create(['viticulturist_id' => $producer->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $producer->id]);

        // Activity owned by this producer
        $ownActivity = AgriculturalActivity::factory()->create([
            'viticulturist_id' => $producer->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
        ]);

        // Activity owned by another producer — must not appear
        $otherPlot = Plot::factory()->create(['viticulturist_id' => $other->id]);
        $otherCampaign = Campaign::factory()->create(['viticulturist_id' => $other->id]);
        AgriculturalActivity::factory()->create([
            'viticulturist_id' => $other->id,
            'plot_id' => $otherPlot->id,
            'campaign_id' => $otherCampaign->id,
        ]);

        $component = Livewire::actingAs($producer)->test(Dashboard::class);

        // recentActivities is a view variable scoped to Auth::id() == producer->id
        $ids = collect($component->viewData('recentActivities'))->pluck('id');

        $this->assertTrue($ids->contains($ownActivity->id));
        $this->assertFalse($ids->contains($other->id));
    }
}
