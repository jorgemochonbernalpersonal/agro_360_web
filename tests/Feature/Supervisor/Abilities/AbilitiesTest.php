<?php

namespace Tests\Feature\Supervisor\Abilities;

use App\Livewire\Admin\Users\Show as AdminUserShow;
use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\Ability;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\UserAbility;
use App\Models\WineryViticulturist;
use Database\Seeders\AbilitySeeder;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class AbilitiesTest extends SupervisorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AbilitySeeder::class);
    }

    // ── retrocompatibilidad ───────────────────────────────────────────────────

    public function test_winery_with_no_abilities_has_full_access(): void
    {
        $winery = $this->makeWinery();

        // Sin abilities configuradas → todo true (retrocompatible)
        $this->assertTrue($winery->hasAbility(Ability::HARVEST_RECEPTION));
        $this->assertTrue($winery->hasAbility(Ability::WINE_PROCESS));
        $this->assertTrue($winery->hasAbility(Ability::PRODUCT_SALES));
    }

    // ── activar ability ───────────────────────────────────────────────────────

    public function test_supervisor_can_grant_ability_to_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::HARVEST_RECEPTION)->first();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAbility', $ability->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('user_abilities', [
            'user_id' => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
        ]);
    }

    public function test_winery_with_ability_granted_has_that_ability(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::WINE_PROCESS)->first();

        UserAbility::create([
            'user_id' => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);
        $winery->update(['abilities_configured' => true]);

        $this->assertTrue($winery->fresh()->hasAbility(Ability::WINE_PROCESS));
    }

    // ── revocar ability ───────────────────────────────────────────────────────

    public function test_supervisor_can_revoke_ability_from_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::PRODUCT_SALES)->first();

        UserAbility::create([
            'user_id' => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAbility', $ability->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('user_abilities', [
            'user_id' => $winery->id,
            'ability_id' => $ability->id,
        ]);
    }

    // ── acceso restringido cuando hay abilities configuradas ──────────────────

    public function test_winery_without_specific_ability_is_denied_when_abilities_are_set(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        // Conceder SOLO una ability — las demás quedan restringidas
        $granted = Ability::where('code', Ability::HARVEST_RECEPTION)->first();
        UserAbility::create([
            'user_id' => $winery->id,
            'ability_id' => $granted->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);
        $winery->update(['abilities_configured' => true]);

        $winery->refresh();
        $this->assertTrue($winery->hasAbility(Ability::HARVEST_RECEPTION));
        $this->assertFalse($winery->hasAbility(Ability::WINE_PROCESS));
        $this->assertFalse($winery->hasAbility(Ability::PRODUCT_SALES));
    }

    // ── la DO puede restringir a CERO módulos (antes imposible) ───────────────

    public function test_configured_winery_with_zero_abilities_is_denied_everything(): void
    {
        $winery = $this->makeWinery();

        // Configurada pero sin ninguna ability concedida → todo denegado.
        $winery->update(['abilities_configured' => true]);
        $winery->refresh();

        $this->assertFalse($winery->hasAbility(Ability::HARVEST_RECEPTION));
        $this->assertFalse($winery->hasAbility(Ability::WINE_PROCESS));
        $this->assertFalse($winery->hasAbility(Ability::PRODUCT_SALES));
    }

    public function test_revoking_last_ability_keeps_winery_restricted(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::WINE_PROCESS)->first();

        // Conceder y luego revocar vía toggle: la bodega queda configurada con
        // cero módulos, NO se le re-otorga acceso total.
        Livewire::actingAs($supervisor)->test(Show::class, ['winery' => $winery])
            ->call('toggleAbility', $ability->id)
            ->call('toggleAbility', $ability->id);

        $winery->refresh();
        $this->assertTrue($winery->abilities_configured);
        $this->assertFalse($winery->hasAbility(Ability::WINE_PROCESS));
        $this->assertFalse($winery->hasAbility(Ability::HARVEST_RECEPTION));
    }

    // ── guard: otro supervisor no puede tocar abilities de bodega ajena ───────

    public function test_another_supervisor_cannot_toggle_abilities(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $ability = Ability::where('code', Ability::LABEL_BATCHES)->first();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($otherSupervisor)
            ->test(Show::class, ['winery' => $winery]);
    }

    // ── vista bodega muestra abilities ────────────────────────────────────────

    public function test_show_displays_granted_abilities_in_ui(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::HARVEST_RECEPTION)->first();
        UserAbility::create([
            'user_id' => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertViewHas('grantedAbilityIds', fn ($ids) => $ids->contains($ability->id));
    }

    // ── desvinculación de la DO devuelve la bodega a estado independiente ──────

    public function test_unlinking_do_restores_winery_to_full_access(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        // La DO restringe la bodega a un único módulo.
        $granted = Ability::where('code', Ability::HARVEST_RECEPTION)->first();
        UserAbility::create([
            'user_id' => $winery->id,
            'ability_id' => $granted->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);
        $winery->update(['abilities_configured' => true]);

        $winery->refresh();
        $this->assertFalse($winery->hasAbility(Ability::WINE_PROCESS));

        // La DO desvincula la bodega.
        SupervisorWinery::where('supervisor_id', $supervisor->id)
            ->where('winery_id', $winery->id)
            ->firstOrFail()
            ->delete();

        // Bodega independiente de nuevo → acceso total, sin abilities residuales.
        $winery->refresh();
        $this->assertFalse($winery->abilities_configured);
        $this->assertDatabaseMissing('user_abilities', ['user_id' => $winery->id]);
        $this->assertTrue($winery->hasAbility(Ability::HARVEST_RECEPTION));
        $this->assertTrue($winery->hasAbility(Ability::WINE_PROCESS));
        $this->assertTrue($winery->hasAbility(Ability::PRODUCT_SALES));
    }

    public function test_unlinking_do_keeps_assigned_viticulturists_as_own(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => true,
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $supervisor->id,
        ]);

        SupervisorWinery::where('supervisor_id', $supervisor->id)
            ->where('winery_id', $winery->id)
            ->firstOrFail()
            ->delete();

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'supervisor_id' => null,
        ]);
    }

    public function test_deleting_supervisor_account_restores_supervised_wineries(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        // La DO había restringido la bodega a un módulo.
        $granted = Ability::where('code', Ability::HARVEST_RECEPTION)->first();
        UserAbility::create([
            'user_id' => $winery->id,
            'ability_id' => $granted->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);
        $winery->update(['abilities_configured' => true]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'can_login' => true,
            'email_verified_at' => now(),
        ]);

        // El admin elimina la cuenta del supervisor.
        Livewire::actingAs($admin)
            ->test(AdminUserShow::class, ['user' => $supervisor])
            ->call('deleteUser');

        $this->assertDatabaseMissing('users', ['id' => $supervisor->id]);

        // Las bodegas que supervisaba quedan independientes (acceso total), no
        // congeladas en las restricciones de la DO ya eliminada.
        $winery->refresh();
        $this->assertFalse($winery->abilities_configured);
        $this->assertDatabaseMissing('user_abilities', ['user_id' => $winery->id]);
        $this->assertTrue($winery->hasAbility(Ability::HARVEST_RECEPTION));
        $this->assertTrue($winery->hasAbility(Ability::WINE_PROCESS));
    }
}
