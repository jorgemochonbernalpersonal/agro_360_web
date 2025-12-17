<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ChangePasswordRequiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_created_by_another_user_is_redirected_to_change_password(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null, // No verificado
            'password' => Hash::make('temporary-password'),
        ]);

        // Crear relación WineryViticulturist
        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Autenticar al usuario
        $this->actingAs($created);

        // Debe redirigir a cambio de contraseña
        $this->get(route('viticulturist.dashboard'))
            ->assertRedirect(route('auth.change-password-required'));
    }

    public function test_user_can_change_password_successfully(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
            'password' => Hash::make('temporary-password'),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($created);

        Livewire::test(\App\Livewire\Auth\ChangePasswordRequired::class)
            ->set('current_password', 'temporary-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('changePassword')
            ->assertRedirect(route('viticulturist.dashboard'));

        // Verificar que el email fue verificado
        $this->assertNotNull($created->fresh()->email_verified_at);
        
        // Verificar que puede hacer login con nueva contraseña usando Livewire
        auth()->logout();
        $this->get('/login');
        
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', $created->email)
            ->set('password', 'new-password-123')
            ->call('login')
            ->assertRedirect(route('viticulturist.dashboard'));
        
        $this->assertAuthenticatedAs($created);
    }

    public function test_user_cannot_use_same_password(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
            'password' => Hash::make('temporary-password'),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($created);

        Livewire::test(\App\Livewire\Auth\ChangePasswordRequired::class)
            ->set('current_password', 'temporary-password')
            ->set('password', 'temporary-password')
            ->set('password_confirmation', 'temporary-password')
            ->call('changePassword')
            ->assertHasErrors(['password']);
    }

    public function test_password_change_verifies_email(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
            'password' => Hash::make('temporary-password'),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->assertNull($created->email_verified_at);

        $this->actingAs($created);

        Livewire::test(\App\Livewire\Auth\ChangePasswordRequired::class)
            ->set('current_password', 'temporary-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('changePassword');

        $this->assertNotNull($created->fresh()->email_verified_at);
    }

    public function test_password_change_clears_session_cache(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
            'password' => Hash::make('temporary-password'),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($created);

        // Simular cache en sesión
        session()->put("user_{$created->id}_needs_password_change", true);

        Livewire::test(\App\Livewire\Auth\ChangePasswordRequired::class)
            ->set('current_password', 'temporary-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('changePassword');

        // El cache debe estar limpio (o el usuario ya no necesita cambiar contraseña)
        $this->assertFalse($created->fresh()->needsPasswordChange());
    }

    public function test_user_cannot_access_other_routes_until_password_changed(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
            'password' => Hash::make('temporary-password'),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($created);

        // Intentar acceder a otras rutas
        $this->get(route('viticulturist.personal.index'))
            ->assertRedirect(route('auth.change-password-required'));
    }
}

