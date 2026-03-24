<?php

namespace Tests\Feature\Observers;

use App\Models\Organization;
use App\Models\User;
use App\Models\ViticultoristAssignment;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationsObserverTest extends TestCase
{
    use RefreshDatabase;

    // ══════════════════════════════════════════════════════════════════════
    // UserObserver
    // ══════════════════════════════════════════════════════════════════════

    public function test_creates_organization_when_winery_user_is_created(): void
    {
        $winery = User::factory()->create(['role' => 'winery', 'name' => 'Bodega La Rioja']);

        $org = Organization::where('owner_user_id', $winery->id)->first();

        $this->assertNotNull($org);
        $this->assertEquals('Bodega La Rioja', $org->name);
        $this->assertEquals(Organization::TYPE_WINERY, $org->type);
        $this->assertTrue($org->active);
    }

    public function test_creates_organization_when_do_user_is_created(): void
    {
        $do = User::factory()->create(['role' => 'supervisor', 'name' => 'DO Ribera del Duero']);

        $org = Organization::where('owner_user_id', $do->id)->first();

        $this->assertNotNull($org);
        $this->assertEquals('DO Ribera del Duero', $org->name);
        $this->assertEquals(Organization::TYPE_DENOMINATION, $org->type);
    }

    public function test_sets_organization_id_on_winery_user_after_creation(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);

        $winery->refresh();
        $org = Organization::where('owner_user_id', $winery->id)->first();

        $this->assertNotNull($winery->organization_id);
        $this->assertEquals($org->id, $winery->organization_id);
    }

    public function test_does_not_create_organization_for_viticulturist(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $this->assertDatabaseMissing('organizations', ['owner_user_id' => $viticulturist->id]);
        $this->assertNull($viticulturist->fresh()->organization_id);
    }

    public function test_creates_winery_type_organization_for_producer(): void
    {
        $producer = User::factory()->create(['role' => 'producer', 'name' => 'Productor El Valle']);

        $org = Organization::where('owner_user_id', $producer->id)->first();

        $this->assertNotNull($org);
        $this->assertEquals(Organization::TYPE_WINERY, $org->type);
        $this->assertEquals('Productor El Valle', $org->name);
        $this->assertNotNull($producer->fresh()->organization_id);
    }

    public function test_does_not_duplicate_organization_if_already_has_one(): void
    {
        $existingOrg = Organization::create([
            'name'   => 'Org Preexistente',
            'type'   => Organization::TYPE_WINERY,
            'slug'   => 'org-preexistente-999',
            'active' => true,
        ]);

        $winery = User::factory()->create([
            'role'            => 'winery',
            'organization_id' => $existingOrg->id,
        ]);

        // Only the pre-existing org — no new one should be created
        $this->assertDatabaseCount('organizations', 1);
        $this->assertEquals($existingOrg->id, $winery->organization_id);
    }

    public function test_syncs_organization_name_when_user_name_changes(): void
    {
        $winery = User::factory()->create(['role' => 'winery', 'name' => 'Nombre Original']);

        $winery->update(['name' => 'Nombre Actualizado']);

        $this->assertEquals('Nombre Actualizado', $winery->organization->fresh()->name);
    }

    public function test_does_not_fail_when_non_org_user_name_changes(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'name' => 'Juan']);

        $viticulturist->update(['name' => 'Juan Actualizado']);

        // Just assert no exception was thrown and nothing was created
        $this->assertDatabaseMissing('organizations', ['owner_user_id' => $viticulturist->id]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // WineryViticulturistObserver
    // ══════════════════════════════════════════════════════════════════════

    public function test_creates_assignment_when_winery_viticulturist_link_is_created(): void
    {
        $winery       = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Winery has organization from UserObserver
        $winery->refresh();
        $this->assertNotNull($winery->organization_id);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $this->assertDatabaseHas('viticultor_assignments', [
            'viticultor_id'      => $viticulturist->id,
            'organization_id'    => $winery->organization_id,
            'assigned_by_org_id' => $winery->organization_id,
            'assigned_by_user_id'=> $winery->id,
        ]);
    }

    public function test_does_not_create_assignment_for_self_registered_link(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Self-registered record (winery_id = null)
        WineryViticulturist::create([
            'winery_id'        => null,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SELF,
            'assigned_by'      => $viticulturist->id,
        ]);

        $this->assertDatabaseCount('viticultor_assignments', 0);
    }

    public function test_does_not_create_assignment_when_winery_has_no_organization(): void
    {
        // Create winery and manually remove organization_id to simulate legacy user
        $winery = User::factory()->create(['role' => 'winery']);
        $winery->updateQuietly(['organization_id' => null]);

        // Remove the auto-created org so the winery truly has none
        Organization::where('owner_user_id', $winery->id)->delete();

        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $this->assertDatabaseCount('viticultor_assignments', 0);
    }

    public function test_syncs_cuaderno_access_grant_to_assignment(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $relation->grantNotebookAccess();

        $assignment = ViticultoristAssignment::where('viticultor_id', $viticulturist->id)
            ->where('organization_id', $winery->fresh()->organization_id)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertTrue($assignment->cuaderno_access);
        $this->assertNotNull($assignment->cuaderno_granted_at);
    }

    public function test_syncs_cuaderno_access_revoke_to_assignment(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $relation->grantNotebookAccess();
        $relation->revokeNotebookAccess();

        $assignment = ViticultoristAssignment::where('viticultor_id', $viticulturist->id)
            ->where('organization_id', $winery->fresh()->organization_id)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertFalse($assignment->cuaderno_access);
        $this->assertNotNull($assignment->cuaderno_revoked_at);
    }

    public function test_deletes_assignment_when_winery_viticulturist_link_is_deleted(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $orgId = $winery->fresh()->organization_id;
        $this->assertDatabaseHas('viticultor_assignments', [
            'viticultor_id'   => $viticulturist->id,
            'organization_id' => $orgId,
        ]);

        $relation->delete();

        $this->assertDatabaseMissing('viticultor_assignments', [
            'viticultor_id'   => $viticulturist->id,
            'organization_id' => $orgId,
        ]);
    }

    public function test_does_not_duplicate_assignment_on_updateorcreate(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Create the link twice (simulating the Invite component re-linking a self-registered viticulturist)
        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        // Simulate a second updateOrCreate on the same link (e.g. updating notes)
        WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->update(['notes' => 'Test notes']);

        $this->assertDatabaseCount('viticultor_assignments', 1);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Gap 1 — Producer Organization + viticultor_assignments
    // ══════════════════════════════════════════════════════════════════════

    public function test_producer_assignment_is_created_when_producer_adds_viticulturist(): void
    {
        $producer      = User::factory()->create(['role' => 'producer']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $producer->refresh();
        $this->assertNotNull($producer->organization_id, 'Producer should have an organization');

        WineryViticulturist::create([
            'winery_id'        => $producer->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $producer->id,
        ]);

        $this->assertDatabaseHas('viticultor_assignments', [
            'viticultor_id'      => $viticulturist->id,
            'organization_id'    => $producer->organization_id,
            'assigned_by_org_id' => $producer->organization_id,
        ]);
    }

    public function test_producer_cuaderno_access_syncs_to_assignment(): void
    {
        $producer      = User::factory()->create(['role' => 'producer']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'        => $producer->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $producer->id,
        ]);

        $relation->grantNotebookAccess();

        $assignment = ViticultoristAssignment::where('viticultor_id', $viticulturist->id)
            ->where('organization_id', $producer->fresh()->organization_id)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertTrue($assignment->cuaderno_access);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Gap 2 — Self-registered viticulturist linked to winery
    // ══════════════════════════════════════════════════════════════════════

    public function test_creates_assignment_when_self_registered_viticulturist_is_linked_to_winery(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Viticulturist self-registers (winery_id = null)
        $relation = WineryViticulturist::create([
            'winery_id'        => null,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SELF,
            'assigned_by'      => $viticulturist->id,
        ]);

        $this->assertDatabaseCount('viticultor_assignments', 0);

        // Winery accepts / links the viticulturist
        $relation->update([
            'winery_id'   => $winery->id,
            'source'      => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $winery->refresh();
        $this->assertDatabaseHas('viticultor_assignments', [
            'viticultor_id'   => $viticulturist->id,
            'organization_id' => $winery->organization_id,
        ]);
    }

    public function test_does_not_create_duplicate_assignment_when_self_registered_linked_twice(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'        => null,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SELF,
            'assigned_by'      => $viticulturist->id,
        ]);

        // Link once
        $relation->update([
            'winery_id'   => $winery->id,
            'source'      => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        // Simulate a second spurious update (e.g., notes change)
        $relation->fresh()->update(['notes' => 'segunda actualización']);

        $this->assertDatabaseCount('viticultor_assignments', 1);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Gap 3 — Ghost viticulturist activation
    // ══════════════════════════════════════════════════════════════════════

    public function test_sets_activated_at_when_ghost_viticulturist_enables_login(): void
    {
        $ghost = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
        ]);

        $this->assertNull($ghost->activated_at);

        $ghost->update(['can_login' => true]);

        $this->assertNotNull($ghost->fresh()->activated_at);
    }

    public function test_does_not_overwrite_activated_at_on_subsequent_can_login_toggle(): void
    {
        $ghost = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
        ]);

        $ghost->update(['can_login' => true]);
        $firstActivation = $ghost->fresh()->activated_at;

        // Disable and re-enable
        $ghost->updateQuietly(['can_login' => false]);
        $ghost->fresh()->update(['can_login' => true]);

        // activated_at must remain the original value
        $this->assertEquals($firstActivation, $ghost->fresh()->activated_at);
    }

    public function test_does_not_set_activated_at_when_already_active_user_is_updated(): void
    {
        $active = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
        ]);

        $active->update(['name' => 'Nuevo Nombre']);

        $this->assertNull($active->fresh()->activated_at);
    }

    public function test_does_not_set_activated_at_for_winery_user(): void
    {
        $winery = User::factory()->create([
            'role'      => 'winery',
            'can_login' => false,
        ]);

        $winery->update(['can_login' => true]);

        // Wineries don't follow the ghost activation flow — activated_at stays null
        $this->assertNull($winery->fresh()->activated_at);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Gap 4 — SOURCE_VITICULTURIST sub-viticulturists
    // ══════════════════════════════════════════════════════════════════════

    public function test_creates_assignment_when_producer_creates_sub_viticulturist(): void
    {
        // Producer has winery access but getWineriesAttribute returns empty (filters role=winery)
        // so the Create component sets winery_id=null — the observer must fall back to assigned_by
        $producer      = User::factory()->create(['role' => 'producer']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $producer->refresh();
        $this->assertNotNull($producer->organization_id);

        WineryViticulturist::create([
            'winery_id'               => null,
            'viticulturist_id'        => $viticulturist->id,
            'source'                  => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $producer->id,
            'assigned_by'             => $producer->id,
        ]);

        $this->assertDatabaseHas('viticultor_assignments', [
            'viticultor_id'      => $viticulturist->id,
            'organization_id'    => $producer->organization_id,
            'assigned_by_org_id' => $producer->organization_id,
        ]);
    }

    public function test_no_assignment_when_independent_viticulturist_creates_sub_viticulturist(): void
    {
        // Independent viticulturist has no organization — sub-viticulturist should get no assignment
        $parentVit = User::factory()->create(['role' => 'viticulturist']);
        $childVit  = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id'               => null,
            'viticulturist_id'        => $childVit->id,
            'source'                  => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $parentVit->id,
            'assigned_by'             => $parentVit->id,
        ]);

        $this->assertDatabaseCount('viticultor_assignments', 0);
    }

    public function test_deletes_assignment_when_producer_removes_sub_viticulturist_link(): void
    {
        $producer      = User::factory()->create(['role' => 'producer']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'               => null,
            'viticulturist_id'        => $viticulturist->id,
            'source'                  => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $producer->id,
            'assigned_by'             => $producer->id,
        ]);

        $orgId = $producer->fresh()->organization_id;
        $this->assertDatabaseHas('viticultor_assignments', [
            'viticultor_id'   => $viticulturist->id,
            'organization_id' => $orgId,
        ]);

        $relation->delete();

        $this->assertDatabaseMissing('viticultor_assignments', [
            'viticultor_id'   => $viticulturist->id,
            'organization_id' => $orgId,
        ]);
    }

    public function test_syncs_cuaderno_access_for_producer_sub_viticulturist(): void
    {
        $producer      = User::factory()->create(['role' => 'producer']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'               => null,
            'viticulturist_id'        => $viticulturist->id,
            'source'                  => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $producer->id,
            'assigned_by'             => $producer->id,
        ]);

        $relation->grantNotebookAccess();

        $assignment = ViticultoristAssignment::where('viticultor_id', $viticulturist->id)
            ->where('organization_id', $producer->fresh()->organization_id)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertTrue($assignment->cuaderno_access);
        $this->assertNotNull($assignment->cuaderno_granted_at);
    }

    public function test_winery_id_change_without_organization_does_not_create_assignment(): void
    {
        $winery        = User::factory()->create(['role' => 'winery']);
        $winery->updateQuietly(['organization_id' => null]);
        Organization::where('owner_user_id', $winery->id)->delete();

        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'winery_id'        => null,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SELF,
            'assigned_by'      => $viticulturist->id,
        ]);

        $relation->update([
            'winery_id'   => $winery->id,
            'source'      => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $this->assertDatabaseCount('viticultor_assignments', 0);
    }
}
