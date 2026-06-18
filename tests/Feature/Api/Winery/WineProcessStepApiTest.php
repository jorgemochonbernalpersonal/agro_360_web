<?php

namespace Tests\Feature\Api\Winery;

use App\Models\User;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WineProcessStepApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/wines/1/process')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);
        $wine = $this->makeWine();

        $this->api($vit)->getJson("/api/v1/winery/wines/{$wine->id}/process")->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_steps_for_own_wine(): void
    {
        $wine = $this->makeWine();
        $this->makeStep($wine);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/wines/{$wine->id}/process")
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_other_winery_wine_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $wine = $this->makeWine($other);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/wines/{$wine->id}/process")
            ->assertStatus(404);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_process_step(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->postJson("/api/v1/winery/wines/{$wine->id}/process", [
                'process_type' => 'fermentation',
                'start_date' => '2026-06-01',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('wine_process_details', [
            'wine_id' => $wine->id,
            'process_type' => 'fermentation',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $wine = $this->makeWine();

        $this->api($this->winery)
            ->postJson("/api/v1/winery/wines/{$wine->id}/process", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['process_type', 'start_date']);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_modifies_step(): void
    {
        $step = $this->makeStep($this->makeWine());

        $this->api($this->winery)
            ->putJson("/api/v1/winery/process/{$step->id}", ['observations' => 'Temperatura estable'])
            ->assertStatus(200);

        $this->assertDatabaseHas('wine_process_details', [
            'id' => $step->id,
            'observations' => 'Temperatura estable',
        ]);
    }

    public function test_update_other_winery_step_returns_404(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true]);
        $step = $this->makeStep($this->makeWine($other));

        $this->api($this->winery)
            ->putJson("/api/v1/winery/process/{$step->id}", ['observations' => 'Hack'])
            ->assertStatus(404);
    }

    // ─── complete ─────────────────────────────────────────────────────────────

    public function test_complete_sets_end_date(): void
    {
        $step = $this->makeStep($this->makeWine());

        $this->api($this->winery)
            ->postJson("/api/v1/winery/process/{$step->id}/complete", ['end_date' => '2026-06-10'])
            ->assertStatus(200);

        $this->assertDatabaseHas('wine_process_details', [
            'id' => $step->id,
            'end_date' => '2026-06-10',
        ]);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_step(): void
    {
        $step = $this->makeStep($this->makeWine());

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/process/{$step->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('wine_process_details', ['id' => $step->id]);
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

    private function makeStep(Wine $wine): WineProcessDetail
    {
        return WineProcessDetail::create([
            'wine_id' => $wine->id,
            'process_type' => 'fermentation',
            'start_date' => '2026-06-01',
            'created_by' => $wine->user_id,
        ]);
    }
}
