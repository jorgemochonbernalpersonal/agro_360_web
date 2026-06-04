<?php

namespace Tests\Feature\Supervisor\Oversight\Wineries;

use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\Ability;
use App\Models\UserAbility;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class ToggleAbilityTest extends SupervisorTestCase
{
    // ── grant ─────────────────────────────────────────────────────────────────

    public function test_supervisor_can_grant_ability_to_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $ability = $this->makeAbility();

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

    // ── revoke ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_revoke_ability_from_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $ability = $this->makeAbility();

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

    // ── ciclo grant → revoke ──────────────────────────────────────────────────

    public function test_toggle_ability_is_reversible(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $ability = $this->makeAbility();

        $component = Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery]);

        $component->call('toggleAbility', $ability->id);
        $this->assertDatabaseHas('user_abilities', [
            'user_id' => $winery->id, 'ability_id' => $ability->id,
        ]);

        $component->call('toggleAbility', $ability->id);
        $this->assertDatabaseMissing('user_abilities', [
            'user_id' => $winery->id, 'ability_id' => $ability->id,
        ]);
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_granted_ability_id_appears_in_view(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $ability = $this->makeAbility();

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

    public function test_all_abilities_passed_to_view(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $ability = $this->makeAbility();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertViewHas('allAbilities', fn ($a) => $a->contains('id', $ability->id));
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_other_supervisor_cannot_toggle_ability(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();
        $ability = $this->makeAbility();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($otherSupervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAbility', $ability->id);
    }

    private function makeAbility(): Ability
    {
        return Ability::create([
            'code' => 'test_ability',
            'name' => 'Módulo de prueba',
            'module' => 'winery',
        ]);
    }
}
