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
        // Beta expirada → el usuario se degrada al plan Free automáticamente.
        // Los módulos premium quedan bloqueados vía require.ability / winery.ability.
        // No se redirige a beta.expired: nadie pierde el acceso operativo.
        return $next($request);
    }
}
