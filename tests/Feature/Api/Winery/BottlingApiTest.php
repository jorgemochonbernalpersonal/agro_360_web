<?php

namespace Tests\Feature\Api\Winery;

use App\Models\User;
use App\Models\Wine;
use App\Models\WineBottling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BottlingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
    }

    // ─── Autenticación / autorización ─────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/bottlings')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/bottlings')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_own_bottlings_only(): void
    {
        $this->makeBottling($this->makeWine());
        $other = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
        $this->makeBottling($this->makeWine($other), $other);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/bottlings')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_bottling(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/bottlings', $this->validPayload($wine))
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_bottlings', [
            'user_id' => $this->winery->id,
            'wine_id' => $wine->id,
            'quantity_bottles' => 1000,
        ]);
    }

    public function test_store_rejects_foreign_wine_with_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
        $foreignWine = $this->makeWine($other);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/bottlings', $this->validPayload($foreignWine))
            ->assertStatus(404);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/bottlings', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['wine_id', 'bottling_date', 'bottle_format', 'quantity_bottles', 'quantity_liters']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_own_bottling(): void
    {
        $bottling = $this->makeBottling($this->makeWine());

        $this->api($this->winery)
            ->getJson("/api/v1/winery/bottlings/{$bottling->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $bottling->id);
    }

    public function test_show_other_winery_bottling_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
        $bottling = $this->makeBottling($this->makeWine($other), $other);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/bottlings/{$bottling->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_bottling(): void
    {
        $bottling = $this->makeBottling($this->makeWine());

        $this->api($this->winery)
            ->putJson("/api/v1/winery/bottlings/{$bottling->id}", ['lot_number' => 'L-001-2026'])
            ->assertStatus(200);

        $this->assertDatabaseHas('wine_bottlings', ['id' => $bottling->id, 'lot_number' => 'L-001-2026']);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_bottling(): void
    {
        $bottling = $this->makeBottling($this->makeWine());

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/bottlings/{$bottling->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('wine_bottlings', ['id' => $bottling->id]);
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
            'name' => 'Vino Test',
            'vintage' => 2024,
            'wine_type' => 'red',
            'volume_liters' => 5000,
            'status' => 'in_progress',
        ]);
    }

    private function makeBottling(Wine $wine, ?User $owner = null): WineBottling
    {
        return WineBottling::create([
            'user_id' => ($owner ?? $this->winery)->id,
            'wine_id' => $wine->id,
            'bottling_date' => '2026-06-01',
            'bottle_format' => '750',
            'quantity_bottles' => 1000,
            'quantity_liters' => 750,
            'created_by' => ($owner ?? $this->winery)->id,
        ]);
    }

    private function validPayload(Wine $wine, array $overrides = []): array
    {
        return array_merge([
            'wine_id' => $wine->id,
            'bottling_date' => '2026-06-01',
            'bottle_format' => '750',
            'quantity_bottles' => 1000,
            'quantity_liters' => 750,
        ], $overrides);
    }
}
