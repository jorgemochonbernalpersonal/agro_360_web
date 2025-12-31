<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Machinery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineryPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $viticulturist;
    protected User $otherViticulturist;
    protected User $winery;
    protected User $admin;
    protected Machinery $machinery;
    protected Machinery $otherMachinery;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuarios de prueba
        $this->viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $this->otherViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $this->winery = User::factory()->create(['role' => 'winery']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Crear maquinaria
        $this->machinery = Machinery::factory()->create([
            'viticulturist_id' => $this->viticulturist->id,
        ]);
        
        $this->otherMachinery = Machinery::factory()->create([
            'viticulturist_id' => $this->otherViticulturist->id,
        ]);
    }

    // ============ viewAny Tests ============
    
    public function test_viticulturist_can_view_any_machinery(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('viewAny', Machinery::class),
            'Viticulturist should be able to view machinery list'
        );
    }

    public function test_non_viticulturist_cannot_view_any_machinery(): void
    {
        $this->assertFalse(
            $this->winery->can('viewAny', Machinery::class),
            'Winery should not be able to view machinery'
        );
        
        $this->assertFalse(
            $this->admin->can('viewAny', Machinery::class),
            'Admin should not be able to view machinery (viticulturist-only feature)'
        );
    }

    // ============ view Tests ============
    
    public function test_viticulturist_can_view_own_machinery(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('view', $this->machinery),
            'Viticulturist should be able to view their own machinery'
        );
    }

    public function test_viticulturist_cannot_view_other_machinery(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('view', $this->otherMachinery),
            'Viticulturist should not be able to view machinery of other viticulturists'
        );
    }

    // ============ create Tests ============
    
    public function test_viticulturist_can_create_machinery(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('create', Machinery::class),
            'Viticulturist should be able to create machinery'
        );
    }

    public function test_non_viticulturist_cannot_create_machinery(): void
    {
        $this->assertFalse(
            $this->winery->can('create', Machinery::class),
            'Winery should not be able to create machinery'
        );
        
        $this->assertFalse(
            $this->admin->can('create', Machinery::class),
            'Admin should not be able to create machinery'
        );
    }

    // ============ update Tests ============
    
    public function test_viticulturist_can_update_own_machinery(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('update', $this->machinery),
            'Viticulturist should be able to update their own machinery'
        );
    }

    public function test_viticulturist_cannot_update_other_machinery(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('update', $this->otherMachinery),
            'Viticulturist should not be able to update machinery of other viticulturists'
        );
    }

    // ============ delete Tests ============
    
    public function test_viticulturist_can_delete_own_machinery(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('delete', $this->machinery),
            'Viticulturist should be able to delete their own machinery'
        );
    }

    public function test_viticulturist_cannot_delete_other_machinery(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('delete', $this->otherMachinery),
            'Viticulturist should not be able to delete machinery of other viticulturists'
        );
    }

    public function test_non_viticulturist_cannot_delete_machinery(): void
    {
        $this->assertFalse(
            $this->winery->can('delete', $this->machinery),
            'Winery should not be able to delete machinery'
        );
        
        $this->assertFalse(
            $this->admin->can('delete', $this->machinery),
            'Admin should not be able to delete machinery'
        );
    }
}
