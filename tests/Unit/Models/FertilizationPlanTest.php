<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\FertilizationPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FertilizationPlanTest extends TestCase
{
    use RefreshDatabase;

    // ── status_label ──────────────────────────────────────────────────────────

    public function test_status_label_returns_borrador_for_draft(): void
    {
        $model = new FertilizationPlan(['status' => 'draft']);
        $this->assertEquals('Borrador', $model->status_label);
    }

    public function test_status_label_returns_activo_for_active(): void
    {
        $model = new FertilizationPlan(['status' => 'active']);
        $this->assertEquals('Activo', $model->status_label);
    }

    public function test_status_label_returns_archivado_for_archived(): void
    {
        $model = new FertilizationPlan(['status' => 'archived']);
        $this->assertEquals('Archivado', $model->status_label);
    }

    public function test_status_label_returns_raw_value_for_unknown_status(): void
    {
        $model = new FertilizationPlan(['status' => 'pendiente']);
        $this->assertEquals('pendiente', $model->status_label);
    }

    // ── status_color ──────────────────────────────────────────────────────────

    public function test_status_color_returns_zinc_for_draft(): void
    {
        $model = new FertilizationPlan(['status' => 'draft']);
        $this->assertEquals('zinc', $model->status_color);
    }

    public function test_status_color_returns_green_for_active(): void
    {
        $model = new FertilizationPlan(['status' => 'active']);
        $this->assertEquals('green', $model->status_color);
    }

    public function test_status_color_returns_amber_for_archived(): void
    {
        $model = new FertilizationPlan(['status' => 'archived']);
        $this->assertEquals('amber', $model->status_color);
    }

    public function test_status_color_returns_zinc_for_unknown_status(): void
    {
        $model = new FertilizationPlan(['status' => 'desconocido']);
        $this->assertEquals('zinc', $model->status_color);
    }

    // ── isDraft() ─────────────────────────────────────────────────────────────

    public function test_is_draft_returns_true_when_status_is_draft(): void
    {
        $model = new FertilizationPlan(['status' => 'draft']);
        $this->assertTrue($model->isDraft());
    }

    public function test_is_draft_returns_false_when_status_is_active(): void
    {
        $model = new FertilizationPlan(['status' => 'active']);
        $this->assertFalse($model->isDraft());
    }

    public function test_is_draft_returns_false_when_status_is_archived(): void
    {
        $model = new FertilizationPlan(['status' => 'archived']);
        $this->assertFalse($model->isDraft());
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    public function test_statuses_constant_contains_all_keys(): void
    {
        $this->assertArrayHasKey('draft', FertilizationPlan::STATUSES);
        $this->assertArrayHasKey('active', FertilizationPlan::STATUSES);
        $this->assertArrayHasKey('archived', FertilizationPlan::STATUSES);
    }

    public function test_status_colors_constant_matches_statuses_keys(): void
    {
        $this->assertSame(
            array_keys(FertilizationPlan::STATUSES),
            array_keys(FertilizationPlan::STATUS_COLORS)
        );
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function test_fertilization_plan_belongs_to_viticulturist(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $plan = FertilizationPlan::create([
            'viticulturist_id' => $user->id,
            'campaign_id'      => $campaign->id,
            'plan_year'        => 2026,
            'status'           => 'draft',
            'active'           => true,
        ]);

        $this->assertEquals($user->id, $plan->viticulturist->id);
    }

    public function test_fertilization_plan_belongs_to_campaign(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $plan = FertilizationPlan::create([
            'viticulturist_id' => $user->id,
            'campaign_id'      => $campaign->id,
            'plan_year'        => 2026,
            'status'           => 'active',
            'active'           => true,
        ]);

        $this->assertEquals($campaign->id, $plan->campaign->id);
    }
}
