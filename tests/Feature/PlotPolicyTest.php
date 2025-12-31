<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plot;
use App\Models\WineryViticulturist;
use App\Models\SupervisorWinery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlotPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $winery;
    protected User $viticulturist;
    protected User $otherViticulturist;
    protected User $subViticulturist;
    protected Plot $plot;
    protected Plot $otherPlot;
    protected Plot $subPlot;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuarios de prueba
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->winery = User::factory()->create(['role' => 'winery']);
        $this->viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $this->otherViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $this->subViticulturist = User::factory()->create(['role' => 'viticulturist']);
        
        // Crear parcelas
        $this->plot = Plot::factory()->create(['viticulturist_id' => $this->viticulturist->id]);
        $this->otherPlot = Plot::factory()->create(['viticulturist_id' => $this->otherViticulturist->id]);
        $this->subPlot = Plot::factory()->create(['viticulturist_id' => $this->subViticulturist->id]);
        
        // Establecer relaciones
        // Supervisor supervisa a winery
        SupervisorWinery::create([
            'supervisor_id' => $this->supervisor->id,
            'winery_id' => $this->winery->id,
            'assigned_by' => $this->supervisor->id,
        ]);
        
        // Winery tiene al viticulturist
        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);
        
        // Viticulturist creó al subViticulturist
        WineryViticulturist::create([
            'viticulturist_id' => $this->subViticulturist->id,
            'parent_viticulturist_id' => $this->viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $this->viticulturist->id,
        ]);
    }

    // ============ viewAny Tests ============
    
    public function test_admin_can_view_any_plots(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Plot::class));
    }

    public function test_supervisor_can_view_any_plots(): void
    {
        $this->assertTrue($this->supervisor->can('viewAny', Plot::class));
    }

    public function test_winery_can_view_any_plots(): void
    {
        $this->assertTrue($this->winery->can('viewAny', Plot::class));
    }

    public function test_viticulturist_can_view_any_plots(): void
    {
        $this->assertTrue($this->viticulturist->can('viewAny', Plot::class));
    }

    // ============ view Tests - Admin ============
    
    public function test_admin_can_view_any_plot(): void
    {
        $this->assertTrue($this->admin->can('view', $this->plot));
        $this->assertTrue($this->admin->can('view', $this->otherPlot));
        $this->assertTrue($this->admin->can('view', $this->subPlot));
    }

    // ============ view Tests - Viticulturist ============
    
    public function test_viticulturist_can_view_own_plot(): void
    {
        $this->assertTrue($this->viticulturist->can('view', $this->plot));
    }

    public function test_viticulturist_can_view_sub_viticulturist_plot(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('view', $this->subPlot),
            'Viticulturist should be able to view plots of sub-viticulturists they created'
        );
    }

    public function test_viticulturist_cannot_view_unrelated_plot(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('view', $this->otherPlot),
            'Viticulturist should not be able to view plots of unrelated viticulturists'
        );
    }

    // ============ view Tests - Winery ============
    
    public function test_winery_can_view_their_viticulturist_plot(): void
    {
        $this->assertTrue(
            $this->winery->can('view', $this->plot),
            'Winery should be able to view plots of their viticulturists'
        );
    }

    public function test_winery_cannot_view_unrelated_plot(): void
    {
        $this->assertFalse(
            $this->winery->can('view', $this->otherPlot),
            'Winery should not be able to view plots of viticulturists not assigned to them'
        );
    }

    // ============ view Tests - Supervisor ============
    
    public function test_supervisor_can_view_supervised_winery_viticulturist_plot(): void
    {
        $this->assertTrue(
            $this->supervisor->can('view', $this->plot),
            'Supervisor should be able to view plots of viticulturists from supervised wineries'
        );
    }

    public function test_supervisor_cannot_view_unsupervised_plot(): void
    {
        $this->assertFalse(
            $this->supervisor->can('view', $this->otherPlot),
            'Supervisor should not be able to view plots from unsupervised wineries'
        );
    }

    // ============ create Tests ============
    
    public function test_admin_can_create_plot(): void
    {
        $this->assertTrue($this->admin->can('create', Plot::class));
    }

    public function test_supervisor_can_create_plot(): void
    {
        $this->assertTrue($this->supervisor->can('create', Plot::class));
    }

    public function test_winery_can_create_plot(): void
    {
        $this->assertTrue($this->winery->can('create', Plot::class));
    }

    public function test_viticulturist_can_create_plot(): void
    {
        $this->assertTrue($this->viticulturist->can('create', Plot::class));
    }

    // ============ update Tests - Admin ============
    
    public function test_admin_can_update_any_plot(): void
    {
        $this->assertTrue($this->admin->can('update', $this->plot));
        $this->assertTrue($this->admin->can('update', $this->otherPlot));
    }

    // ============ update Tests - Viticulturist ============
    
    public function test_viticulturist_can_update_own_plot(): void
    {
        $this->assertTrue($this->viticulturist->can('update', $this->plot));
    }

    public function test_viticulturist_can_update_sub_viticulturist_plot(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('update', $this->subPlot),
            'Viticulturist should be able to update plots of sub-viticulturists they created'
        );
    }

    public function test_viticulturist_cannot_update_unrelated_plot(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('update', $this->otherPlot),
            'Viticulturist should not be able to update plots of unrelated viticulturists'
        );
    }

    // ============ update Tests - Winery ============
    
    public function test_winery_can_update_their_viticulturist_plot(): void
    {
        $this->assertTrue(
            $this->winery->can('update', $this->plot),
            'Winery should be able to update plots of their viticulturists'
        );
    }

    public function test_winery_cannot_update_unrelated_plot(): void
    {
        $this->assertFalse(
            $this->winery->can('update', $this->otherPlot),
            'Winery should not be able to update plots of unrelated viticulturists'
        );
    }

    // ============ update Tests - Supervisor ============
    
    public function test_supervisor_can_update_supervised_winery_viticulturist_plot(): void
    {
        $this->assertTrue(
            $this->supervisor->can('update', $this->plot),
            'Supervisor should be able to update plots of viticulturists from supervised wineries'
        );
    }

    // ============ delete Tests ============
    
    public function test_delete_permissions_match_update_permissions(): void
    {
        // Admin
        $this->assertTrue($this->admin->can('delete', $this->plot));
        
        // Viticulturist - own plot
        $this->assertTrue($this->viticulturist->can('delete', $this->plot));
        
        // Viticulturist - sub plot
        $this->assertTrue($this->viticulturist->can('delete', $this->subPlot));
        
        // Viticulturist - unrelated
        $this->assertFalse($this->viticulturist->can('delete', $this->otherPlot));
        
        // Winery
        $this->assertTrue($this->winery->can('delete', $this->plot));
        $this->assertFalse($this->winery->can('delete', $this->otherPlot));
    }

    // ============ Edge Cases ============
    
    public function test_plot_without_viticulturist_can_be_viewed_by_admin(): void
    {
        $plotWithoutViticulturist = Plot::factory()->create(['viticulturist_id' => null]);
        
        $this->assertTrue($this->admin->can('view', $plotWithoutViticulturist));
    }

    public function test_plot_without_viticulturist_cannot_be_viewed_by_winery(): void
    {
        $plotWithoutViticulturist = Plot::factory()->create(['viticulturist_id' => null]);
        
        $this->assertFalse($this->winery->can('view', $plotWithoutViticulturist));
    }

    public function test_plot_without_viticulturist_cannot_be_viewed_by_supervisor(): void
    {
        $plotWithoutViticulturist = Plot::factory()->create(['viticulturist_id' => null]);
        
        $this->assertFalse($this->supervisor->can('view', $plotWithoutViticulturist));
    }
}
