<?php

namespace Tests\Feature\Supervisor\Settings;

use App\Livewire\Supervisor\Settings\Index;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class SettingsTest extends SupervisorTestCase
{
    // ── updateProfile ─────────────────────────────────────────────────────────

    public function test_update_profile_persists_name_and_email(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('name', 'Nuevo Nombre DO')
            ->set('email', 'nuevo@do.com')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $fresh = $supervisor->fresh();
        $this->assertEquals('Nuevo Nombre DO', $fresh->name);
        $this->assertEquals('nuevo@do.com', $fresh->email);
    }

    public function test_update_profile_requires_name(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('name', '')
            ->set('email', $supervisor->email)
            ->call('updateProfile')
            ->assertHasErrors(['name']);
    }

    public function test_update_profile_requires_valid_email(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('name', $supervisor->name)
            ->set('email', 'no-es-un-email')
            ->call('updateProfile')
            ->assertHasErrors(['email']);
    }

    public function test_update_profile_email_must_be_unique_across_users(): void
    {
        $supervisor = $this->makeSupervisor();
        $other = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('name', $supervisor->name)
            ->set('email', $other->email)
            ->call('updateProfile')
            ->assertHasErrors(['email']);
    }

    public function test_update_profile_allows_same_email_for_own_user(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('name', 'Nombre Actualizado')
            ->set('email', $supervisor->email)
            ->call('updateProfile')
            ->assertHasNoErrors();
    }

    public function test_mount_prefills_name_and_email(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('name', $supervisor->name)
            ->assertSet('email', $supervisor->email);
    }

    // ── updatePassword ────────────────────────────────────────────────────────

    public function test_update_password_changes_hash(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'password' => Hash::make('OldPass123'),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('current_password', 'OldPass123')
            ->set('new_password', 'NewPass456!')
            ->set('confirm_password', 'NewPass456!')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertTrue(Hash::check('NewPass456!', $supervisor->fresh()->password));
    }

    public function test_update_password_rejects_wrong_current_password(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'password' => Hash::make('OldPass123'),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('current_password', 'WrongPassword')
            ->set('new_password', 'NewPass456!')
            ->set('confirm_password', 'NewPass456!')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }

    public function test_update_password_requires_confirmation_match(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'password' => Hash::make('OldPass123'),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('current_password', 'OldPass123')
            ->set('new_password', 'NewPass456!')
            ->set('confirm_password', 'DifferentPass!')
            ->call('updatePassword')
            ->assertHasErrors(['new_password']);
    }

    public function test_update_password_requires_minimum_eight_characters(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'password' => Hash::make('OldPass123'),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('current_password', 'OldPass123')
            ->set('new_password', 'short')
            ->set('confirm_password', 'short')
            ->call('updatePassword')
            ->assertHasErrors(['new_password']);
    }

    public function test_update_password_clears_fields_on_success(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'password' => Hash::make('OldPass123'),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('current_password', 'OldPass123')
            ->set('new_password', 'NewPass456!')
            ->set('confirm_password', 'NewPass456!')
            ->call('updatePassword')
            ->assertSet('current_password', '')
            ->assertSet('new_password', '')
            ->assertSet('confirm_password', '');
    }
}
