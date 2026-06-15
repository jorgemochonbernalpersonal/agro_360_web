<?php

namespace Tests\Feature\Api;

use App\Models\AgriculturalActivity;
use App\Models\Fertilization;
use App\Models\Irrigation;
use App\Models\Observation;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotebookApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Plot $plot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->viticulturist()->create(['can_login' => true]);
        $this->plot = Plot::factory()->forViticulturist($this->user)->create();
    }

    // ─── GET /viticulturist/notebook ──────────────────────────────────────────

    public function test_index_returns_paginated_activities(): void
    {
        AgriculturalActivity::factory()
            ->count(3)
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create();

        $this->actingAsUser()
            ->getJson('/api/v1/viticulturist/notebook')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'current_page', 'last_page'],
            ])
            ->assertJsonPath('meta.total', 3);
    }

    public function test_index_filters_by_type(): void
    {
        AgriculturalActivity::factory()
            ->count(2)
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'irrigation']);

        AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'fertilization']);

        $this->actingAsUser()
            ->getJson('/api/v1/viticulturist/notebook?type=irrigation')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_only_returns_own_activities(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $otherPlot = Plot::factory()->forViticulturist($other)->create();

        AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create();

        AgriculturalActivity::factory()
            ->forViticulturist($other)
            ->forPlot($otherPlot)
            ->create();

        $this->actingAsUser()
            ->getJson('/api/v1/viticulturist/notebook')
            ->assertJsonPath('meta.total', 1);
    }

    // ─── GET /viticulturist/notebook/{id} ─────────────────────────────────────

    public function test_show_returns_activity(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'phenology']);

        $this->actingAsUser()
            ->getJson("/api/v1/viticulturist/notebook/{$activity->id}")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'plot_id', 'date']]);
    }

    public function test_show_other_user_activity_returns_404(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $otherPlot = Plot::factory()->forViticulturist($other)->create();
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($other)
            ->forPlot($otherPlot)
            ->create();

        $this->actingAsUser()
            ->getJson("/api/v1/viticulturist/notebook/{$activity->id}")
            ->assertStatus(404);
    }

    // ─── POST /viticulturist/notebook — phenology ─────────────────────────────

    public function test_store_phenology_creates_base_activity(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'phenology',
                'phenological_stage' => 'brotación',
                'notes' => 'Inicio de brotación',
            ]))
            ->assertStatus(201)
            ->assertJsonPath('data.activity_type', 'phenology')
            ->assertJsonPath('data.phenological_stage', 'brotación');

        $this->assertDatabaseHas('agricultural_activities', [
            'activity_type' => 'phenology',
            'viticulturist_id' => $this->user->id,
            'phenological_stage' => 'brotación',
        ]);
    }

    // ─── POST /viticulturist/notebook — irrigation ────────────────────────────

    public function test_store_irrigation_creates_sub_model(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'irrigation',
                'water_volume' => 500,
                'water_volume_unit' => 'L',
                'duration_minutes' => 60,
                'is_fertirrigation' => false,
            ]))
            ->assertStatus(201)
            ->assertJsonPath('data.activity_type', 'irrigation');

        $activityId = AgriculturalActivity::where('activity_type', 'irrigation')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('irrigations', [
            'activity_id' => $activityId,
            'water_volume_unit' => 'L',
            'duration_minutes' => 60,
        ]);
    }

    public function test_store_irrigation_returns_details_in_response(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'irrigation',
                'water_volume' => 300.5,
                'water_volume_unit' => 'L',
            ]))
            ->assertStatus(201)
            ->assertJsonPath('data.details.water_volume_unit', 'L');
    }

    // ─── POST /viticulturist/notebook — fertilization ─────────────────────────

    public function test_store_fertilization_creates_sub_model(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'fertilization',
                'fertilizer_name' => 'Nitrato amónico',
                'quantity' => 150,
                'nitrogen_uf' => 80,
            ]))
            ->assertStatus(201);

        $activityId = AgriculturalActivity::where('activity_type', 'fertilization')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('fertilizations', [
            'activity_id' => $activityId,
            'fertilizer_name' => 'Nitrato amónico',
        ]);
    }

    // ─── POST /viticulturist/notebook — cultural ──────────────────────────────

    public function test_store_cultural_creates_cultural_work(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'cultural',
                'work_type' => 'deshojado',
                'hours_worked' => 4.5,
                'workers_count' => 3,
                'residue_management' => 'triturado_incorporado',
            ]))
            ->assertStatus(201);

        $activityId = AgriculturalActivity::where('activity_type', 'cultural')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('cultural_works', [
            'activity_id' => $activityId,
            'work_type' => 'deshojado',
            'residue_management' => 'triturado_incorporado',
        ]);
    }

    // ─── POST /viticulturist/notebook — pruning ───────────────────────────────

    public function test_store_pruning_creates_cultural_work_with_work_type_pruning(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'pruning',
                'pruning_type' => 'doble_cordón',
                'productive_buds_per_hectare' => 12000,
            ]))
            ->assertStatus(201);

        $activityId = AgriculturalActivity::where('activity_type', 'pruning')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('cultural_works', [
            'activity_id' => $activityId,
            'work_type' => 'pruning',
            'pruning_type' => 'doble_cordón',
        ]);
    }

    // ─── POST /viticulturist/notebook — observation ───────────────────────────

    public function test_store_observation_creates_observation(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'observation',
                'observation_type' => 'IPM',
                'severity' => 'moderada',
                'affected_area_percentage' => 15.5,
                'threshold_exceeded' => true,
            ]))
            ->assertStatus(201);

        $activityId = AgriculturalActivity::where('activity_type', 'observation')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('observations', [
            'activity_id' => $activityId,
            'observation_type' => 'IPM',
            'threshold_exceeded' => true,
        ]);
    }

    // ─── POST /viticulturist/notebook — phytosanitary ─────────────────────────

    public function test_store_phytosanitary_requires_product_id(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'phytosanitary',
                // sin product_id
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_store_phytosanitary_creates_treatment(): void
    {
        $product = \App\Models\PhytosanitaryProduct::create([
            'registration_number' => 'ES-TEST-0001',
            'name' => 'Fungicida Test',
            'active_ingredient' => 'Cobre',
            'manufacturer' => 'Test Corp',
            'type' => 'fungicide',
            'withdrawal_period_days' => 7,
        ]);

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'phytosanitary',
                'product_id' => $product->id,
                'dose_per_hectare' => 2.5,
                'area_treated' => 1.2,
                'treatment_justification' => 'Prevención mildiu',
            ]))
            ->assertStatus(201)
            ->assertJsonPath('data.activity_type', 'phytosanitary');

        $activityId = AgriculturalActivity::where('activity_type', 'phytosanitary')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('phytosanitary_treatments', [
            'activity_id' => $activityId,
            'product_id' => $product->id,
        ]);
    }

    // ─── POST /viticulturist/notebook — post_harvest ──────────────────────────

    public function test_store_post_harvest_requires_application_type(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'post_harvest',
                // sin application_type
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application_type']);
    }

    public function test_store_post_harvest_creates_treatment(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'activity_type' => 'post_harvest',
                'application_type' => 'copper_treatment',
                'treated_area_ha' => 2.0,
                'dose_per_hectare' => 3.0,
                'reentry_interval_hours' => 24,
            ]))
            ->assertStatus(201);

        $activityId = AgriculturalActivity::where('activity_type', 'post_harvest')
            ->where('viticulturist_id', $this->user->id)
            ->first()->id;

        $this->assertDatabaseHas('post_harvest_treatments', [
            'activity_id' => $activityId,
            'application_type' => 'copper_treatment',
            'reentry_interval_hours' => 24,
        ]);
    }

    // ─── Validaciones comunes ─────────────────────────────────────────────────

    public function test_store_requires_plot_id(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', [
                'activity_type' => 'phenology',
                'activity_date' => '2026-03-15',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['plot_id']);
    }

    public function test_store_rejects_plot_belonging_to_other_user(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $otherPlot = Plot::factory()->forViticulturist($other)->create();

        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base([
                'plot_id' => $otherPlot->id,
            ]))
            ->assertStatus(404);
    }

    public function test_store_assigns_campaign_automatically(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook', $this->base())
            ->assertStatus(201);

        $activity = AgriculturalActivity::where('viticulturist_id', $this->user->id)->first();
        $this->assertNotNull($activity->campaign_id);
    }

    // ─── PUT /viticulturist/notebook/{id} ─────────────────────────────────────

    public function test_update_changes_base_fields(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'phenology', 'notes' => 'Original']);

        $this->actingAsUser()
            ->putJson("/api/v1/viticulturist/notebook/{$activity->id}", [
                'notes' => 'Actualizado',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.notes', 'Actualizado');

        $this->assertDatabaseHas('agricultural_activities', [
            'id' => $activity->id,
            'notes' => 'Actualizado',
        ]);
    }

    public function test_update_changes_irrigation_detail_fields(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'irrigation']);

        Irrigation::create([
            'activity_id' => $activity->id,
            'water_volume' => 100,
            'duration_minutes' => 30,
        ]);

        $this->actingAsUser()
            ->putJson("/api/v1/viticulturist/notebook/{$activity->id}", [
                'duration_minutes' => 90,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('irrigations', [
            'activity_id' => $activity->id,
            'duration_minutes' => 90,
        ]);
    }

    public function test_update_locked_activity_returns_422(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['is_locked' => true]);

        $this->actingAsUser()
            ->putJson("/api/v1/viticulturist/notebook/{$activity->id}", ['notes' => 'X'])
            ->assertStatus(422);
    }

    public function test_update_other_user_activity_returns_404(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $otherPlot = Plot::factory()->forViticulturist($other)->create();
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($other)
            ->forPlot($otherPlot)
            ->create();

        $this->actingAsUser()
            ->putJson("/api/v1/viticulturist/notebook/{$activity->id}", ['notes' => 'X'])
            ->assertStatus(404);
    }

    // ─── DELETE /viticulturist/notebook/{id} ──────────────────────────────────

    public function test_destroy_deletes_activity(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create();

        $this->actingAsUser()
            ->deleteJson("/api/v1/viticulturist/notebook/{$activity->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('agricultural_activities', ['id' => $activity->id]);
    }

    public function test_destroy_locked_activity_returns_422(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['is_locked' => true]);

        $this->actingAsUser()
            ->deleteJson("/api/v1/viticulturist/notebook/{$activity->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('agricultural_activities', ['id' => $activity->id]);
    }

    public function test_destroy_other_user_activity_returns_404(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $otherPlot = Plot::factory()->forViticulturist($other)->create();
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($other)
            ->forPlot($otherPlot)
            ->create();

        $this->actingAsUser()
            ->deleteJson("/api/v1/viticulturist/notebook/{$activity->id}")
            ->assertStatus(404);
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/viticulturist/notebook')->assertStatus(401);
        $this->postJson('/api/v1/viticulturist/notebook', [])->assertStatus(401);
    }

    public function test_requires_viticulturist_role(): void
    {
        $winery = User::factory()->winery()->create(['can_login' => true]);

        $this->actingAs($winery, 'sanctum')
            ->getJson('/api/v1/viticulturist/notebook')
            ->assertStatus(403);
    }

    // ─── GET /viticulturist/notebook/{notebook_type} (indexOfType) ───────────

    public function test_index_of_type_returns_only_that_type(): void
    {
        AgriculturalActivity::factory()
            ->count(2)
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'irrigation']);

        AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'fertilization']);

        $this->actingAsUser()
            ->getJson('/api/v1/viticulturist/notebook/irrigations')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_of_type_excludes_other_users(): void
    {
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $otherPlot = Plot::factory()->forViticulturist($other)->create();

        AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'observation']);

        AgriculturalActivity::factory()
            ->forViticulturist($other)
            ->forPlot($otherPlot)
            ->create(['activity_type' => 'observation']);

        $this->actingAsUser()
            ->getJson('/api/v1/viticulturist/notebook/observations')
            ->assertJsonPath('meta.total', 1);
    }

    // ─── POST /viticulturist/notebook/{notebook_type} (storeTyped) ───────────

    public function test_store_typed_pruning_maps_slug_to_activity_type(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook/pruning', [
                'plot_id' => $this->plot->id,
                'activity_date' => '2026-03-15',
                'pruning_type' => 'vara',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.activity_type', 'pruning');
    }

    public function test_store_typed_pruning_accepts_buds_per_vine_alias(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook/pruning', [
                'plot_id' => $this->plot->id,
                'activity_date' => '2026-03-15',
                'buds_per_vine' => 8,
            ])
            ->assertStatus(201);

        $activity = AgriculturalActivity::where('viticulturist_id', $this->user->id)
            ->where('activity_type', 'pruning')
            ->first();

        $this->assertDatabaseHas('cultural_works', [
            'activity_id' => $activity->id,
            'productive_buds_per_hectare' => 8,
        ]);
    }

    public function test_store_typed_cultural_works_maps_slug_to_cultural(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook/cultural-works', [
                'plot_id' => $this->plot->id,
                'activity_date' => '2026-03-15',
                'work_type' => 'deshojado',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.activity_type', 'cultural');
    }

    public function test_store_typed_post_harvest_requires_application_type(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook/post-harvest-treatments', [
                'plot_id' => $this->plot->id,
                'activity_date' => '2026-03-15',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application_type']);
    }

    public function test_store_typed_post_harvest_rejects_invalid_application_type(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/viticulturist/notebook/post-harvest-treatments', [
                'plot_id' => $this->plot->id,
                'activity_date' => '2026-03-15',
                'application_type' => 'invalid_type',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application_type']);
    }

    // ─── PUT — UpdateNotebookRequest carga tipo desde BD ─────────────────────

    public function test_update_phytosanitary_requires_product_id(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'phytosanitary']);

        $this->actingAsUser()
            ->putJson("/api/v1/viticulturist/notebook/{$activity->id}", [
                'dose_per_hectare' => 2.0,
                // sin product_id
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_update_irrigation_does_not_require_product_id(): void
    {
        $activity = AgriculturalActivity::factory()
            ->forViticulturist($this->user)
            ->forPlot($this->plot)
            ->create(['activity_type' => 'irrigation']);

        $this->actingAsUser()
            ->putJson("/api/v1/viticulturist/notebook/{$activity->id}", [
                'duration_minutes' => 45,
            ])
            ->assertStatus(200);
    }

    private function base(array $override = []): array
    {
        return array_merge([
            'activity_type' => 'phenology',
            'plot_id' => $this->plot->id,
            'activity_date' => '2026-03-15',
        ], $override);
    }

    private function actingAsUser()
    {
        return $this->actingAs($this->user, 'sanctum');
    }
}
