<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Servicio centralizado para logging de eventos de seguridad
 */
class SecurityLogger
{
    /**
     * Canal de log dedicado para eventos de seguridad
     */
    private const CHANNEL = 'security';
    
    /**
     * Loguear intento de login fallido
     */
    public static function logFailedLogin(string $email, ?string $reason = null): void
    {
        Log::channel(self::CHANNEL)->warning('Login fallido', [
            'event' => 'failed_login',
            'email' => $email,
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Loguear cuando se activa CAPTCHA (indica posible bot)
     */
    public static function logCaptchaActivated(string $email): void
    {
        Log::channel(self::CHANNEL)->notice('CAPTCHA activado - Posible bot detectado', [
            'event' => 'captcha_activated',
            'email' => $email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'failed_attempts' => session('login_failed_attempts', 0),
        ]);
    }
    
    /**
     * Loguear cuando se alcanza el rate limit
     */
    public static function logRateLimitReached(string $key, int $maxAttempts): void
    {
        Log::channel(self::CHANNEL)->warning('Rate limit alcanzado', [
            'event' => 'rate_limit_reached',
            'key' => $key,
            'max_attempts' => $maxAttempts,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Loguear login exitoso (solo después de intentos fallidos previos)
     */
    public static function logSuccessfulLoginAfterFailures(int $userId, string $email, int $failedAttempts): void
    {
        Log::channel(self::CHANNEL)->info('Login exitoso después de intentos fallidos', [
            'event' => 'successful_login_after_failures',
            'user_id' => $userId,
            'email' => $email,
            'failed_attempts_before' => $failedAttempts,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Loguear acceso denegado (403)
     */
    public static function logAccessDenied(?int $userId, string $resource, string $action): void
    {
        Log::channel(self::CHANNEL)->warning('Acceso denegado', [
            'event' => 'access_denied',
            'user_id' => $userId,
            'resource' => $resource,
            'action' => $action,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Loguear validación de CAPTCHA fallida
     */
    public static function logCaptchaValidationFailed(string $email): void
    {
        Log::channel(self::CHANNEL)->warning('Validación de CAPTCHA fallida - Posible bot', [
            'event' => 'captcha_validation_failed',
            'email' => $email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Loguear cuenta bloqueada por intentos excesivos
     */
    public static function logAccountLocked(string $email): void
    {
        Log::channel(self::CHANNEL)->alert('Cuenta temporalmente bloqueada', [
            'event' => 'account_locked',
            'email' => $email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Loguear evento de seguridad genérico
     */
    public static function logSecurityEvent(string $eventName, array $data = []): void
    {
        Log::channel(self::CHANNEL)->info($eventName, array_merge([
            'event' => 'security_event',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ], $data));
    }
}
