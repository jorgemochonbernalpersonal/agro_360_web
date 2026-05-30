<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCanLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->can_login) {
            // Permitir que un admin que está impersonando vea cuentas desactivadas.
            if ($request->hasSession() && $request->session()->get('impersonating')) {
                return $next($request);
            }

            // Revocar todos los tokens para forzar re-autenticación si se reactiva
            $user->tokens()->delete();

            SecurityLogger::logAccessDenied($user->id, $request->path(), 'can_login=false');

            // API → JSON 403. Web → cerrar sesión y redirigir al login.
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => __('Cuenta desactivada. Contacta con soporte.')], 403);
            }

            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => __('Cuenta desactivada. Contacta con soporte.'),
            ]);
        }

        return $next($request);
    }
}
