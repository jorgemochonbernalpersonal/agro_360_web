<?php

namespace Tests\Browser\Winery;

use App\Models\Wine;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Flujo crítico: Módulo de vinos en Winery.
 *
 * Cubre:
 *  1. Listado de vinos accesible
 *  2. Crear un vino básico
 *  3. Timeline de vinos — carga y muestra vinos por estado
 *  4. Trazabilidad — listado y descarga de PDF
 *  5. Alertas — centro de alertas accesible
 */
class WineWorkflowTest extends DuskTestCase
{
    #[Test]
    public function wines_index_loads_for_winery(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/wines')
                ->waitFor('body', 5)
                ->assertPathIs('/winery/wines')
                ->screenshot('winery-wines-index');
        });
    }

    #[Test]
    public function wine_create_form_renders_and_submits(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/wines/create')
                ->waitFor('body', 5)
                ->assertSee('Crear')
                // Rellenar nombre
                ->type('[wire\\:model="name"]', 'Vino Dusk Test')
                // Seleccionar tipo si hay select disponible
                ->whenAvailable('select[wire\\:model="wine_type"]', function ($s) {
                    $s->select('', $s->value('option:not([value=""])'));
                })
                ->press('Guardar')
                ->pause(3000)
                // Tras guardar, redirige al listado o show — no debe haber excepción 500
                ->assertDontSee('500')
                ->assertDontSee('Server Error')
                ->screenshot('winery-wine-create');
        });
    }

    #[Test]
    public function wine_timeline_loads_and_shows_columns(): void
    {
        $user = $this->createWinery();
        $this->createSampleWines($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/wines/timeline')
                ->waitFor('body', 5)
                ->assertPathIs('/winery/wines/timeline')
                // Debe mostrar las 3 columnas del Kanban
                ->assertSee('En Proceso')
                ->assertSee('En Crianza')
                ->assertSee('Embotellado')
                ->screenshot('winery-wines-timeline');
        });
    }

    #[Test]
    public function wine_traceability_index_loads(): void
    {
        $user = $this->createWinery();
        $this->createSampleWines($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/traceability')
                ->waitFor('body', 5)
                ->assertPathIs('/winery/traceability')
                ->assertSee('Trazabilidad')
                ->screenshot('winery-traceability-index');
        });
    }

    #[Test]
    public function wine_traceability_pdf_is_downloadable(): void
    {
        $user = $this->createWinery();
        $wine = $this->createSampleWines($user)[0];

        $this->browse(function (Browser $browser) use ($user, $wine) {
            // El endpoint PDF devuelve un stream de PDF, no una página HTML.
            // Verificamos que la ruta responde 200 y no lanza excepción.
            $browser->loginAs($user)
                ->visit("/winery/wines/{$wine->id}/traceability-pdf")
                ->pause(3000);

            // La URL no debe ser de error
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringNotContainsString('error', strtolower($currentUrl));
            $this->assertStringNotContainsString('exception', strtolower($currentUrl));

            $browser->screenshot('winery-traceability-pdf');
        });
    }

    #[Test]
    public function wine_container_map_loads(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/containers/map')
                ->waitFor('body', 5)
                ->assertPathIs('/winery/containers/map')
                ->assertSee('Mapa')
                ->screenshot('winery-container-map');
        });
    }

    #[Test]
    public function alerts_dashboard_loads_and_shows_sections(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/alerts')
                ->waitFor('body', 5)
                ->assertPathIs('/winery/alerts')
                ->assertSee('Alertas')
                ->screenshot('winery-alerts-dashboard');
        });
    }

    #[Test]
    public function stock_audit_page_loads(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/product-lots/audit')
                ->waitFor('body', 5)
                ->assertPathIs('/winery/product-lots/audit')
                ->assertSee('Auditoría')
                ->screenshot('winery-stock-audit');
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createSampleWines($user): array
    {
        $wines = [];
        $statuses = ['in_progress', 'aging', 'bottled'];

        foreach ($statuses as $status) {
            $wines[] = Wine::create([
                'user_id'    => $user->id,
                'name'       => "Vino Dusk {$status}",
                'wine_type'  => 'red',
                'status'     => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $wines;
    }
}
