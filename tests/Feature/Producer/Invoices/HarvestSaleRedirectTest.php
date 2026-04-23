<?php

namespace Tests\Feature\Producer\Invoices;

use App\Models\User;
use Tests\Feature\ProducerTestCase;

/**
 * Verifica que roleRoute() resuelve al prefijo correcto según el rol
 * para las rutas de facturación de venta de cosecha.
 */
class HarvestSaleRedirectTest extends ProducerTestCase
{
    public function test_role_route_resolves_producer_prefix_for_producer(): void
    {
        $this->actingAs($this->makeProducer());

        $this->assertEquals(
            route('producer.invoices.harvest-sale.index'),
            roleRoute('invoices.harvest-sale.index')
        );
    }

    public function test_role_route_resolves_viticulturist_prefix_for_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $this->assertEquals(
            route('viticulturist.invoices.harvest-sale.index'),
            roleRoute('invoices.harvest-sale.index')
        );
    }

    public function test_producer_and_viticulturist_resolve_to_different_routes(): void
    {
        $producer = $this->makeProducer();
        $viticulturist = $this->makeViticulturist();

        $this->actingAs($producer);
        $producerUrl = roleRoute('invoices.harvest-sale.index');

        $this->actingAs($viticulturist);
        $viticulturistUrl = roleRoute('invoices.harvest-sale.index');

        $this->assertNotEquals($producerUrl, $viticulturistUrl);
        $this->assertStringContainsString('/producer/', $producerUrl);
        $this->assertStringContainsString('/viticulturist/', $viticulturistUrl);
    }
}
