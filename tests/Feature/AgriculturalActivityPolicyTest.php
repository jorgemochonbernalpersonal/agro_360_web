<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\GrapeVariety;
use App\Models\Campaign;
use App\Models\AgriculturalActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalActivityPolicyTest extends TestCase
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
        
        // Crear parcelas
        $this->plot = Plot::factory()->create(['viticulturist_id' => $this->viticulturist->id]);
        $this->otherPlot = Plot::factory()->create(['viticulturist_id' => $this->otherViticulturist->id]);
        
        // Crear plantaciones activas para las parcelas
        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        $this->planting = PlotPlanting::create([
            'plot_id' => $this->plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $this->plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);
        
        // Crear campaña
        $this->campaign = Campaign::factory()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'year' => now()->year,
        ]);
        
        // Crear actividad
        $this->activity = AgriculturalActivity::factory()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'plot_id' => $this->plot->id,
            'plot_planting_id' => $this->planting->id,
            'campaign_id' => $this->campaign->id,
        ]);
    }

    public function test_viticulturist_can_view_any_activities(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('viewAny', AgriculturalActivity::class)
        );
    }

    public function test_non_viticulturist_cannot_view_any_activities(): void
    {
        $this->assertFalse(
            $this->winery->can('viewAny', AgriculturalActivity::class)
        );
        
        $this->assertFalse(
            $this->admin->can('viewAny', AgriculturalActivity::class)
        );
    }

    public function test_viticulturist_can_view_own_activity(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('view', $this->activity)
        );
    }

    public function test_viticulturist_cannot_view_other_viticulturist_activity(): void
    {
        $this->assertFalse(
            $this->otherViticulturist->can('view', $this->activity)
        );
    }

    public function test_viticulturist_can_create_activity(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('create', AgriculturalActivity::class)
        );
    }

    public function test_viticulturist_can_create_activity_for_own_plot(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('create', [AgriculturalActivity::class, $this->plot])
        );
    }

    public function test_viticulturist_cannot_create_activity_for_other_plot(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('create', [AgriculturalActivity::class, $this->otherPlot])
        );
    }

    public function test_non_viticulturist_cannot_create_activity(): void
    {
        $this->assertFalse(
            $this->winery->can('create', AgriculturalActivity::class)
        );
        
        $this->assertFalse(
            $this->admin->can('create', AgriculturalActivity::class)
        );
    }

    public function test_viticulturist_can_update_own_activity(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('update', $this->activity)
        );
    }

    public function test_viticulturist_cannot_update_other_activity(): void
    {
        $this->assertFalse(
            $this->otherViticulturist->can('update', $this->activity)
        );
    }

    public function test_viticulturist_can_delete_own_activity(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('delete', $this->activity)
        );
    }

    public function test_viticulturist_cannot_delete_other_activity(): void
    {
        $this->assertFalse(
            $this->otherViticulturist->can('delete', $this->activity)
        );
    }
}
