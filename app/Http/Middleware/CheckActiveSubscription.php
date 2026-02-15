<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Verificar si tiene acceso (suscripción activa o beta)
        if (!$user->hasActiveAccess()) {
            return redirect()->route('pricing')
                ->with('error', 'Tu suscripción ha expirado. Por favor, renueva tu plan para continuar.');
        }

        // Si tiene acceso beta, añadir header informativo
        if ($user->isBetaUser()) {
            $daysRemaining = $user->betaDaysRemaining();
            
            if ($daysRemaining <= 7 && $daysRemaining > 0) {
                session()->flash('warning', "Tu período beta expira en {$daysRemaining} días. Considera suscribirte para seguir usando Agro365.");
            }
        }

        return $next($request);
    }
}
