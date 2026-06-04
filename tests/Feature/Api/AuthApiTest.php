<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    // ── POST /api/v1/register ─────────────────────────────────────────────────

    public function test_register_returns_token_and_user(): void
    {
        // Usar gmail.com — tiene registros MX reales (requerido por email:rfc,dns)
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => 'Password123abc',
            'password_confirmation' => 'Password123abc',
            'role' => 'winery',
        ]);

        // El registro no emite token hasta verificar email
        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'email_unverified', 'user' => ['id', 'email', 'role']])
            ->assertJsonPath('email_unverified', true);

        $this->assertDatabaseHas('users', ['email' => 'testuser@gmail.com', 'role' => 'winery']);
    }

    public function test_register_defaults_role_to_winery(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'testuser2@gmail.com',
            'password' => 'Password123abc',
            'password_confirmation' => 'Password123abc',
        ]);

        $response->assertStatus(201)->assertJsonPath('user.role', 'winery');
    }

    public function test_register_rejects_weak_password(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_password_without_uppercase(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123abc',
            'password_confirmation' => 'password123abc',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/v1/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'Password123abc',
            'password_confirmation' => 'Password123abc',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_invalid_role(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123abc',
            'password_confirmation' => 'Password123abc',
            'role' => 'admin',
        ])->assertStatus(422)->assertJsonValidationErrors(['role']);
    }

    // ── POST /api/v1/login ────────────────────────────────────────────────────

    public function test_login_returns_token_and_user(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123abc'),
            'can_login' => true,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password123abc',
        ])->assertStatus(200)->assertJsonStructure(['token', 'expires_in', 'user']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123abc'),
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Wrongpassword1',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_login_rejects_nonexistent_user(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123abc',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_login_rejects_disabled_user(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123abc'),
            'can_login' => false,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password123abc',
        ])->assertStatus(403);
    }

    public function test_login_replaces_existing_token_for_same_device(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123abc'),
            'can_login' => true,
        ]);
        $user->createToken('android');

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password123abc',
            'device_name' => 'android',
        ])->assertStatus(200);

        $this->assertEquals(1, $user->tokens()->where('name', 'android')->count());
    }

    // ── GET /api/v1/me ────────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonStructure(['user' => [
                'id', 'name', 'email', 'role', 'password_must_reset', 'created_at',
            ]]);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    // ── PUT /api/v1/me ────────────────────────────────────────────────────────

    public function test_update_me_changes_name(): void
    {
        $user = User::factory()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/me', ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonPath('user.name', 'New Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_update_me_creates_profile_when_missing(): void
    {
        $user = User::factory()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/me', ['phone' => '600123456', 'city' => 'Madrid'])
            ->assertStatus(200);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'phone' => '600123456',
            'city' => 'Madrid',
        ]);
    }

    public function test_update_me_updates_existing_profile(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $user->profile()->create(['user_id' => $user->id, 'city' => 'Valencia']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/me', ['city' => 'Barcelona'])
            ->assertStatus(200);

        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'city' => 'Barcelona']);
        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id, 'city' => 'Valencia']);
    }

    // ── POST /api/v1/logout ───────────────────────────────────────────────────

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/logout')->assertStatus(200);

        // Verificar en DB que el token fue eliminado (Sanctum cachea en memoria en tests)
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_logout_only_revokes_current_token(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;
        $user->createToken('tablet');

        $this->withToken($token)->postJson('/api/v1/logout');

        $this->assertEquals(1, $user->tokens()->count());
    }

    // ── POST /api/v1/logout-all ───────────────────────────────────────────────

    public function test_logout_all_revokes_all_tokens(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $t1 = $user->createToken('device1')->plainTextToken;
        $user->createToken('device2');

        $this->withToken($t1)->postJson('/api/v1/logout-all')->assertStatus(200);

        $this->assertEquals(0, $user->tokens()->count());
    }

    // ── POST /api/v1/refresh ──────────────────────────────────────────────────

    public function test_refresh_returns_new_token(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/refresh');

        $response->assertStatus(200)->assertJsonStructure(['token', 'expires_in']);
        $this->assertNotEquals($token, $response->json('token'));
    }

    public function test_refresh_invalidates_old_token(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        // Extraer el ID del token (formato: "id|hash")
        $tokenId = explode('|', $token)[0];

        $this->withToken($token)->postJson('/api/v1/refresh');

        // El token original ya no existe en DB
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_refresh_keeps_same_device_name(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('my-android')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/refresh');

        $this->assertEquals(1, $user->tokens()->where('name', 'my-android')->count());
    }

    // ── POST /api/v1/change-password ──────────────────────────────────────────

    public function test_change_password_succeeds_with_correct_current(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1'),
            'can_login' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/change-password', [
            'current_password' => 'OldPassword1',
            'password' => 'NewPassword2',
            'password_confirmation' => 'NewPassword2',
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('NewPassword2', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1'),
            'can_login' => true,
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/change-password', [
            'current_password' => 'WrongPassword1',
            'password' => 'NewPassword2',
            'password_confirmation' => 'NewPassword2',
        ])->assertStatus(422)->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_clears_password_must_reset_flag(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1'),
            'password_must_reset' => true,
            'can_login' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/change-password', [
            'current_password' => 'OldPassword1',
            'password' => 'NewPassword2',
            'password_confirmation' => 'NewPassword2',
        ]);

        $this->assertFalse($user->fresh()->password_must_reset);
    }

    public function test_change_password_revokes_other_sessions(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1'),
            'can_login' => true,
        ]);
        $current = $user->createToken('mobile')->plainTextToken;
        $user->createToken('tablet');

        $this->withToken($current)->postJson('/api/v1/change-password', [
            'current_password' => 'OldPassword1',
            'password' => 'NewPassword2',
            'password_confirmation' => 'NewPassword2',
        ]);

        // Solo debe quedar el token actual (mobile), el de tablet fue revocado
        $this->assertEquals(1, $user->tokens()->count());
        $this->assertEquals(1, $user->tokens()->where('name', 'mobile')->count());
        $this->assertEquals(0, $user->tokens()->where('name', 'tablet')->count());
    }

    // ── POST /api/v1/forgot-password ─────────────────────────────────────────

    public function test_forgot_password_always_returns_200_for_unknown_email(): void
    {
        $this->postJson('/api/v1/forgot-password', ['email' => 'nobody@example.com'])
            ->assertStatus(200);
    }

    public function test_forgot_password_returns_200_for_existing_email(): void
    {
        User::factory()->create(['email' => 'real@example.com']);

        $this->postJson('/api/v1/forgot-password', ['email' => 'real@example.com'])
            ->assertStatus(200);
    }

    // ── POST /api/v1/reset-password ───────────────────────────────────────────

    public function test_reset_password_rejects_invalid_token(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertStatus(422);
    }

    public function test_reset_password_with_valid_token_updates_password(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'can_login' => true]);
        $token = Password::createToken($user);

        $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
    }

    public function test_reset_password_revokes_all_tokens(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'can_login' => true]);
        $user->createToken('mobile');
        $user->createToken('tablet');
        $token = Password::createToken($user);

        $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $this->assertEquals(0, $user->tokens()->count());
    }

    // ── expires_in en segundos ────────────────────────────────────────────────

    public function test_expires_in_is_seconds_on_login(): void
    {
        // 30 días = 2.592.000 segundos (no minutos)
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123abc'),
            'can_login' => true,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password123abc',
        ])->assertStatus(200);

        $this->assertEquals(30 * 24 * 60 * 60, $response->json('expires_in'));
    }

    public function test_expires_in_is_seconds_on_refresh(): void
    {
        $user = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/refresh')
            ->assertStatus(200);

        $this->assertEquals(30 * 24 * 60 * 60, $response->json('expires_in'));
    }

    // ── Login con email no verificado ─────────────────────────────────────────

    public function test_login_rejects_unverified_email(): void
    {
        User::factory()->unverified()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123abc'),
            'can_login' => true,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password123abc',
        ])->assertStatus(403)
            ->assertJsonPath('email_unverified', true);
    }

    // ── forgot-password usa notificación móvil dedicada ───────────────────────

    public function test_forgot_password_sends_mobile_notification(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/v1/forgot-password', ['email' => 'test@example.com'])
            ->assertStatus(200);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \App\Notifications\MobileResetPasswordNotification::class
        );
    }
}
