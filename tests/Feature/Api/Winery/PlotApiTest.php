<?php

namespace Tests\Feature\Api\Winery;

use App\Models\Plot;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlotApiTest extends TestCase
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
        $this->getJson('/api/v1/winery/plots')->assertStatus(401);
    }

    public function test_viticulturist_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/plots')->assertStatus(403);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_index_returns_plots_of_linked_viticulturists_only(): void
    {
        $linked = $this->linkViticulturist(notebookAccess: true);
        Plot::factory()->forViticulturist($linked)->create();

        $unlinked = User::factory()->viticulturist()->create(['can_login' => true]);
        Plot::factory()->forViticulturist($unlinked)->create();

        $this->api($this->winery)
            ->getJson('/api/v1/winery/plots')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_excludes_plots_of_viticulturists_without_notebook_access(): void
    {
        $linked = $this->linkViticulturist(notebookAccess: false);
        Plot::factory()->forViticulturist($linked)->create();

        $this->api($this->winery)
            ->getJson('/api/v1/winery/plots')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 0);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function test_show_returns_linked_viticulturist_plot(): void
    {
        $linked = $this->linkViticulturist(notebookAccess: true);
        $plot = Plot::factory()->forViticulturist($linked)->create();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/plots/{$plot->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $plot->id);
    }

    public function test_show_unlinked_viticulturist_plot_returns_404(): void
    {
        $unlinked = User::factory()->viticulturist()->create(['can_login' => true]);
        $plot = Plot::factory()->forViticulturist($unlinked)->create();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/plots/{$plot->id}")
            ->assertStatus(404);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_store_creates_plot_for_linked_viticulturist(): void
    {
        $linked = $this->linkViticulturist();
        $refPlot = Plot::factory()->forViticulturist($linked)->make();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/plots', [
                'viticulturist_id' => $linked->id,
                'name' => 'Parcela Nueva',
                'area' => 2.5,
                'autonomous_community_id' => $refPlot->autonomous_community_id,
                'province_id' => $refPlot->province_id,
                'municipality_id' => $refPlot->municipality_id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('plots', [
            'viticulturist_id' => $linked->id,
            'name' => 'Parcela Nueva',
        ]);
    }

    public function test_store_rejects_unlinked_viticulturist_with_422(): void
    {
        $unlinked = User::factory()->viticulturist()->create(['can_login' => true]);
        $refPlot = Plot::factory()->forViticulturist($unlinked)->make();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/plots', [
                'viticulturist_id' => $unlinked->id,
                'name' => 'Parcela Hack',
                'area' => 1.0,
                'autonomous_community_id' => $refPlot->autonomous_community_id,
                'province_id' => $refPlot->province_id,
                'municipality_id' => $refPlot->municipality_id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['viticulturist_id']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function linkViticulturist(bool $notebookAccess = true): User
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $vit->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
            'notebook_access' => $notebookAccess,
        ]);

        return $vit;
    }
}
