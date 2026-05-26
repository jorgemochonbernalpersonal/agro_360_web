<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCanLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->can_login) {
            // Revocar todos los tokens para forzar re-autenticación si se reactiva
            $user->tokens()->delete();

            SecurityLogger::logAccessDenied($user->id, $request->path(), 'can_login=false');

            return response()->json(['message' => __('Cuenta desactivada. Contacta con soporte.')], 403);
        }

        return $next($request);
    }
}
