<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            // Loguear intento de acceso denegado
            \App\Services\SecurityLogger::logAccessDenied(
                $user->id,
                $request->fullUrl(),
                'role_mismatch: required=' . implode(',', $roles) . ' actual=' . $user->role
            );
            
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}
