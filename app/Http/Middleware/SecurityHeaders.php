<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request y agregar headers de seguridad.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Detectar si estamos en desarrollo
        $isDevelopment = app()->environment('local', 'development');

        // Build CSP directive
        // unsafe-inline es necesario para Livewire/Alpine.js (wire: directives generan scripts inline)
        // unsafe-eval es obligatorio en todos los entornos: el Alpine que trae Livewire 3 evalúa
        // las expresiones x-data/@click con new AsyncFunction(); sin él no funciona ningún componente
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://challenges.cloudflare.com";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net";
        $fontSrc = "'self' data: https://fonts.gstatic.com https://fonts.bunny.net";
        $connectSrc = "'self'";

        // Add Vite HMR support in development (only localhost, IPv6 bracket syntax is invalid in CSP)
        if ($isDevelopment) {
            $scriptSrc .= ' http://localhost:5173';
            $styleSrc .= ' http://localhost:5173';
            $connectSrc .= ' http://localhost:5173 ws://localhost:5173';
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS - solo en producción con HTTPS
        if (! $isDevelopment && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP - siempre establecer para sobrescribir .htaccess
        // En desarrollo incluye soporte para Vite HMR
        $csp = "default-src 'self'; ".
               "script-src {$scriptSrc} https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://googletagmanager.com; ".
               "style-src {$styleSrc}; ".
               "img-src 'self' data: blob: https://www.google.com https://maps.gstatic.com https://maps.googleapis.com https://*.googletagmanager.com https://*.google-analytics.com; ".
               "font-src {$fontSrc}; ".
               "connect-src {$connectSrc} https://agro365.es https://*.googletagmanager.com https://*.google-analytics.com https://*.analytics.google.com https://challenges.cloudflare.com; ".
               'frame-src https://www.google.com https://challenges.cloudflare.com https://www.googletagmanager.com; '.
               "frame-ancestors 'self'; ".
               "object-src 'none'; ".
               "base-uri 'self'; ".
               "form-action 'self'";

        // Solo agregar upgrade-insecure-requests en producción con HTTPS
        if (! $isDevelopment && $request->secure()) {
            $csp .= '; upgrade-insecure-requests';
        }

        $csp .= ';';

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
