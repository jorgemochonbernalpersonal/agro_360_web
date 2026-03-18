<?php

namespace Tests\Feature\Winery\Financial;

use Tests\Feature\WineryTestCase;

class FinancialTest extends WineryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->makeWinery());
    }

    public function test_financial_summary_renders(): void
    {
        $this->get(route('winery.financial-summary.index'))->assertOk();
    }

    public function test_financial_stats_renders(): void
    {
        $this->get(route('winery.financial-stats.index'))->assertOk();
    }
}
