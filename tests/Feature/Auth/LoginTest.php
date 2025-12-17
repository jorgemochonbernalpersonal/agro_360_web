<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_login_component(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'viticulturist',
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('viticulturist.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_email(): void
    {
        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'nonexistent@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_redirects_to_correct_dashboard_by_role(): void
    {
        $roles = [
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
        ];

        foreach ($roles as $role => $dashboardRoute) {
            $user = User::factory()->create([
                'email' => "{$role}@example.com",
                'password' => bcrypt('password'),
                'role' => $role,
            ]);

            $this->get('/login');

            Livewire::test(\App\Livewire\Auth\Login::class)
                ->set('email', "{$role}@example.com")
                ->set('password', 'password')
                ->call('login')
                ->assertRedirect(route($dashboardRoute));

            $this->assertAuthenticatedAs($user);
            
            auth()->logout();
        }
    }

    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('remember', true)
            ->call('login');

        $this->assertAuthenticatedAs($user);
        // Verificar que el remember token está configurado
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_user_created_by_another_user_can_login_without_email_verification(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $created = User::factory()->create([
            'email' => 'created@example.com',
            'password' => Hash::make('temporary-password'),
            'role' => 'viticulturist',
            'email_verified_at' => null, // No verificado
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'created@example.com')
            ->set('password', 'temporary-password')
            ->call('login')
            ->assertRedirect(route('auth.change-password-required')); // Redirige a cambio de contraseña

        $this->assertAuthenticatedAs($created);
    }

    public function test_user_created_by_another_user_is_redirected_to_change_password(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'email' => 'created@example.com',
            'password' => Hash::make('temporary-password'),
            'role' => 'viticulturist',
            'email_verified_at' => null,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'created@example.com')
            ->set('password', 'temporary-password')
            ->call('login')
            ->assertRedirect(route('auth.change-password-required'));
    }

    public function test_user_with_password_change_required_cannot_access_dashboard(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        
        $created = User::factory()->create([
            'email' => 'created@example.com',
            'password' => Hash::make('temporary-password'),
            'role' => 'viticulturist',
            'email_verified_at' => null,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($created)
            ->get(route('viticulturist.dashboard'))
            ->assertRedirect(route('auth.change-password-required'));
    }
}

