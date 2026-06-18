<?php

namespace Tests\Feature\Api\Winery;

use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WineProcessApiTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;
    private UnitOfMeasurement $uom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = User::factory()->winery()->create(['can_login' => true]);
        $this->uom = UnitOfMeasurement::where('symbol', 'L')->firstOrFail();
    }

    // ─── Autenticación / autorización ─────────────────────────────────────────

    public function test_transfers_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/transfers')->assertStatus(401);
    }

    public function test_losses_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/losses')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/transfers')->assertStatus(403);
        $this->api($vit)->getJson('/api/v1/winery/losses')->assertStatus(403);
    }

    // ─── index transfers ──────────────────────────────────────────────────────

    public function test_index_transfers_returns_own_only(): void
    {
        [$wine, $from, $to] = $this->makeWineAndContainers();
        $this->makeTransfer($wine, $from, $to);

        $other = User::factory()->winery()->create(['can_login' => true]);
        [$otherWine, $otherFrom, $otherTo] = $this->makeWineAndContainers($other);
        $this->makeTransfer($otherWine, $otherFrom, $otherTo);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/transfers')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store transfer ───────────────────────────────────────────────────────

    public function test_store_transfer_creates_record(): void
    {
        [$wine, $from, $to] = $this->makeWineAndContainers();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/transfers', [
                'wine_id' => $wine->id,
                'from_container_id' => $from->id,
                'to_container_id' => $to->id,
                'quantity' => 100,
                'transfer_type' => 'racking',
                'transfer_date' => '2026-06-10',
                'unit_of_measurement_id' => $this->uom->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_transfers', [
            'wine_id' => $wine->id,
            'from_container_id' => $from->id,
            'to_container_id' => $to->id,
        ]);
    }

    public function test_store_transfer_rejects_foreign_wine_with_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        [$foreignWine, $from, $to] = $this->makeWineAndContainers($other);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/transfers', [
                'wine_id' => $foreignWine->id,
                'from_container_id' => $from->id,
                'to_container_id' => $to->id,
                'quantity' => 100,
                'transfer_type' => 'racking',
                'transfer_date' => '2026-06-10',
            ])
            ->assertStatus(404);
    }

    public function test_store_transfer_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/transfers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['wine_id', 'from_container_id', 'to_container_id', 'quantity', 'transfer_type', 'transfer_date']);
    }

    // ─── destroy transfer ─────────────────────────────────────────────────────

    public function test_destroy_transfer_deletes_record(): void
    {
        [$wine, $from, $to] = $this->makeWineAndContainers();
        $transfer = $this->makeTransfer($wine, $from, $to);

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/transfers/{$transfer->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('wine_transfers', ['id' => $transfer->id]);
    }

    // ─── index losses ─────────────────────────────────────────────────────────

    public function test_index_losses_returns_own_only(): void
    {
        [$wine, $container] = $this->makeWineAndContainer();
        $this->makeLoss($wine, $container);

        $other = User::factory()->winery()->create(['can_login' => true]);
        [$otherWine, $otherContainer] = $this->makeWineAndContainer($other);
        $this->makeLoss($otherWine, $otherContainer);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/losses')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ─── store loss ───────────────────────────────────────────────────────────

    public function test_store_loss_creates_record(): void
    {
        [$wine, $container] = $this->makeWineAndContainer();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/losses', [
                'wine_id' => $wine->id,
                'container_id' => $container->id,
                'quantity' => 50,
                'loss_type' => 'evaporation',
                'loss_authorization' => 'authorized',
                'unit_of_measurement_id' => $this->uom->id,
                'loss_date' => '2026-06-10',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_losses', [
            'wine_id' => $wine->id,
            'container_id' => $container->id,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function makeWineAndContainers(?User $owner = null): array
    {
        $owner = $owner ?? $this->winery;
        $wine = Wine::create([
            'user_id' => $owner->id,
            'name' => 'Vino Test',
            'vintage' => 2024,
            'wine_type' => 'red',
            'volume_liters' => 2000,
            'status' => 'in_progress',
        ]);
        $from = Container::factory()->create([
            'user_id' => $owner->id,
            'used_capacity' => 500,
            'capacity' => 1000,
            'wine_volume_liters' => 500,
        ]);
        $to = Container::factory()->create([
            'user_id' => $owner->id,
            'used_capacity' => 0,
            'capacity' => 1000,
            'wine_volume_liters' => 0,
        ]);

        return [$wine, $from, $to];
    }

    private function makeWineAndContainer(?User $owner = null): array
    {
        $owner = $owner ?? $this->winery;
        $wine = Wine::create([
            'user_id' => $owner->id,
            'name' => 'Vino Test',
            'vintage' => 2024,
            'wine_type' => 'red',
            'volume_liters' => 1000,
            'status' => 'in_progress',
        ]);
        $container = Container::factory()->create([
            'user_id' => $owner->id,
            'used_capacity' => 500,
            'capacity' => 1000,
            'wine_volume_liters' => 500,
        ]);

        return [$wine, $container];
    }

    private function makeTransfer(Wine $wine, Container $from, Container $to): WineTransfer
    {
        return WineTransfer::create([
            'wine_id' => $wine->id,
            'from_container_id' => $from->id,
            'to_container_id' => $to->id,
            'quantity' => 50,
            'unit_of_measurement_id' => $this->uom->id,
            'transfer_type' => 'racking',
            'transfer_date' => '2026-06-01',
            'created_by' => $wine->user_id,
        ]);
    }

    private function makeLoss(Wine $wine, Container $container): WineLoss
    {
        return WineLoss::create([
            'wine_id' => $wine->id,
            'container_id' => $container->id,
            'quantity' => 10,
            'unit_of_measurement_id' => $this->uom->id,
            'loss_type' => 'evaporation',
            'loss_authorization' => 'authorized',
            'loss_date' => '2026-06-01',
            'created_by' => $wine->user_id,
        ]);
    }
}
