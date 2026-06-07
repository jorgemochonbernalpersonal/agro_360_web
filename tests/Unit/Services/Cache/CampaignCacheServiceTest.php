<?php

namespace Tests\Unit\Services\Cache;

use App\Models\Campaign;
use App\Models\User;
use App\Services\Cache\CampaignCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\WithGeographyData;

class CampaignCacheServiceTest extends TestCase
{
    use RefreshDatabase, WithGeographyData;

    protected CampaignCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createGeographyData();
        $this->service = new CampaignCacheService;
        Cache::flush();
    }

    public function test_can_cache_campaign_stats(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        // Primera llamada - no cacheado
        $stats1 = $this->service->getStats($campaign);

        // Segunda llamada - debe venir de cache
        $stats2 = $this->service->getStats($campaign);

        $this->assertEquals($stats1, $stats2);
        $this->assertArrayHasKey('total_activities', $stats1);
        $this->assertArrayHasKey('activities_by_type', $stats1);
        $this->assertArrayHasKey('calculated_at', $stats1);
    }

    public function test_cache_key_is_unique_per_campaign(): void
    {
        $user = User::factory()->create();
        $campaign1 = Campaign::factory()->create(['viticulturist_id' => $user->id]);
        $campaign2 = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $stats1 = $this->service->getStats($campaign1);
        $stats2 = $this->service->getStats($campaign2);

        // Deben ser diferentes
        $this->assertNotEquals($stats1, $stats2);
    }

    public function test_can_invalidate_campaign_stats(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        // Cachear
        $this->service->getStats($campaign);

        // Verificar que existe en cache
        $this->assertTrue(Cache::has("campaign_{$campaign->id}_stats"));

        // Invalidar
        $this->service->invalidateStats($campaign);

        // Verificar que no existe
        $this->assertFalse(Cache::has("campaign_{$campaign->id}_stats"));
    }

    public function test_can_get_user_campaigns_cached(): void
    {
        $user = User::factory()->create(['can_login' => false]);
        Campaign::factory()->count(3)->create(['viticulturist_id' => $user->id]);

        $campaigns1 = $this->service->getUserCampaigns($user);
        $campaigns2 = $this->service->getUserCampaigns($user);

        $this->assertEquals($campaigns1->count(), $campaigns2->count());
        $this->assertEquals(3, $campaigns1->count());
    }

    public function test_invalidate_all_clears_related_caches(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        // Cachear ambos
        $this->service->getStats($campaign);
        $this->service->getUserCampaigns($user);

        // Invalidar todo
        $this->service->invalidateAll($campaign);

        // Verificar que no existen
        $this->assertFalse(Cache::has("campaign_{$campaign->id}_stats"));
        $this->assertFalse(Cache::has("user_{$user->id}_campaigns"));
    }

    public function test_stats_calculation_is_accurate(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        // Crear actividades de diferentes tipos
        $plot = \App\Models\Plot::factory()->create(['viticulturist_id' => $user->id]);

        \App\Models\AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
        ]);

        \App\Models\AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
        ]);

        $stats = $this->service->getStats($campaign);

        $this->assertEquals(2, $stats['total_activities']);
        $this->assertEquals(1, $stats['activities_by_type']['phytosanitary'] ?? 0);
        $this->assertEquals(1, $stats['activities_by_type']['irrigation'] ?? 0);
    }
}
