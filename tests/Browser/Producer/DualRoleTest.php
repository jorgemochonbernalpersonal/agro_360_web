<?php

namespace Tests\Browser\Producer;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Flujo crítico: Rol Producer — acceso dual viñedo + bodega.
 *
 * El producer es una unión de viticulturist + winery.
 * Tiene acceso a todas las rutas bajo /producer/* que incluyen
 * tanto módulos de campo (parcelas, cuaderno, cosechas) como
 * módulos de bodega (vinos, contenedores, trazabilidad).
 *
 * Este test verifica que el rol producer puede navegar por ambos mundos
 * y que la navegación lateral (tab switcher Viñedo/Bodega) funciona.
 */
class DualRoleTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'SpainGeographySeeder']);
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    #[Test]
    public function producer_dashboard_loads(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/dashboard')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/producer/')
                ->screenshot('producer-dashboard');
        });
    }

    // ─── Módulos de campo (herencia viticulturist) ────────────────────────────

    #[Test]
    public function producer_can_access_plots(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/plots')
                ->waitFor('body', 5)
                ->assertPathIs('/producer/plots')
                ->screenshot('producer-plots');
        });
    }

    #[Test]
    public function producer_can_access_campaigns(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/campaigns')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/producer/')
                ->screenshot('producer-campaigns');
        });
    }

    #[Test]
    public function producer_plot_cascade_selects_work(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/plots/create')
                ->waitFor('body', 5)
                // El cascade de ubicación funciona igual que en viticulturist
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                ->select('select[wire\\:model="autonomous_community_id"]', 1)
                ->pause(500)
                ->assertEnabled('select[wire\\:model="province_id"]')
                // El componente Livewire no se rompe (sin Snapshot missing)
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                ->screenshot('producer-plots-cascade');
        });
    }

    // ─── Módulos de bodega (herencia winery) ─────────────────────────────────

    #[Test]
    public function producer_can_access_wines(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/wines')
                ->waitFor('body', 5)
                ->assertPathIs('/producer/wines')
                ->screenshot('producer-wines');
        });
    }

    #[Test]
    public function producer_can_access_wine_timeline(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/wines/timeline')
                ->waitFor('body', 5)
                ->assertPathIs('/producer/wines/timeline')
                ->assertSee('En Proceso')
                ->screenshot('producer-wine-timeline');
        });
    }

    #[Test]
    public function producer_can_access_containers_map(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/containers/map')
                ->waitFor('body', 5)
                ->assertPathIs('/producer/containers/map')
                ->screenshot('producer-container-map');
        });
    }

    #[Test]
    public function producer_can_access_traceability(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/traceability')
                ->waitFor('body', 5)
                ->assertPathIs('/producer/traceability')
                ->screenshot('producer-traceability');
        });
    }

    #[Test]
    public function producer_can_access_alerts(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/alerts')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/producer/')
                ->screenshot('producer-alerts');
        });
    }

    // ─── Navegación: tab switcher Viñedo / Bodega ─────────────────────────────

    #[Test]
    public function producer_sidebar_has_vineyard_and_bodega_tabs(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/dashboard')
                ->waitFor('body', 5)
                // El rail tiene los dos botones de tab
                ->assertPresent('button[title="Viñedo"]')
                ->assertPresent('button[title="Bodega"]')
                ->screenshot('producer-sidebar-tabs');
        });
    }

    #[Test]
    public function producer_sidebar_bodega_tab_shows_winery_sections(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/dashboard')
                ->waitFor('body', 5)
                // Clic en tab Bodega
                ->press('button[title="Bodega"]')
                ->pause(500)
                // Hacer clic en el capítulo Bodega Elab para abrir el flyout
                ->press('button[title="Bodega — Elab."]')
                ->pause(500)
                // El flyout debe mostrar los items de bodega
                ->assertSee('Mapa de Bodega')
                ->assertSee('Timeline de Vinos')
                ->screenshot('producer-sidebar-bodega-flyout');
        });
    }
}
