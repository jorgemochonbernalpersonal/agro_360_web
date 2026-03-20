<?php

namespace App\Helpers\Navigation;

use Illuminate\Support\Facades\Cache;

class WineryMenu
{
    public static function build($user): array
    {
        $menu = [];

        $menu['main'] = [
            [
                'icon'   => 'home',
                'label'  => 'Dashboard',
                'route'  => 'winery.dashboard',
                'active' => request()->routeIs('winery.dashboard'),
            ],
        ];

        $menu['harvest'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Campañas de Vendimia', 'route' => 'winery.campaigns.index',        'active' => request()->routeIs('winery.campaigns*')],
            ['icon' => 'user-group',              'label' => 'Mis Viticultores',      'route' => 'winery.viticulturists.index',   'active' => request()->routeIs('winery.viticulturists*')],
            ['divider' => true],
            ['icon' => 'chart-bar',               'label' => 'Cuadro de Mando',       'route' => 'winery.harvest-summary.index',  'active' => request()->routeIs('winery.harvest-summary*')],
            ['icon' => 'calculator',              'label' => 'Aforos viticultores',   'route' => 'winery.vitic-estimates.index',  'active' => request()->routeIs('winery.vitic-estimates*')],
            ['icon' => 'clipboard-document-list', 'label' => 'Previsiones',           'route' => 'winery.harvest-forecasts.index','active' => request()->routeIs('winery.harvest-forecasts*')],
            ['icon' => 'archive-box-arrow-down',  'label' => 'Recepciones',           'route' => 'winery.grape-reception.index',  'active' => request()->routeIs('winery.grape-reception*')],
            ['icon' => 'exclamation-triangle',    'label' => 'Disputas',              'route' => 'winery.grape-reception.disputes','active' => request()->routeIs('winery.grape-reception.disputes')],
            ['icon' => 'beaker',                  'label' => 'Análisis de Calidad',   'route' => 'winery.harvest-quality.index',  'active' => request()->routeIs('winery.harvest-quality*')],
        ];

        $menu['cellar_elab'] = [
            ['icon' => 'beaker',            'label' => 'Contenedores',          'route' => 'winery.containers.index',       'active' => request()->routeIs('winery.containers.index') || request()->routeIs('winery.containers.create') || request()->routeIs('winery.containers.edit') || request()->routeIs('winery.containers.show')],
            ['icon' => 'map',               'label' => 'Mapa de Bodega',        'route' => 'winery.containers.map',         'active' => request()->routeIs('winery.containers.map'), 'new' => true],
            ['icon' => 'building-office',   'label' => 'Salas de Bodega',       'route' => 'winery.container-rooms.index',  'active' => request()->routeIs('winery.container-rooms*')],
            ['icon' => 'archive-box',       'label' => 'Uva / Mosto externo',   'route' => 'winery.external-grape.index',   'active' => request()->routeIs('winery.external-grape*')],
            ['divider' => true],
            ['icon' => 'arrows-right-left', 'label' => 'Vinos',                 'route' => 'winery.wines.index',            'active' => request()->routeIs('winery.wines.index') || request()->routeIs('winery.wines.create') || request()->routeIs('winery.wines.edit') || request()->routeIs('winery.wines.show')],
            ['icon' => 'queue-list',        'label' => 'Timeline de Vinos',     'route' => 'winery.wines.timeline',         'active' => request()->routeIs('winery.wines.timeline'), 'new' => true],
            ['icon' => 'user-circle',       'label' => 'Enólogos',              'route' => 'winery.oenologists.index',      'active' => request()->routeIs('winery.oenologists*')],
            ['icon' => 'magnifying-glass',  'label' => 'Análisis de Lab.',      'route' => 'winery.wine-analysis.index',    'active' => request()->routeIs('winery.wine-analysis*')],
            ['icon' => 'calendar-days',     'label' => 'Operaciones de Bodega', 'route' => 'winery.cellar-operations.index','active' => request()->routeIs('winery.cellar-operations*'), 'new' => true],
        ];

        $menu['cellar_salida'] = [
            ['icon' => 'archive-box',             'label' => 'Productos',               'route' => 'winery.product-lots.index',    'active' => request()->routeIs('winery.product-lots*') && !request()->routeIs('winery.product-lots.audit')],
            ['icon' => 'shield-check',            'label' => 'Auditoría de Stock',      'route' => 'winery.product-lots.audit',    'active' => request()->routeIs('winery.product-lots.audit')],
            ['icon' => 'magnifying-glass-circle', 'label' => 'Trazabilidad',            'route' => 'winery.traceability.index',    'active' => request()->routeIs('winery.traceability*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down',  'label' => 'Embotellado y Etiquetado','route' => 'winery.bottling.index',        'active' => request()->routeIs('winery.bottling*') || request()->routeIs('winery.label-batches*') || request()->routeIs('winery.labeling*')],
            ['icon' => 'document-text',           'label' => 'Fichas Técnicas y Catas', 'route' => 'winery.product-sheets.index', 'active' => request()->routeIs('winery.product-sheets*') || request()->routeIs('winery.tasting-notes*')],
            ['icon' => 'archive-box-x-mark',      'label' => 'Subproductos',            'route' => 'winery.subproducts.index',    'active' => request()->routeIs('winery.subproducts*')],
        ];

        $menu['territory'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'winery.plots.index',           'active' => request()->routeIs('winery.plots*') && !request()->routeIs('plots.plantings.*') || request()->routeIs('sigpac.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',        'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',              'active' => request()->routeIs('plots.territory')],
            ['divider' => true],
            ['icon' => 'globe-alt',           'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',     'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'cloud',               'label' => 'Meteorología',        'route' => 'winery.meteorology.index',     'active' => request()->routeIs('winery.meteorology*'), 'wip' => true, 'new' => true],
            ['divider' => true],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'winery.field-activities.index','active' => request()->routeIs('winery.field-activities*')],
        ];

        $menu['resources'] = [
            ['icon' => 'building-storefront', 'label' => 'Insumos de Bodega', 'route' => 'winery.winery-supplies.index', 'active' => request()->routeIs('winery.winery-supplies*')],
            ['icon' => 'truck',               'label' => 'Proveedores',        'route' => 'winery.suppliers.index',       'active' => request()->routeIs('winery.suppliers*')],
        ];

        $menu['billing'] = [
            ['icon' => 'chart-bar-square',        'label' => 'Resumen Económico',      'route' => 'winery.financial-summary.index',       'active' => request()->routeIs('winery.financial-summary*'), 'new' => true],
            ['icon' => 'presentation-chart-bar',  'label' => 'Estadísticas Financieras','route' => 'winery.financial-stats.index',          'active' => request()->routeIs('winery.financial-stats*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'arrow-down-tray',         'label' => 'Compra de Uva',          'route' => 'winery.invoices.grape-purchase.index',  'active' => request()->routeIs('winery.invoices.grape-purchase*')],
            ['icon' => 'arrow-up-tray',           'label' => 'Venta de Productos',     'route' => 'winery.invoices.products.index',        'active' => request()->routeIs('winery.invoices.products*')],
            ['icon' => 'document-check',          'label' => 'VeriFactu',              'route' => 'winery.verifactu.index',                'active' => request()->routeIs('winery.verifactu*')],
            ['icon' => 'users',                   'label' => 'Clientes y Canales',     'route' => 'winery.clients.index',                  'active' => request()->routeIs('winery.clients*')],
            ['icon' => 'globe-alt',               'label' => 'Exportación',            'route' => 'winery.exports.index',                  'active' => request()->routeIs('winery.exports*'), 'wip' => true, 'new' => true],
            ['icon' => 'sparkles',                'label' => 'Enoturismo',             'route' => 'winery.enotourism.index',               'active' => request()->routeIs('winery.enotourism*'), 'wip' => true, 'new' => true],
        ];

        $menu['winery_normativa'] = [
            ['icon' => 'document-chart-bar', 'label' => 'SILICIE',                       'route' => 'winery.silicie.dashboard',              'active' => request()->routeIs('winery.silicie.dashboard') || request()->routeIs('winery.silicie.movements*')],
            ['icon' => 'chart-bar',          'label' => 'INFOVI (AICA)',                 'route' => 'winery.silicie.infovi',                 'active' => request()->routeIs('winery.silicie.infovi')],
            ['divider' => true],
            ['icon' => 'document-text',      'label' => 'AICA',                          'route' => 'winery.aica.index',                    'active' => request()->routeIs('winery.aica*'), 'wip' => true, 'new' => true],
            ['icon' => 'shield-check',       'label' => 'Registros Sanitarios',          'route' => 'winery.sanitary-registrations.index',  'active' => request()->routeIs('winery.sanitary-registrations*'), 'new' => true],
            ['icon' => 'identification',     'label' => 'Autorizaciones de Embotellado', 'route' => 'winery.bottling-authorizations.index', 'active' => request()->routeIs('winery.bottling-authorizations*'), 'new' => true],
            ['icon' => 'sparkles',           'label' => 'Certificaciones Ecológicas',    'route' => 'winery.eco-certifications.index',      'active' => request()->routeIs('winery.eco-certifications*'), 'new' => true],
        ];

        $menu['compliance'] = [
            ['icon' => 'folder-open', 'label' => 'Documentos Bodega', 'route' => 'winery.documents.index', 'active' => request()->routeIs('winery.documents*')],
        ];

        $menu['system'] = [
            ['icon' => 'cog-6-tooth', 'label' => 'Configuración',    'route' => 'winery.settings',    'active' => request()->routeIs('winery.settings')],
            ['icon' => 'bell-alert',  'label' => 'Centro de Alertas','route' => 'winery.alerts.index', 'active' => request()->routeIs('winery.alerts*'), 'new' => true],
        ];

        return $menu;
    }
}
