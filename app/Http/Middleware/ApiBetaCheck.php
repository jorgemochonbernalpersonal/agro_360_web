<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBetaCheck
{
    /**
     * Verifica acceso beta/suscripción en rutas API.
     * Equivalente a CheckBetaAccess pero devuelve JSON en lugar de redirect.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Beta expirada → el usuario se degrada al plan Free automáticamente.
        // Los módulos premium quedan bloqueados vía winery.ability / require.ability.
        // No se rechaza la petición aquí: la lógica de acceso vive en los gates de ability.
        return $next($request);
    }
}
