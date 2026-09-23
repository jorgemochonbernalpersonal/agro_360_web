<?php

namespace Tests\Unit\Tools;

use App\Services\Tools\HarvestYieldEstimator;
use App\Services\Tools\NotebookReadinessQuiz;
use App\Services\Tools\SprayMixCalculator;
use PHPUnit\Framework\TestCase;

class ToolCalculatorsTest extends TestCase
{
    public function test_spray_mix_with_dose_per_hectare(): void
    {
        // 3 ha × 400 L/ha = 1.200 L → 1 cuba de 1.000 L + una de 200 L.
        $r = (new SprayMixCalculator)->calculate(3, 400, 1000, [
            ['name' => 'Azufre', 'dose' => 4, 'mode' => 'per_ha'],
        ]);

        $this->assertEqualsWithDelta(1200, $r['total_litres'], 1e-9);
        $this->assertSame(2, $r['tanks']);
        $this->assertSame(1, $r['full_tanks']);
        $this->assertEqualsWithDelta(200, $r['last_tank_litres'], 1e-9);
        $this->assertEqualsWithDelta(2.5, $r['hectares_per_tank'], 1e-9);

        $product = $r['products'][0];
        $this->assertSame('Azufre', $product['name']);
        $this->assertEqualsWithDelta(4, $product['per_hectare'], 1e-9);
        $this->assertEqualsWithDelta(10, $product['per_tank'], 1e-9);   // 2,5 ha por cuba × 4 kg/ha
        $this->assertEqualsWithDelta(2, $product['last_tank'], 1e-9);
        $this->assertEqualsWithDelta(12, $product['total'], 1e-9);
    }

    public function test_spray_mix_with_percent_and_cc_per_hl(): void
    {
        $r = (new SprayMixCalculator)->calculate(2, 500, 1000, [
            ['name' => '', 'dose' => 0.3, 'mode' => 'percent'],  // 0,3 L por 100 L
            ['name' => null, 'dose' => 300, 'mode' => 'per_hl'], // 300 cc por 100 L
        ]);

        $this->assertSame(1, $r['tanks']);
        $this->assertEqualsWithDelta(0, $r['last_tank_litres'], 1e-9);
        $this->assertSame('Producto 1', $r['products'][0]['name']);
        $this->assertEqualsWithDelta(3, $r['products'][0]['per_tank'], 1e-9);
        $this->assertEqualsWithDelta(1.5, $r['products'][0]['per_hectare'], 1e-9);
        $this->assertSame('Producto 2', $r['products'][1]['name']);
        $this->assertEqualsWithDelta(3, $r['products'][1]['per_tank'], 1e-9);
    }

    public function test_spray_mix_exact_multiple_does_not_add_empty_tank(): void
    {
        $r = (new SprayMixCalculator)->calculate(0.3, 1000, 100, [
            ['dose' => 1, 'mode' => 'per_ha'],
        ]);

        $this->assertSame(3, $r['tanks']);
        $this->assertSame(0.0, $r['last_tank_litres']);
    }

    public function test_spray_mix_rejects_unknown_mode(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new SprayMixCalculator)->calculate(1, 400, 1000, [['dose' => 1, 'mode' => 'foo']]);
    }

    public function test_harvest_yield_estimate(): void
    {
        // Marco 2,5 × 1,2 = 3.333 cepas/ha; 10 % marras → 3.000; 12 racimos × 200 g = 2,4 kg/cepa.
        $r = (new HarvestYieldEstimator)->estimate(2, 2.5, 1.2, 12, 200, 10, 70, 8000);

        $this->assertEqualsWithDelta(3333.33, $r['vines_per_hectare'], 0.01);
        $this->assertEqualsWithDelta(3000, $r['productive_vines_per_hectare'], 0.01);
        $this->assertEqualsWithDelta(2.4, $r['kg_per_vine'], 1e-9);
        $this->assertEqualsWithDelta(7200, $r['kg_per_hectare'], 0.01);
        $this->assertEqualsWithDelta(14400, $r['total_kg'], 0.01);
        $this->assertEqualsWithDelta(10080, $r['wine_litres'], 0.01);
        $this->assertEqualsWithDelta(0.9, $r['limit_ratio'], 1e-6);
    }

    public function test_harvest_yield_without_limit(): void
    {
        $r = (new HarvestYieldEstimator)->estimate(1, 2, 1, 10, 100);

        $this->assertNull($r['limit_ratio']);
        $this->assertEqualsWithDelta(5000, $r['kg_per_hectare'], 1e-9);
    }

    public function test_quiz_levels(): void
    {
        $quiz = new NotebookReadinessQuiz;
        $keys = array_keys($quiz->questions());

        $all = $quiz->evaluate(array_fill_keys($keys, true));
        $this->assertSame(100, $all['score']);
        $this->assertSame('ready', $all['level']);
        $this->assertSame([], $all['pending']);

        $half = array_fill_keys($keys, false);
        foreach (array_slice($keys, 0, 4) as $key) {
            $half[$key] = true;
        }
        $partial = $quiz->evaluate($half);
        $this->assertSame(50, $partial['score']);
        $this->assertSame('partial', $partial['level']);
        $this->assertCount(4, $partial['pending']);

        $none = $quiz->evaluate([]);
        $this->assertSame(0, $none['score']);
        $this->assertSame('risk', $none['level']);
    }
}
