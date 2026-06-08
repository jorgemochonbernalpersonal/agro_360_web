<?php

namespace App\Helpers\Navigation;

use App\Models\SupervisorRequest;
use Illuminate\Support\Facades\Cache;

class ProducerMenu
{
    public static function build($user): array
    {
        $menu = [];

        // ── Sección principal ─────────────────────────────────────────────────
        $menu['main'] = [
            ['icon' => 'squares-2x2',          'label' => __('Vista general'),        'route' => 'producer.dashboard',        'active' => request()->routeIs('producer.dashboard')],
            ['icon' => 'calendar-days',        'label' => __('Calendario'),           'route' => 'producer.calendar',         'active' => request()->routeIs('producer.calendar')],
            ['icon' => 'bolt',                 'label' => __('Entrada Rápida'),       'route' => 'producer.quick-entry',      'active' => request()->routeIs('producer.quick-entry')],
            ['icon' => 'arrow-trending-up',    'label' => __('Trazabilidad Completa'), 'route' => 'producer.full-traceability', 'active' => request()->routeIs('producer.full-traceability')],
        ];

        // ── Viñedo: Campaña ───────────────────────────────────────────────────
        $menu['campaigns'] = [
            ['icon' => 'clipboard-document-list', 'label' => __('Campañas'),              'route' => 'producer.campaign.index',           'active' => request()->routeIs('producer.campaign*') && ! request()->routeIs('producer.campaign-documents.*') && ! request()->routeIs('producer.campaign-sign.*')],
            ['icon' => 'folder-open',             'label' => __('Documentos de Campaña'), 'route' => 'producer.campaign-documents.index', 'active' => request()->routeIs('producer.campaign-documents.*')],
            ['icon' => 'check-badge',             'label' => __('Firma y Cierre'),        'route' => 'producer.campaign-sign.index',      'active' => request()->routeIs('producer.campaign-sign.*')],
            ['divider' => true],
            ['icon' => 'squares-plus',     'label' => __('Dashboard Integrado'),   'route' => 'producer.integrated-campaign',    'active' => request()->routeIs('producer.integrated-campaign')],
            ['icon' => 'queue-list',       'label' => __('Plan de Trabajos'),      'route' => 'producer.planned-works.index',    'active' => request()->routeIs('producer.planned-works.*')],
            ['icon' => 'chart-bar-square', 'label' => __('Comparativa de Campañas'), 'route' => 'producer.campaign-comparison',   'active' => request()->routeIs('producer.campaign-comparison')],
        ];

        // ── Plan de acceso del producer ───────────────────────────────────────
        $producerLocked = ! $user->hasActiveAccess();

        // ── Viñedo: Cuaderno de Campo ─────────────────────────────────────────
        $menu['notebook_inputs'] = ViticulturistMenu::notebookInputs('producer', 'Vendimia Campo', $producerLocked);

        // ── Viñedo: Seguimiento (plagas + alertas) ───────────────────────────
        $menu['monitoring'] = ViticulturistMenu::monitoring('producer', $producerLocked);

        // ── Viñedo: Medioambiente ─────────────────────────────────────────────
        $menu['environmental'] = ViticulturistMenu::environmental('producer', $producerLocked);

        // ── Viñedo: Declaraciones y Certificaciones ───────────────────────────
        $menu['declarations'] = ViticulturistMenu::officialDeclarations('producer', $producerLocked);

        // ── Bodega: Vendimia ──────────────────────────────────────────────────
        $harvestItems = [
            ['icon' => 'chart-bar',               'label' => __('Cuadro de Mando'),        'route' => 'producer.harvest-summary.index',    'active' => request()->routeIs('producer.harvest-summary*')],
            ['icon' => 'clipboard-document-list', 'label' => __('Campañas Bodega'),   'route' => 'producer.winery-campaigns.index',   'active' => request()->routeIs('producer.winery-campaigns*')],
            ['icon' => 'clipboard-document-list', 'label' => __('Previsiones'),        'route' => 'producer.harvest-forecasts.index',  'active' => request()->routeIs('producer.harvest-forecasts*')],
            ['icon' => 'archive-box-arrow-down',  'label' => __('Recepciones'),        'route' => 'producer.grape-reception.index',    'active' => request()->routeIs('producer.grape-reception*') && ! request()->routeIs('producer.grape-reception.disputes')],
            ['icon' => 'beaker',                  'label' => __('Análisis de Calidad'), 'route' => 'producer.harvest-quality.index',    'active' => request()->routeIs('producer.harvest-quality*')],
        ];

        if ($user->compra_uva_externa) {
            $harvestItems[] = ['divider' => true];
            $harvestItems[] = ['icon' => 'user-group',           'label' => __('Mis Viticultores'),    'route' => 'producer.viticulturists.index',     'active' => request()->routeIs('producer.viticulturists*')];
            $harvestItems[] = ['icon' => 'calculator',           'label' => __('Aforos Viticultores'), 'route' => 'producer.vitic-estimates.index',    'active' => request()->routeIs('producer.vitic-estimates*')];
            $harvestItems[] = ['icon' => 'exclamation-triangle', 'label' => __('Disputas'),            'route' => 'producer.grape-reception.disputes', 'active' => request()->routeIs('producer.grape-reception.disputes')];
        }

        $menu['harvest'] = $harvestItems;

        // ── Bodega: Elaboración ───────────────────────────────────────────────
        $menu['cellar_elaboration'] = WineryMenu::cellarElaboration('producer', operacionesAfterSalas: true);

        // ── Bodega: Salida ────────────────────────────────────────────────────
        $menu['cellar_output'] = WineryMenu::cellarOutput('producer');

        // ── Bodega: Normativa ─────────────────────────────────────────────────
        $menu['winery_compliance'] = WineryMenu::wineryCompliance('producer');

        // ── Bodega: Insumos, Proveedores y Alertas ────────────────────────────
        $menu['winery_resources'] = [
            ['icon' => 'building-storefront', 'label' => __('Insumos de Bodega'), 'route' => 'producer.winery-supplies.index', 'active' => request()->routeIs('producer.winery-supplies*')],
            ['icon' => 'truck',               'label' => __('Proveedores'),        'route' => 'producer.suppliers.index',       'active' => request()->routeIs('producer.suppliers*')],
            ['divider' => true],
            ['icon' => 'bell-alert',          'label' => __('Centro de Alertas'),  'route' => 'producer.alerts.index',          'active' => request()->routeIs('producer.alerts*')],
        ];

        // ── Bodega: Documentos ────────────────────────────────────────────────
        $menu['winery_docs'] = [
            ['icon' => 'folder-open', 'label' => __('Documentos Bodega'), 'route' => 'producer.documents.index', 'active' => request()->routeIs('producer.documents*')],
        ];

        // ── Parcelas (unión viñedo + bodega) ──────────────────────────────────
        $menu['estate'] = [
            ['icon' => 'squares-plus',        'label' => __('Panel de Finca'),      'route' => 'producer.integrated-estate',       'active' => request()->routeIs('producer.integrated-estate')],
            ['divider' => true],
            ['icon' => 'map',                 'label' => __('Parcelas'),            'route' => 'producer.plots.index',             'active' => request()->routeIs('producer.plots.*') && ! request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'book-open',           'label' => __('Plantaciones'),        'route' => 'plots.plantings.index',            'active' => request()->routeIs('plots.plantings.*') || request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => __('SIGPAC'),              'route' => 'sigpac.codes',                     'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-europe-africa', 'label' => __('Gestión Territorial'), 'route' => 'plots.territory',                  'active' => request()->routeIs('plots.territory')],
            ['divider' => true],
            ['icon' => 'pencil-square',       'label' => __('Actividades de Campo'), 'route' => 'producer.field-activities.index',  'active' => request()->routeIs('producer.field-activities*')],
        ];

        // ── Análisis de Finca ─────────────────────────────────────────────────
        $menu['analytics'] = [
            ['icon' => 'globe-alt',         'label' => __('Teledetección'),       'route' => 'remote-sensing.dashboard',              'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'cloud',             'label' => __('Meteorología'),        'route' => 'producer.meteorology.index',            'active' => request()->routeIs('producer.meteorology*')],
            ['icon' => 'viewfinder-circle', 'label' => __('Entorno de Parcelas'), 'route' => 'producer.plot-environments.index',      'active' => request()->routeIs('producer.plot-environments.*')],
            ['divider' => true],
            ['icon' => 'beaker',            'label' => __('Análisis de Suelo'),   'route' => 'producer.soil-analyses.index',          'active' => request()->routeIs('producer.soil-analyses.*')],
        ];

        // ── Recursos ──────────────────────────────────────────────────────────
        $menu['resources'] = ViticulturistMenu::resources('producer', includeContainers: false, locked: $producerLocked);

        // ── Normativa viñedo ──────────────────────────────────────────────────
        $menu['compliance'] = ViticulturistMenu::compliance('producer', $producerLocked);

        // ── PAC ───────────────────────────────────────────────────────────────
        $menu['pac'] = ViticulturistMenu::pac('producer', $producerLocked);

        // ── Negocio viñedo (usado por tab viñedo del producer) ────────────────
        $menu['billing'] = [
            ['icon' => 'calculator',             'label' => __('Facturas'),               'route' => 'producer.invoices.index',          'active' => request()->routeIs('producer.invoices.*') && ! request()->routeIs('producer.invoices.products.*') && ! request()->routeIs('producer.invoices.grape-purchase.*') && ! request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-arrow-up',      'label' => __('Albaranes Mixtos'),       'route' => 'producer.invoices.mixed.index',    'active' => request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-check',         'label' => __('VeriFactu'),              'route' => 'producer.verifactu.index',         'active' => request()->routeIs('producer.verifactu*')],
            ['icon' => 'shopping-cart',          'label' => __('Cosecha Comercializada'), 'route' => 'producer.marketed-harvests.index', 'active' => request()->routeIs('producer.marketed-harvests.*')],
            ['icon' => 'table-cells',            'label' => __('Costes por Parcela'),     'route' => 'producer.plot-costs.index',        'active' => request()->routeIs('producer.plot-costs*')],
            ['icon' => 'users',                  'label' => __('Clientes'),               'route' => 'producer.clients.index',           'active' => request()->routeIs('producer.clients.*')],
            ['icon' => 'presentation-chart-bar', 'label' => __('Estadísticas Viñedo'),    'route' => 'producer.financial-stats.index',   'active' => request()->routeIs('producer.financial-stats.index')],
        ];

        // ── Negocio bodega (usado por tab bodega del producer) ────────────────
        $menu['winery_billing'] = [
            ['icon' => 'chart-bar-square',        'label' => __('Resumen Económico'),   'route' => 'producer.financial-summary.index',       'active' => request()->routeIs('producer.financial-summary*')],
            ['icon' => 'presentation-chart-line', 'label' => __('Estadísticas Bodega'), 'route' => 'producer.financial-stats-winery',        'active' => request()->routeIs('producer.financial-stats-winery')],
            ['divider' => true],
            ['icon' => 'arrow-down-tray',         'label' => __('Compra de Uva'),       'route' => 'producer.invoices.grape-purchase.index', 'active' => request()->routeIs('producer.invoices.grape-purchase*')],
            ['icon' => 'arrow-up-tray',           'label' => __('Venta de Productos'),  'route' => 'producer.invoices.products.index',       'active' => request()->routeIs('producer.invoices.products*')],
            ['icon' => 'calculator',              'label' => __('Costes de Producción'), 'route' => 'producer.production-costs.index',        'active' => request()->routeIs('producer.production-costs*')],
            ['icon' => 'users',                   'label' => __('Clientes Bodega'),     'route' => 'producer.winery-clients.index',          'active' => request()->routeIs('producer.winery-clients*')],
        ];

        // ── Denominación de Origen (solo si tiene supervisor) ────────────────
        $hasSupervisor = $user->hasWinerySupervisor();

        if ($hasSupervisor) {
            $pendingDO = Cache::remember(
                "winery:{$user->id}:pending_do_requests",
                60,
                fn () => SupervisorRequest::forWinery($user->id)
                    ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
                    ->count()
            );

            $menu['denomination'] = [
                ['icon' => 'building-office-2', 'label' => __('Mi Denominación'),  'route' => 'producer.denomination.index',          'active' => request()->routeIs('producer.denomination.index')],
                ['icon' => 'document-text',     'label' => __('Solicitudes DO'),   'route' => 'producer.denomination.requests.index', 'active' => request()->routeIs('producer.denomination.requests*'),
                    'badge' => $pendingDO ?: null],
            ];
        }

        // ── Rail bottom ───────────────────────────────────────────────────────
        $railBottom = [
            ['icon' => 'cog-6-tooth', 'label' => __('Configuración'), 'route' => 'producer.settings', 'active' => request()->routeIs('producer.settings')],
        ];
        if ($user->compra_uva_externa) {
            $railBottom[] = ['icon' => 'megaphone', 'label' => __('Avisos a Viticultores'), 'route' => 'producer.announcements.index', 'active' => request()->routeIs('producer.announcements*')];
        }
        $railBottom[] = ['icon' => 'question-mark-circle', 'label' => __('Soporte'), 'route' => 'producer.support.index', 'active' => request()->routeIs('producer.support.*'),
            'badge' => Cache::remember("nav_badge_support_{$user->id}", 120, fn () => $user->supportTickets()->open()->count())];
        $menu['rail_bottom'] = $railBottom;

        return $menu;
    }
}
