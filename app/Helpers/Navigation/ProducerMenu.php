<?php

namespace App\Helpers\Navigation;

use Illuminate\Support\Facades\Cache;

class ProducerMenu
{
    public static function build($user): array
    {
        $menu = [];

        // ── Sección principal ─────────────────────────────────────────────────
        $menu['main'] = [
            ['icon' => 'squares-2x2',  'label' => 'Vista general',  'route' => 'producer.dashboard',               'active' => request()->routeIs('producer.dashboard')],
            ['icon' => 'calendar-days','label' => 'Calendario',     'route' => 'viticulturist.calendar',            'active' => request()->routeIs('viticulturist.calendar')],
            ['icon' => 'bell',         'label' => 'Notificaciones', 'route' => 'viticulturist.notifications.index', 'active' => request()->routeIs('viticulturist.notifications*'), 'new' => true],
        ];

        // ── Viñedo: Campaña ───────────────────────────────────────────────────
        $menu['operations'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Campañas',              'route' => 'producer.campaign.index',                     'active' => request()->routeIs('producer.campaign*') && !request()->routeIs('producer.campaign-documents.*') && !request()->routeIs('producer.campaign-sign.*')],
            ['icon' => 'folder-open',             'label' => 'Documentos de Campaña', 'route' => 'producer.campaign-documents.index',           'active' => request()->routeIs('producer.campaign-documents.*')],
            ['icon' => 'check-badge',             'label' => 'Firma y Cierre',        'route' => 'producer.campaign-sign.index',                'active' => request()->routeIs('producer.campaign-sign.*')],
            ['divider' => true],
            ['icon' => 'chart-bar-square',        'label' => 'Rendimientos Estimados','route' => 'producer.digital-notebook.estimated-yields.index', 'active' => request()->routeIs('producer.digital-notebook.estimated-yields.*')],
        ];

        // ── Viñedo: Cuaderno de Campo ─────────────────────────────────────────
        $menu['cuaderno_inputs'] = ViticulturistMenu::cuadernoInputs('producer', 'Vendimia Campo');

        // ── Viñedo: Registros Oficiales ───────────────────────────────────────
        $menu['registros_oficiales'] = ViticulturistMenu::registrosOficiales('producer');

        // ── Bodega: Vendimia ──────────────────────────────────────────────────
        $menu['harvest'] = [
            ['icon' => 'chart-bar',               'label' => 'Cuadro de Mando',    'route' => 'producer.harvest-summary.index',    'active' => request()->routeIs('producer.harvest-summary*')],
            ['icon' => 'clipboard-document-list', 'label' => 'Previsiones',        'route' => 'producer.harvest-forecasts.index',  'active' => request()->routeIs('producer.harvest-forecasts*')],
            ['icon' => 'archive-box-arrow-down',  'label' => 'Recepciones',        'route' => 'producer.grape-reception.index',    'active' => request()->routeIs('producer.grape-reception*') && !request()->routeIs('producer.grape-reception.disputes')],
            ['icon' => 'beaker',                  'label' => 'Análisis de Calidad','route' => 'producer.harvest-quality.index',    'active' => request()->routeIs('producer.harvest-quality*')],
            ['divider' => true],
            ['icon' => 'user-group',              'label' => 'Mis Viticultores',   'route' => 'producer.viticulturists.index',     'active' => request()->routeIs('producer.viticulturists*')],
            ['icon' => 'calculator',              'label' => 'Aforos Viticultores','route' => 'producer.vitic-estimates.index',    'active' => request()->routeIs('producer.vitic-estimates*')],
            ['icon' => 'exclamation-triangle',    'label' => 'Disputas',           'route' => 'producer.grape-reception.disputes', 'active' => request()->routeIs('producer.grape-reception.disputes')],
        ];

        // ── Bodega: Elaboración ───────────────────────────────────────────────
        $menu['cellar_elab'] = WineryMenu::cellarElab('producer', operacionesAfterSalas: true);

        // ── Bodega: Salida + Insumos + Alertas ────────────────────────────────
        $menu['cellar_salida'] = WineryMenu::cellarSalida('producer', [
            ['divider' => true],
            ['icon' => 'building-storefront', 'label' => 'Insumos de Bodega', 'route' => 'producer.winery-supplies.index', 'active' => request()->routeIs('producer.winery-supplies*')],
            ['icon' => 'truck',               'label' => 'Proveedores',        'route' => 'producer.suppliers.index',       'active' => request()->routeIs('producer.suppliers*')],
            ['divider' => true],
            ['icon' => 'bell-alert',          'label' => 'Centro de Alertas',  'route' => 'producer.alerts.index',          'active' => request()->routeIs('producer.alerts*'), 'new' => true],
        ]);

        // ── Bodega: Normativa ─────────────────────────────────────────────────
        $menu['winery_normativa'] = WineryMenu::wineryNormativa('producer', silicieWip: true, includeDocumentos: true);

        // ── Parcelas (unión viñedo + bodega) ──────────────────────────────────
        $menu['plots_analysis'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'producer.plots.index',             'active' => request()->routeIs('producer.plots.*') && !request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',            'active' => request()->routeIs('plots.plantings.*') || request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',              'route' => 'sigpac.codes',                     'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-alt',           'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',         'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',                  'active' => request()->routeIs('plots.territory')],
            ['icon' => 'cloud',               'label' => 'Meteorología',        'route' => 'producer.meteorology.index',       'active' => request()->routeIs('producer.meteorology*'), 'new' => true],
            ['icon' => 'viewfinder-circle',   'label' => 'Entorno de Parcelas', 'route' => 'producer.plot-environments.index', 'active' => request()->routeIs('producer.plot-environments.*')],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'producer.field-activities.index',  'active' => request()->routeIs('producer.field-activities*')],
        ];

        // ── Recursos ──────────────────────────────────────────────────────────
        $menu['resources'] = ViticulturistMenu::resources('producer', includeContainers: false);

        // ── Normativa viñedo ──────────────────────────────────────────────────
        $menu['compliance'] = ViticulturistMenu::compliance('producer');

        // ── PAC ───────────────────────────────────────────────────────────────
        $menu['pac'] = ViticulturistMenu::pac('producer');

        // ── Negocio viñedo (usado por tab viñedo del producer) ────────────────
        $menu['billing'] = [
            ['icon' => 'calculator',             'label' => 'Facturas',               'route' => 'producer.invoices.index',          'active' => request()->routeIs('producer.invoices.*') && !request()->routeIs('producer.invoices.products.*') && !request()->routeIs('producer.invoices.grape-purchase.*') && !request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-arrow-up',      'label' => 'Albaranes Mixtos',       'route' => 'producer.invoices.mixed.index',    'active' => request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-check',         'label' => 'VeriFactu',              'route' => 'producer.verifactu.index',         'active' => request()->routeIs('producer.verifactu*'), 'new' => true],
            ['icon' => 'shopping-cart',          'label' => 'Cosecha Comercializada', 'route' => 'producer.marketed-harvests.index', 'active' => request()->routeIs('producer.marketed-harvests.*')],
            ['icon' => 'table-cells',            'label' => 'Costes por Parcela',     'route' => 'producer.plot-costs.index',        'active' => request()->routeIs('producer.plot-costs*'), 'new' => true],
            ['icon' => 'users',                  'label' => 'Clientes',               'route' => 'producer.clients.index',           'active' => request()->routeIs('producer.clients.*')],
            ['icon' => 'presentation-chart-bar', 'label' => 'Estadísticas Viñedo',    'route' => 'producer.financial-stats',         'active' => request()->routeIs('producer.financial-stats')],
        ];

        // ── Negocio bodega (usado por tab bodega del producer) ────────────────
        $menu['winery_billing'] = [
            ['icon' => 'chart-bar-square',        'label' => 'Resumen Económico',   'route' => 'producer.financial-summary.index',       'active' => request()->routeIs('producer.financial-summary*'), 'new' => true],
            ['icon' => 'presentation-chart-line', 'label' => 'Estadísticas Bodega', 'route' => 'producer.financial-stats-winery',        'active' => request()->routeIs('producer.financial-stats-winery'), 'new' => true],
            ['divider' => true],
            ['icon' => 'arrow-down-tray',         'label' => 'Compra de Uva',       'route' => 'producer.invoices.grape-purchase.index', 'active' => request()->routeIs('producer.invoices.grape-purchase*')],
            ['icon' => 'arrow-up-tray',           'label' => 'Venta de Productos',  'route' => 'producer.invoices.products.index',       'active' => request()->routeIs('producer.invoices.products*')],
            ['icon' => 'users',                   'label' => 'Clientes Bodega',     'route' => 'producer.winery-clients.index',          'active' => request()->routeIs('producer.winery-clients*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'globe-alt',               'label' => 'Exportación',         'route' => 'producer.exports.index',                 'active' => request()->routeIs('producer.exports*'), 'wip' => true, 'new' => true],
            ['icon' => 'sparkles',                'label' => 'Enoturismo',          'route' => 'producer.enotourism.index',              'active' => request()->routeIs('producer.enotourism*'), 'wip' => true, 'new' => true],
        ];

        // ── Negocio unificado (usado por capítulo Negocio del rail) ───────────
        $menu['billing_all'] = [
            ['icon' => 'calculator',              'label' => 'Facturas',               'route' => 'producer.invoices.index',               'active' => request()->routeIs('producer.invoices.*') && !request()->routeIs('producer.invoices.products.*') && !request()->routeIs('producer.invoices.grape-purchase.*') && !request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-arrow-up',       'label' => 'Albaranes Mixtos',       'route' => 'producer.invoices.mixed.index',         'active' => request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'arrow-down-tray',         'label' => 'Compra de Uva',          'route' => 'producer.invoices.grape-purchase.index', 'active' => request()->routeIs('producer.invoices.grape-purchase*')],
            ['icon' => 'arrow-up-tray',           'label' => 'Venta de Productos',     'route' => 'producer.invoices.products.index',       'active' => request()->routeIs('producer.invoices.products*')],
            ['icon' => 'document-check',          'label' => 'VeriFactu',              'route' => 'producer.verifactu.index',               'active' => request()->routeIs('producer.verifactu*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'chart-bar-square',        'label' => 'Resumen Económico',      'route' => 'producer.financial-summary.index',       'active' => request()->routeIs('producer.financial-summary*'), 'new' => true],
            ['icon' => 'presentation-chart-bar',  'label' => 'Estadísticas Viñedo',    'route' => 'producer.financial-stats.index',         'active' => request()->routeIs('producer.financial-stats.index')],
            ['icon' => 'presentation-chart-line', 'label' => 'Estadísticas Bodega',    'route' => 'producer.financial-stats-winery',        'active' => request()->routeIs('producer.financial-stats-winery'), 'new' => true],
            ['icon' => 'shopping-cart',           'label' => 'Cosecha Comercializada', 'route' => 'producer.marketed-harvests.index',       'active' => request()->routeIs('producer.marketed-harvests.*')],
            ['icon' => 'table-cells',             'label' => 'Costes por Parcela',     'route' => 'producer.plot-costs.index',              'active' => request()->routeIs('producer.plot-costs*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'users',                   'label' => 'Clientes Viñedo',        'route' => 'producer.clients.index',                 'active' => request()->routeIs('producer.clients.*')],
            ['icon' => 'user-group',              'label' => 'Clientes Bodega',        'route' => 'producer.winery-clients.index',          'active' => request()->routeIs('producer.winery-clients*'), 'new' => true],
            ['icon' => 'globe-alt',               'label' => 'Exportación',            'route' => 'producer.exports.index',                 'active' => request()->routeIs('producer.exports*'), 'wip' => true, 'new' => true],
            ['icon' => 'sparkles',                'label' => 'Enoturismo',             'route' => 'producer.enotourism.index',              'active' => request()->routeIs('producer.enotourism*'), 'wip' => true, 'new' => true],
        ];

        // ── Rail bottom ───────────────────────────────────────────────────────
        $menu['rail_bottom'] = [
            ['icon' => 'cog-6-tooth',         'label' => 'Configuración', 'route' => 'producer.settings',      'active' => request()->routeIs('producer.settings')],
            ['icon' => 'question-mark-circle','label' => 'Soporte',       'route' => 'producer.support.index',  'active' => request()->routeIs('producer.support.*'),
             'badge' => Cache::remember("nav_badge_support_{$user->id}", 120, fn() => $user->supportTickets()->open()->count())],
        ];

        return $menu;
    }
}
