<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompleteAccess
{
    /**
     * Bloquea el acceso a funcionalidades del plan Completo para viticultores
     * que solo tienen el plan Básico gratuito (vinculados a bodega sin suscripción).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Beta activo o suscripción activa → acceso completo
        if ($user->hasActiveAccess()) {
            return $next($request);
        }

        // Viticultor vinculado a bodega en plan básico → redirigir a upgrade
        if ($user->hasBasicFreeAccess()) {
            return redirect()->route('subscription.manage')
                ->with('info', 'Esta funcionalidad requiere el plan Completo (9€/mes). Actualiza tu plan para acceder.');
        }

        // Sin acceso en absoluto → página de precios
        return redirect()->route('pricing')
            ->with('error', 'Necesitas una suscripción para acceder a esta funcionalidad.');
    }
}
