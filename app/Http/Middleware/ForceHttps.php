<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para forzar HTTPS en producción
 * 
 * Redirige todas las peticiones HTTP a HTTPS cuando la aplicación
 * está en entorno de producción. Esto es esencial para la seguridad
 * de las cookies y datos transmitidos.
 */
class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo forzar HTTPS en producción
        if (app()->environment('production') && !$request->secure()) {
            // Redirigir a la versión HTTPS de la URL
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}

