<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_can_create_money_object(): void
    {
        $money = new Money(100.50, 'EUR');

        $this->assertEquals(100.50, $money->amount);
        $this->assertEquals('EUR', $money->currency);
    }

    public function test_can_create_eur_money(): void
    {
        $money = Money::EUR(50.25);

        $this->assertEquals(50.25, $money->amount);
        $this->assertEquals('EUR', $money->currency);
    }

    public function test_throws_exception_for_negative_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Money(-10, 'EUR');
    }

    public function test_can_add_money(): void
    {
        $money1 = Money::EUR(100);
        $money2 = Money::EUR(50);

        $result = $money1->add($money2);

        $this->assertEquals(150, $result->amount);
    }

    public function test_can_subtract_money(): void
    {
        $money1 = Money::EUR(100);
        $money2 = Money::EUR(30);

        $result = $money1->subtract($money2);

        $this->assertEquals(70, $result->amount);
    }

    public function test_can_multiply_money(): void
    {
        $money = Money::EUR(50);

        $result = $money->multiply(3);

        $this->assertEquals(150, $result->amount);
    }

    public function test_can_apply_tax(): void
    {
        $money = Money::EUR(100);

        $result = $money->applyTax(21);

        $this->assertEquals(121, $result->amount);
    }

    public function test_can_calculate_percentage(): void
    {
        $money = Money::EUR(200);

        $result = $money->percentage(10);

        $this->assertEquals(20, $result->amount);
    }

    public function test_throws_exception_when_adding_different_currencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $eur = Money::EUR(100);
        $usd = new Money(100, 'USD');

        $eur->add($usd);
    }

    public function test_can_compare_money(): void
    {
        $money1 = Money::EUR(100);
        $money2 = Money::EUR(50);

        $this->assertTrue($money1->isGreaterThan($money2));
        $this->assertFalse($money1->isLessThan($money2));
    }

    public function test_can_format_money(): void
    {
        $money = Money::EUR(1234.56);

        $this->assertEquals('1.234,56 EUR', $money->format());
    }

    public function test_is_zero(): void
    {
        $money = Money::EUR(0);

        $this->assertTrue($money->isZero());
    }
}
