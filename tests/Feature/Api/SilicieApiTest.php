<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SilicieApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->winery()->create(['can_login' => true]);
    }

    // ─── Auth y rol ───────────────────────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/silicie')->assertStatus(401);
    }

    public function test_requires_winery_role(): void
    {
        $viticulturist = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->actingAs($viticulturist, 'sanctum')
            ->getJson('/api/v1/winery/silicie')
            ->assertStatus(403);
    }

    // ─── GET /winery/silicie — validación de vintage ──────────────────────────

    public function test_index_returns_200_without_vintage(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['stats', 'vintages', 'vintage']]);
    }

    public function test_index_accepts_valid_vintage(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie?vintage=2023')
            ->assertStatus(200);
    }

    public function test_index_rejects_vintage_below_minimum(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie?vintage=1989')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['vintage']);
    }

    public function test_index_rejects_non_integer_vintage(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie?vintage=abc')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['vintage']);
    }

    // ─── GET /winery/silicie/entries ──────────────────────────────────────────

    public function test_entries_returns_200(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/entries')
            ->assertStatus(200);
    }

    public function test_entries_rejects_invalid_vintage(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/entries?vintage=1980')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['vintage']);
    }

    // ─── GET /winery/silicie/inventory ───────────────────────────────────────

    public function test_inventory_returns_200(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/inventory')
            ->assertStatus(200);
    }

    // ─── GET /winery/silicie/opening — validación de fiscal_year ─────────────

    public function test_opening_rejects_fiscal_year_below_minimum(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/opening?fiscal_year=1980')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fiscal_year']);
    }

    public function test_opening_accepts_valid_fiscal_year(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/opening?fiscal_year=2024')
            ->assertStatus(200);
    }

    // ─── GET /winery/silicie/elaboration, outputs, opening ───────────────────

    public function test_elaboration_returns_200(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/elaboration')
            ->assertStatus(200);
    }

    public function test_outputs_returns_200(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/outputs')
            ->assertStatus(200);
    }

    public function test_opening_returns_200(): void
    {
        $this->actingAsWinery()
            ->getJson('/api/v1/winery/silicie/opening')
            ->assertStatus(200);
    }

    private function actingAsWinery()
    {
        return $this->actingAs($this->user, 'sanctum');
    }
}
