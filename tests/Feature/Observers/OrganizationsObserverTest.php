<?php

namespace Tests\Feature\Observers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationsObserverTest extends TestCase
{
    use RefreshDatabase;

    // ══════════════════════════════════════════════════════════════════════
    // UserObserver — auto-create Organization
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
            'name' => 'Org Preexistente',
            'type' => Organization::TYPE_WINERY,
            'slug' => 'org-preexistente-999',
            'active' => true,
        ]);

        $winery = User::factory()->create([
            'role' => 'winery',
            'organization_id' => $existingOrg->id,
        ]);

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

        $this->assertDatabaseMissing('organizations', ['owner_user_id' => $viticulturist->id]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // UserObserver — ghost viticulturist activation
    // ══════════════════════════════════════════════════════════════════════

    public function test_sets_activated_at_when_ghost_viticulturist_enables_login(): void
    {
        $ghost = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => false,
        ]);

        $this->assertNull($ghost->activated_at);

        $ghost->update(['can_login' => true]);

        $this->assertNotNull($ghost->fresh()->activated_at);
    }

    public function test_does_not_overwrite_activated_at_on_subsequent_can_login_toggle(): void
    {
        $ghost = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => false,
        ]);

        $ghost->update(['can_login' => true]);
        $firstActivation = $ghost->fresh()->activated_at;

        $ghost->updateQuietly(['can_login' => false]);
        $ghost->fresh()->update(['can_login' => true]);

        $this->assertEquals($firstActivation, $ghost->fresh()->activated_at);
    }

    public function test_does_not_set_activated_at_when_already_active_user_is_updated(): void
    {
        $active = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => true,
        ]);

        $active->update(['name' => 'Nuevo Nombre']);

        $this->assertNull($active->fresh()->activated_at);
    }

    public function test_does_not_set_activated_at_for_winery_user(): void
    {
        $winery = User::factory()->create([
            'role' => 'winery',
            'can_login' => false,
        ]);

        $winery->update(['can_login' => true]);

        $this->assertNull($winery->fresh()->activated_at);
    }
}
