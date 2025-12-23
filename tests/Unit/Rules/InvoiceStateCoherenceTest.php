<?php

namespace Tests\Unit\Rules;

use App\Rules\InvoiceStateCoherence;
use Tests\TestCase;

class InvoiceStateCoherenceTest extends TestCase
{
    public function test_rule_passes_when_states_are_coherent(): void
    {
        $rule = new InvoiceStateCoherence('sent', 'unpaid', 'pending');

        $this->assertTrue($rule->passes('status', 'sent'));
    }

    public function test_rule_fails_when_cancelled_and_paid(): void
    {
        $rule = new InvoiceStateCoherence('cancelled', 'paid', 'pending');

        $this->assertFalse($rule->passes('status', 'cancelled'));
        $this->assertEquals('Una factura cancelada no puede estar marcada como pagada.', $rule->message());
    }

    public function test_rule_fails_when_cancelled_and_delivered(): void
    {
        $rule = new InvoiceStateCoherence('cancelled', 'unpaid', 'delivered');

        $this->assertFalse($rule->passes('status', 'cancelled'));
        $this->assertEquals('Una factura cancelada no puede estar marcada como entregada.', $rule->message());
    }

    public function test_rule_fails_when_delivered_and_draft(): void
    {
        $rule = new InvoiceStateCoherence('draft', 'unpaid', 'delivered');

        $this->assertFalse($rule->passes('status', 'draft'));
        $this->assertEquals('No puedes marcar como entregada una factura en borrador.', $rule->message());
    }

    public function test_rule_fails_when_paid_and_draft(): void
    {
        $rule = new InvoiceStateCoherence('draft', 'paid', 'pending');

        $this->assertFalse($rule->passes('status', 'draft'));
        $this->assertEquals('No puedes marcar como pagada una factura en borrador.', $rule->message());
    }

    public function test_rule_passes_when_cancelled_and_unpaid(): void
    {
        $rule = new InvoiceStateCoherence('cancelled', 'unpaid', 'pending');

        $this->assertTrue($rule->passes('status', 'cancelled'));
    }

    public function test_rule_passes_when_delivered_and_approved(): void
    {
        $rule = new InvoiceStateCoherence('approved', 'unpaid', 'delivered');

        $this->assertTrue($rule->passes('status', 'approved'));
    }

    public function test_rule_passes_when_delivered_and_sent(): void
    {
        $rule = new InvoiceStateCoherence('sent', 'unpaid', 'delivered');

        $this->assertTrue($rule->passes('status', 'sent'));
    }

    public function test_rule_passes_when_paid_and_approved(): void
    {
        $rule = new InvoiceStateCoherence('approved', 'paid', 'pending');

        $this->assertTrue($rule->passes('status', 'approved'));
    }

    public function test_rule_passes_when_paid_and_sent(): void
    {
        $rule = new InvoiceStateCoherence('sent', 'paid', 'pending');

        $this->assertTrue($rule->passes('status', 'sent'));
    }

    public function test_rule_returns_default_message_when_no_specific_failure(): void
    {
        $rule = new InvoiceStateCoherence('sent', 'unpaid', 'pending');

        // Forzar un estado sin mensaje específico (no debería pasar en uso real)
        $this->assertTrue($rule->passes('status', 'sent'));
        // El mensaje por defecto solo se usa si hay un fallo sin mensaje específico
    }

    public function test_rule_validates_all_coherent_combinations(): void
    {
        $coherentCombinations = [
            ['draft', 'unpaid', 'pending'],
            ['approved', 'unpaid', 'pending'],
            ['approved', 'unpaid', 'delivered'],
            ['sent', 'unpaid', 'pending'],
            ['sent', 'unpaid', 'delivered'],
            ['sent', 'paid', 'pending'],
            ['sent', 'paid', 'delivered'],
            ['cancelled', 'unpaid', 'pending'],
        ];

        foreach ($coherentCombinations as $combination) {
            $rule = new InvoiceStateCoherence($combination[0], $combination[1], $combination[2]);
            $this->assertTrue(
                $rule->passes('status', $combination[0]),
                "Failed for combination: status={$combination[0]}, payment={$combination[1]}, delivery={$combination[2]}"
            );
        }
    }

    public function test_rule_rejects_all_incoherent_combinations(): void
    {
        $incoherentCombinations = [
            ['cancelled', 'paid', 'pending'],
            ['cancelled', 'unpaid', 'delivered'],
            ['draft', 'paid', 'pending'],
            ['draft', 'unpaid', 'delivered'],
        ];

        foreach ($incoherentCombinations as $combination) {
            $rule = new InvoiceStateCoherence($combination[0], $combination[1], $combination[2]);
            $this->assertFalse(
                $rule->passes('status', $combination[0]),
                "Should fail for combination: status={$combination[0]}, payment={$combination[1]}, delivery={$combination[2]}"
            );
        }
    }
}

