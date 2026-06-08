<?php

namespace Tests\Feature\Navigation;

use App\Helpers\NavigationHelper;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que NavigationHelper devuelve las secciones correctas para cada rol.
 *
 * Tras el refactor a clases separadas (ViticulturistMenu, WineryMenu,
 * ProducerMenu, DOMenu) es crítico garantizar que ningún rol recibe
 * secciones de otro rol y que todas las secciones esperadas están presentes.
 */
class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    // ── Usuario no autenticado ────────────────────────────────────────────────

    public function test_unauthenticated_returns_empty_menu(): void
    {
        $menu = NavigationHelper::getMenu();

        $this->assertSame([], $menu);
    }

    // ── Viticulturist ─────────────────────────────────────────────────────────

    public function test_viticulturist_receives_correct_sections(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $menu = NavigationHelper::getMenu();

        $expected = ['main', 'campaigns', 'winery_rel', 'notebook_inputs', 'monitoring', 'environmental', 'declarations', 'estate', 'analytics', 'resources', 'compliance', 'pac', 'billing', 'rail_bottom'];
        foreach ($expected as $section) {
            $this->assertArrayHasKey($section, $menu, "Viticulturist debe tener la sección '{$section}'");
        }
    }

    public function test_viticulturist_has_no_winery_sections(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $menu = NavigationHelper::getMenu();

        $wineryOnly = ['harvest', 'cellar_elaboration', 'cellar_output', 'territory', 'winery_compliance', 'winery_resources', 'winery_docs', 'registrations', 'system'];
        foreach ($wineryOnly as $section) {
            $this->assertArrayNotHasKey($section, $menu, "Viticulturist NO debe tener la sección winery '{$section}'");
        }
    }

    public function test_viticulturist_main_includes_dashboard_calendar_notifications(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['main'], 'route');

        $this->assertContains('viticulturist.dashboard', $routes);
        $this->assertContains('viticulturist.calendar', $routes);
        $this->assertContains('viticulturist.notifications.index', $routes);
    }

    public function test_viticulturist_winery_rel_includes_access_and_messages(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['winery_rel'], 'route');

        $this->assertContains('viticulturist.winery-access.index', $routes);
        $this->assertContains('viticulturist.winery-messages.index', $routes);
    }

    public function test_viticulturist_notebook_inputs_starts_with_overview(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $menu = NavigationHelper::getMenu();
        $first = $menu['notebook_inputs'][0];

        $this->assertSame('viticulturist.digital-notebook', $first['route']);
        $this->assertSame('Cuaderno Digital', $first['label']);
    }

    public function test_viticulturist_rail_bottom_has_soporte_with_badge(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['rail_bottom'], 'route');

        $this->assertContains('viticulturist.support.index', $routes);

        $soporte = collect($menu['rail_bottom'])->firstWhere('route', 'viticulturist.support.index');
        $this->assertArrayHasKey('badge', $soporte);
    }

    // ── Winery ────────────────────────────────────────────────────────────────

    public function test_winery_receives_correct_sections(): void
    {
        $winery = $this->makeWinery();
        $this->actingAs($winery);

        $menu = NavigationHelper::getMenu();

        $expected = ['main', 'harvest', 'cellar_infra', 'cellar_wines', 'cellar_output', 'winery_compliance', 'territory', 'analytics', 'billing', 'winery_resources', 'winery_docs', 'system'];
        foreach ($expected as $section) {
            $this->assertArrayHasKey($section, $menu, "Winery debe tener la sección '{$section}'");
        }
    }

    public function test_winery_has_no_viticulturist_sections(): void
    {
        $winery = $this->makeWinery();
        $this->actingAs($winery);

        $menu = NavigationHelper::getMenu();

        $viticulturistOnly = ['campaigns', 'winery_rel', 'notebook_inputs', 'pac', 'rail_bottom'];
        foreach ($viticulturistOnly as $section) {
            $this->assertArrayNotHasKey($section, $menu, "Winery NO debe tener la sección '{$section}'");
        }
    }

    public function test_winery_main_points_to_winery_dashboard(): void
    {
        $winery = $this->makeWinery();
        $this->actingAs($winery);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['main'], 'route');

        $this->assertContains('winery.dashboard', $routes);
        $this->assertNotContains('viticulturist.dashboard', $routes);
        $this->assertNotContains('producer.dashboard', $routes);
    }

    // ── Producer ──────────────────────────────────────────────────────────────

    public function test_producer_receives_vineyard_sections(): void
    {
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();

        $vineyardSections = ['campaigns', 'notebook_inputs', 'monitoring', 'environmental', 'declarations', 'estate', 'analytics', 'resources', 'compliance', 'pac', 'billing'];
        foreach ($vineyardSections as $section) {
            $this->assertArrayHasKey($section, $menu, "Producer debe tener la sección viñedo '{$section}'");
        }
    }

    public function test_producer_receives_winery_sections(): void
    {
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();

        $winerySections = ['harvest', 'cellar_elaboration', 'cellar_output', 'winery_compliance', 'winery_resources', 'winery_docs', 'winery_billing'];
        foreach ($winerySections as $section) {
            $this->assertArrayHasKey($section, $menu, "Producer debe tener la sección bodega '{$section}'");
        }
    }

    public function test_producer_main_points_to_producer_dashboard(): void
    {
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['main'], 'route');

        $this->assertContains('producer.dashboard', $routes);
        $this->assertNotContains('winery.dashboard', $routes);
        $this->assertNotContains('viticulturist.dashboard', $routes);
    }

    public function test_producer_notebook_inputs_starts_with_overview(): void
    {
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();
        $first = $menu['notebook_inputs'][0];

        $this->assertSame('producer.digital-notebook', $first['route']);
    }

    public function test_producer_has_no_winery_rel_section(): void
    {
        // Producer es la bodega, por lo que no tiene 'Comunicación con Bodega' ni 'Acceso Bodegas al Cuaderno'
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();

        $this->assertArrayNotHasKey('winery_rel', $menu);
    }

    public function test_producer_winery_billing_has_no_verifactu_duplicate(): void
    {
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();

        $billingRoutes = array_column($menu['billing'], 'route');
        $wineryBillingRoutes = array_column($menu['winery_billing'], 'route');

        // VeriFactu existe en billing (viñedo) pero NO en winery_billing (bodega)
        $this->assertContains('producer.verifactu.index', $billingRoutes);
        $this->assertNotContains('producer.verifactu.index', $wineryBillingRoutes);
    }

    public function test_producer_has_no_territory_section(): void
    {
        // Producer gestiona parcelas desde plots_analysis (viñedo), no desde territory (winery)
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();

        $this->assertArrayNotHasKey('territory', $menu);
    }

    public function test_producer_rail_bottom_has_soporte(): void
    {
        $producer = $this->makeProducer();
        $this->actingAs($producer);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['rail_bottom'], 'route');

        $this->assertContains('producer.support.index', $routes);
        $this->assertContains('producer.settings', $routes);
    }

    // ── Supervisor (DO) ───────────────────────────────────────────────────────

    public function test_supervisor_receives_do_sections(): void
    {
        $supervisor = $this->makeSupervisor();
        $this->actingAs($supervisor);

        $menu = NavigationHelper::getMenu();

        $doSections = ['main', 'do_census', 'do_growers', 'do_campaigns', 'do_oversight_wineries', 'do_oversight_growers', 'do_qualification', 'do_labels', 'do_inspection', 'do_regulation', 'do_territory', 'do_statistics', 'do_finance', 'do_settings'];
        foreach ($doSections as $section) {
            $this->assertArrayHasKey($section, $menu, "Supervisor debe tener la sección '{$section}'");
        }
    }

    public function test_supervisor_has_no_viticulturist_or_winery_sections(): void
    {
        $supervisor = $this->makeSupervisor();
        $this->actingAs($supervisor);

        $menu = NavigationHelper::getMenu();

        $otherRoleSections = ['campaigns', 'notebook_inputs', 'harvest', 'cellar_elaboration', 'billing', 'pac'];
        foreach ($otherRoleSections as $section) {
            $this->assertArrayNotHasKey($section, $menu, "Supervisor NO debe tener '{$section}'");
        }
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public function test_admin_receives_admin_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin);

        $menu = NavigationHelper::getMenu();
        $routes = array_column($menu['main'], 'route');

        $this->assertContains('admin.users.index', $routes);
        $this->assertContains('admin.support.index', $routes);
    }

    public function test_admin_has_no_viticulturist_sections(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin);

        $menu = NavigationHelper::getMenu();

        $this->assertArrayNotHasKey('operations', $menu);
        $this->assertArrayNotHasKey('cuaderno_inputs', $menu);
        $this->assertArrayNotHasKey('harvest', $menu);
    }

    // ── getRoleName ───────────────────────────────────────────────────────────

    public function test_get_role_name_returns_correct_labels(): void
    {
        $this->assertSame('Administrador', NavigationHelper::getRoleName('admin'));
        $this->assertSame('Denominación de Origen', NavigationHelper::getRoleName('supervisor'));
        $this->assertSame('Denominación de Origen', NavigationHelper::getRoleName('denomination_of_origin'));
        $this->assertSame('Bodega', NavigationHelper::getRoleName('winery'));
        $this->assertSame('Viticultor', NavigationHelper::getRoleName('viticulturist'));
        $this->assertSame('Productor', NavigationHelper::getRoleName('producer'));
    }

    public function test_get_role_name_fallback_ucfirst(): void
    {
        $this->assertSame('Unknown', NavigationHelper::getRoleName('unknown'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturist(): User
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        return $viticulturist;
    }

    private function makeWinery(): User
    {
        return User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);
    }

    private function makeProducer(): User
    {
        return User::factory()->create(['role' => 'producer', 'email_verified_at' => now()]);
    }

    private function makeSupervisor(): User
    {
        return User::factory()->create(['role' => 'supervisor', 'email_verified_at' => now()]);
    }
}
