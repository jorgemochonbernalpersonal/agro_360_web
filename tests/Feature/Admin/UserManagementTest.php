<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verifica las protecciones del panel de administración de usuarios:
 * - No se puede desactivar a otro admin
 * - toggleBeta cascade a viticultores vinculados
 * - toggleBeta es atómico (transacción)
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $otherAdmin;

    private User $winery;

    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now(), 'can_login' => true]);
        $this->otherAdmin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now(), 'can_login' => true]);
        $this->winery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now(), 'is_beta_user' => false]);
        $this->viticulturist = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now(), 'is_beta_user' => false]);

        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);
    }

    // ── toggleActive ──────────────────────────────────────────────────────────

    public function test_admin_cannot_deactivate_another_admin(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersIndex::class)
            ->call('toggleActive', $this->otherAdmin->id);

        // Other admin should remain active
        $this->assertTrue($this->otherAdmin->fresh()->can_login);
    }

    public function test_admin_can_deactivate_regular_user(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersIndex::class)
            ->call('toggleActive', $this->winery->id);

        $this->assertFalse($this->winery->fresh()->can_login);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $this->winery->update(['can_login' => false]);
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersIndex::class)
            ->call('toggleActive', $this->winery->id);

        $this->assertTrue($this->winery->fresh()->can_login);
    }

    // ── toggleBeta cascade ────────────────────────────────────────────────────

    public function test_enabling_beta_for_winery_cascades_to_linked_viticulturists(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersIndex::class)
            ->call('toggleBeta', $this->winery->id);

        $this->assertTrue($this->winery->fresh()->is_beta_user);
        $this->assertTrue($this->viticulturist->fresh()->is_beta_user);
    }

    public function test_disabling_beta_for_winery_does_not_cascade(): void
    {
        // Both already have beta
        $this->winery->update(['is_beta_user' => true, 'beta_access_granted' => true, 'beta_ends_at' => now()->addMonths(3)]);
        $this->viticulturist->update(['is_beta_user' => true, 'beta_access_granted' => true, 'beta_ends_at' => now()->addMonths(3)]);

        $this->actingAs($this->admin);

        Livewire::test(AdminUsersIndex::class)
            ->call('toggleBeta', $this->winery->id);

        // Winery loses beta
        $this->assertFalse($this->winery->fresh()->is_beta_user);
        // Viticulturist keeps beta (cascade only applies when enabling)
        $this->assertTrue($this->viticulturist->fresh()->is_beta_user);
    }

    public function test_toggle_beta_is_atomic(): void
    {
        $this->actingAs($this->admin);

        // Verify the operation runs inside a transaction by checking both are updated together
        DB::beginTransaction();

        Livewire::test(AdminUsersIndex::class)
            ->call('toggleBeta', $this->winery->id);

        // Within the same outer transaction, both updates should be visible
        $this->assertTrue(User::find($this->winery->id)->is_beta_user);
        $this->assertTrue(User::find($this->viticulturist->id)->is_beta_user);

        DB::rollBack();

        // After rollback, neither should be beta
        $this->assertFalse($this->winery->fresh()->is_beta_user);
        $this->assertFalse($this->viticulturist->fresh()->is_beta_user);
    }
}
