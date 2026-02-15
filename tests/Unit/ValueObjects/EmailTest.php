<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_can_create_valid_email(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->address);
    }

    public function test_throws_exception_for_invalid_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('invalid-email');
    }

    public function test_can_get_domain(): void
    {
        $email = Email::from('user@agro365.es');

        $this->assertEquals('agro365.es', $email->domain());
    }

    public function test_can_get_local_part(): void
    {
        $email = Email::from('john.doe@example.com');

        $this->assertEquals('john.doe', $email->localPart());
    }

    public function test_can_check_domain(): void
    {
        $email = Email::from('test@agro365.es');

        $this->assertTrue($email->isFromDomain('agro365.es'));
        $this->assertFalse($email->isFromDomain('example.com'));
    }

    public function test_can_obfuscate_email(): void
    {
        $email = Email::from('john@example.com');

        $this->assertEquals('jo***@example.com', $email->obfuscate());
    }

    public function test_email_is_lowercased(): void
    {
        $email = Email::from('USER@EXAMPLE.COM');

        $this->assertEquals('user@example.com', $email->address);
    }

    public function test_email_is_trimmed(): void
    {
        $email = Email::from('  user@example.com  ');

        $this->assertEquals('user@example.com', $email->address);
    }
}
