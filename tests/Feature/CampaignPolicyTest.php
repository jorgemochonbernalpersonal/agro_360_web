<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuarios de prueba
        $this->viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $this->otherViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $this->winery = User::factory()->create(['role' => 'winery']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Crear campañas
        $this->campaign = Campaign::factory()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'year' => now()->year,
        ]);
        
        $this->otherCampaign = Campaign::factory()->create([
            'viticulturist_id' => $this->otherViticulturist->id,
            'year' => now()->year,
        ]);
    }

    public function test_viticulturist_can_view_any_campaigns(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('viewAny', Campaign::class)
        );
    }

    public function test_non_viticulturist_cannot_view_any_campaigns(): void
    {
        $this->assertFalse(
            $this->winery->can('viewAny', Campaign::class)
        );
        
        $this->assertFalse(
            $this->admin->can('viewAny', Campaign::class)
        );
    }

    public function test_viticulturist_can_view_own_campaign(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('view', $this->campaign)
        );
    }

    public function test_viticulturist_cannot_view_other_campaign(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('view', $this->otherCampaign)
        );
    }

    public function test_viticulturist_can_create_campaign(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('create', Campaign::class)
        );
    }

    public function test_non_viticulturist_cannot_create_campaign(): void
    {
        $this->assertFalse(
            $this->winery->can('create', Campaign::class)
        );
        
        $this->assertFalse(
            $this->admin->can('create', Campaign::class)
        );
    }

    public function test_viticulturist_can_update_own_campaign(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('update', $this->campaign)
        );
    }

    public function test_viticulturist_cannot_update_other_campaign(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('update', $this->otherCampaign)
        );
    }

    public function test_viticulturist_can_delete_own_campaign(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('delete', $this->campaign)
        );
    }

    public function test_viticulturist_cannot_delete_other_campaign(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('delete', $this->otherCampaign)
        );
    }

    public function test_viticulturist_can_activate_own_campaign(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('activate', $this->campaign)
        );
    }

    public function test_viticulturist_cannot_activate_other_campaign(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('activate', $this->otherCampaign)
        );
    }
}
