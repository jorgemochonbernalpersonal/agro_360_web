<?php

namespace Tests\Unit\Rules;

use App\Rules\SigpacCodeFormat;
use Tests\TestCase;

class SigpacCodeFormatTest extends TestCase
{
    public function test_rule_passes_with_valid_code_with_dashes(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertTrue($rule->passes('sigpac_code', '13-28-079-0-0-12-00045-003'));
    }

    public function test_rule_passes_with_valid_code_without_dashes(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertTrue($rule->passes('sigpac_code', '1328079001200045003'));
    }

    public function test_rule_passes_with_valid_code_with_spaces(): void
    {
        $rule = new SigpacCodeFormat();

        // Debe limpiar espacios automáticamente
        $this->assertTrue($rule->passes('sigpac_code', '13 28 079 0 0 12 00045 003'));
    }

    public function test_rule_fails_with_invalid_length(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertFalse($rule->passes('sigpac_code', '13280790012000450')); // 17 dígitos
        $this->assertStringContainsString('19 dígitos', $rule->message());
    }

    public function test_rule_fails_with_too_long_code(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertFalse($rule->passes('sigpac_code', '132807900120004500300')); // 21 dígitos
        $this->assertStringContainsString('19 dígitos', $rule->message());
    }

    public function test_rule_fails_with_non_numeric_characters(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertFalse($rule->passes('sigpac_code', '13-28-079-0-0-12-00045-ABC'));
        $this->assertStringContainsString('solo puede contener números', $rule->message());
    }

    public function test_rule_fails_with_letters(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertFalse($rule->passes('sigpac_code', 'ABCDEFGHIJKLMNOPQRS'));
        $this->assertStringContainsString('solo puede contener números', $rule->message());
    }

    public function test_rule_fails_with_special_characters(): void
    {
        $rule = new SigpacCodeFormat();

        $this->assertFalse($rule->passes('sigpac_code', '13-28-079-0-0-12-00045-00@'));
        $this->assertStringContainsString('solo puede contener números', $rule->message());
    }

    public function test_rule_returns_default_message_when_no_exception(): void
    {
        $rule = new SigpacCodeFormat();

        // Forzar un estado donde no hay mensaje específico (no debería pasar en uso real)
        // El mensaje por defecto solo se usa si hay un fallo sin mensaje específico
        $this->assertNotEmpty($rule->message());
    }

    public function test_rule_message_contains_expected_format(): void
    {
        $rule = new SigpacCodeFormat();

        // Intentar con código inválido
        $rule->passes('sigpac_code', 'invalid');

        $message = $rule->message();
        // El mensaje debe contener información útil
        $this->assertNotEmpty($message);
    }

    public function test_rule_validates_correct_sigpac_structure(): void
    {
        $rule = new SigpacCodeFormat();

        // Código válido: CA(2) - Provincia(2) - Municipio(3) - Agregado(1) - Zona(1) - Polígono(2) - Parcela(5) - Recinto(3)
        $validCodes = [
            '13-28-079-0-0-12-00045-003',
            '1328079001200045003',
            '01-01-001-0-0-01-00001-001',
            '99-99-999-9-9-99-99999-999',
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
        $rule = new SigpacCodeFormat();

        $invalidCodes = [
            '', // Vacío
            '13', // Muy corto
            '13-28', // Incompleto
            '13-28-079', // Incompleto
            '132807900120004500300', // Muy largo
            'ABC-28-079-0-0-12-00045-003', // Letras
            '13-28-079-0-0-12-00045-003-EXTRA', // Formato incorrecto
        ];

        foreach ($invalidCodes as $code) {
            $this->assertFalse(
                $rule->passes('sigpac_code', $code),
                "Should fail for code: {$code}"
            );
        }
    }
}

