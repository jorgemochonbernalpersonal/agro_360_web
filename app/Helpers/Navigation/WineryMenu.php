<?php

namespace App\Helpers\Navigation;

use App\Models\SupervisorRequest;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Cache;

class WineryMenu
{
    public static function build($user): array
    {
        $menu = [];

        $menu['main'] = [
            ['icon' => 'home', 'label' => __('Dashboard'), 'route' => 'winery.dashboard', 'active' => request()->routeIs('winery.dashboard')],
        ];

        // ── Denominación de Origen (solo si la bodega tiene supervisor asignado) ──
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
                ['icon' => 'building-office-2', 'label' => __('Mi Denominación'),   'route' => 'winery.denomination.index',    'active' => request()->routeIs('winery.denomination.index')],
                ['icon' => 'document-text',     'label' => __('Solicitudes DO'),    'route' => 'winery.denomination.requests.index', 'active' => request()->routeIs('winery.denomination.requests*'),
                    'badge' => $pendingDO ?: null],
                ['icon' => 'tag',                      'label' => __('Etiquetas DO'),      'route' => 'winery.denomination.labels.index',         'active' => request()->routeIs('winery.denomination.labels*')],
                ['icon' => 'clipboard-document-check', 'label' => __('Inspecciones DO'),   'route' => 'winery.denomination.inspections.index',    'active' => request()->routeIs('winery.denomination.inspections*')],
                ['icon' => 'check-badge',              'label' => __('Calificaciones DO'), 'route' => 'winery.denomination.qualifications.index', 'active' => request()->routeIs('winery.denomination.qualifications*')],
            ];
        }

        $hasViticulturists = Cache::remember(
            "winery:{$user->id}:has_viticulturists",
            60,
            fn () => WineryViticulturist::where('winery_id', $user->id)->exists()
        );

        // ── Vendimia ─────────────────────────────────────────────────────
        if ($hasViticulturists) {
            $menu['harvest'] = [
                ['icon' => 'clipboard-document-list', 'label' => __('Campañas de Vendimia'), 'route' => 'winery.campaigns.index',        'active' => request()->routeIs('winery.campaigns*')],
                ['icon' => 'user-group',              'label' => __('Mis Viticultores'),      'route' => 'winery.viticulturists.index',   'active' => request()->routeIs('winery.viticulturists*')],
                ['divider' => true],
                ['icon' => 'chart-bar',               'label' => __('Cuadro de Mando'),       'route' => 'winery.harvest-summary.index',  'active' => request()->routeIs('winery.harvest-summary*')],
                ['icon' => 'calculator',              'label' => __('Aforos viticultores'),   'route' => 'winery.vitic-estimates.index',  'active' => request()->routeIs('winery.vitic-estimates*')],
                ['icon' => 'clipboard-document-list', 'label' => __('Previsiones'),           'route' => 'winery.harvest-forecasts.index', 'active' => request()->routeIs('winery.harvest-forecasts*')],
                ['icon' => 'archive-box-arrow-down',  'label' => __('Recepciones'),           'route' => 'winery.grape-reception.index',  'active' => request()->routeIs('winery.grape-reception*')],
                ['icon' => 'exclamation-triangle',    'label' => __('Disputas'),              'route' => 'winery.grape-reception.disputes', 'active' => request()->routeIs('winery.grape-reception.disputes')],
                ['icon' => 'clipboard-document-check', 'label' => __('Análisis de Calidad'),   'route' => 'winery.harvest-quality.index',  'active' => request()->routeIs('winery.harvest-quality*')],
            ];
        } else {
            $menu['harvest'] = [
                ['icon' => 'clipboard-document-list', 'label' => __('Campañas de Vendimia'), 'route' => 'winery.campaigns.index',        'active' => request()->routeIs('winery.campaigns*')],
                ['icon' => 'user-group',              'label' => __('Mis Viticultores'),      'route' => 'winery.viticulturists.index',   'active' => request()->routeIs('winery.viticulturists*')],
                ['divider' => true],
                ['icon' => 'archive-box-arrow-down',  'label' => __('Recepciones'),           'route' => 'winery.grape-reception.index',  'active' => request()->routeIs('winery.grape-reception*')],
                ['icon' => 'clipboard-document-check', 'label' => __('Análisis de Calidad'),   'route' => 'winery.harvest-quality.index',  'active' => request()->routeIs('winery.harvest-quality*')],
            ];
        }

        // ── Bodega (infraestructura) ─────────────────────────────────────
        $menu['cellar_infra'] = [
            ['icon' => 'cube',                'label' => __('Contenedores'),      'route' => 'winery.containers.index',              'active' => request()->routeIs('winery.containers.index') || request()->routeIs('winery.containers.create') || request()->routeIs('winery.containers.edit') || request()->routeIs('winery.containers.show')],
            ['icon' => 'map',                 'label' => __('Mapa de Bodega'),    'route' => 'winery.containers.map',                'active' => request()->routeIs('winery.containers.map')],
            ['icon' => 'chart-bar-square',    'label' => __('Analítica'),         'route' => 'winery.containers.analytics',          'active' => request()->routeIs('winery.containers.analytics')],
            ['icon' => 'home-modern',         'label' => __('Salas de Bodega'),   'route' => 'winery.container-rooms.index',          'active' => request()->routeIs('winery.container-rooms*')],
            ['icon' => 'wrench-screwdriver',  'label' => __('Mantenimientos'),    'route' => 'winery.container-maintenances.index',   'active' => request()->routeIs('winery.container-maintenances*')],
            ['icon' => 'calendar-days',       'label' => __('Operaciones'),       'route' => 'winery.cellar-operations.index',        'active' => request()->routeIs('winery.cellar-operations*')],
        ];

        // ── Vinos (vinificación) ─────────────────────────────────────────
        $menu['cellar_wines'] = [
            ['icon' => 'beaker',              'label' => __('Vinos'),                 'route' => 'winery.wines.index',                'active' => request()->routeIs('winery.wines.index') || request()->routeIs('winery.wines.create') || request()->routeIs('winery.wines.edit') || request()->routeIs('winery.wines.show')],
            ['icon' => 'queue-list',          'label' => __('Timeline'),              'route' => 'winery.wines.timeline',             'active' => request()->routeIs('winery.wines.timeline')],
            ['divider' => true, 'label' => __('Operaciones')],
            ['icon' => 'fire',                'label' => __('Controles Fermentación'), 'route' => 'winery.fermentation-controls.index', 'active' => request()->routeIs('winery.fermentation-controls*')],
            ['icon' => 'arrow-path',          'label' => __('Traslados'),             'route' => 'winery.wine-transfers.index',       'active' => request()->routeIs('winery.wine-transfers*')],
            ['icon' => 'funnel',              'label' => __('Coupage'),               'route' => 'winery.coupage.index',              'active' => request()->routeIs('winery.coupage*')],
            ['icon' => 'exclamation-circle',  'label' => __('Mermas'),                'route' => 'winery.wine-losses.index',          'active' => request()->routeIs('winery.wine-losses*')],
            ['icon' => 'cube-transparent', 'label' => __('Aditivos'),              'route' => 'winery.wine-additives.index',       'active' => request()->routeIs('winery.wine-additives*')],
            ['divider' => true, 'label' => __('Laboratorio')],
            ['icon' => 'magnifying-glass',    'label' => __('Análisis de Lab.'),      'route' => 'winery.wine-analysis.index',        'active' => request()->routeIs('winery.wine-analysis*')],
            ['icon' => 'user-circle',         'label' => __('Enólogos'),              'route' => 'winery.oenologists.index',          'active' => request()->routeIs('winery.oenologists*')],
            ['icon' => 'archive-box',         'label' => __('Uva / Mosto externo'),  'route' => 'winery.external-grape.index',       'active' => request()->routeIs('winery.external-grape*')],
        ];

        // ── Producto (salida) ────────────────────────────────────────────
        $menu['cellar_output'] = self::cellarOutput('winery');

        // ── Parcelas y Análisis ──────────────────────────────────────────
        $menu['territory'] = [
            ['icon' => 'map',                   'label' => __('Parcelas'),            'route' => 'winery.plots.index',             'active' => request()->routeIs('winery.plots*') && ! request()->routeIs('plots.plantings.*')],
            ['icon' => 'book-open',             'label' => __('Plantaciones'),        'route' => 'plots.plantings.index',          'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'map-pin',               'label' => __('SIGPAC'),              'route' => 'sigpac.codes',                   'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-europe-africa',   'label' => __('Gestión Territorial'), 'route' => 'plots.territory',                'active' => request()->routeIs('plots.territory')],
            ['icon' => 'pencil-square',         'label' => __('Actividades de Campo'), 'route' => 'winery.field-activities.index',  'active' => request()->routeIs('winery.field-activities*')],
        ];

        $menu['analytics'] = [
            ['icon' => 'globe-alt',           'label' => __('Teledetección'),       'route' => 'remote-sensing.dashboard',        'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'cloud',               'label' => __('Meteorología'),        'route' => 'winery.meteorology.index',        'active' => request()->routeIs('winery.meteorology*')],
            ['icon' => 'viewfinder-circle',   'label' => __('Entorno de Parcelas'), 'route' => 'winery.plot-environments.index',  'active' => request()->routeIs('winery.plot-environments*')],
        ];

        // ── Normativa ────────────────────────────────────────────────────
        $menu['winery_compliance'] = self::wineryCompliance('winery');

        // ── Negocio ──────────────────────────────────────────────────────
        $menu['billing'] = [
            ['icon' => 'chart-bar-square',        'label' => __('Resumen Económico'),       'route' => 'winery.financial-summary.index',      'active' => request()->routeIs('winery.financial-summary*')],
            ['icon' => 'presentation-chart-bar',  'label' => __('Estadísticas Financieras'), 'route' => 'winery.financial-stats.index',         'active' => request()->routeIs('winery.financial-stats*')],
            ['divider' => true],
            ['icon' => 'arrow-down-tray',         'label' => __('Compra de Uva'),           'route' => 'winery.invoices.grape-purchase.index', 'active' => request()->routeIs('winery.invoices.grape-purchase*')],
            ['icon' => 'arrow-up-tray',           'label' => __('Venta de Productos'),      'route' => 'winery.invoices.products.index',       'active' => request()->routeIs('winery.invoices.products*')],
            ['icon' => 'document-check',          'label' => __('VeriFactu'),               'route' => 'winery.verifactu.index',               'active' => request()->routeIs('winery.verifactu*')],
            ['icon' => 'calculator',              'label' => __('Costes de Producción'),    'route' => 'winery.production-costs.index',        'active' => request()->routeIs('winery.production-costs*')],
            ['icon' => 'users',                   'label' => __('Clientes y Canales'),      'route' => 'winery.clients.index',                 'active' => request()->routeIs('winery.clients*')],
        ];

        // ── Insumos y Proveedores ────────────────────────────────────────
        $menu['winery_resources'] = [
            ['icon' => 'building-storefront', 'label' => __('Insumos de Bodega'), 'route' => 'winery.winery-supplies.index', 'active' => request()->routeIs('winery.winery-supplies*')],
            ['icon' => 'truck',               'label' => __('Proveedores'),        'route' => 'winery.suppliers.index',       'active' => request()->routeIs('winery.suppliers*')],
        ];

        $menu['winery_docs'] = [
            ['icon' => 'folder-open', 'label' => __('Documentos Bodega'), 'route' => 'winery.documents.index', 'active' => request()->routeIs('winery.documents*')],
        ];

        // ── Sistema ──────────────────────────────────────────────────────
        $menu['system'] = [
            ['icon' => 'cog-6-tooth',   'label' => __('Configuración'),         'route' => 'winery.settings',            'active' => request()->routeIs('winery.settings')],
            ['icon' => 'megaphone',     'label' => __('Avisos a Viticultores'), 'route' => 'winery.announcements.index', 'active' => request()->routeIs('winery.announcements*')],
            ['icon' => 'bell-alert',    'label' => __('Centro de Alertas'),     'route' => 'winery.alerts.index',        'active' => request()->routeIs('winery.alerts*')],
        ];

        return $menu;
    }

    // ── Secciones compartidas (usadas también por ProducerMenu) ───────────────

    /**
     * @param bool $operacionesAfterSalas true = Operaciones de Bodega aparece justo después
     *                                    de Salas (orden Producer); false = al final (orden Winery)
     */
    public static function cellarElaboration(string $prefix, bool $operacionesAfterSalas = false): array
    {
        $operaciones = ['icon' => 'calendar-days', 'label' => __('Operaciones de Bodega'), 'route' => "{$prefix}.cellar-operations.index", 'active' => request()->routeIs("{$prefix}.cellar-operations*")];

        $top = [
            ['icon' => 'cube',              'label' => __('Contenedores'),      'route' => "{$prefix}.containers.index",              'active' => request()->routeIs("{$prefix}.containers.index") || request()->routeIs("{$prefix}.containers.create") || request()->routeIs("{$prefix}.containers.edit") || request()->routeIs("{$prefix}.containers.show")],
            ['icon' => 'map',               'label' => __('Mapa de Bodega'),    'route' => "{$prefix}.containers.map",                'active' => request()->routeIs("{$prefix}.containers.map")],
            ['icon' => 'chart-bar-square',  'label' => __('Analítica'),         'route' => "{$prefix}.containers.analytics",          'active' => request()->routeIs("{$prefix}.containers.analytics")],
            ['icon' => 'home-modern',       'label' => __('Salas de Bodega'),   'route' => "{$prefix}.container-rooms.index",          'active' => request()->routeIs("{$prefix}.container-rooms*")],
            ['icon' => 'wrench-screwdriver', 'label' => __('Mantenimientos'),    'route' => "{$prefix}.container-maintenances.index",   'active' => request()->routeIs("{$prefix}.container-maintenances*")],
        ];

        if ($operacionesAfterSalas) {
            $top[] = $operaciones;
        }

        $bottom = [
            ['divider' => true, 'label' => __('Vinificación')],
            ['icon' => 'beaker',              'label' => __('Vinos'),                 'route' => "{$prefix}.wines.index",                   'active' => request()->routeIs("{$prefix}.wines.index") || request()->routeIs("{$prefix}.wines.create") || request()->routeIs("{$prefix}.wines.edit") || request()->routeIs("{$prefix}.wines.show")],
            ['icon' => 'queue-list',          'label' => __('Timeline de Vinos'),     'route' => "{$prefix}.wines.timeline",                'active' => request()->routeIs("{$prefix}.wines.timeline")],
            ['divider' => true, 'label' => __('Operaciones')],
            ['icon' => 'fire',                'label' => __('Controles Fermentación'), 'route' => "{$prefix}.fermentation-controls.index",   'active' => request()->routeIs("{$prefix}.fermentation-controls*")],
            ['icon' => 'arrow-path',          'label' => __('Traslados'),             'route' => "{$prefix}.wine-transfers.index",          'active' => request()->routeIs("{$prefix}.wine-transfers*")],
            ['icon' => 'funnel',              'label' => __('Coupage'),               'route' => "{$prefix}.coupage.index",                 'active' => request()->routeIs("{$prefix}.coupage*")],
            ['icon' => 'exclamation-circle',  'label' => __('Mermas'),                'route' => "{$prefix}.wine-losses.index",             'active' => request()->routeIs("{$prefix}.wine-losses*")],
            ['icon' => 'cube-transparent', 'label' => __('Aditivos'),              'route' => "{$prefix}.wine-additives.index",          'active' => request()->routeIs("{$prefix}.wine-additives*")],
            ['divider' => true, 'label' => __('Laboratorio')],
            ['icon' => 'magnifying-glass',    'label' => __('Análisis de Lab.'),      'route' => "{$prefix}.wine-analysis.index",           'active' => request()->routeIs("{$prefix}.wine-analysis*")],
            ['icon' => 'user-circle',         'label' => __('Enólogos'),              'route' => "{$prefix}.oenologists.index",             'active' => request()->routeIs("{$prefix}.oenologists*")],
            ['icon' => 'archive-box',         'label' => __('Uva / Mosto externo'),  'route' => "{$prefix}.external-grape.index",          'active' => request()->routeIs("{$prefix}.external-grape*")],
        ];

        if (! $operacionesAfterSalas) {
            $bottom[] = $operaciones;
        }

        return array_merge($top, $bottom);
    }

    /**
     * @param array $extraItems Items adicionales a añadir al final (con divider incluido si se necesita)
     */
    public static function cellarOutput(string $prefix, array $extraItems = []): array
    {
        $items = [
            ['icon' => 'archive-box',             'label' => __('Productos'),               'route' => "{$prefix}.product-lots.index",      'active' => request()->routeIs("{$prefix}.product-lots.index") || request()->routeIs("{$prefix}.product-lots.create") || request()->routeIs("{$prefix}.product-lots.edit") || request()->routeIs("{$prefix}.product-lots.show")],
            ['icon' => 'presentation-chart-bar',  'label' => __('Insights de Lotes'),   'route' => "{$prefix}.product-lots.insights", 'active' => request()->routeIs("{$prefix}.product-lots.insights")],
            ['icon' => 'shield-check',            'label' => __('Auditoría de Stock'),      'route' => "{$prefix}.product-lots.audit",    'active' => request()->routeIs("{$prefix}.product-lots.audit")],
            ['icon' => 'magnifying-glass-circle', 'label' => __('Trazabilidad'),            'route' => "{$prefix}.traceability.index",    'active' => request()->routeIs("{$prefix}.traceability*")],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down',  'label' => __('Embotellado y Etiquetado'), 'route' => "{$prefix}.bottling.index",        'active' => request()->routeIs("{$prefix}.bottling*") || request()->routeIs("{$prefix}.label-batches*") || request()->routeIs("{$prefix}.labeling*")],
            ['icon' => 'document-text',           'label' => __('Fichas Técnicas y Catas'), 'route' => "{$prefix}.product-sheets.index", 'active' => request()->routeIs("{$prefix}.product-sheets*") || request()->routeIs("{$prefix}.tasting-notes*")],
            ['icon' => 'archive-box-x-mark',      'label' => __('Subproductos'),            'route' => "{$prefix}.subproducts.index",    'active' => request()->routeIs("{$prefix}.subproducts*")],
        ];

        return array_merge($items, $extraItems);
    }

    /**
     * @param bool $silicieWip        true = SILICIE e INFOVI marcados como wip (Producer aún no los tiene activos)
     * @param bool $includeDocumentos true = añade "Documentos Bodega" al final (Producer lo tiene en esta sección)
     */
    public static function wineryCompliance(string $prefix, bool $silicieWip = false, bool $includeDocumentos = false): array
    {
        $items = [
            ['icon' => 'document-chart-bar', 'label' => __('SILICIE'),                       'route' => "{$prefix}.silicie.dashboard",              'active' => request()->routeIs("{$prefix}.silicie.dashboard") || request()->routeIs("{$prefix}.silicie.movements*"), 'wip' => $silicieWip],
            ['icon' => 'chart-bar',          'label' => __('INFOVI (AICA)'),                 'route' => "{$prefix}.silicie.infovi",                 'active' => request()->routeIs("{$prefix}.silicie.infovi"), 'wip' => $silicieWip],
            ['divider' => true],
            ['icon' => 'shield-check',       'label' => __('Registros Sanitarios'),          'route' => "{$prefix}.sanitary-registrations.index",  'active' => request()->routeIs("{$prefix}.sanitary-registrations*")],
            ['icon' => 'identification',     'label' => __('Autorizaciones de Embotellado'), 'route' => "{$prefix}.bottling-authorizations.index", 'active' => request()->routeIs("{$prefix}.bottling-authorizations*")],
            ['icon' => 'sparkles',           'label' => __('Certificaciones Ecológicas'),    'route' => "{$prefix}.eco-certifications.index",      'active' => request()->routeIs("{$prefix}.eco-certifications*")],
        ];

        if ($includeDocumentos) {
            $items[] = ['divider' => true];
            $items[] = ['icon' => 'folder-open', 'label' => __('Documentos Bodega'), 'route' => "{$prefix}.documents.index", 'active' => request()->routeIs("{$prefix}.documents*")];
        }

        return $items;
    }
}
