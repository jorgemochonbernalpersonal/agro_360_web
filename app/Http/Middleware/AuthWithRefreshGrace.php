<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication middleware for the /refresh endpoint.
 *
 * Unlike auth:sanctum, this middleware accepts tokens that have recently expired,
 * giving the mobile app a grace window to silently renew the session without
 * forcing a manual re-login.
 *
 * Flow:
 *   1. Token still valid            → refresh granted (normal case).
 *   2. Token expired ≤ GRACE_DAYS   → refresh granted (silent renewal).
 *   3. Token expired > GRACE_DAYS   → 401, user must log in again.
 *   4. Token not found / tampered   → 401.
 */
class AuthWithRefreshGrace
{
    /** Days after expiry during which a refresh is still accepted. */
    private const GRACE_DAYS = 7;

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // PersonalAccessToken::findToken() verifies the SHA-256 hash without
        // checking expiry — exactly what we need here.
        $token = PersonalAccessToken::findToken($bearer);

        if (! $token) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // Allow tokens that are still valid OR within the grace window.
        if ($token->expires_at !== null) {
            $deadline = Carbon::parse($token->expires_at)->addDays(self::GRACE_DAYS);
            if (now()->isAfter($deadline)) {
                return response()->json([
                    'message' => 'La sesión ha expirado. Por favor, inicia sesión de nuevo.',
                ], 401);
            }
        }

        $user = $token->tokenable;

        // Guard against orphaned tokens (token row exists but user was deleted).
        if (! $user) {
            $token->delete();
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // withAccessToken() makes $request->user()->currentAccessToken() return
        // this token, so the controller can call $currentToken->delete() etc.
        $user->withAccessToken($token);

        // setUserResolver() makes $request->user() work for downstream middleware
        // (check.can_login) and the controller itself.
        $request->setUserResolver(fn (?string $guard = null) => $user);

        $token->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
