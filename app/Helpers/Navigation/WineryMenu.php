<?php

namespace App\Helpers\Navigation;

use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Cache;

class WineryMenu
{
    public static function build($user): array
    {
        $menu = [];

        $menu['main'] = [
            ['icon' => 'home', 'label' => 'Dashboard', 'route' => 'winery.dashboard', 'active' => request()->routeIs('winery.dashboard')],
        ];

        // ── Denominación de Origen (solo si la bodega tiene supervisor asignado) ──
        $hasSupervisor = Cache::remember(
            "winery:{$user->id}:has_supervisor",
            300,
            fn () => SupervisorWinery::where('winery_id', $user->id)->exists()
        );

        if ($hasSupervisor) {
            $pendingDO = Cache::remember(
                "winery:{$user->id}:pending_do_requests",
                60,
                fn () => SupervisorRequest::forWinery($user->id)
                    ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
                    ->count()
            );

            $menu['denomination'] = [
                ['icon' => 'building-office-2', 'label' => 'Mi Denominación',   'route' => 'winery.denomination.index',    'active' => request()->routeIs('winery.denomination.index')],
                ['icon' => 'document-text',     'label' => 'Solicitudes DO',    'route' => 'winery.denomination.requests.index', 'active' => request()->routeIs('winery.denomination.requests*'),
                    'badge' => $pendingDO ?: null],
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
                ['icon' => 'clipboard-document-list', 'label' => 'Campañas de Vendimia', 'route' => 'winery.campaigns.index',        'active' => request()->routeIs('winery.campaigns*')],
                ['icon' => 'user-group',              'label' => 'Mis Viticultores',      'route' => 'winery.viticulturists.index',   'active' => request()->routeIs('winery.viticulturists*')],
                ['divider' => true],
                ['icon' => 'chart-bar',               'label' => 'Cuadro de Mando',       'route' => 'winery.harvest-summary.index',  'active' => request()->routeIs('winery.harvest-summary*')],
                ['icon' => 'eye',                     'label' => 'Panel Visual',          'route' => 'winery.visual',                 'active' => request()->routeIs('winery.visual')],
                ['icon' => 'calculator',              'label' => 'Aforos viticultores',   'route' => 'winery.vitic-estimates.index',  'active' => request()->routeIs('winery.vitic-estimates*')],
                ['icon' => 'clipboard-document-list', 'label' => 'Previsiones',           'route' => 'winery.harvest-forecasts.index','active' => request()->routeIs('winery.harvest-forecasts*')],
                ['icon' => 'archive-box-arrow-down',  'label' => 'Recepciones',           'route' => 'winery.grape-reception.index',  'active' => request()->routeIs('winery.grape-reception*')],
                ['icon' => 'exclamation-triangle',    'label' => 'Disputas',              'route' => 'winery.grape-reception.disputes','active' => request()->routeIs('winery.grape-reception.disputes')],
                ['icon' => 'beaker',                  'label' => 'Análisis de Calidad',   'route' => 'winery.harvest-quality.index',  'active' => request()->routeIs('winery.harvest-quality*')],
            ];
        } else {
            $menu['harvest'] = [
                ['icon' => 'clipboard-document-list', 'label' => 'Campañas de Vendimia', 'route' => 'winery.campaigns.index',        'active' => request()->routeIs('winery.campaigns*')],
                ['icon' => 'user-group',              'label' => 'Mis Viticultores',      'route' => 'winery.viticulturists.index',   'active' => request()->routeIs('winery.viticulturists*')],
                ['divider' => true],
                ['icon' => 'archive-box-arrow-down',  'label' => 'Recepciones',           'route' => 'winery.grape-reception.index',  'active' => request()->routeIs('winery.grape-reception*')],
                ['icon' => 'beaker',                  'label' => 'Análisis de Calidad',   'route' => 'winery.harvest-quality.index',  'active' => request()->routeIs('winery.harvest-quality*')],
                ['icon' => 'eye',                     'label' => 'Panel Visual',          'route' => 'winery.visual',                 'active' => request()->routeIs('winery.visual')],
            ];
        }

        // ── Bodega (infraestructura) ─────────────────────────────────────
        $menu['cellar_infra'] = [
            ['icon' => 'cube',                'label' => 'Contenedores',      'route' => 'winery.containers.index',              'active' => request()->routeIs('winery.containers.index') || request()->routeIs('winery.containers.create') || request()->routeIs('winery.containers.edit') || request()->routeIs('winery.containers.show')],
            ['icon' => 'map',                 'label' => 'Mapa de Bodega',    'route' => 'winery.containers.map',                'active' => request()->routeIs('winery.containers.map')],
            ['icon' => 'chart-bar-square',    'label' => 'Analítica',         'route' => 'winery.containers.analytics',          'active' => request()->routeIs('winery.containers.analytics')],
            ['icon' => 'home-modern',         'label' => 'Salas de Bodega',   'route' => 'winery.container-rooms.index',          'active' => request()->routeIs('winery.container-rooms*')],
            ['icon' => 'wrench-screwdriver',  'label' => 'Mantenimientos',    'route' => 'winery.container-maintenances.index',   'active' => request()->routeIs('winery.container-maintenances*')],
            ['icon' => 'calendar-days',       'label' => 'Operaciones',       'route' => 'winery.cellar-operations.index',        'active' => request()->routeIs('winery.cellar-operations*')],
        ];

        // ── Vinos (vinificación) ─────────────────────────────────────────
        $menu['cellar_wines'] = [
            ['icon' => 'beaker',              'label' => 'Vinos',                 'route' => 'winery.wines.index',                'active' => request()->routeIs('winery.wines.index') || request()->routeIs('winery.wines.create') || request()->routeIs('winery.wines.edit') || request()->routeIs('winery.wines.show')],
            ['icon' => 'queue-list',          'label' => 'Timeline',              'route' => 'winery.wines.timeline',             'active' => request()->routeIs('winery.wines.timeline')],
            ['divider' => true, 'label' => 'Operaciones'],
            ['icon' => 'fire',                'label' => 'Controles Fermentación','route' => 'winery.fermentation-controls.index','active' => request()->routeIs('winery.fermentation-controls*')],
            ['icon' => 'arrow-path',          'label' => 'Traslados',             'route' => 'winery.wine-transfers.index',       'active' => request()->routeIs('winery.wine-transfers*')],
            ['icon' => 'exclamation-circle',  'label' => 'Mermas',                'route' => 'winery.wine-losses.index',          'active' => request()->routeIs('winery.wine-losses*')],
            ['icon' => 'eyedropper',          'label' => 'Aditivos',              'route' => 'winery.wine-additives.index',       'active' => request()->routeIs('winery.wine-additives*')],
            ['divider' => true, 'label' => 'Laboratorio'],
            ['icon' => 'magnifying-glass',    'label' => 'Análisis de Lab.',      'route' => 'winery.wine-analysis.index',        'active' => request()->routeIs('winery.wine-analysis*')],
            ['icon' => 'user-circle',         'label' => 'Enólogos',              'route' => 'winery.oenologists.index',          'active' => request()->routeIs('winery.oenologists*')],
            ['icon' => 'archive-box',         'label' => 'Uva / Mosto externo',  'route' => 'winery.external-grape.index',       'active' => request()->routeIs('winery.external-grape*')],
        ];

        // ── Producto (salida) ────────────────────────────────────────────
        $menu['cellar_output'] = self::cellarOutput('winery');

        // ── Parcelas y Análisis ──────────────────────────────────────────
        $menu['territory'] = [
            ['icon' => 'map',                   'label' => 'Parcelas',            'route' => 'winery.plots.index',             'active' => request()->routeIs('winery.plots*') && !request()->routeIs('plots.plantings.*')],
            ['icon' => 'book-open',             'label' => 'Plantaciones',        'route' => 'plots.plantings.index',          'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'map-pin',               'label' => 'SIGPAC',              'route' => 'sigpac.codes',                   'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-europe-africa',   'label' => 'Gestión Territorial', 'route' => 'plots.territory',                'active' => request()->routeIs('plots.territory')],
            ['icon' => 'pencil-square',         'label' => 'Actividades de Campo','route' => 'winery.field-activities.index',  'active' => request()->routeIs('winery.field-activities*')],
        ];

        $menu['analytics'] = [
            ['icon' => 'globe-alt',           'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',        'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'cloud',               'label' => 'Meteorología',        'route' => 'winery.meteorology.index',        'active' => request()->routeIs('winery.meteorology*')],
            ['icon' => 'viewfinder-circle',   'label' => 'Entorno de Parcelas', 'route' => 'winery.plot-environments.index',  'active' => request()->routeIs('winery.plot-environments*')],
        ];

        // ── Normativa ────────────────────────────────────────────────────
        $menu['winery_compliance'] = self::wineryCompliance('winery');

        $menu['registrations'] = [
            ['icon' => 'building-office',   'label' => 'Explotación RGSEAA',       'route' => 'winery.exploitations.index',             'active' => request()->routeIs('winery.exploitations.*')],
            ['icon' => 'shield-check',      'label' => 'Autorizaciones Comerciales','route' => 'winery.commercial-authorizations.index', 'active' => request()->routeIs('winery.commercial-authorizations.*')],
            ['icon' => 'identification',    'label' => 'Aplicadores ROPO',          'route' => 'winery.field-applicators.index',         'active' => request()->routeIs('winery.field-applicators.*')],
            ['icon' => 'cog-8-tooth',       'label' => 'Equipos ITB/ITEA',          'route' => 'winery.field-equipment.index',           'active' => request()->routeIs('winery.field-equipment.*')],
        ];

        // ── Negocio ──────────────────────────────────────────────────────
        $menu['billing'] = [
            ['icon' => 'chart-bar-square',        'label' => 'Resumen Económico',       'route' => 'winery.financial-summary.index',      'active' => request()->routeIs('winery.financial-summary*')],
            ['icon' => 'presentation-chart-bar',  'label' => 'Estadísticas Financieras','route' => 'winery.financial-stats.index',         'active' => request()->routeIs('winery.financial-stats*')],
            ['divider' => true],
            ['icon' => 'arrow-down-tray',         'label' => 'Compra de Uva',           'route' => 'winery.invoices.grape-purchase.index', 'active' => request()->routeIs('winery.invoices.grape-purchase*')],
            ['icon' => 'arrow-up-tray',           'label' => 'Venta de Productos',      'route' => 'winery.invoices.products.index',       'active' => request()->routeIs('winery.invoices.products*')],
            ['icon' => 'document-check',          'label' => 'VeriFactu',               'route' => 'winery.verifactu.index',               'active' => request()->routeIs('winery.verifactu*')],
            ['icon' => 'users',                   'label' => 'Clientes y Canales',      'route' => 'winery.clients.index',                 'active' => request()->routeIs('winery.clients*')],
            ['icon' => 'globe-alt',               'label' => 'Exportación',             'route' => 'winery.exports.index',                 'active' => request()->routeIs('winery.exports*'), 'wip' => true],
            ['icon' => 'sparkles',                'label' => 'Enoturismo',              'route' => 'winery.enotourism.index',              'active' => request()->routeIs('winery.enotourism*'), 'wip' => true],
        ];

        // ── Insumos y Proveedores ────────────────────────────────────────
        $menu['winery_resources'] = [
            ['icon' => 'building-storefront', 'label' => 'Insumos de Bodega', 'route' => 'winery.winery-supplies.index', 'active' => request()->routeIs('winery.winery-supplies*')],
            ['icon' => 'truck',               'label' => 'Proveedores',        'route' => 'winery.suppliers.index',       'active' => request()->routeIs('winery.suppliers*')],
        ];

        $menu['winery_docs'] = [
            ['icon' => 'folder-open', 'label' => 'Documentos Bodega', 'route' => 'winery.documents.index', 'active' => request()->routeIs('winery.documents*')],
        ];

        // ── Sistema ──────────────────────────────────────────────────────
        $menu['system'] = [
            ['icon' => 'cog-6-tooth',   'label' => 'Configuración',         'route' => 'winery.settings',            'active' => request()->routeIs('winery.settings')],
            ['icon' => 'megaphone',     'label' => 'Avisos a Viticultores', 'route' => 'winery.announcements.index', 'active' => request()->routeIs('winery.announcements*')],
            ['icon' => 'bell-alert',    'label' => 'Centro de Alertas',     'route' => 'winery.alerts.index',        'active' => request()->routeIs('winery.alerts*')],
        ];

        return $menu;
    }

    // ── Secciones compartidas (usadas también por ProducerMenu) ───────────────

    /**
     * @param bool $operacionesAfterSalas  true = Operaciones de Bodega aparece justo después
     *                                     de Salas (orden Producer); false = al final (orden Winery)
     */
    public static function cellarElaboration(string $prefix, bool $operacionesAfterSalas = false): array
    {
        $operaciones = ['icon' => 'calendar-days', 'label' => 'Operaciones de Bodega', 'route' => "{$prefix}.cellar-operations.index", 'active' => request()->routeIs("{$prefix}.cellar-operations*")];

        $top = [
            ['icon' => 'cube',              'label' => 'Contenedores',      'route' => "{$prefix}.containers.index",              'active' => request()->routeIs("{$prefix}.containers.index") || request()->routeIs("{$prefix}.containers.create") || request()->routeIs("{$prefix}.containers.edit") || request()->routeIs("{$prefix}.containers.show")],
            ['icon' => 'map',               'label' => 'Mapa de Bodega',    'route' => "{$prefix}.containers.map",                'active' => request()->routeIs("{$prefix}.containers.map")],
            ['icon' => 'chart-bar-square',  'label' => 'Analítica',         'route' => "{$prefix}.containers.analytics",          'active' => request()->routeIs("{$prefix}.containers.analytics")],
            ['icon' => 'home-modern',       'label' => 'Salas de Bodega',   'route' => "{$prefix}.container-rooms.index",          'active' => request()->routeIs("{$prefix}.container-rooms*")],
            ['icon' => 'wrench-screwdriver','label' => 'Mantenimientos',    'route' => "{$prefix}.container-maintenances.index",   'active' => request()->routeIs("{$prefix}.container-maintenances*")],
        ];

        if ($operacionesAfterSalas) {
            $top[] = $operaciones;
        }

        $bottom = [
            ['divider' => true, 'label' => 'Vinificación'],
            ['icon' => 'beaker',              'label' => 'Vinos',                 'route' => "{$prefix}.wines.index",                   'active' => request()->routeIs("{$prefix}.wines.index") || request()->routeIs("{$prefix}.wines.create") || request()->routeIs("{$prefix}.wines.edit") || request()->routeIs("{$prefix}.wines.show")],
            ['icon' => 'queue-list',          'label' => 'Timeline de Vinos',     'route' => "{$prefix}.wines.timeline",                'active' => request()->routeIs("{$prefix}.wines.timeline")],
            ['divider' => true, 'label' => 'Operaciones'],
            ['icon' => 'fire',                'label' => 'Controles Fermentación','route' => "{$prefix}.fermentation-controls.index",   'active' => request()->routeIs("{$prefix}.fermentation-controls*")],
            ['icon' => 'arrow-path',          'label' => 'Traslados',             'route' => "{$prefix}.wine-transfers.index",          'active' => request()->routeIs("{$prefix}.wine-transfers*")],
            ['icon' => 'exclamation-circle',  'label' => 'Mermas',                'route' => "{$prefix}.wine-losses.index",             'active' => request()->routeIs("{$prefix}.wine-losses*")],
            ['icon' => 'eyedropper',          'label' => 'Aditivos',              'route' => "{$prefix}.wine-additives.index",          'active' => request()->routeIs("{$prefix}.wine-additives*")],
            ['divider' => true, 'label' => 'Laboratorio'],
            ['icon' => 'magnifying-glass',    'label' => 'Análisis de Lab.',      'route' => "{$prefix}.wine-analysis.index",           'active' => request()->routeIs("{$prefix}.wine-analysis*")],
            ['icon' => 'user-circle',         'label' => 'Enólogos',              'route' => "{$prefix}.oenologists.index",             'active' => request()->routeIs("{$prefix}.oenologists*")],
            ['icon' => 'archive-box',         'label' => 'Uva / Mosto externo',  'route' => "{$prefix}.external-grape.index",          'active' => request()->routeIs("{$prefix}.external-grape*")],
        ];

        if (!$operacionesAfterSalas) {
            $bottom[] = $operaciones;
        }

        return array_merge($top, $bottom);
    }

    /**
     * @param array $extraItems  Items adicionales a añadir al final (con divider incluido si se necesita)
     */
    public static function cellarOutput(string $prefix, array $extraItems = []): array
    {
        $items = [
            ['icon' => 'archive-box',             'label' => 'Productos',               'route' => "{$prefix}.product-lots.index",      'active' => request()->routeIs("{$prefix}.product-lots.index") || request()->routeIs("{$prefix}.product-lots.create") || request()->routeIs("{$prefix}.product-lots.edit") || request()->routeIs("{$prefix}.product-lots.show")],
            ['icon' => 'presentation-chart-bar',  'label' => 'Insights de Lotes',   'route' => "{$prefix}.product-lots.insights", 'active' => request()->routeIs("{$prefix}.product-lots.insights")],
            ['icon' => 'shield-check',            'label' => 'Auditoría de Stock',      'route' => "{$prefix}.product-lots.audit",    'active' => request()->routeIs("{$prefix}.product-lots.audit")],
            ['icon' => 'magnifying-glass-circle', 'label' => 'Trazabilidad',            'route' => "{$prefix}.traceability.index",    'active' => request()->routeIs("{$prefix}.traceability*")],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down',  'label' => 'Embotellado y Etiquetado','route' => "{$prefix}.bottling.index",        'active' => request()->routeIs("{$prefix}.bottling*") || request()->routeIs("{$prefix}.label-batches*") || request()->routeIs("{$prefix}.labeling*")],
            ['icon' => 'document-text',           'label' => 'Fichas Técnicas y Catas', 'route' => "{$prefix}.product-sheets.index", 'active' => request()->routeIs("{$prefix}.product-sheets*") || request()->routeIs("{$prefix}.tasting-notes*")],
            ['icon' => 'archive-box-x-mark',      'label' => 'Subproductos',            'route' => "{$prefix}.subproducts.index",    'active' => request()->routeIs("{$prefix}.subproducts*")],
        ];

        return array_merge($items, $extraItems);
    }

    /**
     * @param bool $silicieWip       true = SILICIE e INFOVI marcados como wip (Producer aún no los tiene activos)
     * @param bool $includeDocumentos true = añade "Documentos Bodega" al final (Producer lo tiene en esta sección)
     */
    public static function wineryCompliance(string $prefix, bool $silicieWip = false, bool $includeDocumentos = false): array
    {
        $items = [
            ['icon' => 'document-chart-bar', 'label' => 'SILICIE',                       'route' => "{$prefix}.silicie.dashboard",              'active' => request()->routeIs("{$prefix}.silicie.dashboard") || request()->routeIs("{$prefix}.silicie.movements*"), 'wip' => $silicieWip],
            ['icon' => 'chart-bar',          'label' => 'INFOVI (AICA)',                 'route' => "{$prefix}.silicie.infovi",                 'active' => request()->routeIs("{$prefix}.silicie.infovi"), 'wip' => $silicieWip],
            ['divider' => true],
            ['icon' => 'document-text',      'label' => 'AICA',                          'route' => "{$prefix}.aica.index",                    'active' => request()->routeIs("{$prefix}.aica*"), 'wip' => true],
            ['icon' => 'shield-check',       'label' => 'Registros Sanitarios',          'route' => "{$prefix}.sanitary-registrations.index",  'active' => request()->routeIs("{$prefix}.sanitary-registrations*")],
            ['icon' => 'identification',     'label' => 'Autorizaciones de Embotellado', 'route' => "{$prefix}.bottling-authorizations.index", 'active' => request()->routeIs("{$prefix}.bottling-authorizations*")],
            ['icon' => 'sparkles',           'label' => 'Certificaciones Ecológicas',    'route' => "{$prefix}.eco-certifications.index",      'active' => request()->routeIs("{$prefix}.eco-certifications*")],
        ];

        if ($includeDocumentos) {
            $items[] = ['divider' => true];
            $items[] = ['icon' => 'folder-open', 'label' => 'Documentos Bodega', 'route' => "{$prefix}.documents.index", 'active' => request()->routeIs("{$prefix}.documents*")];
        }

        return $items;
    }
}
