<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_public_users_can_register_as_winery(): void
    {
        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Bodega Test')
            ->set('email', 'winery@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'winery')
            ->call('register')
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'winery@example.com',
            'role' => 'winery',
        ]);

        $this->assertAuthenticated();
    }

    public function test_public_users_can_register_as_viticulturist(): void
    {
        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Viticultor Test')
            ->set('email', 'viticulturist@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'viticulturist')
            ->call('register')
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'viticulturist@example.com',
            'role' => 'viticulturist',
        ]);

        $this->assertAuthenticated();
    }

    public function test_public_users_cannot_register_as_admin_or_supervisor(): void
    {
        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Admin Test')
            ->set('email', 'admin@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'admin')
            ->call('register')
            ->assertHasErrors(['role']);

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@example.com',
        ]);

        $this->assertGuest();
    }

    public function test_registration_requires_all_fields(): void
    {
        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('register')
            ->assertHasErrors(['name', 'email', 'password']);
    }

    public function test_registration_requires_valid_email(): void
    {
        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'viticulturist')
            ->call('register')
            ->assertHasErrors(['email']);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'viticulturist')
            ->call('register')
            ->assertHasErrors(['email']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different-password')
            ->set('role', 'viticulturist')
            ->call('register')
            ->assertHasErrors(['password']);
    }

    public function test_supervisor_can_create_winery(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Nueva Bodega')
            ->set('email', 'newwinery@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'winery')
            ->call('register')
            ->assertRedirect(route('supervisor.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'newwinery@example.com',
            'role' => 'winery',
        ]);

        // Verificar que se creó la relación supervisor_winery
        $winery = User::where('email', 'newwinery@example.com')->first();
        $this->assertDatabaseHas('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);
    }

    public function test_supervisor_can_create_viticulturist(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'newviticulturist@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'viticulturist')
            ->call('register')
            ->assertRedirect(route('supervisor.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'newviticulturist@example.com',
            'role' => 'viticulturist',
        ]);
    }

    public function test_winery_can_create_viticulturist(): void
    {
        $winery = User::factory()->create([
            'role' => 'winery',
        ]);

        $this->actingAs($winery);

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'newviticulturist@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'viticulturist')
            ->call('register')
            ->assertRedirect(route('winery.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'newviticulturist@example.com',
            'role' => 'viticulturist',
        ]);

        // Verificar que se creó la relación winery_viticulturist
        $viticulturist = User::where('email', 'newviticulturist@example.com')->first();
        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => 'own',
            'assigned_by' => $winery->id,
        ]);
    }

    public function test_admin_can_create_any_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $roles = ['admin', 'supervisor', 'winery', 'viticulturist'];

        foreach ($roles as $role) {
            Livewire::test(\App\Livewire\Auth\Register::class)
                ->set('name', "Test {$role}")
                ->set('email', "{$role}@test.com")
                ->set('password', 'password123')
                ->set('password_confirmation', 'password123')
                ->set('role', $role)
                ->call('register');

            $this->assertDatabaseHas('users', [
                'email' => "{$role}@test.com",
                'role' => $role,
            ]);
        }
    }

    public function test_viticulturist_cannot_create_other_roles(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
        ]);

        $this->actingAs($viticulturist);

        // Intentar crear winery (no permitido)
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test Winery')
            ->set('email', 'winery@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'winery')
            ->call('register')
            ->assertHasErrors(['role']);

        $this->assertDatabaseMissing('users', [
            'email' => 'winery@test.com',
        ]);
    }
}

