<?php

namespace Tests\Feature;

use App\Models\AgriculturalActivity;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Wine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura de las Policies de los modelos de bodega/facturación
 * (Wine, Container, Invoice, Harvest), añadidas como defensa en profundidad.
 *
 * Verifica además que Laravel las auto-descubre por convención
 * (Modelo => App\Policies\ModeloPolicy), sin registro manual.
 */
class CellarOwnershipPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $other;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'winery']);
        $this->other = User::factory()->create(['role' => 'winery']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ── Wine (ownership por user_id) ──────────────────────────────────────────

    public function test_wine_owner_can_view_and_update_other_cannot(): void
    {
        $wine = Wine::create(['user_id' => $this->owner->id, 'name' => 'Tempranillo 2021']);

        $this->assertTrue($this->owner->can('view', $wine));
        $this->assertTrue($this->owner->can('update', $wine));
        $this->assertFalse($this->other->can('view', $wine));
        $this->assertFalse($this->other->can('update', $wine));
        $this->assertTrue($this->admin->can('view', $wine));
    }

    // ── Container (ownership por user_id) ─────────────────────────────────────

    public function test_container_owner_can_view_and_update_other_cannot(): void
    {
        $container = Container::factory()->create(['user_id' => $this->owner->id]);

        $this->assertTrue($this->owner->can('view', $container));
        $this->assertTrue($this->owner->can('update', $container));
        $this->assertFalse($this->other->can('view', $container));
        $this->assertFalse($this->other->can('update', $container));
        $this->assertTrue($this->admin->can('view', $container));
    }

    // ── Invoice (ownership por user_id) ───────────────────────────────────────

    public function test_invoice_owner_can_view_and_update_other_cannot(): void
    {
        $invoice = Invoice::factory()->create(['user_id' => $this->owner->id]);

        $this->assertTrue($this->owner->can('view', $invoice));
        $this->assertTrue($this->owner->can('update', $invoice));
        $this->assertFalse($this->other->can('view', $invoice));
        $this->assertFalse($this->other->can('update', $invoice));
        $this->assertTrue($this->admin->can('view', $invoice));
    }

    // ── Harvest (visibilidad dual: bodega via winery_id, viticultor via activity) ──

    public function test_harvest_winery_owner_can_view_via_winery_id(): void
    {
        $harvest = Harvest::factory()->create(['winery_id' => $this->owner->id]);

        $this->assertTrue($this->owner->can('view', $harvest));
        $this->assertTrue($this->owner->can('update', $harvest));
        $this->assertFalse($this->other->can('view', $harvest));
        $this->assertTrue($this->admin->can('view', $harvest));
    }

    public function test_harvest_grower_can_view_via_activity_viticulturist(): void
    {
        $grower = User::factory()->create(['role' => 'viticulturist']);
        $activity = AgriculturalActivity::factory()->create(['viticulturist_id' => $grower->id]);
        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'winery_id' => null,
        ]);

        $this->assertTrue($grower->can('view', $harvest));
        $this->assertTrue($grower->can('update', $harvest));
        // Otro viticultor sin relación no puede.
        $stranger = User::factory()->create(['role' => 'viticulturist']);
        $this->assertFalse($stranger->can('view', $harvest));
    }
}
