<?php

namespace Tests\Feature\Api\Winery;

use App\Models\User;
use App\Models\Wine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WineApiTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = User::factory()->winery()->create(['can_login' => true]);
    }

    // ─── Autenticación / autorización ─────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/wines')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/wines')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_own_wines_only(): void
    {
        $this->makeWine();
        $other = User::factory()->winery()->create(['can_login' => true]);
        $this->makeWine($other);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/wines')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_wine(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/wines', $this->validPayload())
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('wines', [
            'user_id' => $this->winery->id,
            'name' => 'Tempranillo Test',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/wines', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'vintage', 'wine_type', 'volume_liters']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_own_wine(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/wines/{$wine->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $wine->id);
    }

    public function test_show_other_winery_wine_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $wine = $this->makeWine($other);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/wines/{$wine->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_wine(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->putJson("/api/v1/winery/wines/{$wine->id}", ['name' => 'Garnacha Reserva'])
            ->assertStatus(200);

        $this->assertDatabaseHas('wines', ['id' => $wine->id, 'name' => 'Garnacha Reserva']);
    }

    public function test_update_other_winery_wine_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $wine = $this->makeWine($other);

        $this->api($this->winery)
            ->putJson("/api/v1/winery/wines/{$wine->id}", ['name' => 'Hack'])
            ->assertStatus(404);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_cancels_wine(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/wines/{$wine->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('wines', ['id' => $wine->id, 'status' => 'cancelled']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function makeWine(?User $owner = null): Wine
    {
        return Wine::create([
            'user_id' => ($owner ?? $this->winery)->id,
            'name' => 'Tempranillo Test',
            'vintage' => 2024,
            'wine_type' => 'red',
            'volume_liters' => 1000,
            'status' => 'in_progress',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tempranillo Test',
            'vintage' => 2024,
            'wine_type' => 'red',
            'volume_liters' => 1000,
        ], $overrides);
    }
}
