<?php

namespace Tests\Feature\Api;

use App\Models\Plot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlotApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->viticulturist()->create(['can_login' => true]);
    }

    // ─── GET /viticulturist/plots ─────────────────────────────────────────────

    public function test_index_returns_plots_with_meta(): void
    {
        Plot::factory()->count(3)->forViticulturist($this->user)->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page', 'total_area', 'organic_area'],
            ])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_index_paginates_results(): void
    {
        Plot::factory()->count(5)->forViticulturist($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots?per_page=2')
            ->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(5, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    public function test_index_search_filters_by_name(): void
    {
        Plot::factory()->forViticulturist($this->user)->create(['name' => 'Finca Norte']);
        Plot::factory()->forViticulturist($this->user)->create(['name' => 'Finca Sur']);
        Plot::factory()->forViticulturist($this->user)->create(['name' => 'Parcela Este']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots?search=Finca')
            ->assertStatus(200);

        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_index_per_page_capped_at_100(): void
    {
        Plot::factory()->count(3)->forViticulturist($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots?per_page=999')
            ->assertStatus(200);

        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    public function test_index_only_returns_own_plots(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        Plot::factory()->count(2)->forViticulturist($this->user)->create();
        Plot::factory()->forViticulturist($other)->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_only_returns_active_plots(): void
    {
        Plot::factory()->count(2)->forViticulturist($this->user)->create(['active' => true]);
        Plot::factory()->forViticulturist($this->user)->create(['active' => false]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots')
            ->assertJsonPath('meta.total', 2);
    }

    // ─── GET /viticulturist/plots/{id} ────────────────────────────────────────

    public function test_show_returns_plot_details(): void
    {
        $plot = Plot::factory()->forViticulturist($this->user)->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/viticulturist/plots/{$plot->id}")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name']]);
    }

    public function test_show_other_user_plot_returns_404(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $plot = Plot::factory()->forViticulturist($other)->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/viticulturist/plots/{$plot->id}")
            ->assertStatus(404);
    }

    // ─── PUT /viticulturist/plots/{id} ────────────────────────────────────────

    public function test_update_changes_plot_name(): void
    {
        $plot = Plot::factory()->forViticulturist($this->user)->create(['name' => 'Parcela Vieja']);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/viticulturist/plots/{$plot->id}", ['name' => 'Parcela Nueva'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Parcela Nueva');

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'name' => 'Parcela Nueva',
        ]);
    }

    public function test_update_validates_name_max_length(): void
    {
        $plot = Plot::factory()->forViticulturist($this->user)->create();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/viticulturist/plots/{$plot->id}", [
                'name' => str_repeat('a', 256),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_other_user_plot_returns_404(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $plot = Plot::factory()->forViticulturist($other)->create();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/viticulturist/plots/{$plot->id}", ['name' => 'Hack'])
            ->assertStatus(404);
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/viticulturist/plots')->assertStatus(401);
    }

    public function test_requires_viticulturist_role(): void
    {
        $winery = User::factory()->winery()->create(['can_login' => true]);

        $this->actingAs($winery, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots')
            ->assertStatus(403);
    }
}
