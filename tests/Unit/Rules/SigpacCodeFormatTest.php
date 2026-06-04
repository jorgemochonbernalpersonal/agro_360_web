<?php

namespace Tests\Unit\Rules;

use App\Rules\SigpacCodeFormat;
use Tests\TestCase;

class SigpacCodeFormatTest extends TestCase
{
    public function test_rule_passes_with_valid_code_with_dashes(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertTrue($rule->passes('sigpac_code', '13-28-079-000-000-012-00045-003'));
    }

    public function test_rule_passes_with_valid_code_without_dashes(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertTrue($rule->passes('sigpac_code', '132807900000001200045003'));
    }

    public function test_rule_passes_with_valid_code_with_spaces(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertTrue($rule->passes('sigpac_code', '13 28 079 000 000 012 00045 003'));
    }

    public function test_rule_fails_with_invalid_length(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertFalse($rule->passes('sigpac_code', '1328079001200045003')); // 19 dígitos (formato viejo)
        $this->assertStringContainsString('24 dígitos', $rule->message());
    }

    public function test_rule_fails_with_too_long_code(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertFalse($rule->passes('sigpac_code', '1328079000000012000450030')); // 25 dígitos
        $this->assertStringContainsString('24 dígitos', $rule->message());
    }

    public function test_rule_fails_with_non_numeric_characters(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertFalse($rule->passes('sigpac_code', '13-28-079-000-000-012-00045-ABC'));
        $this->assertStringContainsString('solo puede contener números', $rule->message());
    }

    public function test_rule_fails_with_letters(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertFalse($rule->passes('sigpac_code', 'ABCDEFGHIJKLMNOPQRSTUVWX'));
        $this->assertStringContainsString('solo puede contener números', $rule->message());
    }

    public function test_rule_fails_with_special_characters(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertFalse($rule->passes('sigpac_code', '13-28-079-000-000-012-00045-00@'));
        $this->assertStringContainsString('solo puede contener números', $rule->message());
    }

    public function test_rule_returns_default_message_when_no_exception(): void
    {
        $rule = new SigpacCodeFormat;

        $this->assertNotEmpty($rule->message());
    }

    public function test_rule_message_contains_expected_format(): void
    {
        $rule = new SigpacCodeFormat;

        $rule->passes('sigpac_code', 'invalid');

        $this->assertNotEmpty($rule->message());
    }

    public function test_rule_validates_correct_sigpac_structure(): void
    {
        $rule = new SigpacCodeFormat;

        // Formato: CA(2)-Provincia(2)-Municipio(3)-Agregado(3)-Zona(3)-Polígono(3)-Parcela(5)-Recinto(3) = 24 dígitos
        $validCodes = [
            '13-28-079-000-000-012-00045-003',
            '132807900000001200045003',
            '01-01-001-000-000-001-00001-001',
            '99-99-999-999-999-999-99999-999',
        ];

        foreach ($validCodes as $code) {
            $this->assertTrue(
                $rule->passes('sigpac_code', $code),
                "Failed for code: {$code}"
            );
        }
    }

    public function test_rule_rejects_incorrect_structure(): void
    {
        $rule = new SigpacCodeFormat;

        $invalidCodes = [
            '',                               // Vacío
            '13',                             // Muy corto
            '13-28',                          // Incompleto
            '13-28-079',                      // Incompleto
            '13-28-079-0-0-12-00045-003',     // Formato viejo 19 dígitos
            '1328079000000012000450030',       // 25 dígitos (muy largo)
            'ABC-28-079-000-000-012-00045-003', // Letras
        ];

        foreach ($invalidCodes as $code) {
            $this->assertFalse(
                $rule->passes('sigpac_code', $code),
                "Should fail for code: {$code}"
            );
        }
    }
}
