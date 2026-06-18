<?php

namespace Tests\Feature\Api\Winery;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/suppliers')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/suppliers')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_own_suppliers_only(): void
    {
        $this->makeSupplier();
        $other = User::factory()->winery()->create(['can_login' => true]);
        $this->makeSupplier($other);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/suppliers')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_supplier(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/suppliers', ['name' => 'Proveedor Envases SL', 'category' => 'packaging'])
            ->assertStatus(201);

        $this->assertDatabaseHas('suppliers', [
            'user_id' => $this->winery->id,
            'name' => 'Proveedor Envases SL',
            'active' => true,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/suppliers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_own_supplier(): void
    {
        $supplier = $this->makeSupplier();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/suppliers/{$supplier->id}")
            ->assertStatus(200);
    }

    public function test_show_other_winery_supplier_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $supplier = $this->makeSupplier($other);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/suppliers/{$supplier->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_supplier(): void
    {
        $supplier = $this->makeSupplier();

        $this->api($this->winery)
            ->putJson("/api/v1/winery/suppliers/{$supplier->id}", ['name' => 'Nuevo Nombre SL'])
            ->assertStatus(200);

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Nuevo Nombre SL']);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_deactivates_supplier(): void
    {
        $supplier = $this->makeSupplier();

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/suppliers/{$supplier->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'active' => false]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function makeSupplier(?User $owner = null): Supplier
    {
        return Supplier::create([
            'user_id' => ($owner ?? $this->winery)->id,
            'name' => 'Proveedor Test',
            'category' => 'packaging',
            'active' => true,
        ]);
    }
}
