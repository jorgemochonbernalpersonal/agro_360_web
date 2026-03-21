<?php

namespace App\Helpers\Navigation;

use Illuminate\Support\Facades\Cache;

class ProducerMenu
{
    public static function build($user): array
    {
        $menu = [];

        $menu['main'] = [
            ['icon' => 'squares-2x2',   'label' => 'Vista general', 'route' => 'producer.dashboard',              'active' => request()->routeIs('producer.dashboard')],
            ['icon' => 'calendar-days', 'label' => 'Calendario',    'route' => 'viticulturist.calendar',           'active' => request()->routeIs('viticulturist.calendar')],
            ['icon' => 'bell',          'label' => 'Notificaciones','route' => 'viticulturist.notifications.index', 'active' => request()->routeIs('viticulturist.notifications*'), 'new' => true],
        ];

        // Acceso directo a recursos comunes a ambos lados del producer (viñedo + bodega).
        // Se renderizan entre main y el tab switcher 🌿/🏛, con separador visual propio.
        $menu['main_shortcuts'] = [
            ['icon' => 'map',               'label' => 'Parcelas',         'route' => 'producer.plots.index',          'active' => request()->routeIs('producer.plots.*') && !request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'book-open',         'label' => 'Plantaciones',     'route' => 'plots.plantings.index',         'active' => request()->routeIs('plots.plantings.*') || request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'map-pin',           'label' => 'SIGPAC',           'route' => 'sigpac.codes',                  'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'document-arrow-up', 'label' => 'Albaranes Mixtos', 'route' => 'producer.invoices.mixed.index', 'active' => request()->routeIs('producer.invoices.mixed.*')],
        ];

        $menu['operations'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Campañas',              'route' => 'producer.campaign.index',                    'active' => request()->routeIs('producer.campaign*') && !request()->routeIs('producer.campaign-documents.*') && !request()->routeIs('producer.campaign-sign.*')],
            ['icon' => 'folder-open',             'label' => 'Documentos de Campaña', 'route' => 'producer.campaign-documents.index',          'active' => request()->routeIs('producer.campaign-documents.*')],
            ['icon' => 'check-badge',             'label' => 'Firma y Cierre',        'route' => 'producer.campaign-sign.index',               'active' => request()->routeIs('producer.campaign-sign.*')],
            ['divider' => true],
            ['icon' => 'chart-bar-square',        'label' => 'Rendimientos Estimados','route' => 'producer.digital-notebook.estimated-yields.index','active' => request()->routeIs('producer.digital-notebook.estimated-yields.*')],
        ];

        $menu['cuaderno_inputs'] = [
            ['icon' => 'book-open',              'label' => 'Cuaderno Digital',   'route' => 'producer.digital-notebook',                      'active' => request()->routeIs('producer.digital-notebook') && !request()->routeIs('producer.digital-notebook.*')],
            ['divider' => true],
            ['icon' => 'shield-exclamation',     'label' => 'Tratamientos',       'route' => 'producer.digital-notebook.treatment.index',      'active' => request()->routeIs('producer.digital-notebook.treatment.*')],
            ['icon' => 'funnel',                 'label' => 'Fertilizaciones',    'route' => 'producer.digital-notebook.fertilization.index',  'active' => request()->routeIs('producer.digital-notebook.fertilization.*')],
            ['icon' => 'cloud',                  'label' => 'Riegos',             'route' => 'producer.digital-notebook.irrigation.index',     'active' => request()->routeIs('producer.digital-notebook.irrigation.*')],
            ['icon' => 'wrench-screwdriver',     'label' => 'Labores Culturales', 'route' => 'producer.digital-notebook.cultural.index',       'active' => request()->routeIs('producer.digital-notebook.cultural.*')],
            ['icon' => 'eye',                    'label' => 'Observaciones',      'route' => 'producer.digital-notebook.observation.index',    'active' => request()->routeIs('producer.digital-notebook.observation.*')],
            ['icon' => 'sun',                    'label' => 'Fenología',          'route' => 'producer.phenology.index',                       'active' => request()->routeIs('producer.phenology.*')],
            ['icon' => 'scissors',               'label' => 'Podas',              'route' => 'producer.digital-notebook.pruning.index',        'active' => request()->routeIs('producer.digital-notebook.pruning.*')],
            ['icon' => 'archive-box',            'label' => 'Post-Vendimia',      'route' => 'producer.digital-notebook.post-harvest.index',   'active' => request()->routeIs('producer.digital-notebook.post-harvest.*')],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down', 'label' => 'Vendimia Campo',     'route' => 'producer.harvests.index',                        'active' => request()->routeIs('producer.harvests.*')],
            ['icon' => 'bug-ant',                'label' => 'Gestión de Plagas',  'route' => 'producer.pest-management.index',                 'active' => request()->routeIs('producer.pest-management.*')],
        ];

        $menu['registros_oficiales'] = [
            ['icon' => 'chart-bar',                'label' => 'Cumplimiento Cuaderno', 'route' => 'producer.pac-compliance',            'active' => request()->routeIs('producer.pac-compliance')],
            ['icon' => 'clipboard-document-check', 'label' => 'Análisis de Residuos',  'route' => 'producer.residue-analyses.index',    'active' => request()->routeIs('producer.residue-analyses.*')],
            ['icon' => 'trash',                    'label' => 'Gestión de Residuos',   'route' => 'producer.residue-managements.index', 'active' => request()->routeIs('producer.residue-managements.*')],
            ['icon' => 'bolt',                     'label' => 'Consumo Energético',    'route' => 'producer.energy-usages.index',       'active' => request()->routeIs('producer.energy-usages.*')],
            ['icon' => 'arrow-up-tray',            'label' => 'Exportaciones CUE',     'route' => 'producer.cue-exports.index',         'active' => request()->routeIs('producer.cue-exports.*')],
            ['icon' => 'document',                 'label' => 'Informes Oficiales',    'route' => 'producer.official-reports.index',    'active' => request()->routeIs('producer.official-reports.*')],
        ];

        $menu['plots_analysis'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'producer.plots.index',             'active' => request()->routeIs('producer.plots.*') && !request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',            'active' => request()->routeIs('plots.plantings.*') || request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',              'route' => 'sigpac.codes',                     'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-alt',           'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',         'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',                  'active' => request()->routeIs('plots.territory')],
            ['icon' => 'viewfinder-circle',   'label' => 'Entorno de Parcelas', 'route' => 'producer.plot-environments.index', 'active' => request()->routeIs('producer.plot-environments.*')],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'producer.field-activities.index',  'active' => request()->routeIs('producer.field-activities*')],
        ];

