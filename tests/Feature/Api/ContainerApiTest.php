<?php

namespace Tests\Feature\Api;

use App\Models\Container;
use App\Models\User;
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
            'id'   => $container->id,
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
}
