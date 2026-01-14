<?php

namespace Tests\Unit\Services;

use App\Services\SecurityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class SecurityLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Simular request para que request()->ip() y request()->userAgent() funcionen
        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_log_failed_login_logs_warning(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('warning')
            ->once()
            ->with('Login fallido', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'failed_login' &&
                       isset($context['email']) && $context['email'] === 'test@example.com' &&
                       isset($context['reason']) && $context['reason'] === 'Invalid credentials' &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logFailedLogin('test@example.com', 'Invalid credentials');
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_captcha_activated_logs_notice(): void
    {
        session(['login_failed_attempts' => 5]);
        
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'CAPTCHA activado');
            }), Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'captcha_activated' &&
                       isset($context['email']) && $context['email'] === 'test@example.com' &&
                       isset($context['failed_attempts']) && $context['failed_attempts'] === 5 &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logCaptchaActivated('test@example.com');
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_rate_limit_reached_logs_warning(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('warning')
            ->once()
            ->with('Rate limit alcanzado', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'rate_limit_reached' &&
                       isset($context['key']) && $context['key'] === 'login:127.0.0.1' &&
                       isset($context['max_attempts']) && $context['max_attempts'] === 5 &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logRateLimitReached('login:127.0.0.1', 5);
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_successful_login_after_failures_logs_info(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('info')
            ->once()
            ->with('Login exitoso después de intentos fallidos', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'successful_login_after_failures' &&
                       isset($context['user_id']) && $context['user_id'] === 1 &&
                       isset($context['email']) && $context['email'] === 'test@example.com' &&
                       isset($context['failed_attempts_before']) && $context['failed_attempts_before'] === 3 &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logSuccessfulLoginAfterFailures(1, 'test@example.com', 3);
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_access_denied_logs_warning(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('warning')
            ->once()
            ->with('Acceso denegado', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'access_denied' &&
                       isset($context['user_id']) && $context['user_id'] === 1 &&
                       isset($context['resource']) && $context['resource'] === 'Campaign' &&
                       isset($context['action']) && $context['action'] === 'edit' &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['url']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logAccessDenied(1, 'Campaign', 'edit');
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_access_denied_without_user_id(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('warning')
            ->once()
            ->with('Acceso denegado', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'access_denied' &&
                       $context['user_id'] === null;
            }));

        SecurityLogger::logAccessDenied(null, 'Campaign', 'edit');
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_captcha_validation_failed_logs_warning(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('warning')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'Validación de CAPTCHA fallida');
            }), Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'captcha_validation_failed' &&
                       isset($context['email']) && $context['email'] === 'test@example.com' &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logCaptchaValidationFailed('test@example.com');
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_account_locked_logs_alert(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('alert')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'Cuenta temporalmente bloqueada');
            }), Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'account_locked' &&
                       isset($context['email']) && $context['email'] === 'test@example.com' &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logAccountLocked('test@example.com');
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_security_event_logs_info(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('info')
            ->once()
            ->with('custom_event', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'security_event' &&
                       isset($context['custom_field']) && $context['custom_field'] === 'custom_value' &&
                       isset($context['user_id']) && $context['user_id'] === 123 &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logSecurityEvent('custom_event', [
            'custom_field' => 'custom_value',
            'user_id' => 123,
        ]);
        
        $this->assertTrue(true); // Assertion explícita
    }

    public function test_log_security_event_without_additional_data(): void
    {
        $logChannel = Mockery::mock();
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturn($logChannel);
        $logChannel->shouldReceive('info')
            ->once()
            ->with('simple_event', Mockery::on(function ($context) {
                return isset($context['event']) && $context['event'] === 'security_event' &&
                       isset($context['ip']) &&
                       isset($context['user_agent']) &&
                       isset($context['timestamp']);
            }));

        SecurityLogger::logSecurityEvent('simple_event');
        
        $this->assertTrue(true); // Assertion explícita
    }
}
