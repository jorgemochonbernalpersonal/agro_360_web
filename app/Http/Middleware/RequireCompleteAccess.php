<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompleteAccess
{
    /**
     * Bloquea el acceso a funcionalidades del plan Completo para viticultores
     * que solo tienen el plan Básico gratuito (disponible para todos los viticultores sin suscripción activa).
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

        // Viticultor en plan básico gratuito → redirigir a upgrade
        if ($user->hasBasicFreeAccess()) {
            $price = number_format($user->viticulturistMonthlyPrice(), 0);

            return redirect()->route('subscription.manage')
                ->with('upgrade_required', [
                    'price'   => $price,
                    'feature' => $request->route()?->getName() ?? $request->path(),
                ]);
        }

        // Sin acceso en absoluto → página de precios
        return redirect()->route('pricing')
            ->with('error', 'Necesitas una suscripción para acceder a esta funcionalidad.');
    }
}
