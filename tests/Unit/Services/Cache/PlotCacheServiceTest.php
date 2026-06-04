<?php

namespace Tests\Unit\Services\Cache;

use App\Models\Plot;
use App\Models\User;
use App\Services\Cache\PlotCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\WithGeographyData;

class PlotCacheServiceTest extends TestCase
{
    use RefreshDatabase, WithGeographyData;

    protected PlotCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createGeographyData();
        $this->service = new PlotCacheService;
        Cache::flush();
    }

    public function test_can_cache_user_plots(): void
    {
        $user = User::factory()->create();
        Plot::factory()->count(5)->create(['viticulturist_id' => $user->id, 'active' => true]);

        $plots1 = $this->service->getUserPlots($user);
        $plots2 = $this->service->getUserPlots($user);

        $this->assertEquals(5, $plots1->count());
        $this->assertEquals($plots1->count(), $plots2->count());
    }

    public function test_only_returns_active_plots(): void
    {
        $user = User::factory()->create();
        Plot::factory()->count(3)->create(['viticulturist_id' => $user->id, 'active' => true]);
        Plot::factory()->count(2)->create(['viticulturist_id' => $user->id, 'active' => false]);

        $plots = $this->service->getUserPlots($user);

        $this->assertEquals(3, $plots->count());
    }

    public function test_can_cache_plot_details(): void
    {
        $user = User::factory()->create();
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);

        $details1 = $this->service->getPlotDetails($plot);
        $details2 = $this->service->getPlotDetails($plot);

        $this->assertEquals($details1->id, $details2->id);
        $this->assertTrue($details1->relationLoaded('viticulturist'));
    }

    public function test_can_cache_plot_stats(): void
    {
        $user = User::factory()->create();
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);

        $stats = $this->service->getPlotStats($plot);

        $this->assertArrayHasKey('total_activities', $stats);
        $this->assertArrayHasKey('total_area', $stats);
        $this->assertArrayHasKey('plantings_count', $stats);
    }

    public function test_invalidate_plot_clears_cache(): void
    {
        $user = User::factory()->create();
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);

        // Cachear
        $this->service->getPlotDetails($plot);
        $this->service->getPlotStats($plot);

        // Verificar cache
        $this->assertTrue(Cache::has("plot_{$plot->id}_details"));
        $this->assertTrue(Cache::has("plot_{$plot->id}_stats"));

        // Invalidar
        $this->service->invalidatePlot($plot);

        // Verificar que no existe
        $this->assertFalse(Cache::has("plot_{$plot->id}_details"));
        $this->assertFalse(Cache::has("plot_{$plot->id}_stats"));
    }

    public function test_invalidate_user_plots_clears_user_cache(): void
    {
        $user = User::factory()->create();
        Plot::factory()->count(3)->create(['viticulturist_id' => $user->id]);

        // Cachear
        $this->service->getUserPlots($user);

        // Invalidar
        $this->service->invalidateUserPlots($user);

        // Nueva llamada debe recalcular
        $plots = $this->service->getUserPlots($user);
        $this->assertEquals(3, $plots->count());
    }

    public function test_warm_up_cache_preloads_data(): void
    {
        $user = User::factory()->create();
        Plot::factory()->count(5)->create(['viticulturist_id' => $user->id]);

        // Precalentar
        $this->service->warmUpUserCache($user);

        // Debe estar en cache ahora
        $plots = $this->service->getUserPlots($user, ['plantings', 'province', 'municipality']);

        $this->assertEquals(5, $plots->count());
    }
}
