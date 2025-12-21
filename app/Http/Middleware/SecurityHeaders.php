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
        
        // Vite dev server URLs
        $viteUrl = $isDevelopment ? 'http://localhost:5173 http://[::1]:5173' : '';
        
        // Build CSP directive
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://challenges.cloudflare.com";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net";
        $fontSrc = "'self' data: https://fonts.gstatic.com https://fonts.bunny.net";
        $connectSrc = "'self'";
        
        // Add Vite support in development
        if ($isDevelopment) {
            $scriptSrc .= " {$viteUrl}";
            $styleSrc .= " {$viteUrl}";
            $connectSrc .= " {$viteUrl} ws://localhost:5173 ws://[::1]:5173";
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

        // CSP - desactivado en desarrollo para permitir Vite HMR
        if (!$isDevelopment) {
            $csp = "default-src 'self'; " .
                   "script-src {$scriptSrc}; " .
                   "style-src {$styleSrc}; " .
                   "img-src 'self' data: https: blob:; " .
                   "font-src {$fontSrc}; " .
                   "connect-src {$connectSrc}; " .
                   "frame-src https://www.google.com https://challenges.cloudflare.com; " .
                   "object-src 'none'; " .
                   "base-uri 'self'; " .
                   "form-action 'self'; " .
                   "upgrade-insecure-requests;";
            
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
