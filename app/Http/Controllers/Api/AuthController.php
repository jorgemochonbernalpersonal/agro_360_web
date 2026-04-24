<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES    = 15;

    // ─── POST /register ───────────────────────────────────────────────────────

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email:rfc,dns|max:255|unique:users,email',
            'password'    => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
            'role'        => 'sometimes|string|in:winery,viticulturist,producer,supervisor',
            'device_name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-_. ]+$/'],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'] ?? 'winery',
            'can_login' => true,
        ]);

        $user->notify(new \App\Notifications\MobileVerifyEmailNotification());

        SecurityLogger::logSecurityEvent('register_success', [
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        // No emitir token hasta que el email esté verificado
        return response()->json([
            'message'          => 'Cuenta creada. Verifica tu email para continuar.',
            'email_unverified' => true,
            'user'             => new UserResource($user),
        ], 201);
    }

    // ─── POST /login ──────────────────────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-_. ]+$/'],
        ]);

        $throttleKey = 'login:' . strtolower($request->email) . '|' . $request->ip();

        // ── Bloqueo por intentos excesivos ────────────────────────────────────
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            SecurityLogger::logAccountLocked($request->email);

            return response()->json([
                'message'     => 'Demasiados intentos. Inténtalo en ' . ceil($seconds / 60) . ' minutos.',
                'retry_after' => $seconds,
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_MINUTES * 60);
            SecurityLogger::logFailedLogin($request->email);

            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        if (! $user->can_login) {
            SecurityLogger::logAccessDenied($user->id, 'login', 'can_login=false');
            return response()->json(['message' => 'Cuenta desactivada. Contacta con soporte.'], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message'          => 'Verifica tu email para continuar.',
                'email_unverified' => true,
            ], 403);
        }

        // Login exitoso → guardar contador antes de limpiar
        $failedAttempts = RateLimiter::attempts($throttleKey);
        RateLimiter::clear($throttleKey);

        if ($failedAttempts > 0) {
            SecurityLogger::logSuccessfulLoginAfterFailures(
                $user->id,
                $user->email,
                $failedAttempts
            );
        }

        // Beta expirada sin acceso básico gratuito → bloquear login
        if ($user->betaExpired() && !$user->hasBasicFreeAccess()) {
            return response()->json([
                'message'      => 'Tu periodo de prueba ha finalizado. Renueva tu suscripción para continuar usando Agro365.',
                'beta_expired' => true,
            ], 403);
        }

        $device = $request->device_name ?? 'mobile';

        // Revocar token previo del mismo dispositivo para no acumular
        $user->tokens()->where('name', $device)->delete();

        $token = $user->createToken($device, [$user->role], $this->tokenExpiresAt())->plainTextToken;

        return response()->json([
            'token'      => $token,
            'expires_in' => $this->tokenExpiresIn(),
            'user'       => new UserResource($user->load('profile')),
        ]);
    }

    // ─── GET /me ──────────────────────────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->betaExpired() && !$user->hasBasicFreeAccess()) {
            return response()->json([
                'message'      => 'Tu periodo de prueba ha finalizado.',
                'beta_expired' => true,
            ], 403);
        }

        return response()->json([
            'user' => new UserResource($user->load('profile')),
        ]);
    }

    // ─── POST /claim-account ──────────────────────────────────────────────────

    public function claimAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'       => 'required|string',
            'name'        => 'required|string|max:255',
            'email'       => 'required|email:rfc|max:255',
            'password'    => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
            'device_name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-_. ]+$/'],
        ]);

        // Búsqueda directa por hash SHA-256 (O(1) vs O(n) bcrypt)
        $user = User::where('can_login', false)
            ->where('invitation_expires_at', '>', now())
            ->where('invitation_token', hash('sha256', $validated['token']))
            ->first();

        if (! $user) {
            return response()->json(['message' => 'El token de invitación no es válido o ha expirado.'], 422);
        }

        if (User::where('email', $validated['email'])->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => 'Este email ya está registrado.'], 422);
        }

        $user->update([
            'name'                  => $validated['name'],
            'email'                 => $validated['email'],
            'password'              => Hash::make($validated['password']),
            'can_login'             => true,
            'email_verified_at'     => now(),
            'invitation_token'      => null,
            'invitation_expires_at' => null,
            'invitation_sent_at'    => null,
        ]);

        $device = $validated['device_name'] ?? 'mobile';
        $token  = $user->fresh()->createToken($device, [$user->role], $this->tokenExpiresAt())->plainTextToken;

        return response()->json([
            'token'      => $token,
            'expires_in' => $this->tokenExpiresIn(),
            'user'       => new UserResource($user->fresh()),
        ], 201);
    }

    // ─── POST /logout ─────────────────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    // ─── POST /logout-all ─────────────────────────────────────────────────────

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Todas las sesiones han sido cerradas.']);
    }

    // ─── PUT /me ──────────────────────────────────────────────────────────────

    public function updateMe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone'        => 'sometimes|nullable|string|max:20',
            'address'      => 'sometimes|nullable|string|max:255',
            'city'         => 'sometimes|nullable|string|max:100',
            'postal_code'  => 'sometimes|nullable|string|max:10',
            'country'      => 'sometimes|nullable|string|max:100',
            'province_id'  => 'sometimes|nullable|integer|exists:provinces,id',
        ]);

        $user = $request->user();

        if (isset($validated['name'])) {
            $user->update(['name' => $validated['name']]);
        }

        $profileFields = array_intersect_key($validated, array_flip([
            'phone', 'address', 'city', 'postal_code', 'country', 'province_id',
        ]));

        if (! empty($profileFields)) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileFields);
        }

        return response()->json([
            'user' => new UserResource($user->fresh()->load('profile')),
        ]);
    }

    // ─── POST /change-password ────────────────────────────────────────────────

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $user->update([
            'password'            => Hash::make($request->password),
            'password_must_reset' => false,
        ]);

        // Revocar todos los tokens excepto el actual
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        SecurityLogger::logSecurityEvent('password_changed', ['user_id' => $user->id]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    // ─── POST /refresh ────────────────────────────────────────────────────────

    public function refresh(Request $request): JsonResponse
    {
        $user         = $request->user();
        $currentToken = $user->currentAccessToken();
        $device       = $currentToken->name;

        // Beta expirada sin acceso básico gratuito → bloquear refresh
        if ($user->betaExpired() && !$user->hasBasicFreeAccess()) {
            $currentToken->delete();

            return response()->json([
                'message'      => 'Tu periodo de prueba ha finalizado. Renueva tu suscripción para continuar usando Agro365.',
                'beta_expired' => true,
            ], 403);
        }

        $currentToken->delete();

        $token = $user->createToken($device, [$user->role], $this->tokenExpiresAt())->plainTextToken;

        return response()->json([
            'token'      => $token,
            'expires_in' => $this->tokenExpiresIn(),
        ]);
    }

    // ─── POST /email/resend ───────────────────────────────────────────────────

    public function resendVerification(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'El email ya está verificado.']);
        }

        $request->user()->notify(new \App\Notifications\MobileVerifyEmailNotification());

        return response()->json(['message' => 'Correo de verificación enviado.']);
    }

    // ─── DELETE /account ─────────────────────────────────────────────────────

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña no es correcta.'],
            ]);
        }

        SecurityLogger::logSecurityEvent('account_deleted', [
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        // Revocar todos los tokens antes de borrar (evita race conditions)
        $user->tokens()->delete();

        // Borrado en cascada: todas las tablas con FK user_id/winery_id/viticulturist_id
        // tienen ON DELETE CASCADE en las migraciones.
        $user->delete();

        return response()->json(['message' => 'Cuenta eliminada correctamente.'], 200);
    }

    // ─── POST /forgot-password ────────────────────────────────────────────────

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // Buscamos el usuario sin revelar si existe o no en la respuesta
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Password::createToken($user);
            $user->notify(new \App\Notifications\MobileResetPasswordNotification($token));
        }

        return response()->json([
            'message' => 'Si el correo está registrado, recibirás un enlace de recuperación.',
        ]);
    }

    // ─── POST /auth/google ────────────────────────────────────────────────────

    public function loginWithGoogle(Request $request): JsonResponse
    {
        $request->validate([
            'id_token'    => 'required|string',
            'device_name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-_. ]+$/'],
        ]);

        // Verificar el ID token con Google
        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->id_token,
        ]);

        if (! $response->successful()) {
            return response()->json(['message' => 'Token de Google inválido.'], 422);
        }

        $payload = $response->json();

        // Verificar issuer válido de Google
        $validIssuers = ['accounts.google.com', 'https://accounts.google.com'];
        if (! in_array($payload['iss'] ?? '', $validIssuers, true)) {
            return response()->json(['message' => 'Token de Google inválido.'], 422);
        }

        // Verificar que el token no ha expirado (defensa en profundidad)
        if ((int) ($payload['exp'] ?? 0) < time()) {
            return response()->json(['message' => 'Token de Google expirado.'], 422);
        }

        // Verificar que el email está verificado por Google
        $emailVerified = $payload['email_verified'] ?? false;
        if ($emailVerified !== true && $emailVerified !== 'true') {
            return response()->json(['message' => 'El email de Google no está verificado.'], 422);
        }

        // Verificar que el token es para esta app
        $clientId = config('services.google.client_id');
        if ($clientId && ($payload['aud'] ?? '') !== $clientId) {
            return response()->json(['message' => 'Token de Google inválido.'], 422);
        }

        $googleId = $payload['sub'] ?? null;
        $email    = $payload['email'] ?? null;
        $name     = $payload['name'] ?? null;

        if (! $googleId || ! $email) {
            return response()->json(['message' => 'No se pudo obtener la información del perfil de Google.'], 422);
        }

        // Buscar usuario por google_id o email
        $user = User::where('google_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            // Cuenta desactivada manualmente
            if (! $user->can_login) {
                return response()->json(['message' => 'Cuenta desactivada. Contacta con soporte.'], 403);
            }

            // Vincular google_id si aún no lo tiene
            if (! $user->google_id) {
                $user->update(['google_id' => $googleId]);
            }
        } else {
            // Crear nuevo usuario — Google ya verificó el email
            $user = User::create([
                'name'              => $name ?? $email,
                'email'             => $email,
                'google_id'         => $googleId,
                'password'          => Hash::make(str()->random(32)), // password inutilizable
                'role'              => 'winery',
                'can_login'         => true,
                'email_verified_at' => now(),
            ]);

            SecurityLogger::logSecurityEvent('register_google', [
                'user_id' => $user->id,
                'email'   => $email,
            ]);
        }

        // Beta expirada
        if ($user->betaExpired() && ! $user->hasBasicFreeAccess()) {
            return response()->json([
                'message'      => 'Tu periodo de prueba ha finalizado. Renueva tu suscripción para continuar usando Agro365.',
                'beta_expired' => true,
            ], 403);
        }

        $device = $request->device_name ?? 'mobile';
        $user->tokens()->where('name', $device)->delete();
        $token = $user->createToken($device, [$user->role], $this->tokenExpiresAt())->plainTextToken;

        SecurityLogger::logSecurityEvent('login_google', ['user_id' => $user->id]);

        return response()->json([
            'token'      => $token,
            'expires_in' => $this->tokenExpiresIn(),
            'user'       => new UserResource($user->fresh()->load('profile')),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function tokenExpiresAt(): \Illuminate\Support\Carbon
    {
        return now()->addMinutes((int) config('sanctum.expiration', 43200));
    }

    private function tokenExpiresIn(): int
    {
        return (int) config('sanctum.expiration', 43200) * 60;
    }

    // ─── POST /reset-password ─────────────────────────────────────────────────

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                // Revocar todos los tokens activos al cambiar la contraseña
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'El enlace de recuperación no es válido o ha expirado.',
            ], 422);
        }

        SecurityLogger::logSecurityEvent('password_reset_completed', [
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente. Por favor, inicia sesión de nuevo.',
        ]);
    }
}
