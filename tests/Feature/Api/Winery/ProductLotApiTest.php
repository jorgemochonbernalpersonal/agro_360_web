<?php

namespace Tests\Feature\Api\Winery;

use App\Models\ProductLot;
use App\Models\User;
use App\Models\Wine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductLotApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/product-lots')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/product-lots')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_own_lots_only(): void
    {
        $this->makeLot();
        $other = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
        $this->makeLot($other);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/product-lots')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_lot(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/product-lots', $this->validPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_lots', [
            'user_id' => $this->winery->id,
            'name' => 'Lote Test 2024',
            'initial_quantity' => 1200,
            'available_quantity' => 1200,
            'reserved_quantity' => 0,
        ]);
    }

    public function test_store_rejects_foreign_wine_with_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
        $foreignWine = $this->makeWine($other);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/product-lots', $this->validPayload(['wine_id' => $foreignWine->id]))
            ->assertStatus(404);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/product-lots', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'quantity', 'unit']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_own_lot(): void
    {
        $lot = $this->makeLot();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/product-lots/{$lot->id}")
            ->assertStatus(200);
    }

    public function test_show_other_winery_lot_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true, 'is_beta_user' => true]);
        $lot = $this->makeLot($other);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/product-lots/{$lot->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_lot(): void
    {
        $lot = $this->makeLot();

        $this->api($this->winery)
            ->putJson("/api/v1/winery/product-lots/{$lot->id}", ['name' => 'Lote Actualizado'])
            ->assertStatus(200);

        $this->assertDatabaseHas('wine_lots', ['id' => $lot->id, 'name' => 'Lote Actualizado']);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_archives_lot(): void
    {
        $lot = $this->makeLot();

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/product-lots/{$lot->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('wine_lots', ['id' => $lot->id, 'archived' => true]);
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

    private function makeLot(?User $owner = null): ProductLot
    {
        return ProductLot::create([
            'user_id' => ($owner ?? $this->winery)->id,
            'name' => 'Lote Test 2024',
            'unit' => 'botellas',
            'quantity' => 1200,
            'initial_quantity' => 1200,
            'available_quantity' => 1200,
            'reserved_quantity' => 0,
            'sold_quantity' => 0,
            'archived' => false,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Lote Test 2024',
            'unit' => 'botellas',
            'quantity' => 1200,
        ], $overrides);
    }
}
