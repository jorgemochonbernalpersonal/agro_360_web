<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cachear en sesión para evitar queries repetidas en la misma sesión
            // Solo se recalcula si el usuario cambia su contraseña o verifica email
            $sessionKey = "user_{$user->id}_needs_password_change";
            $needsChange = session()->get($sessionKey);
            
            if ($needsChange === null) {
                $needsChange = $user->needsPasswordChange();
                session()->put($sessionKey, $needsChange);
            }
            
            // Si necesita cambiar contraseña y no está en la ruta de cambio de contraseña
            if ($needsChange && !$request->routeIs('auth.change-password-required')) {
                return redirect()->route('auth.change-password-required');
            }
        }

        return $next($request);
    }
}

