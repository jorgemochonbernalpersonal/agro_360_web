<?php

namespace Tests\Unit\Models;

use App\Models\Subscription;
use Tests\TestCase;

class SubscriptionTiersTest extends TestCase
{
    // ── wineryTierForCount ────────────────────────────────────────────────────

    public function test_winery_tier_0_viticultores_es_solo_vino(): void
    {
        $tier = Subscription::wineryTierForCount(0);

        $this->assertSame('solo_vino', $tier['label']);
        $this->assertSame(19.00, $tier['monthly']);
        $this->assertSame(180.00, $tier['yearly']);
    }

    public function test_winery_tier_1_viticultor_es_red_s(): void
    {
        $tier = Subscription::wineryTierForCount(1);

        $this->assertSame('red_s', $tier['label']);
        $this->assertSame(29.00, $tier['monthly']);
        $this->assertSame(290.00, $tier['yearly']);
    }

    public function test_winery_tier_10_viticultores_sigue_en_red_s(): void
    {
        $this->assertSame('red_s', Subscription::wineryTierForCount(10)['label']);
    }

    public function test_winery_tier_11_viticultores_sube_a_red_m(): void
    {
        $tier = Subscription::wineryTierForCount(11);

        $this->assertSame('red_m', $tier['label']);
        $this->assertSame(49.00, $tier['monthly']);
        $this->assertSame(490.00, $tier['yearly']);
    }

    public function test_winery_tier_30_viticultores_sigue_en_red_m(): void
    {
        $this->assertSame('red_m', Subscription::wineryTierForCount(30)['label']);
    }

    public function test_winery_tier_31_viticultores_sube_a_red_l(): void
    {
        $tier = Subscription::wineryTierForCount(31);

        $this->assertSame('red_l', $tier['label']);
        $this->assertSame(79.00, $tier['monthly']);
        $this->assertSame(790.00, $tier['yearly']);
    }

    public function test_winery_tier_60_viticultores_sigue_en_red_l(): void
    {
        $this->assertSame('red_l', Subscription::wineryTierForCount(60)['label']);
    }

    public function test_winery_tier_61_viticultores_sube_a_red_xl(): void
    {
        $tier = Subscription::wineryTierForCount(61);

        $this->assertSame('red_xl', $tier['label']);
        $this->assertSame(119.00, $tier['monthly']);
        $this->assertSame(1190.00, $tier['yearly']);
    }

    public function test_winery_tier_100_viticultores_sigue_en_red_xl(): void
    {
        $this->assertSame('red_xl', Subscription::wineryTierForCount(100)['label']);
    }

    public function test_winery_tier_101_viticultores_es_enterprise(): void
    {
        $tier = Subscription::wineryTierForCount(101);

        $this->assertSame('enterprise', $tier['label']);
        $this->assertNull($tier['monthly']);
        $this->assertNull($tier['yearly']);
    }

    // ── doTierForCount ────────────────────────────────────────────────────────

    public function test_do_tier_0_bodegas_es_do_s(): void
    {
        $tier = Subscription::doTierForCount(0);

        $this->assertSame('do_s', $tier['label']);
        $this->assertSame(149.00, $tier['monthly']);
        $this->assertSame(1400.00, $tier['yearly']);
    }

    public function test_do_tier_25_bodegas_sigue_en_do_s(): void
    {
        $this->assertSame('do_s', Subscription::doTierForCount(25)['label']);
    }

    public function test_do_tier_26_bodegas_sube_a_do_m(): void
    {
        $tier = Subscription::doTierForCount(26);

        $this->assertSame('do_m', $tier['label']);
        $this->assertSame(249.00, $tier['monthly']);
        $this->assertSame(2350.00, $tier['yearly']);
    }

    public function test_do_tier_50_bodegas_sigue_en_do_m(): void
    {
        $this->assertSame('do_m', Subscription::doTierForCount(50)['label']);
    }

    public function test_do_tier_51_bodegas_sube_a_do_l(): void
    {
        $tier = Subscription::doTierForCount(51);

        $this->assertSame('do_l', $tier['label']);
        $this->assertSame(349.00, $tier['monthly']);
        $this->assertSame(3300.00, $tier['yearly']);
    }

    public function test_do_tier_75_bodegas_sigue_en_do_l(): void
    {
        $this->assertSame('do_l', Subscription::doTierForCount(75)['label']);
    }

    public function test_do_tier_76_bodegas_sube_a_do_xl(): void
    {
        $tier = Subscription::doTierForCount(76);

        $this->assertSame('do_xl', $tier['label']);
        $this->assertSame(449.00, $tier['monthly']);
        $this->assertSame(4250.00, $tier['yearly']);
    }

    public function test_do_tier_100_bodegas_sigue_en_do_xl(): void
    {
        $this->assertSame('do_xl', Subscription::doTierForCount(100)['label']);
    }

    public function test_do_tier_101_bodegas_es_enterprise(): void
    {
        $tier = Subscription::doTierForCount(101);

        $this->assertSame('enterprise', $tier['label']);
        $this->assertNull($tier['monthly']);
        $this->assertNull($tier['yearly']);
    }
}
