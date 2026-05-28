<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if (!$user) return route('login');
            return match($user->role) {
                'admin'         => route('admin.dashboard'),
                'supervisor'    => route('supervisor.dashboard'),
                'winery'        => route('winery.dashboard'),
                'viticulturist' => route('viticulturist.dashboard'),
                'producer'      => route('producer.dashboard'),
                default         => route('login'),
            };
        });

        // Forzar HTTPS en producción (debe ir primero)
        $middleware->append(\App\Http\Middleware\ForceHttps::class);
        
        // Middleware global de seguridad - aplica a todas las respuestas
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        // Defensa contra bots
        $middleware->append(\App\Http\Middleware\BotDefense::class);
        
        $middleware->validateCsrfTokens(except: [
            '/logout',
            '/__cypress/*',
        ]);

        // La cookie de locale no se cifra para que SetLocale pueda leerla
        // antes de que EncryptCookies procese el request
        $middleware->encryptCookies(except: ['app_locale']);

        // Timeout automático de impersonación (60 min)
        $middleware->append(\App\Http\Middleware\ImpersonationTimeout::class);

        // Locale del usuario — debe ir en el grupo web (después de StartSession)
        // para que la sesión esté disponible al leer/escribir el locale.
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);

        $middleware->alias([
            'role'             => \App\Http\Middleware\CheckRole::class,
            'api.role'         => \App\Http\Middleware\ApiRole::class,
            'check.can_login'  => \App\Http\Middleware\CheckCanLogin::class,
            'auth.refresh'     => \App\Http\Middleware\AuthWithRefreshGrace::class,
            'require.password.change' => \App\Http\Middleware\RequirePasswordChange::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            'check.beta'       => \App\Http\Middleware\CheckBetaAccess::class,
            'api.check.beta'   => \App\Http\Middleware\ApiBetaCheck::class,
            'require.complete' => \App\Http\Middleware\RequireCompleteAccess::class,
            'winery.ability'   => \App\Http\Middleware\CheckWineryAbility::class,
            'require.winery'   => \App\Http\Middleware\RequireWinery::class,
            'require.supervisor' => \App\Http\Middleware\RequireSupervisor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ── API: devolver JSON siempre (nunca HTML en rutas /api) ─────────────

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Los datos enviados no son válidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Error en la solicitud.',
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                return response()->json([
                    'message' => app()->isProduction() ? 'Error interno del servidor.' : $e->getMessage(),
                ], $status);
            }
        });
    })
    ->create();
