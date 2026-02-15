<?php

namespace Tests\Unit\Services\RemoteSensing\Calculators;

use App\Services\RemoteSensing\Calculators\LAICalculator;
use Tests\TestCase;

class LAICalculatorTest extends TestCase
{
    private LAICalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new LAICalculator();
    }

    /** @test */
    public function it_calculates_lai_from_ndvi()
    {
        // Test different NDVI values
        $testCases = [
            ['ndvi' => 0.1, 'expectedLAI' => 0],    // Below minimum
            ['ndvi' => 0.4, 'expectedLAI' => 0.5],   // Low vegetation
            ['ndvi' => 0.6, 'expectedLAI' => 1.5],   // Moderate
            ['ndvi' => 0.8, 'expectedLAI' => 3.0],   // High
        ];

        foreach ($testCases as $case) {
            $lai = $this->calculator->calculateFromNDVI($case['ndvi']);
            
            $this->assertIsFloat($lai);
            $this->assertGreaterThanOrEqual(0, $lai);
            $this->assertLessThanOrEqual(5.0, $lai);
            
            // LAI should increase with NDVI
            if (isset($previousLAI)) {
                $this->assertGreaterThan($previousLAI, $lai);
            }
            $previousLAI = $lai;
        }
    }

    /** @test */
    public function it_classifies_lai_correctly()
    {
        $classifications = [
            0.3 => 'very_low',
            1.0 => 'low',
            2.0 => 'moderate',
            3.0 => 'good',
            4.5 => 'very_high',
        ];

        foreach ($classifications as $lai => $expectedStatus) {
            $result = $this->calculator->classifyLAI($lai);
            
            $this->assertEquals($expectedStatus, $result['status']);
            $this->assertArrayHasKey('label', $result);
            $this->assertArrayHasKey('color', $result);
            $this->assertArrayHasKey('description', $result);
            $this->assertArrayHasKey('icon', $result);
        }
    }

    /** @test */
    public function it_estimates_yield_from_lai()
    {
        $lai = 2.5;
        $areaHa = 5.0;

        $result = $this->calculator->estimateYield($lai, $areaHa, 'red');

        $this->assertArrayHasKey('lai', $result);
        $this->assertArrayHasKey('yield_per_ha', $result);
        $this->assertArrayHasKey('total_yield_kg', $result);
        $this->assertArrayHasKey('total_yield_tons', $result);
        $this->assertArrayHasKey('confidence', $result);
        
        $this->assertEquals($lai, $result['lai']);
        $this->assertGreaterThan(0, $result['yield_per_ha']);
        $this->assertEquals($result['yield_per_ha'] * $areaHa, $result['total_yield_kg']);
        
        // White varieties should have higher yield
        $whiteResult = $this->calculator->estimateYield($lai, $areaHa, 'white');
        $this->assertGreaterThan($result['yield_per_ha'], $whiteResult['yield_per_ha']);
    }

    /** @test */
    public function it_provides_management_recommendations()
    {
        $recommendations = $this->calculator->getManagementRecommendations(4.5, 7); // July, high LAI
        
        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        
        foreach ($recommendations as $rec) {
            $this->assertArrayHasKey('type', $rec);
            $this->assertArrayHasKey('icon', $rec);
            $this->assertArrayHasKey('title', $rec);
            $this->assertArrayHasKey('text', $rec);
        }
    }

    /** @test */
    public function it_adjusts_treatment_dose_by_lai()
    {
        $baseDose = 100; // L/ha
        
        // Low LAI = less product needed
        $lowLAI = 1.0;
        $adjustedLow = $this->calculator->adjustTreatmentDose($lowLAI, $baseDose);
        $this->assertLessThan($baseDose, $adjustedLow);
        
        // High LAI = more product needed
        $highLAI = 4.0;
        $adjustedHigh = $this->calculator->adjustTreatmentDose($highLAI, $baseDose);
        $this->assertGreaterThan($baseDose, $adjustedHigh);
        
        // Should respect limits (50-150% of base)
        $this->assertGreaterThanOrEqual($baseDose * 0.5, $adjustedLow);
        $this->assertLessThanOrEqual($baseDose * 1.5, $adjustedHigh);
    }
}
