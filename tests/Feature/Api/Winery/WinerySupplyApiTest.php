<?php

namespace Tests\Feature\Api\Winery;

use App\Models\User;
use App\Models\WinerySupply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WinerySupplyApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/supplies')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/supplies')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_own_supplies_only(): void
    {
        $this->makeSupply();
        $other = User::factory()->winery()->create(['can_login' => true]);
        $this->makeSupply($other);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/supplies')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_supply(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/supplies', ['name' => 'Levadura EC1118', 'supply_type' => 'yeast'])
            ->assertStatus(201);

        $this->assertDatabaseHas('winery_supplies', [
            'user_id' => $this->winery->id,
            'name' => 'Levadura EC1118',
            'supply_type' => 'yeast',
            'active' => true,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/supplies', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'supply_type']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_own_supply(): void
    {
        $supply = $this->makeSupply();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/supplies/{$supply->id}")
            ->assertStatus(200);
    }

    public function test_show_other_winery_supply_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $supply = $this->makeSupply($other);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/supplies/{$supply->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_supply(): void
    {
        $supply = $this->makeSupply();

        $this->api($this->winery)
            ->putJson("/api/v1/winery/supplies/{$supply->id}", ['name' => 'Levadura Activada'])
            ->assertStatus(200);

        $this->assertDatabaseHas('winery_supplies', ['id' => $supply->id, 'name' => 'Levadura Activada']);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_deactivates_supply(): void
    {
        $supply = $this->makeSupply();

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/supplies/{$supply->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('winery_supplies', ['id' => $supply->id, 'active' => false]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function makeSupply(?User $owner = null): WinerySupply
    {
        return WinerySupply::create([
            'user_id' => ($owner ?? $this->winery)->id,
            'name' => 'Insumo Test',
            'supply_type' => 'yeast',
            'active' => true,
        ]);
    }
}
