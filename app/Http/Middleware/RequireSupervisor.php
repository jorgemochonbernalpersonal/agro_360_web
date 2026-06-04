<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSupervisor
{
    /**
     * Bloquea el acceso a funcionalidades exclusivas de viticultores adscritos a una DO.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasViticulturistAccess() || ! $user->hasSupervisor()) {
            return redirect()->route('viticulturist.dashboard')
                ->with('warning', __('Esta funcionalidad requiere estar adscrito a una Denominación de Origen.'));
        }

        return $next($request);
    }
}
