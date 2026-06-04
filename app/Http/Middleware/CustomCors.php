<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCors
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        $allowedOrigin = $origin ? $this->resolveAllowedOrigin($origin) : null;

        // Preflight OPTIONS — responder sin pasar al siguiente middleware
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 204);
            if ($allowedOrigin) {
                $this->addCorsHeaders($response, $allowedOrigin);
                $response->headers->set('Access-Control-Max-Age', '86400');
            }

            return $response;
        }

        $response = $next($request);

        if ($allowedOrigin) {
            $this->addCorsHeaders($response, $allowedOrigin);
        }

        return $response;
    }

    /**
     * Resuelve el origen permitido. Devuelve null si el origen no está en la whitelist.
     * Nunca devuelve '*' como fallback implícito.
     */
    protected function resolveAllowedOrigin(string $origin): ?string
    {
        $allowedOrigins = config('cors.allowed_origins', []);

        if (empty($allowedOrigins)) {
            return null; // Sin whitelist configurada → denegar todo cross-origin
        }

        if ($allowedOrigins === ['*']) {
            return '*'; // Wildcard explícito en config (solo dev)
        }

        return in_array($origin, $allowedOrigins, true) ? $origin : null;
    }

    private function addCorsHeaders(Response $response, string $allowedOrigin): void
    {
        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');

        // Allow-Credentials: true es incompatible con wildcard según la spec CORS
        if ($allowedOrigin !== '*') {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');
        }
    }
}
