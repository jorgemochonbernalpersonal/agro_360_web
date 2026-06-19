<?php

namespace App\Services;

use App\Models\SecurityEvent;
use Illuminate\Support\Facades\Log;

/**
 * Servicio centralizado para logging de eventos de seguridad.
 *
 * Testabilidad: usar SecurityLogger::swap(new FakeRequest(...)) en setUp()
 * y SecurityLogger::swap(null) en tearDown() para inyectar un request falso.
 */
class SecurityLogger
{
    private const CHANNEL = 'security';

    /** @var \Illuminate\Http\Request|null Sólo usado en tests */
    protected static ?\Illuminate\Http\Request $fakeRequest = null;

    /**
     * Inyecta un Request alternativo (para tests). Pasar null restaura el comportamiento real.
     */
    public static function swap(?\Illuminate\Http\Request $request): void
    {
        static::$fakeRequest = $request;
    }

    // ─── Log methods ──────────────────────────────────────────────────────────

    public static function logFailedLogin(string $email, ?string $reason = null): void
    {
        $ctx = [
            'event' => 'failed_login',
            'email' => $email,
            'reason' => $reason,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->warning('Login fallido', $ctx);
        static::persist('warning', 'failed_login', 'Login fallido', $ctx);
    }

    public static function logCaptchaActivated(string $email, int $failedAttempts = 0): void
    {
        $ctx = [
            'event' => 'captcha_activated',
            'email' => $email,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'failed_attempts' => $failedAttempts,
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->notice('CAPTCHA activado - Posible bot detectado', $ctx);
        static::persist('notice', 'captcha_activated', 'CAPTCHA activado', $ctx);
    }

    public static function logRateLimitReached(string $key, int $maxAttempts): void
    {
        $ctx = [
            'event' => 'rate_limit_reached',
            'key' => $key,
            'max_attempts' => $maxAttempts,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->warning('Rate limit alcanzado', $ctx);
        static::persist('warning', 'rate_limit_reached', 'Rate limit alcanzado', $ctx);
    }

    public static function logSuccessfulLoginAfterFailures(int $userId, string $email, int $failedAttempts): void
    {
        $ctx = [
            'event' => 'successful_login_after_failures',
            'user_id' => $userId,
            'email' => $email,
            'failed_attempts_before' => $failedAttempts,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->info('Login exitoso después de intentos fallidos', $ctx);
        static::persist('info', 'successful_login_after_failures', 'Login exitoso tras intentos fallidos', $ctx);
    }

    public static function logAccessDenied(?int $userId, string $resource, string $action): void
    {
        $ctx = [
            'event' => 'access_denied',
            'user_id' => $userId,
            'resource' => $resource,
            'action' => $action,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'url' => static::resolveRequest()->fullUrl(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->warning('Acceso denegado', $ctx);
        static::persist('warning', 'access_denied', 'Acceso denegado', $ctx);
    }

    public static function logCaptchaValidationFailed(string $email): void
    {
        $ctx = [
            'event' => 'captcha_validation_failed',
            'email' => $email,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->warning('Validación de CAPTCHA fallida - Posible bot', $ctx);
        static::persist('warning', 'captcha_validation_failed', 'Validación CAPTCHA fallida', $ctx);
    }

    public static function logAccountLocked(string $email): void
    {
        $ctx = [
            'event' => 'account_locked',
            'email' => $email,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->alert('Cuenta temporalmente bloqueada', $ctx);
        static::persist('alert', 'account_locked', 'Cuenta temporalmente bloqueada', $ctx);
    }

    public static function logSecurityEvent(string $eventName, array $data = []): void
    {
        $ctx = array_merge([
            'event' => 'security_event',
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ], $data);
        Log::channel(self::CHANNEL)->info($eventName, $ctx);
        static::persist('info', $eventName, $eventName, $ctx);
    }

    public static function logImpersonation(int $adminId, int $targetUserId): void
    {
        $ctx = [
            'event' => 'user_impersonation_started',
            'admin_id' => $adminId,
            'target_user_id' => $targetUserId,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'url' => static::resolveRequest()->fullUrl(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->warning('Impersonación de usuario iniciada', $ctx);
        static::persist('warning', 'user_impersonation_started', 'Impersonación iniciada', $ctx);
    }

    public static function log(array $data): void
    {
        $level = $data['event'] === 'critical_action_completed' ? 'info' : 'warning';
        $ctx = array_merge([
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ], $data);
        Log::channel(self::CHANNEL)->$level('Acción crítica', $ctx);
        static::persist($level, $data['event'] ?? 'critical_action', 'Acción crítica', $ctx);
    }

    public static function logImpersonationEnded(int $adminId, int $targetUserId): void
    {
        $ctx = [
            'event' => 'user_impersonation_ended',
            'admin_id' => $adminId,
            'target_user_id' => $targetUserId,
            'ip' => static::resolveRequest()->ip(),
            'user_agent' => static::resolveRequest()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
        Log::channel(self::CHANNEL)->info('Impersonación de usuario finalizada', $ctx);
        static::persist('info', 'user_impersonation_ended', 'Impersonación finalizada', $ctx);
    }

    protected static function resolveRequest(): \Illuminate\Http\Request
    {
        return static::$fakeRequest ?? request();
    }

    // ─── DB persistence ──────────────────────────────────────────────────────

    protected static function persist(string $level, string $event, string $message, array $context): void
    {
        try {
            SecurityEvent::create([
                'level' => $level,
                'event' => $event,
                'message' => $message,
                'ip' => $context['ip'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'email' => $context['email'] ?? $context['user_email'] ?? null,
                'user_id' => $context['user_id'] ?? null,
                'admin_id' => $context['admin_id'] ?? null,
                'context' => array_diff_key($context, array_flip(SecurityEvent::CONTEXT_SKIP)),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // El fichero de log ya capturó el evento; no propagar el fallo de DB
        }
    }
}
