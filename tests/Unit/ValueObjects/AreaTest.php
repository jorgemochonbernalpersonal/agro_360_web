<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Area;
use PHPUnit\Framework\TestCase;

class AreaTest extends TestCase
{
    public function test_can_create_area_from_hectares(): void
    {
        $area = Area::fromHectares(5.5);

        $this->assertEquals(5.5, $area->hectares);
    }

    public function test_can_create_area_from_square_meters(): void
    {
        $area = Area::fromSquareMeters(10000);

        $this->assertEquals(1, $area->hectares);
    }

    public function test_can_convert_to_square_meters(): void
    {
        $area = Area::fromHectares(2);

        $this->assertEquals(20000, $area->toSquareMeters());
    }

    public function test_throws_exception_for_negative_area(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Area::fromHectares(-5);
    }

    public function test_can_add_areas(): void
    {
        $area1 = Area::fromHectares(3);
        $area2 = Area::fromHectares(2);

        $result = $area1->add($area2);

        $this->assertEquals(5, $result->hectares);
    }

    public function test_can_subtract_areas(): void
    {
        $area1 = Area::fromHectares(10);
        $area2 = Area::fromHectares(3);

        $result = $area1->subtract($area2);

        $this->assertEquals(7, $result->hectares);
    }

    public function test_can_multiply_area(): void
    {
        $area = Area::fromHectares(4);

        $result = $area->multiply(2.5);

        $this->assertEquals(10, $result->hectares);
    }

    public function test_can_compare_areas(): void
    {
        $area1 = Area::fromHectares(10);
        $area2 = Area::fromHectares(5);

        $this->assertTrue($area1->isGreaterThan($area2));
    }

    public function test_can_format_area(): void
    {
        $area = Area::fromHectares(5.75);

        $this->assertEquals('5,75 ha', $area->format());
    }

    public function test_is_zero(): void
    {
        $area = Area::fromHectares(0);

        $this->assertTrue($area->isZero());
    }
}
