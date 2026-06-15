<?php

namespace Tests\Feature\Api;

use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\ContainerRoom;
use App\Models\User;
use App\Models\Wine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->winery()->create(['can_login' => true]);
    }

    // ─── GET /winery/containers ───────────────────────────────────────────────

    public function test_index_returns_containers_with_meta(): void
    {
        Container::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/winery/containers')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'total_capacity', 'total_used'],
            ])
            ->assertJsonPath('meta.total', 3);
    }

    public function test_index_does_not_return_archived_containers(): void
    {
        Container::factory()->count(2)->create(['user_id' => $this->user->id]);
        Container::factory()->archived()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/winery/containers')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_does_not_return_other_user_containers(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        Container::factory()->count(2)->create(['user_id' => $this->user->id]);
        Container::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/winery/containers')
            ->assertJsonPath('meta.total', 2);
    }

    // ─── GET /winery/containers/{id} ──────────────────────────────────────────

    public function test_show_returns_container(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/winery/containers/{$container->id}")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name']]);
    }

    public function test_show_other_user_container_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $container = Container::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/winery/containers/{$container->id}")
            ->assertStatus(404);
    }

    // ─── PUT /winery/containers/{id} ──────────────────────────────────────────

    public function test_update_changes_container_name(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id, 'name' => 'Cuba 1']);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/winery/containers/{$container->id}", ['name' => 'Depósito Principal'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Depósito Principal');

        $this->assertDatabaseHas('containers', [
            'id' => $container->id,
            'name' => 'Depósito Principal',
        ]);
    }

    public function test_update_validates_name_max_length(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/winery/containers/{$container->id}", [
                'name' => str_repeat('a', 256),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_other_user_container_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $container = Container::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/winery/containers/{$container->id}", ['name' => 'Hack'])
            ->assertStatus(404);
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/containers')->assertStatus(401);
    }

    public function test_requires_winery_role(): void
    {
        $viticulturist = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->actingAs($viticulturist, 'sanctum')
            ->getJson('/api/v1/winery/containers')
            ->assertStatus(403);
    }

    // ─── POST /winery/containers ──────────────────────────────────────────────

    public function test_store_creates_container(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/containers', [
                'name' => 'Depósito 1',
                'capacity' => 5000,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Depósito 1');

        $this->assertDatabaseHas('containers', [
            'user_id' => $this->user->id,
            'name' => 'Depósito 1',
            'capacity' => 5000,
        ]);
    }

    public function test_store_requires_name(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/containers', ['capacity' => 1000])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_capacity(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/containers', ['name' => 'Cuba'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['capacity']);
    }

    public function test_store_rejects_zero_capacity(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/containers', ['name' => 'Cuba', 'capacity' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['capacity']);
    }

    // ─── DELETE /winery/containers/{id} ──────────────────────────────────────

    public function test_destroy_archives_container(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);

        $this->actingAsWinery()
            ->deleteJson("/api/v1/winery/containers/{$container->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('containers', ['id' => $container->id, 'archived' => true]);
    }

    public function test_destroy_other_user_container_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $container = Container::factory()->create(['user_id' => $other->id]);

        $this->actingAsWinery()
            ->deleteJson("/api/v1/winery/containers/{$container->id}")
            ->assertStatus(404);
    }

    // ─── Container Rooms (/winery/container-rooms) ───────────────────────────

    public function test_rooms_index_returns_own_rooms(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        ContainerRoom::create(['user_id' => $this->user->id, 'name' => 'Nave A']);
        ContainerRoom::create(['user_id' => $other->id, 'name' => 'Nave B']);

        $this->actingAsWinery()
            ->getJson('/api/v1/winery/container-rooms')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_rooms_store_creates_room(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/container-rooms', ['name' => 'Bodega Norte'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Bodega Norte');

        $this->assertDatabaseHas('container_rooms', [
            'user_id' => $this->user->id,
            'name' => 'Bodega Norte',
        ]);
    }

    public function test_rooms_store_requires_name(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/container-rooms', ['description' => 'Sin nombre'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_rooms_store_rejects_out_of_range_temperature(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/container-rooms', [
                'name' => 'Sala Fría',
                'temperature' => 99,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['temperature']);
    }

    public function test_rooms_update_changes_name(): void
    {
        $room = ContainerRoom::create(['user_id' => $this->user->id, 'name' => 'Nave Vieja']);

        $this->actingAsWinery()
            ->putJson("/api/v1/winery/container-rooms/{$room->id}", ['name' => 'Nave Nueva'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Nave Nueva');
    }

    public function test_rooms_show_other_user_room_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $room = ContainerRoom::create(['user_id' => $other->id, 'name' => 'Ajena']);

        $this->actingAsWinery()
            ->getJson("/api/v1/winery/container-rooms/{$room->id}")
            ->assertStatus(404);
    }

    // ─── Container Stock Entries (/winery/container-stock-entries) ───────────

    public function test_stock_entry_store_creates_entry(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);
        $wine = Wine::create([
            'user_id' => $this->user->id,
            'name' => 'Tinto Reserva',
            'vintage' => 2024,
        ]);

        $this->actingAsWinery()
            ->postJson('/api/v1/winery/container-stock-entries', [
                'wine_id' => $wine->id,
                'container_id' => $container->id,
                'quantity_liters' => 1500.0,
                'entry_date' => '2026-01-10',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_container_stock_entries', [
            'container_id' => $container->id,
            'quantity_liters' => 1500.0,
        ]);
    }

    public function test_stock_entry_requires_wine_id(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);

        $this->actingAsWinery()
            ->postJson('/api/v1/winery/container-stock-entries', [
                'container_id' => $container->id,
                'quantity_liters' => 100,
                'entry_date' => '2026-01-10',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['wine_id']);
    }

    public function test_stock_entry_requires_quantity(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);
        $wine = Wine::create(['user_id' => $this->user->id, 'name' => 'Blanco', 'vintage' => 2024]);

        $this->actingAsWinery()
            ->postJson('/api/v1/winery/container-stock-entries', [
                'wine_id' => $wine->id,
                'container_id' => $container->id,
                'entry_date' => '2026-01-10',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity_liters']);
    }

    // ─── Container Maintenances (/winery/maintenances) ───────────────────────

    public function test_maintenance_store_creates_record(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);

        $this->actingAsWinery()
            ->postJson('/api/v1/winery/maintenances', [
                'container_id' => $container->id,
                'maintenance_type' => 'cleaning',
                'maintenance_name' => 'Limpieza trimestral',
                'scheduled_date' => '2026-07-01',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('container_maintenances', [
            'container_id' => $container->id,
            'maintenance_type' => 'cleaning',
            'maintenance_name' => 'Limpieza trimestral',
        ]);
    }

    public function test_maintenance_store_requires_container_id(): void
    {
        $this->actingAsWinery()
            ->postJson('/api/v1/winery/maintenances', [
                'maintenance_type' => 'cleaning',
                'maintenance_name' => 'Limpieza',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['container_id']);
    }

    public function test_maintenance_store_rejects_invalid_type(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);

        $this->actingAsWinery()
            ->postJson('/api/v1/winery/maintenances', [
                'container_id' => $container->id,
                'maintenance_type' => 'invalid_type',
                'maintenance_name' => 'Test',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['maintenance_type']);
    }

    public function test_maintenance_update_changes_status(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);
        $maintenance = ContainerMaintenance::create([
            'container_id' => $container->id,
            'maintenance_type' => 'inspection',
            'maintenance_name' => 'Inspección anual',
            'scheduled_date' => '2026-07-01',
            'status' => 'scheduled',
        ]);

        $this->actingAsWinery()
            ->putJson("/api/v1/winery/maintenances/{$maintenance->id}", [
                'status' => 'completed',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('container_maintenances', [
            'id' => $maintenance->id,
            'status' => 'completed',
        ]);
    }

    public function test_maintenance_update_rejects_invalid_status(): void
    {
        $container = Container::factory()->create(['user_id' => $this->user->id]);
        $maintenance = ContainerMaintenance::create([
            'container_id' => $container->id,
            'maintenance_type' => 'cleaning',
            'maintenance_name' => 'Limpieza',
            'scheduled_date' => '2026-07-01',
        ]);

        $this->actingAsWinery()
            ->putJson("/api/v1/winery/maintenances/{$maintenance->id}", [
                'status' => 'invalid_status',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    private function actingAsWinery()
    {
        return $this->actingAs($this->user, 'sanctum');
    }
}
