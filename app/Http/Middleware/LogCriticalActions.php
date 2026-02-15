<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogCriticalActions
{
    /**
     * Acciones críticas que deben ser registradas
     */
    protected array $criticalRoutes = [
        'reports.generate',
        'plots.destroy',
        'activities.destroy',
        'users.destroy',
        'subscriptions.cancel',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        // Si es una ruta crítica, registrar antes de ejecutar
        if ($this->isCriticalRoute($routeName)) {
            SecurityLogger::log([
                'event' => 'critical_action_attempt',
                'route' => $routeName,
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'params' => $request->except(['password', '_token']),
            ]);
        }

        $response = $next($request);

        // Registrar después si fue exitoso
        if ($this->isCriticalRoute($routeName) && $response->isSuccessful()) {
            SecurityLogger::log([
                'event' => 'critical_action_completed',
                'route' => $routeName,
                'user_id' => $request->user()?->id,
                'status_code' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    /**
     * Check if route is critical
     */
    protected function isCriticalRoute(?string $routeName): bool
    {
        if (!$routeName) {
            return false;
        }

        foreach ($this->criticalRoutes as $pattern) {
            if (str_contains($routeName, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