        $menu['resources'] = [
            ['icon' => 'user-group',          'label' => 'Personal',                 'route' => 'producer.personal.index',               'active' => request()->routeIs('producer.personal*')],
            ['icon' => 'adjustments-vertical','label' => 'Maquinaria',               'route' => 'producer.machinery.index',              'active' => request()->routeIs('producer.machinery*')],
            ['icon' => 'building-storefront', 'label' => 'Almacén de Insumos',       'route' => 'producer.almacen.index',                'active' => request()->routeIs('producer.almacen.*')],
            ['icon' => 'beaker',              'label' => 'Productos Fitosanitarios', 'route' => 'producer.phytosanitary-products.index', 'active' => request()->routeIs('producer.phytosanitary-products.*')],
            ['icon' => 'user-plus',           'label' => 'Subcontratación',          'route' => 'producer.subcontracting.index',         'active' => request()->routeIs('producer.subcontracting*'), 'new' => true],
        ];

        $menu['compliance'] = [
            ['icon' => 'building-office',    'label' => 'Explotación SIEX/REA',      'route' => 'producer.exploitations.index',             'active' => request()->routeIs('producer.exploitations.*')],
            ['icon' => 'shield-check',       'label' => 'Autorizaciones Comerciales', 'route' => 'producer.commercial-authorizations.index', 'active' => request()->routeIs('producer.commercial-authorizations.*')],
            ['icon' => 'user',               'label' => 'Asesorías Técnicas',         'route' => 'producer.advisory-memberships.index',      'active' => request()->routeIs('producer.advisory-memberships.*')],
            ['icon' => 'identification',     'label' => 'Aplicadores ROPO',           'route' => 'producer.field-applicators.index',         'active' => request()->routeIs('producer.field-applicators.*')],
            ['icon' => 'cog-6-tooth',        'label' => 'Equipos ITB/ITEA',           'route' => 'producer.field-equipment.index',           'active' => request()->routeIs('producer.field-equipment.*')],
            ['icon' => 'lifebuoy',           'label' => 'Seguros Agrarios',           'route' => 'producer.agri-insurance.index',            'active' => request()->routeIs('producer.agri-insurance*'), 'new' => true],
        ];

