<?php

namespace Tests\Feature\Producer;

use App\Models\User;
use Tests\Feature\ProducerTestCase;

/**
 * Verifica que el producer tiene acceso híbrido:
 * puede acceder a rutas winery.* Y a rutas producer.*
 * con el mismo usuario.
 */
class HybridAccessTest extends ProducerTestCase
{
    private User $producer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);
    }

    // Producer puede acceder a rutas winery.* (middleware role:winery,producer)

    public function test_producer_can_access_winery_dashboard(): void
    {
        $this->get(route('winery.dashboard'))->assertOk();
    }

    public function test_producer_can_access_winery_wines(): void
    {
        $this->get(route('winery.wines.index'))->assertOk();
    }

    public function test_producer_can_access_winery_containers(): void
    {
        $this->get(route('winery.containers.index'))->assertOk();
    }

    // Producer también puede acceder a sus rutas propias producer.*

    public function test_producer_can_access_own_dashboard(): void
    {
        $this->get(route('producer.dashboard'))->assertOk();
    }

    public function test_producer_can_access_own_plots(): void
    {
        $this->get(route('producer.plots.index'))->assertOk();
    }

    // hasWineryAccess() y hasViticulturistAccess() devuelven true

    public function test_producer_has_winery_access(): void
    {
        $this->assertTrue($this->producer->hasWineryAccess());
    }

    public function test_producer_has_viticulturist_access(): void
    {
        $this->assertTrue($this->producer->hasViticulturistAccess());
    }

    public function test_producer_can_select_viticulturist(): void
    {
        $this->assertTrue($this->producer->canSelectViticulturist());
    }

    // Winery NO puede acceder a rutas producer.*

    public function test_winery_cannot_access_producer_routes(): void
    {
        $this->actingAs($this->makeWinery())
            ->get(route('producer.dashboard'))
            ->assertForbidden();
    }

    // Viticulturist NO puede acceder a rutas producer.*

    public function test_viticulturist_cannot_access_producer_routes(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.dashboard'))
            ->assertForbidden();
    }
}
