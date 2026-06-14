<?php

namespace Tests\Unit\Services;

use App\Models\Tax;
use App\Services\InvoiceService;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    // ── calculateVatLine ───────────────────────────────────────────────────────

    public function test_vat_line_basic_without_discount(): void
    {
        $service = new InvoiceService;

        $line = $service->calculateVatLine(
            ['quantity' => 10, 'unit_price' => 5, 'discount_percentage' => 0],
            $this->tax(21),
        );

        $this->assertSame(50.0, $line['subtotal']);
        $this->assertSame(0.0, $line['discount_amount']);
        $this->assertSame(50.0, $line['tax_base']);
        $this->assertSame(21.0, $line['tax_rate']);
        $this->assertSame(10.5, $line['tax_amount']);
        $this->assertSame(60.5, $line['total']);
    }

    public function test_vat_line_applies_discount_before_tax(): void
    {
        $service = new InvoiceService;

        $line = $service->calculateVatLine(
            ['quantity' => 2, 'unit_price' => 100, 'discount_percentage' => 10],
            $this->tax(21),
        );

        $this->assertSame(200.0, $line['subtotal']);
        $this->assertSame(20.0, $line['discount_amount']);
        $this->assertSame(180.0, $line['tax_base']);
        $this->assertSame(37.8, $line['tax_amount']);
        $this->assertSame(217.8, $line['total']);
    }

    public function test_vat_line_without_tax_is_zero_rate(): void
    {
        $service = new InvoiceService;

        $line = $service->calculateVatLine(
            ['quantity' => 3, 'unit_price' => 7],
            null,
        );

        $this->assertSame(21.0, $line['subtotal']);
        $this->assertSame(0.0, $line['tax_rate']);
        $this->assertSame(0.0, $line['tax_amount']);
        $this->assertSame(21.0, $line['total']);
    }

    public function test_vat_line_is_internally_consistent(): void
    {
        $service = new InvoiceService;

        $line = $service->calculateVatLine(
            ['quantity' => 3.5, 'unit_price' => 12.99, 'discount_percentage' => 7.5],
            $this->tax(10),
        );

        // tax_base = subtotal - discount_amount; total = tax_base + tax_amount.
        $this->assertSame(
            round($line['subtotal'] - $line['discount_amount'], 3),
            $line['tax_base'],
        );
        $this->assertSame(
            round($line['tax_base'] + $line['tax_amount'], 3),
            $line['total'],
        );
    }

    public function test_vat_line_defaults_missing_keys_to_zero(): void
    {
        $service = new InvoiceService;

        $line = $service->calculateVatLine([], $this->tax(21));

        $this->assertSame(0.0, $line['subtotal']);
        $this->assertSame(0.0, $line['tax_amount']);
        $this->assertSame(0.0, $line['total']);
    }

    private function tax(float $rate): Tax
    {
        $tax = new Tax;
        $tax->rate = $rate;

        return $tax;
    }
}
