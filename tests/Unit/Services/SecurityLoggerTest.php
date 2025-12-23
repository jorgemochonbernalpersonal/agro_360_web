<?php

namespace Tests\Unit\Services;

use App\Services\SecurityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Log::fake();
    }

    public function test_log_failed_login_logs_warning(): void
    {
        SecurityLogger::logFailedLogin('test@example.com', 'Invalid credentials');

        Log::channel('security')->assertLogged('warning', function ($message, $context) {
            return $message === 'Login fallido' &&
                   $context['event'] === 'failed_login' &&
                   $context['email'] === 'test@example.com' &&
                   $context['reason'] === 'Invalid credentials' &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_captcha_activated_logs_notice(): void
    {
        session(['login_failed_attempts' => 5]);
        
        SecurityLogger::logCaptchaActivated('test@example.com');

        Log::channel('security')->assertLogged('notice', function ($message, $context) {
            return str_contains($message, 'CAPTCHA activado') &&
                   $context['event'] === 'captcha_activated' &&
                   $context['email'] === 'test@example.com' &&
                   $context['failed_attempts'] === 5 &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_rate_limit_reached_logs_warning(): void
    {
        SecurityLogger::logRateLimitReached('login:127.0.0.1', 5);

        Log::channel('security')->assertLogged('warning', function ($message, $context) {
            return $message === 'Rate limit alcanzado' &&
                   $context['event'] === 'rate_limit_reached' &&
                   $context['key'] === 'login:127.0.0.1' &&
                   $context['max_attempts'] === 5 &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_successful_login_after_failures_logs_info(): void
    {
        SecurityLogger::logSuccessfulLoginAfterFailures(1, 'test@example.com', 3);

        Log::channel('security')->assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Login exitoso después de intentos fallidos') &&
                   $context['event'] === 'successful_login_after_failures' &&
                   $context['user_id'] === 1 &&
                   $context['email'] === 'test@example.com' &&
                   $context['failed_attempts_before'] === 3 &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_access_denied_logs_warning(): void
    {
        SecurityLogger::logAccessDenied(1, 'Campaign', 'edit');

        Log::channel('security')->assertLogged('warning', function ($message, $context) {
            return $message === 'Acceso denegado' &&
                   $context['event'] === 'access_denied' &&
                   $context['user_id'] === 1 &&
                   $context['resource'] === 'Campaign' &&
                   $context['action'] === 'edit' &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['url']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_access_denied_without_user_id(): void
    {
        SecurityLogger::logAccessDenied(null, 'Campaign', 'edit');

        Log::channel('security')->assertLogged('warning', function ($message, $context) {
            return $context['event'] === 'access_denied' &&
                   $context['user_id'] === null;
        });
    }

    public function test_log_captcha_validation_failed_logs_warning(): void
    {
        SecurityLogger::logCaptchaValidationFailed('test@example.com');

        Log::channel('security')->assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Validación de CAPTCHA fallida') &&
                   $context['event'] === 'captcha_validation_failed' &&
                   $context['email'] === 'test@example.com' &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_account_locked_logs_alert(): void
    {
        SecurityLogger::logAccountLocked('test@example.com');

        Log::channel('security')->assertLogged('alert', function ($message, $context) {
            return str_contains($message, 'Cuenta temporalmente bloqueada') &&
                   $context['event'] === 'account_locked' &&
                   $context['email'] === 'test@example.com' &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_security_event_logs_info(): void
    {
        SecurityLogger::logSecurityEvent('custom_event', [
            'custom_field' => 'custom_value',
            'user_id' => 123,
        ]);

        Log::channel('security')->assertLogged('info', function ($message, $context) {
            return $message === 'custom_event' &&
                   $context['event'] === 'security_event' &&
                   $context['custom_field'] === 'custom_value' &&
                   $context['user_id'] === 123 &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }

    public function test_log_security_event_without_additional_data(): void
    {
        SecurityLogger::logSecurityEvent('simple_event');

        Log::channel('security')->assertLogged('info', function ($message, $context) {
            return $message === 'simple_event' &&
                   $context['event'] === 'security_event' &&
                   isset($context['ip']) &&
                   isset($context['user_agent']) &&
                   isset($context['timestamp']);
        });
    }
}

