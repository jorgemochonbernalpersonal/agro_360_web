<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Plot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlotApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_can_list_plots(): void
    {
        Plot::factory()->count(5)->create(['viticulturist_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/plots');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_can_show_single_plot(): void
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/plots/{$plot->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'area',
                    'location',
                    'created_at',
                ],
            ]);
    }

    public function test_cannot_view_other_user_plots(): void
    {
        $otherUser = User::factory()->create();
        $plot = Plot::factory()->create(['viticulturist_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/plots/{$plot->id}");

        $response->assertStatus(403);
    }

    public function test_can_create_plot(): void
    {
        $plotData = [
            'name' => 'Nueva Parcela',
            'area' => 5.5,
            'tenure_regime' => 'propiedad',
            'viticulturist_id' => $this->user->id,
            'autonomous_community_id' => 1,
            'province_id' => 1,
            'municipality_id' => 1,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/plots', $plotData);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Nueva Parcela']);

        $this->assertDatabaseHas('plots', ['name' => 'Nueva Parcela']);
    }

    public function test_cannot_create_plot_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/plots', [
                'name' => '', // Invalid: required
                'area' => -5, // Invalid: must be positive
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'area']);
    }
}
