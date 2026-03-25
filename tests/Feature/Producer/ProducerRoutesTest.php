<?php

namespace Tests\Feature\Producer;

use Tests\Feature\ProducerTestCase;

/**
 * Verifies that key producer routes are accessible to producers
 * and blocked for other roles.
 */
class ProducerRoutesTest extends ProducerTestCase
{
    private \App\Models\User $producer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);
    }

    // ── Dashboard ──────────────────────────────────────────────────────────

    public function test_dashboard_renders(): void
    {
        $this->get(route('producer.dashboard'))->assertOk();
    }

    // ── Rutas de bodega ────────────────────────────────────────────────────

    public function test_wines_index_renders(): void
    {
        $this->get(route('producer.wines.index'))->assertOk();
    }

    public function test_containers_index_renders(): void
    {
        $this->get(route('producer.containers.index'))->assertOk();
    }

    public function test_container_rooms_index_renders(): void
    {
        $this->get(route('producer.container-rooms.index'))->assertOk();
    }

    public function test_cellar_operations_index_renders(): void
    {
        $this->get(route('producer.cellar-operations.index'))->assertOk();
    }

    public function test_financial_summary_renders(): void
    {
        $this->get(route('producer.financial-summary.index'))->assertOk();
    }

    public function test_financial_stats_winery_renders(): void
    {
        $this->get(route('producer.financial-stats-winery'))->assertOk();
    }

    public function test_winery_clients_index_renders(): void
    {
        $this->get(route('producer.winery-clients.index'))->assertOk();
    }

    public function test_documents_index_renders(): void
    {
        $this->get(route('producer.documents.index'))->assertOk();
    }

    public function test_alerts_index_renders(): void
    {
        $this->get(route('producer.alerts.index'))->assertOk();
    }

    public function test_product_lots_index_renders(): void
    {
        $this->get(route('producer.product-lots.index'))->assertOk();
    }

    public function test_product_lots_audit_renders(): void
    {
        $this->get(route('producer.product-lots.audit'))->assertOk();
    }

    // ── Rutas de viticultor ────────────────────────────────────────────────

    public function test_plots_index_renders(): void
    {
        $this->get(route('producer.plots.index'))->assertOk();
    }

    public function test_digital_notebook_renders(): void
    {
        $this->get(route('producer.digital-notebook'))->assertOk();
    }

    public function test_financial_stats_campo_renders(): void
    {
        $this->get(route('producer.financial-stats.index'))->assertOk();
    }

    // ── Bloqueo a otros roles ──────────────────────────────────────────────

    public function test_winery_cannot_access_producer_routes(): void
    {
        $this->actingAs($this->makeWinery())
            ->get(route('producer.dashboard'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_producer_routes(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.dashboard'))
            ->assertForbidden();
    }
}
