<?php

namespace Tests\Feature\Winery\Alerts;

use Tests\Feature\WineryTestCase;

class AlertsTest extends WineryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->makeWinery());
    }

    public function test_alerts_dashboard_renders(): void
    {
        $this->get(route('winery.alerts.index'))->assertOk();
    }
}
