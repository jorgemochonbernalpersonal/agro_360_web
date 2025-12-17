<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Models\SupervisorViticulturist;
use App\Models\ViticulturistHierarchy;
use App\Models\Crew;
use App\Models\CrewMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role_constants(): void
    {
        $this->assertEquals('admin', User::ROLE_ADMIN);
        $this->assertEquals('supervisor', User::ROLE_SUPERVISOR);
        $this->assertEquals('winery', User::ROLE_WINERY);
        $this->assertEquals('viticulturist', User::ROLE_VITICULTURIST);
    }

    public function test_isAdmin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSupervisor());
        $this->assertFalse($user->isWinery());
        $this->assertFalse($user->isViticulturist());
    }

    public function test_isSupervisor_returns_true_for_supervisor_role(): void
    {
        $user = User::factory()->create(['role' => 'supervisor']);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isSupervisor());
        $this->assertFalse($user->isWinery());
        $this->assertFalse($user->isViticulturist());
    }

    public function test_isWinery_returns_true_for_winery_role(): void
    {
        $user = User::factory()->create(['role' => 'winery']);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSupervisor());
        $this->assertTrue($user->isWinery());
        $this->assertFalse($user->isViticulturist());
    }

    public function test_isViticulturist_returns_true_for_viticulturist_role(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSupervisor());
        $this->assertFalse($user->isWinery());
        $this->assertTrue($user->isViticulturist());
    }

    public function test_supervisor_has_supervised_wineries_relationship(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $winery1 = User::factory()->create(['role' => 'winery']);
        $winery2 = User::factory()->create(['role' => 'winery']);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery1->id,
            'assigned_by' => $supervisor->id,
        ]);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery2->id,
            'assigned_by' => $supervisor->id,
        ]);

        $this->assertCount(2, $supervisor->supervisedWineries);
        $this->assertTrue($supervisor->supervisedWineries->contains('winery_id', $winery1->id));
        $this->assertTrue($supervisor->supervisedWineries->contains('winery_id', $winery2->id));
    }

    public function test_supervisor_has_supervised_viticulturists_relationship(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);

        $this->assertCount(1, $supervisor->supervisedViticulturists);
        $this->assertEquals($viticulturist->id, $supervisor->supervisedViticulturists->first()->viticulturist_id);
    }

    public function test_winery_has_winery_viticulturists_relationship(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist1->id,
            'source' => 'own',
            'assigned_by' => $winery->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist2->id,
            'source' => 'own',
            'assigned_by' => $winery->id,
        ]);

        $this->assertCount(2, $winery->wineryViticulturists);
        $this->assertTrue($winery->wineryViticulturists->contains('viticulturist_id', $viticulturist1->id));
        $this->assertTrue($winery->wineryViticulturists->contains('viticulturist_id', $viticulturist2->id));
    }

    public function test_winery_has_supervisor_relations_relationship(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $winery = User::factory()->create(['role' => 'winery']);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);

        $this->assertCount(1, $winery->supervisorRelations);
        $this->assertEquals($supervisor->id, $winery->supervisorRelations->first()->supervisor_id);
    }

    public function test_viticulturist_has_winery_relations_relationship(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => 'own',
            'assigned_by' => $winery->id,
        ]);

        $this->assertCount(1, $viticulturist->wineryRelationsAsViticulturist);
        $this->assertEquals($winery->id, $viticulturist->wineryRelationsAsViticulturist->first()->winery_id);
    }

    public function test_viticulturist_has_supervisor_relations_relationship(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);

        $this->assertCount(1, $viticulturist->supervisorRelationsAsViticulturist);
        $this->assertEquals($supervisor->id, $viticulturist->supervisorRelationsAsViticulturist->first()->supervisor_id);
    }

    public function test_viticulturist_has_parent_hierarchies_relationship(): void
    {
        $parentViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $childViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $winery = User::factory()->create(['role' => 'winery']);

        ViticulturistHierarchy::create([
            'parent_viticulturist_id' => $parentViticulturist->id,
            'child_viticulturist_id' => $childViticulturist->id,
            'winery_id' => $winery->id,
            'assigned_by' => $winery->id,
        ]);

        $this->assertCount(1, $parentViticulturist->parentHierarchies);
        $this->assertEquals($childViticulturist->id, $parentViticulturist->parentHierarchies->first()->child_viticulturist_id);
    }

    public function test_viticulturist_has_child_hierarchies_relationship(): void
    {
        $parentViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $childViticulturist = User::factory()->create(['role' => 'viticulturist']);
        $winery = User::factory()->create(['role' => 'winery']);

        ViticulturistHierarchy::create([
            'parent_viticulturist_id' => $parentViticulturist->id,
            'child_viticulturist_id' => $childViticulturist->id,
            'winery_id' => $winery->id,
            'assigned_by' => $winery->id,
        ]);

        $this->assertCount(1, $childViticulturist->childHierarchies);
        $this->assertEquals($parentViticulturist->id, $childViticulturist->childHierarchies->first()->parent_viticulturist_id);
    }

    public function test_user_implements_must_verify_email(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Contracts\Auth\MustVerifyEmail::class, $user);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-password',
        ]);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plain-password', $user->password));
    }

    public function test_user_hidden_attributes_are_not_serialized(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
            'remember_token' => 'token123',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
    }

    public function test_user_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    public function test_user_has_verified_email_method(): void
    {
        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertTrue($verifiedUser->hasVerifiedEmail());
        $this->assertFalse($unverifiedUser->hasVerifiedEmail());
    }

    public function test_wasCreatedByAnotherUser_returns_true_when_created_by_another_user(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->assertTrue($created->wasCreatedByAnotherUser());
    }

    public function test_wasCreatedByAnotherUser_returns_false_when_self_registered(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $this->assertFalse($user->wasCreatedByAnotherUser());
    }

    public function test_needsPasswordChange_returns_true_when_created_by_another_user(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->assertTrue($created->needsPasswordChange());
    }

    public function test_needsPasswordChange_returns_false_when_password_changed(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(), // Email verificado = password cambiado
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->assertFalse($created->needsPasswordChange());
    }

    public function test_getWineriesAttribute_returns_cached_wineries(): void
    {
        $winery1 = User::factory()->create(['role' => 'winery']);
        $winery2 = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id' => $winery1->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery1->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery2->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery2->id,
        ]);

        // Primera llamada
        $wineries1 = $viticulturist->wineries;
        $this->assertCount(2, $wineries1);

        // Segunda llamada (debe usar cache)
        $wineries2 = $viticulturist->wineries;
        $this->assertCount(2, $wineries2);
        $this->assertSame($wineries1, $wineries2); // Misma instancia = cache
    }

    public function test_getSupervisorAttribute_returns_cached_supervisor(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $viticulturist->id,
            'supervisor_id' => $supervisor->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $supervisor->id,
        ]);

        // Primera llamada
        $supervisor1 = $viticulturist->supervisor;
        $this->assertNotNull($supervisor1);
        $this->assertEquals($supervisor->id, $supervisor1->id);

        // Segunda llamada (debe usar cache)
        $supervisor2 = $viticulturist->supervisor;
        $this->assertNotNull($supervisor2);
        $this->assertSame($supervisor1, $supervisor2); // Misma instancia = cache
    }

    public function test_clearAttributeCache_clears_all_caches(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        // Acceder para crear cache
        $viticulturist->wineries;
        $viticulturist->supervisor;

        // Limpiar cache
        $viticulturist->clearAttributeCache();

        // Verificar que se puede acceder de nuevo (sin error)
        $this->assertCount(1, $viticulturist->wineries);
    }

    public function test_individualWorkers_returns_only_workers_without_crew(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $worker1 = User::factory()->create(['role' => 'viticulturist']);
        $worker2 = User::factory()->create(['role' => 'viticulturist']);
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        // Trabajador individual (sin crew) - asignado por el viticultor
        CrewMember::create([
            'viticulturist_id' => $worker1->id,
            'assigned_by' => $viticulturist->id,
            'crew_id' => null,
        ]);

        // Trabajador en cuadrilla
        CrewMember::create([
            'viticulturist_id' => $worker2->id,
            'assigned_by' => $viticulturist->id,
            'crew_id' => $crew->id,
        ]);

        $individualWorkers = $viticulturist->individualWorkers;
        $this->assertCount(1, $individualWorkers);
        $this->assertEquals($worker1->id, $individualWorkers->first()->viticulturist_id);
    }
}

