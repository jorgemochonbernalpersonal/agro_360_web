<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBetaAccess
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Si el usuario es beta y la beta expiró...
        if ($user->betaExpired()) {
            // Todos los viticultores (vinculados o independientes) y productores
            // conservan el plan Básico gratis de forma permanente. Las funciones del
            // plan Completo se bloquean aparte vía el middleware require.complete.
            if ($user->hasBasicFreeAccess()) {
                return $next($request);
            }

            return redirect()->route('beta.expired');
        }

        return $next($request);
    }
}
