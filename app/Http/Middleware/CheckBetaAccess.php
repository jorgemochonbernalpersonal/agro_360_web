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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }
        
        // Si el usuario es beta y la beta expiró...
        if ($user->betaExpired()) {
            // Viticultores vinculados a bodega tienen acceso básico gratis permanente
            if ($user->hasBasicFreeAccess()) {
                return $next($request);
            }
            return redirect()->route('beta.expired');
        }
        
        return $next($request);
    }
}
