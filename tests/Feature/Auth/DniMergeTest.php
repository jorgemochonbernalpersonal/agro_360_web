<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Register;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class DniMergeTest extends TestCase
{
    use RefreshDatabase;

    private function makeGhost(string $dni, ?string $email = null): User
    {
        return User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
            'dni'       => strtoupper($dni),
            'email'     => $email ?? 'viticultores.' . fake()->unique()->numerify('######') . '@agro365.es',
        ]);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_ghost_with_matching_dni_is_activated(): void
    {
        $ghost = $this->makeGhost('12345678A');

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Real')
            ->set('email', 'juan@example.com')
            ->set('dni', '12345678A')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $ghost->refresh();

        $this->assertTrue($ghost->can_login);
        $this->assertEquals('juan@example.com', $ghost->email);
        $this->assertEquals('Juan Real', $ghost->name);
        $this->assertNotNull($ghost->email_verified_at);
    }

    public function test_merge_logs_in_the_ghost_user(): void
    {
        $ghost = $this->makeGhost('12345678A');

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Real')
            ->set('email', 'juan@example.com')
            ->set('dni', '12345678A')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertTrue(Auth::check());
        $this->assertEquals($ghost->id, Auth::id());
    }

    public function test_winery_relations_are_preserved_after_merge(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $ghost  = $this->makeGhost('12345678A');

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ghost->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Real')
            ->set('email', 'juan@example.com')
            ->set('dni', '12345678A')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        // Ghost id preserved → relation still valid
        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ghost->id,
        ]);
    }

    public function test_dni_is_case_insensitive(): void
    {
        $ghost = $this->makeGhost('12345678A');

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Real')
            ->set('email', 'juan@example.com')
            ->set('dni', '12345678a') // lowercase
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertTrue(Auth::check());
        $this->assertEquals($ghost->id, Auth::id());
    }

    public function test_merge_replaces_ghost_fake_email_with_real_email(): void
    {
        $ghost = $this->makeGhost('12345678A', 'viticultores.000001@agro365.es');

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Real')
            ->set('email', 'juan@example.com')
            ->set('dni', '12345678A')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertEquals('juan@example.com', $ghost->fresh()->email);
    }

    // ── No merge — normal registration ────────────────────────────────────────

    public function test_no_merge_when_dni_does_not_match_any_ghost(): void
    {
        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Nuevo')
            ->set('email', 'nuevo@example.com')
            ->set('dni', '99999999Z')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'dni'   => '99999999Z',
        ]);
    }

    public function test_dni_is_saved_for_new_viticulturist_when_no_ghost_found(): void
    {
        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'María')
            ->set('email', 'maria@example.com')
            ->set('dni', '11111111H')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'dni'   => '11111111H',
        ]);
    }

    public function test_no_dni_provided_creates_account_without_it(): void
    {
        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Sin DNI')
            ->set('email', 'sodni@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertDatabaseHas('users', ['email' => 'sodni@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'sodni@example.com', 'dni' => null]);
    }

    // ── Guard: email already taken ─────────────────────────────────────────────

    public function test_merge_blocked_when_email_belongs_to_active_account(): void
    {
        $this->makeGhost('12345678A');

        // Another active user already owns the email the ghost wants to use
        User::factory()->create([
            'email'     => 'taken@example.com',
            'can_login' => true,
        ]);

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Juan Real')
            ->set('email', 'taken@example.com')
            ->set('dni', '12345678A')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['email']);
    }

    // ── Guard: DNI already active ──────────────────────────────────────────────

    public function test_duplicate_active_dni_shows_validation_error(): void
    {
        User::factory()->create([
            'email'     => 'active@example.com',
            'dni'       => '12345678A',
            'can_login' => true,
        ]);

        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Clon')
            ->set('email', 'clon@example.com')
            ->set('dni', '12345678A')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['dni']);
    }

    // ── DNI validation ─────────────────────────────────────────────────────────

    public function test_invalid_dni_format_shows_validation_error(): void
    {
        Livewire::test(Register::class)
            ->set('role', 'viticulturist')
            ->set('name', 'Test')
            ->set('email', 'test@example.com')
            ->set('dni', '12345 678 A!') // spaces and special char
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['dni']);
    }
}