        $menu['pac'] = [
            ['icon' => 'chart-pie',    'label' => 'Resumen PAC',          'route' => 'producer.pac.dashboard',          'active' => request()->routeIs('producer.pac.dashboard')],
            ['icon' => 'check-circle', 'label' => 'Superficies Elegibles','route' => 'producer.pac.surfaces.index',     'active' => request()->routeIs('producer.pac.surfaces.*')],
            ['icon' => 'document-text','label' => 'Declaraciones',        'route' => 'producer.pac.declarations.index', 'active' => request()->routeIs('producer.pac.declarations.*')],
            ['icon' => 'sparkles',     'label' => 'Eco-regímenes',        'route' => 'producer.pac.eco-schemes.index',  'active' => request()->routeIs('producer.pac.eco-schemes.*')],
            ['icon' => 'banknotes',    'label' => 'Historial de Ayudas',  'route' => 'producer.pac.payments.index',     'active' => request()->routeIs('producer.pac.payments.*')],
        ];

        $menu['billing'] = [
            ['icon' => 'calculator',             'label' => 'Facturas',               'route' => 'producer.invoices.index',          'active' => request()->routeIs('producer.invoices.*') && !request()->routeIs('producer.invoices.products.*') && !request()->routeIs('producer.invoices.grape-purchase.*') && !request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-arrow-up',      'label' => 'Albaranes Mixtos',       'route' => 'producer.invoices.mixed.index',    'active' => request()->routeIs('producer.invoices.mixed.*')],
            ['icon' => 'document-check',         'label' => 'VeriFactu',              'route' => 'producer.verifactu.index',         'active' => request()->routeIs('producer.verifactu*'), 'new' => true],
            ['icon' => 'shopping-cart',          'label' => 'Cosecha Comercializada', 'route' => 'producer.marketed-harvests.index', 'active' => request()->routeIs('producer.marketed-harvests.*')],
            ['icon' => 'table-cells',            'label' => 'Costes por Parcela',     'route' => 'producer.plot-costs.index',        'active' => request()->routeIs('producer.plot-costs*'), 'new' => true],
            ['icon' => 'users',                  'label' => 'Clientes',               'route' => 'producer.clients.index',           'active' => request()->routeIs('producer.clients.*')],
            ['icon' => 'presentation-chart-bar', 'label' => 'Estadísticas Viñedo',    'route' => 'producer.financial-stats',         'active' => request()->routeIs('producer.financial-stats')],
        ];

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

        $menu['cellar_elab'] = [
            ['icon' => 'beaker',            'label' => 'Contenedores',          'route' => 'producer.containers.index',        'active' => request()->routeIs('producer.containers.index') || request()->routeIs('producer.containers.create') || request()->routeIs('producer.containers.edit') || request()->routeIs('producer.containers.show')],
            ['icon' => 'map',               'label' => 'Mapa de Bodega',        'route' => 'producer.containers.map',          'active' => request()->routeIs('producer.containers.map'), 'new' => true],
            ['icon' => 'home-modern',       'label' => 'Salas de Bodega',       'route' => 'producer.container-rooms.index',   'active' => request()->routeIs('producer.container-rooms*'), 'new' => true],
            ['icon' => 'calendar-days',     'label' => 'Operaciones de Bodega', 'route' => 'producer.cellar-operations.index', 'active' => request()->routeIs('producer.cellar-operations*'), 'new' => true],
            ['icon' => 'archive-box',       'label' => 'Uva / Mosto externo',   'route' => 'producer.external-grape.index',    'active' => request()->routeIs('producer.external-grape*')],
            ['divider' => true],
            ['icon' => 'arrows-right-left', 'label' => 'Vinos',                 'route' => 'producer.wines.index',             'active' => request()->routeIs('producer.wines.index') || request()->routeIs('producer.wines.create') || request()->routeIs('producer.wines.edit') || request()->routeIs('producer.wines.show')],
            ['icon' => 'queue-list',        'label' => 'Timeline de Vinos',     'route' => 'producer.wines.timeline',          'active' => request()->routeIs('producer.wines.timeline'), 'new' => true],
            ['icon' => 'user-circle',       'label' => 'Enólogos',              'route' => 'producer.oenologists.index',       'active' => request()->routeIs('producer.oenologists*')],
            ['icon' => 'magnifying-glass',  'label' => 'Análisis de Lab.',      'route' => 'producer.wine-analysis.index',     'active' => request()->routeIs('producer.wine-analysis*')],
        ];

        $menu['cellar_salida'] = [
            ['icon' => 'archive-box',             'label' => 'Productos',                  'route' => 'producer.product-lots.index',    'active' => request()->routeIs('producer.product-lots*') && !request()->routeIs('producer.product-lots.audit')],
            ['icon' => 'shield-check',            'label' => 'Auditoría de Stock',         'route' => 'producer.product-lots.audit',    'active' => request()->routeIs('producer.product-lots.audit'), 'new' => true],
            ['icon' => 'magnifying-glass-circle', 'label' => 'Trazabilidad',               'route' => 'producer.traceability.index',    'active' => request()->routeIs('producer.traceability*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down',  'label' => 'Embotellado y Expediciones', 'route' => 'producer.bottling.index',        'active' => request()->routeIs('producer.bottling*'), 'new' => true],
            ['icon' => 'tag',                     'label' => 'Etiquetado',                 'route' => 'producer.label-batches.index',   'active' => request()->routeIs('producer.label-batches*') || request()->routeIs('producer.labeling*'), 'new' => true],
            ['icon' => 'document-text',           'label' => 'Fichas Técnicas y Catas',    'route' => 'producer.product-sheets.index', 'active' => request()->routeIs('producer.product-sheets*') || request()->routeIs('producer.tasting-notes*'), 'new' => true],
            ['icon' => 'archive-box-x-mark',      'label' => 'Subproductos',               'route' => 'producer.subproducts.index',    'active' => request()->routeIs('producer.subproducts*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'building-storefront',     'label' => 'Insumos de Bodega',          'route' => 'producer.winery-supplies.index','active' => request()->routeIs('producer.winery-supplies*')],
            ['icon' => 'truck',                   'label' => 'Proveedores',                'route' => 'producer.suppliers.index',      'active' => request()->routeIs('producer.suppliers*')],
            ['divider' => true],
            ['icon' => 'bell-alert',              'label' => 'Centro de Alertas',          'route' => 'producer.alerts.index',         'active' => request()->routeIs('producer.alerts*'), 'new' => true],
        ];

        $menu['winery_normativa'] = [
            ['icon' => 'document-chart-bar', 'label' => 'SILICIE',                       'route' => 'producer.silicie.index',                 'active' => request()->routeIs('producer.silicie.index'), 'wip' => true],
            ['icon' => 'chart-bar',          'label' => 'INFOVI (AICA)',                 'route' => 'producer.silicie.infovi',                'active' => request()->routeIs('producer.silicie.infovi'), 'wip' => true],
            ['divider' => true],
            ['icon' => 'document-text',      'label' => 'AICA',                          'route' => 'producer.aica.index',                   'active' => request()->routeIs('producer.aica*'), 'wip' => true, 'new' => true],
            ['icon' => 'shield-check',       'label' => 'Registros Sanitarios',          'route' => 'producer.sanitary-registrations.index', 'active' => request()->routeIs('producer.sanitary-registrations*'), 'new' => true],
            ['icon' => 'identification',     'label' => 'Autorizaciones de Embotellado', 'route' => 'producer.bottling-authorizations.index','active' => request()->routeIs('producer.bottling-authorizations*'), 'new' => true],
            ['icon' => 'sparkles',           'label' => 'Certificaciones Ecológicas',    'route' => 'producer.eco-certifications.index',     'active' => request()->routeIs('producer.eco-certifications*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'folder-open',        'label' => 'Documentos Bodega',             'route' => 'producer.documents.index',              'active' => request()->routeIs('producer.documents*'), 'new' => true],
        ];

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

        $menu['rail_bottom'] = [
            ['icon' => 'cog-6-tooth',        'label' => 'Configuración', 'route' => 'producer.settings',     'active' => request()->routeIs('producer.settings')],
            ['icon' => 'question-mark-circle','label' => 'Soporte',      'route' => 'producer.support.index', 'active' => request()->routeIs('producer.support.*'), 'badge' => Cache::remember("nav_badge_support_{$user->id}", 120, fn() => $user->supportTickets()->open()->count())],
        ];

        return $menu;
    }
}
