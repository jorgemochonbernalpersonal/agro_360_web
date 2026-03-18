<?php

namespace Tests\Feature\Supervisor\Abilities;

use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\Ability;
use App\Models\User;
use App\Models\UserAbility;
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
            'user_id'    => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
        ]);
    }

    public function test_winery_with_ability_granted_has_that_ability(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::WINE_PROCESS)->first();

        UserAbility::create([
            'user_id'    => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);

        $this->assertTrue($winery->fresh()->hasAbility(Ability::WINE_PROCESS));
    }

    // ── revocar ability ───────────────────────────────────────────────────────

    public function test_supervisor_can_revoke_ability_from_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ability = Ability::where('code', Ability::PRODUCT_SALES)->first();

        UserAbility::create([
            'user_id'    => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAbility', $ability->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('user_abilities', [
            'user_id'    => $winery->id,
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
            'user_id'    => $winery->id,
            'ability_id' => $granted->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);

        $winery->refresh();
        $this->assertTrue($winery->hasAbility(Ability::HARVEST_RECEPTION));
        $this->assertFalse($winery->hasAbility(Ability::WINE_PROCESS));
        $this->assertFalse($winery->hasAbility(Ability::PRODUCT_SALES));
    }

    // ── guard: otro supervisor no puede tocar abilities de bodega ajena ───────

    public function test_another_supervisor_cannot_toggle_abilities(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor       = $this->makeSupervisor();

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
            'user_id'    => $winery->id,
            'ability_id' => $ability->id,
            'granted_by' => $supervisor->id,
            'granted_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertViewHas('grantedAbilityIds', fn ($ids) => $ids->contains($ability->id));
    }
}
