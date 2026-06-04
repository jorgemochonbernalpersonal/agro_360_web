<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireWinery
{
    /**
     * Bloquea el acceso a funcionalidades exclusivas de viticultores vinculados a bodega.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasViticulturistAccess() || ! $user->hasWinery()) {
            return redirect()->route('viticulturist.dashboard')
                ->with('warning', __('Esta funcionalidad requiere estar vinculado a una bodega.'));
        }

        return $next($request);
    }
}
