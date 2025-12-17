<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequirePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_redirects_when_password_change_required(): void
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

        $this->actingAs($created)
            ->get(route('viticulturist.dashboard'))
            ->assertRedirect(route('auth.change-password-required'));
    }

    public function test_middleware_allows_access_when_password_changed(): void
    {
        $user = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(), // Email verificado = password cambiado
        ]);

        $this->actingAs($user)
            ->get(route('viticulturist.dashboard'))
            ->assertStatus(200);
    }

    public function test_middleware_caches_result_in_session(): void
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

        // Primera llamada (crea cache)
        $this->get(route('viticulturist.dashboard'));

        // Verificar que el cache existe
        $cacheKey = "user_{$created->id}_needs_password_change";
        $this->assertTrue(session()->has($cacheKey));
        $this->assertTrue(session()->get($cacheKey));
    }

    public function test_middleware_clears_cache_after_password_change(): void
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

        // Simular cache
        session()->put("user_{$created->id}_needs_password_change", true);

        // Cambiar contraseña
        $created->password = Hash::make('new-password');
        $created->email_verified_at = now();
        $created->save();

        // El cache debe estar limpio
        $cacheKey = "user_{$created->id}_needs_password_change";
        $this->assertFalse(session()->has($cacheKey));
    }

    public function test_middleware_allows_access_to_change_password_route(): void
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

        $this->actingAs($created)
            ->get(route('auth.change-password-required'))
            ->assertStatus(200);
    }
}

