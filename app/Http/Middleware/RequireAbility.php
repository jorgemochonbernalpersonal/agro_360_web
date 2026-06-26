<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gatea una ruta por ability del plan efectivo del usuario.
 *
 * Uso:  ->middleware('require.ability:remote_sensing')
 *
 * Sustituye a require.complete + check.beta para el gating por capacidad.
 * El plan efectivo (free / vit_pro / winery / do) se resuelve en User::effectivePlan().
 */
class RequireAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasPlanAbility($ability)) {
            return $next($request);
        }

        // Viticultor / productor en plan free → upgrade a Pro
        if ($user->hasBasicFreeAccess()) {
            return redirect()->route('subscription.manage')
                ->with('upgrade_required', [
                    'price' => number_format($user->viticulturistMonthlyPrice(), 0),
                    'feature' => $request->route()?->getName() ?? $request->path(),
                ]);
        }

        // Bodega / DO sin acceso al módulo → página de precios
        return redirect()->route('pricing')
            ->with('error', __('Necesitas una suscripción activa para acceder a esta funcionalidad.'));
    }
}
