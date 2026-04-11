<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que la bodega autenticada tenga la habilidad/módulo requerido.
 *
 * Uso en rutas:  ->middleware('winery.ability:harvest_reception')
 *
 * Lógica:
 *  - Si el usuario no tiene acceso a bodega → 403.
 *  - Si el usuario no tiene NINGUNA ability configurada → acceso total (sin supervisor restrictivo).
 *  - Si tiene abilities configuradas → comprueba que tenga la requerida.
 *
 * Cache:
 *  - Clave: "winery:{id}:granted_abilities"  TTL: 60s
 *  - Se invalida en Supervisor\Oversight\Wineries\Show::toggleAbility()
 */
class CheckWineryAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->hasWineryAccess()) {
            abort(403);
        }

        $granted = Cache::remember(
            "winery:{$user->id}:granted_abilities",
            60,
            fn () => $user->abilities()->pluck('code')->all()
        );

        // Sin restricciones → acceso total
        if (empty($granted)) {
            return $next($request);
        }

        if (! in_array($ability, $granted, true)) {
            abort(403, 'Tu denominación de origen no ha habilitado el módulo requerido para acceder a esta sección.');
        }

        return $next($request);
    }
}
