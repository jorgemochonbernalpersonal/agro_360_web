<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->password_must_reset) {
            // Si ya está en la ruta de cambio de password, permitir acceso
            if (!$request->routeIs('password.force-reset')) {
                return redirect()->route('password.force-reset');
            }
        }

        return $next($request);
    }
}
