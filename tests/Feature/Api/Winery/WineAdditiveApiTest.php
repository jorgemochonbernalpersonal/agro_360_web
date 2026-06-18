<?php

namespace Tests\Feature\Api\Winery;

use App\Models\User;
use App\Models\Wine;
use App\Models\WineAdditive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WineAdditiveApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/additives')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/additives')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_own_additives_only(): void
    {
        $wine = $this->makeWine();
        $this->makeAdditive($wine);

        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $otherWine = $this->makeWine($otherWinery);
        $this->makeAdditive($otherWine);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/additives')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_additive(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/additives', [
                'wine_id' => $wine->id,
                'additive_name' => 'Sulfito Potásico',
                'quantity' => 5.5,
                'application_date' => '2026-06-01',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_additives', [
            'wine_id' => $wine->id,
            'additive_name' => 'Sulfito Potásico',
        ]);
    }

    public function test_store_rejects_foreign_wine_with_404(): void
    {
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $foreignWine = $this->makeWine($otherWinery);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/additives', [
                'wine_id' => $foreignWine->id,
                'additive_name' => 'Hack',
                'quantity' => 1,
                'application_date' => '2026-06-01',
            ])
            ->assertStatus(404);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/additives', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['wine_id', 'additive_name', 'quantity', 'application_date']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_own_additive(): void
    {
        $additive = $this->makeAdditive($this->makeWine());

        $this->api($this->winery)
            ->getJson("/api/v1/winery/additives/{$additive->id}")
            ->assertStatus(200);
    }

    public function test_show_other_winery_additive_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $additive = $this->makeAdditive($this->makeWine($other));

        $this->api($this->winery)
            ->getJson("/api/v1/winery/additives/{$additive->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_additive(): void
    {
        $additive = $this->makeAdditive($this->makeWine());

        $this->api($this->winery)
            ->putJson("/api/v1/winery/additives/{$additive->id}", ['additive_name' => 'Metabisulfito'])
            ->assertStatus(200);

        $this->assertDatabaseHas('wine_additives', ['id' => $additive->id, 'additive_name' => 'Metabisulfito']);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_additive(): void
    {
        $additive = $this->makeAdditive($this->makeWine());

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/additives/{$additive->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('wine_additives', ['id' => $additive->id]);
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
            'volume_liters' => 1000,
            'status' => 'in_progress',
        ]);
    }

    private function makeAdditive(Wine $wine): WineAdditive
    {
        return WineAdditive::create([
            'wine_id' => $wine->id,
            'additive_name' => 'Sulfito Test',
            'quantity' => 5,
            'application_date' => '2026-06-01',
            'created_by' => $wine->user_id,
        ]);
    }
}
