<?php

namespace Tests\Feature\Producer;

use Tests\Feature\ProducerTestCase;

/**
 * Verifica el middleware EnsureProducerBuysExternalGrape.
 *
 * Los módulos de gestión de viticultores (producer.viticulturists.*)
 * y de facturas de compra de uva (producer.invoices.grape-purchase.*)
 * solo están disponibles cuando el producer tiene compra_uva_externa = true.
 *
 * El middleware comprueba: !$user->isProducer() || !$user->compra_uva_externa → 403
 */
class ExternalGrapeMiddlewareTest extends ProducerTestCase
{
    // ── Viticulturists module ─────────────────────────────────────────────────

    public function test_producer_with_external_grape_can_access_viticulturists(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => true]);

        $this->actingAs($producer)
            ->get(route('producer.viticulturists.index'))
            ->assertOk();
    }

    public function test_producer_without_external_grape_cannot_access_viticulturists(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => false]);

        $this->actingAs($producer)
            ->get(route('producer.viticulturists.index'))
            ->assertForbidden();
    }

    public function test_producer_without_external_grape_cannot_access_viticulturist_create(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => false]);

        $this->actingAs($producer)
            ->get(route('producer.viticulturists.create'))
            ->assertForbidden();
    }

    public function test_producer_without_external_grape_cannot_access_viticulturist_invite(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => false]);

        $this->actingAs($producer)
            ->get(route('producer.viticulturists.invite'))
            ->assertForbidden();
    }

    // ── Grape purchase invoices module ────────────────────────────────────────

    public function test_producer_with_external_grape_can_access_grape_purchase(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => true]);

        $this->actingAs($producer)
            ->get(route('producer.invoices.grape-purchase.index'))
            ->assertOk();
    }

    public function test_producer_without_external_grape_cannot_access_grape_purchase(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => false]);

        $this->actingAs($producer)
            ->get(route('producer.invoices.grape-purchase.index'))
            ->assertForbidden();
    }

    // ── Módulos sin restricción siguen accesibles ─────────────────────────────

    public function test_producer_without_external_grape_can_still_access_own_dashboard(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => false]);

        $this->actingAs($producer)
            ->get(route('producer.dashboard'))
            ->assertOk();
    }

    public function test_producer_without_external_grape_can_access_product_invoices(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => false]);

        // Venta de productos no requiere compra de uva externa
        $this->actingAs($producer)
            ->get(route('producer.invoices.products.index'))
            ->assertOk();
    }

    // ── compra_uva_externa null actúa como false ──────────────────────────────

    public function test_producer_with_null_external_grape_cannot_access_viticulturists(): void
    {
        $producer = $this->makeProducer();
        // compra_uva_externa defaults to null/false — not explicitly set

        $this->actingAs($producer)
            ->get(route('producer.viticulturists.index'))
            ->assertForbidden();
    }
}
