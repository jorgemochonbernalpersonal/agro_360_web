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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Detectar si estamos en desarrollo
        $isDevelopment = app()->environment('local', 'development');
        
        // Build CSP directive
        // unsafe-inline es necesario para Livewire/Alpine.js (wire: directives generan scripts inline)
        // unsafe-eval solo se permite en desarrollo (Vite HMR lo requiere)
        $scriptSrc = "'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://challenges.cloudflare.com";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net";
        $fontSrc = "'self' data: https://fonts.gstatic.com https://fonts.bunny.net";
        $connectSrc = "'self'";

        // Add Vite HMR support in development (only localhost, IPv6 bracket syntax is invalid in CSP)
        if ($isDevelopment) {
            $scriptSrc .= " http://localhost:5173 'unsafe-eval'";
            $styleSrc .= " http://localhost:5173";
            $connectSrc .= " http://localhost:5173 ws://localhost:5173";
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // HSTS - solo en producción con HTTPS
        if (!$isDevelopment && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP - siempre establecer para sobrescribir .htaccess
        // En desarrollo incluye soporte para Vite HMR
        $csp = "default-src 'self'; " .
               "script-src {$scriptSrc} https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://googletagmanager.com; " .
               "style-src {$styleSrc}; " .
               "img-src 'self' data: https: blob:; " .
               "font-src {$fontSrc}; " .
               "connect-src {$connectSrc} https: https://www.googletagmanager.com https://www.google-analytics.com; " .
               "frame-src https://www.google.com https://challenges.cloudflare.com; " .
               "frame-ancestors 'self'; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
        
        // Solo agregar upgrade-insecure-requests en producción con HTTPS
        if (!$isDevelopment && $request->secure()) {
            $csp .= " upgrade-insecure-requests;";
        } else {
            $csp .= ";";
        }
        
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
