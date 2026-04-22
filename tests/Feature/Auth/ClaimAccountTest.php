<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ClaimAccount;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el flujo completo de activación de cuenta de viticultor fantasma
 * mediante token de invitación (ClaimAccount component).
 *
 * Flujo: Bodega crea ghost → envía invitación → ghost visita /claim-account/{token}
 *        → ClaimAccount valida token → viticultor activa cuenta → login automático
 */
class ClaimAccountTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Creates a ghost viticulturist with a hashed invitation token (as production
     * code does) plus a WineryViticulturist relation (required by ClaimAccount::mount).
     * The plain-text token is stored in $user->plainToken for use in component calls.
     */
    private function makeGhostWithToken(array $overrides = []): User
    {
        $plainToken = Str::random(64);

        // ClaimAccount::mount() requires a linked winery to set tokenValid = true.
        $winery = User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(array_merge([
            'role'                  => 'viticulturist',
            'can_login'             => false,
            'email'                 => 'viticultores.' . Str::uuid() . '@noemail.agro365.es',
            'invitation_token'      => Hash::make($plainToken), // production stores hashed
            'invitation_sent_at'    => now()->subHour(),
            'invitation_expires_at' => now()->addDays(7),
            'email_verified_at'     => null,
        ], $overrides));

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $user->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        // Expose plain-text token for passing to the Livewire component.
        $user->plainToken = $plainToken;

        return $user;
    }

    // ── mount: validación de token ─────────────────────────────────────────────

    public function test_valid_token_marks_component_as_token_valid(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('tokenValid', true);
    }

    public function test_invalid_token_marks_component_as_not_valid(): void
    {
        Livewire::test(ClaimAccount::class, ['token' => 'invalid-token-xyz'])
            ->assertSet('tokenValid', false);
    }

    public function test_expired_token_marks_component_as_not_valid(): void
    {
        $ghost = $this->makeGhostWithToken([
            'invitation_expires_at' => now()->subDay(),
        ]);

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('tokenValid', false);
    }

    public function test_token_belonging_to_already_active_user_is_invalid(): void
    {
        // Usuario con can_login=true → no es ghost → token no debe funcionar
        $plainToken = Str::random(64);
        $user = User::factory()->create([
            'role'                  => 'viticulturist',
            'can_login'             => true,
            'invitation_token'      => Hash::make($plainToken),
            'invitation_expires_at' => now()->addDays(7),
        ]);

        Livewire::test(ClaimAccount::class, ['token' => $plainToken])
            ->assertSet('tokenValid', false);
    }

    public function test_mount_prefills_name_from_ghost(): void
    {
        $ghost = $this->makeGhostWithToken(['name' => 'Juan Viticultor']);

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('name', 'Juan Viticultor');
    }

    public function test_mount_prefills_email_when_ghost_has_real_email(): void
    {
        $ghost = $this->makeGhostWithToken(['email' => 'real@example.com']);

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('email', 'real@example.com');
    }

    public function test_mount_does_not_prefill_placeholder_email(): void
    {
        $ghost = $this->makeGhostWithToken([
            'email' => 'viticultores.abc123@noemail.agro365.es',
        ]);

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('email', '');
    }

    // ── activate: activación correcta ────────────────────────────────────────

    public function test_activate_sets_can_login_true(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan Viticultor')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertTrue($ghost->fresh()->can_login);
    }

    public function test_activate_sets_email_verified_at(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan Viticultor')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertNotNull($ghost->fresh()->email_verified_at);
    }

    public function test_activate_clears_invitation_token(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan Viticultor')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertDatabaseHas('users', [
            'id'                    => $ghost->id,
            'invitation_token'      => null,
            'invitation_expires_at' => null,
            'invitation_sent_at'    => null,
        ]);
    }

    public function test_activate_updates_name_and_email(): void
    {
        $ghost = $this->makeGhostWithToken(['name' => 'Nombre Antiguo']);

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Nombre Nuevo')
            ->set('email', 'nuevo@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertDatabaseHas('users', [
            'id'    => $ghost->id,
            'name'  => 'Nombre Nuevo',
            'email' => 'nuevo@example.com',
        ]);
    }

    public function test_activate_logs_in_user_automatically(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan Viticultor')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertAuthenticatedAs($ghost->fresh());
    }

    public function test_activate_redirects_to_viticulturist_dashboard(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan Viticultor')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate')
            ->assertRedirect(route('viticulturist.dashboard'));
    }

    public function test_activate_preserves_winery_viticulturist_relation(): void
    {
        $winery = User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
        ]);

        $ghost = $this->makeGhostWithToken();

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ghost->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan Viticultor')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        // La relación bodega→viticultor sigue existiendo con el mismo id
        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ghost->id,
        ]);
    }

    // ── activate: validaciones ────────────────────────────────────────────────

    public function test_activate_requires_name(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', '')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate')
            ->assertHasErrors(['name']);
    }

    public function test_activate_requires_valid_email(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan')
            ->set('email', 'no-es-email')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate')
            ->assertHasErrors(['email']);
    }

    public function test_activate_rejects_email_already_taken_by_another_user(): void
    {
        User::factory()->create(['email' => 'ocupado@example.com']);

        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan')
            ->set('email', 'ocupado@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate')
            ->assertHasErrors(['email']);
    }

    public function test_activate_rejects_password_mismatch(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'OtraPassword!')
            ->call('activate')
            ->assertHasErrors(['password']);
    }

    public function test_activate_rejects_short_password(): void
    {
        $ghost = $this->makeGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Juan')
            ->set('email', 'juan@example.com')
            ->set('password', '123')
            ->set('password_confirmation', '123')
            ->call('activate')
            ->assertHasErrors(['password']);
    }

    public function test_activate_does_nothing_when_token_invalid(): void
    {
        Livewire::test(ClaimAccount::class, ['token' => 'token-falso'])
            ->set('name', 'Juan')
            ->set('email', 'juan@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertGuest();
    }

    // ── Supervisor ghost: flujo invitado por DO ───────────────────────────────

    /**
     * Ghost creado por supervisor (sin WineryViticulturist — solo SupervisorViticulturist).
     * El token debe ser válido y el invitorType debe ser 'supervisor'.
     */
    private function makeSupervisorGhostWithToken(array $overrides = []): User
    {
        $plainToken = Str::random(64);

        $supervisor = User::factory()->create([
            'role'              => 'supervisor',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(array_merge([
            'role'                  => 'viticulturist',
            'can_login'             => false,
            'email'                 => 'viticultores.' . Str::uuid() . '@noemail.agro365.es',
            'invitation_token'      => Hash::make($plainToken),
            'invitation_sent_at'    => now()->subHour(),
            'invitation_expires_at' => now()->addDays(7),
            'email_verified_at'     => null,
        ], $overrides));

        SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $user->id,
            'assigned_by'      => $supervisor->id,
        ]);

        $user->plainToken    = $plainToken;
        $user->supervisorId  = $supervisor->id;   // plain property, no conflict with Eloquent relation
        $user->supervisorName = $supervisor->name;

        return $user;
    }

    public function test_supervisor_ghost_token_is_valid(): void
    {
        $ghost = $this->makeSupervisorGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('tokenValid', true)
            ->assertSet('invitorType', 'supervisor');
    }

    public function test_supervisor_ghost_invitor_name_is_supervisor_name(): void
    {
        $ghost = $this->makeSupervisorGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->assertSet('wineryName', $ghost->supervisorName);
    }

    public function test_supervisor_ghost_activate_sets_can_login_true(): void
    {
        $ghost = $this->makeSupervisorGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Viticultor DO')
            ->set('email', 'do@viticultor.es')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('activate');

        $this->assertTrue($ghost->fresh()->can_login);
    }

    public function test_supervisor_ghost_activate_grants_notebook_to_supervisor_when_accepted(): void
    {
        $ghost = $this->makeSupervisorGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Viticultor DO')
            ->set('email', 'do@viticultor.es')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('shareCuaderno', true)
            ->call('activate');

        $this->assertDatabaseHas('supervisor_viticulturist', [
            'supervisor_id'    => $ghost->supervisorId,
            'viticulturist_id' => $ghost->id,
            'notebook_access'  => true,
        ]);
    }

    public function test_supervisor_ghost_activate_does_not_grant_notebook_when_declined(): void
    {
        $ghost = $this->makeSupervisorGhostWithToken();

        Livewire::test(ClaimAccount::class, ['token' => $ghost->plainToken])
            ->set('name', 'Viticultor DO')
            ->set('email', 'do@viticultor.es')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('shareCuaderno', false)
            ->call('activate');

        $this->assertDatabaseHas('supervisor_viticulturist', [
            'supervisor_id'    => $ghost->supervisorId,
            'viticulturist_id' => $ghost->id,
            'notebook_access'  => false,
        ]);
    }

    public function test_ghost_without_winery_or_supervisor_relation_is_invalid(): void
    {
        $plainToken = Str::random(64);

        // Ghost con token pero SIN ninguna relación (winery ni supervisor)
        User::factory()->create([
            'role'                  => 'viticulturist',
            'can_login'             => false,
            'invitation_token'      => Hash::make($plainToken),
            'invitation_expires_at' => now()->addDays(7),
        ]);

        Livewire::test(ClaimAccount::class, ['token' => $plainToken])
            ->assertSet('tokenValid', false);
    }

    // ── Ruta HTTP ─────────────────────────────────────────────────────────────

    public function test_claim_account_page_renders_with_valid_token(): void
    {
        $ghost = $this->makeGhostWithToken();

        $this->get(route('auth.claim-account', $ghost->plainToken))
            ->assertOk();
    }

    public function test_claim_account_page_renders_with_invalid_token(): void
    {
        $this->get(route('auth.claim-account', 'token-que-no-existe'))
            ->assertOk(); // Renderiza la vista de token inválido, no 404
    }
}
