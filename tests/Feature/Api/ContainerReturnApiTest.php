<?php

namespace Tests\Feature\Api;

use App\Models\Campaign;
use App\Models\PhytosanitaryContainerReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerReturnApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->viticulturist()->create(['can_login' => true, 'is_beta_user' => true]);
    }

    // ─── Auth y rol ───────────────────────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/viticulturist/container-returns')->assertStatus(401);
        $this->postJson('/api/v1/viticulturist/container-returns', [])->assertStatus(401);
    }

    public function test_requires_viticulturist_role(): void
    {
        $winery = User::factory()->winery()->create(['can_login' => true]);

        $this->actingAs($winery, 'sanctum')
            ->getJson('/api/v1/viticulturist/container-returns')
            ->assertStatus(403);
    }

    // ─── GET /viticulturist/container-returns ─────────────────────────────────

    public function test_index_returns_own_records(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $campaign = Campaign::factory()->forViticulturist($this->user)->create();
        $otherCampaign = Campaign::factory()->forViticulturist($other)->create();

        PhytosanitaryContainerReturn::create([...$this->returnData(), 'viticulturist_id' => $this->user->id, 'campaign_id' => $campaign->id, 'active' => true]);
        PhytosanitaryContainerReturn::create([...$this->returnData(), 'viticulturist_id' => $other->id, 'campaign_id' => $otherCampaign->id, 'active' => true]);

        $this->actingAsUser()
            ->getJson('/api/v1/viticulturist/container-returns')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    // ─── POST /viticulturist/container-returns ────────────────────────────────

    public function test_store_creates_return(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('data.product_name', 'Fungicida Test');

        $this->assertDatabaseHas('phytosanitary_container_returns', [
            'viticulturist_id' => $this->user->id,
            'product_name' => 'Fungicida Test',
            'container_type' => 'plastic',
        ]);
    }

    public function test_store_requires_date(): void
    {
        $payload = $this->payload();
        unset($payload['date']);

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_store_requires_product_name(): void
    {
        $payload = $this->payload();
        unset($payload['product_name']);

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_name']);
    }

    public function test_store_requires_container_type(): void
    {
        $payload = $this->payload();
        unset($payload['container_type']);

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['container_type']);
    }

    public function test_store_rejects_invalid_container_type(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $this->payload([
                'container_type' => 'wood',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['container_type']);
    }

    public function test_store_requires_containers_quantity(): void
    {
        $payload = $this->payload();
        unset($payload['containers_quantity']);

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['containers_quantity']);
    }

    public function test_store_requires_collection_point(): void
    {
        $payload = $this->payload();
        unset($payload['collection_point']);

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['collection_point']);
    }

    public function test_store_rejects_invalid_collection_system(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/container-returns', $this->payload([
                'collection_system' => 'invalid',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['collection_system']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'date' => '2026-05-10',
            'product_name' => 'Fungicida Test',
            'container_type' => 'plastic',
            'containers_quantity' => 3,
            'collection_point' => 'Punto SIGFITO Almería',
        ], $override);
    }

    private function returnData(): array
    {
        return [
            'date' => '2026-05-10',
            'product_name' => 'Fungicida Test',
            'container_type' => 'plastic',
            'containers_quantity' => 1,
            'collection_point' => 'Punto SIGFITO',
        ];
    }

    private function actingAsUser()
    {
        return $this->actingAs($this->user, 'sanctum');
    }
}
