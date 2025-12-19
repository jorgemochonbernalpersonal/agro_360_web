<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_belongs_to_viticulturist(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->assertTrue($campaign->relationLoaded('viticulturist') === false);
        $this->assertEquals($viticulturist->id, $campaign->viticulturist->id);
    }

    public function test_scopes_filter_correctly(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);

        $campaign1 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist1->id,
            'year' => 2025,
            'active' => true,
        ]);

        $campaign2 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist1->id,
            'year' => 2024,
            'active' => false,
        ]);

        $campaign3 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist2->id,
            'year' => 2025,
            'active' => true,
        ]);

        // Scope active: debe devolver todas las campañas activas, independientemente del viticultor
        $this->assertEquals(
            [$campaign1->id, $campaign3->id],
            Campaign::active()->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$campaign1->id, $campaign2->id],
            Campaign::forViticulturist($viticulturist1->id)->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$campaign1->id, $campaign3->id],
            Campaign::forYear(2025)->pluck('id')->sort()->values()->all()
        );
    }

    public function test_activate_makes_only_one_active_per_viticulturist(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $campaign1 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2024,
            'active' => false,
        ]);

        $campaign2 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2025,
            'active' => true,
        ]);

        $campaign1->activate();

        $this->assertTrue($campaign1->fresh()->active);
        $this->assertFalse($campaign2->fresh()->active);
    }

    public function test_get_or_create_active_for_year_creates_new_campaign_when_none_exists(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $campaign = Campaign::getOrCreateActiveForYear($viticulturist->id, 2030);

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals(2030, $campaign->year);
        $this->assertEquals($viticulturist->id, $campaign->viticulturist_id);
        $this->assertTrue($campaign->active);

        $this->assertEquals(1, Campaign::forViticulturist($viticulturist->id)->count());
    }

    public function test_get_or_create_active_for_year_returns_existing_active_campaign(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $existing = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2026,
            'active' => true,
        ]);

        $campaign = Campaign::getOrCreateActiveForYear($viticulturist->id, 2026);

        $this->assertEquals($existing->id, $campaign->id);
        $this->assertTrue($campaign->active);
    }

    public function test_get_or_create_active_for_year_activates_existing_inactive_campaign(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $existing = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2027,
            'active' => false,
        ]);

        $campaign = Campaign::getOrCreateActiveForYear($viticulturist->id, 2027);

        $this->assertEquals($existing->id, $campaign->id);
        $this->assertTrue($campaign->fresh()->active);
    }
}


