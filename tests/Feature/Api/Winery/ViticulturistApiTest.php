<?php

namespace Tests\Feature\Api\Winery;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ViticulturistApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/viticulturists')->assertStatus(401);
    }

    public function test_viticulturist_role_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/viticulturists')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_only_linked_viticulturists(): void
    {
        $linked = $this->linkViticulturist();
        $unlinked = User::factory()->viticulturist()->create(['can_login' => true]);

        $response = $this->api($this->winery)
            ->getJson('/api/v1/winery/viticulturists')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($linked->id));
        $this->assertFalse($ids->contains($unlinked->id));
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_and_links_viticulturist(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/viticulturists', ['name' => 'Juan Viticultor'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Juan Viticultor');

        $this->assertDatabaseHas('users', ['name' => 'Juan Viticultor', 'role' => 'viticulturist']);
        $newVit = User::where('name', 'Juan Viticultor')->firstOrFail();
        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $newVit->id,
        ]);
    }

    public function test_store_validates_required_name(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/viticulturists', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_linked_viticulturist(): void
    {
        $vit = $this->linkViticulturist();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/viticulturists/{$vit->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $vit->id);
    }

    public function test_show_unlinked_viticulturist_returns_404(): void
    {
        $unlinked = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/viticulturists/{$unlinked->id}")
            ->assertStatus(404);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_own_viticulturist(): void
    {
        $vit = $this->linkViticulturist(source: WineryViticulturist::SOURCE_OWN);

        $this->api($this->winery)
            ->putJson("/api/v1/winery/viticulturists/{$vit->id}", ['name' => 'Nombre Actualizado'])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $vit->id, 'name' => 'Nombre Actualizado']);
    }

    // ─── search ───────────────────────────────────────────────────────────────

    public function test_search_returns_matching_viticulturists(): void
    {
        $vit = User::factory()->viticulturist()->create(['name' => 'María García', 'can_login' => true]);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/viticulturists/search?q=Gar')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $vit->id]);
    }

    public function test_search_excludes_already_linked(): void
    {
        $linked = $this->linkViticulturist();

        $response = $this->api($this->winery)
            ->getJson('/api/v1/winery/viticulturists/search?q=Viticultor')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($linked->id));
    }

    // ─── invite ───────────────────────────────────────────────────────────────

    public function test_invite_sends_notification(): void
    {
        Notification::fake();

        $vit = $this->linkViticulturist();

        $this->api($this->winery)
            ->postJson("/api/v1/winery/viticulturists/{$vit->id}/invite", [
                'email' => 'viticultor@example.com',
            ])
            ->assertStatus(200);

        Notification::assertSentTo($vit, \App\Notifications\ViticulturistInvitationNotification::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function linkViticulturist(?User $winery = null, string $source = WineryViticulturist::SOURCE_OWN): User
    {
        $winery = $winery ?? $this->winery;
        $vit = User::factory()->viticulturist()->create(['name' => 'Viticultor Test', 'can_login' => true]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $vit->id,
            'source' => $source,
            'assigned_by' => $winery->id,
        ]);

        return $vit;
    }
}
