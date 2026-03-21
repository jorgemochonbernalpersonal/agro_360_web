<?php

namespace App\Helpers\Navigation;

use App\Models\NotebookAccessRequest;
use Illuminate\Support\Facades\Cache;

class ViticulturistMenu
{
    public static function build($user): array
    {
        $menu = [];

        $menu['main'] = [
            [
                'icon'   => 'home',
                'label'  => 'Dashboard',
                'route'  => 'viticulturist.dashboard',
                'active' => request()->routeIs('viticulturist.dashboard'),
            ],
            [
                'icon'   => 'calendar-days',
                'label'  => 'Calendario',
                'route'  => 'viticulturist.calendar',
                'active' => request()->routeIs('viticulturist.calendar'),
            ],
            [
                'icon'   => 'bell',
                'label'  => 'Notificaciones',
                'route'  => 'viticulturist.notifications.index',
                'active' => request()->routeIs('viticulturist.notifications*'),
                'badge'  => Cache::remember("nav_badge_notifications_{$user->id}", 60, fn() => $user->unreadNotifications()->count()),
            ],
        ];

        $menu['operations'] = [
            [
                'icon'   => 'clipboard-document-list',
                'label'  => 'Campañas',
                'route'  => 'viticulturist.campaign.index',
                'active' => request()->routeIs('viticulturist.campaign.*'),
            ],
            [
                'icon'   => 'folder-open',
                'label'  => 'Documentos de Campaña',
                'route'  => 'viticulturist.campaign-documents.index',
                'active' => request()->routeIs('viticulturist.campaign-documents.*'),
            ],
            [
                'icon'   => 'check-badge',
                'label'  => 'Firma y Cierre',
                'route'  => 'viticulturist.campaign-sign.index',
                'active' => request()->routeIs('viticulturist.campaign-sign.*'),
            ],
            ['divider' => true],
            [
                'icon'   => 'chart-bar-square',
                'label'  => 'Rendimientos Estimados',
                'route'  => 'viticulturist.digital-notebook.estimated-yields.index',
                'active' => request()->routeIs('viticulturist.digital-notebook.estimated-yields.*'),
            ],
            [
                'icon'   => 'chat-bubble-left-right',
                'label'  => 'Comunicación con Bodega',
                'route'  => 'viticulturist.bodega-messages.index',
                'active' => request()->routeIs('viticulturist.bodega-messages*'),
            ],
            [
                'icon'   => 'lock-closed',
                'label'  => 'Acceso Bodegas al Cuaderno',
                'route'  => 'viticulturist.winery-access.index',
                'active' => request()->routeIs('viticulturist.winery-access*'),
                'badge'  => Cache::remember("nav_badge_notebook_access_{$user->id}", 120, fn() =>
                    NotebookAccessRequest::where('viticulturist_id', $user->id)
                        ->where('status', NotebookAccessRequest::STATUS_PENDING)
                        ->count()
                ),
            ],
        ];

        $menu['cuaderno_inputs'] = [
            [
                'icon'   => 'book-open',
                'label'  => 'Cuaderno Digital',
                'route'  => 'viticulturist.digital-notebook',
                'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*'),
            ],
            ['divider' => true],
            ['icon' => 'shield-exclamation', 'label' => 'Tratamientos',      'route' => 'viticulturist.digital-notebook.treatment.index',    'active' => request()->routeIs('viticulturist.digital-notebook.treatment.*')],
            ['icon' => 'funnel',             'label' => 'Fertilizaciones',   'route' => 'viticulturist.digital-notebook.fertilization.index', 'active' => request()->routeIs('viticulturist.digital-notebook.fertilization.*')],
            ['icon' => 'cloud',              'label' => 'Riegos',            'route' => 'viticulturist.digital-notebook.irrigation.index',    'active' => request()->routeIs('viticulturist.digital-notebook.irrigation.*')],
            ['icon' => 'wrench-screwdriver', 'label' => 'Labores Culturales','route' => 'viticulturist.digital-notebook.cultural.index',      'active' => request()->routeIs('viticulturist.digital-notebook.cultural.*')],
            ['icon' => 'eye',                'label' => 'Observaciones',     'route' => 'viticulturist.digital-notebook.observation.index',   'active' => request()->routeIs('viticulturist.digital-notebook.observation.*')],
            ['icon' => 'sun',                'label' => 'Fenología',         'route' => 'viticulturist.phenology.index',                      'active' => request()->routeIs('viticulturist.phenology.*')],
            ['icon' => 'scissors',           'label' => 'Podas',             'route' => 'viticulturist.digital-notebook.pruning.index',       'active' => request()->routeIs('viticulturist.digital-notebook.pruning.*')],
            ['icon' => 'archive-box',        'label' => 'Post-Vendimia',     'route' => 'viticulturist.digital-notebook.post-harvest.index',  'active' => request()->routeIs('viticulturist.digital-notebook.post-harvest.*')],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down', 'label' => 'Vendimia',         'route' => 'viticulturist.harvests.index',        'active' => request()->routeIs('viticulturist.harvests.*')],
            ['icon' => 'bug-ant',                'label' => 'Gestión de Plagas', 'route' => 'viticulturist.pest-management.index', 'active' => request()->routeIs('viticulturist.pest-management.*')],
        ];

        $menu['registros_oficiales'] = [
            ['icon' => 'chart-bar',                'label' => 'Cumplimiento Cuaderno',      'route' => 'viticulturist.pac-compliance',                  'active' => request()->routeIs('viticulturist.pac-compliance')],
            ['icon' => 'clipboard-document-check', 'label' => 'Análisis de Residuos',       'route' => 'viticulturist.residue-analyses.index',          'active' => request()->routeIs('viticulturist.residue-analyses.*')],
            ['icon' => 'trash',                    'label' => 'Gestión de Residuos',        'route' => 'viticulturist.residue-managements.index',       'active' => request()->routeIs('viticulturist.residue-managements.*')],
            ['icon' => 'bolt',                     'label' => 'Consumo Energético',         'route' => 'viticulturist.energy-usages.index',             'active' => request()->routeIs('viticulturist.energy-usages.*')],
            ['icon' => 'archive-box-x-mark',       'label' => 'Envases Fitosanitarios',     'route' => 'viticulturist.container-returns.index',         'active' => request()->routeIs('viticulturist.container-returns.*')],
            ['icon' => 'document-arrow-up',        'label' => 'Declaración de Vendimia',    'route' => 'viticulturist.harvest-declarations.index',      'active' => request()->routeIs('viticulturist.harvest-declarations.*')],
            ['icon' => 'arrow-up-tray',            'label' => 'Exportaciones CUE',          'route' => 'viticulturist.cue-exports.index',               'active' => request()->routeIs('viticulturist.cue-exports.*')],
            ['icon' => 'document',                 'label' => 'Informes Oficiales',         'route' => 'viticulturist.official-reports.index',          'active' => request()->routeIs('viticulturist.official-reports.*')],
        ];

        $menu['plots_analysis'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'plots.index',                           'active' => request()->routeIs('plots.*') && !request()->routeIs('plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',                 'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',              'route' => 'sigpac.codes',                          'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-alt',           'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',              'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',                       'active' => request()->routeIs('plots.territory')],
            ['icon' => 'cloud',               'label' => 'Meteorología',        'route' => 'viticulturist.meteorology.index',       'active' => request()->routeIs('viticulturist.meteorology*')],
            ['icon' => 'viewfinder-circle',   'label' => 'Entorno de Parcelas', 'route' => 'viticulturist.plot-environments.index', 'active' => request()->routeIs('viticulturist.plot-environments.*')],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'viticulturist.field-activities.index',  'active' => request()->routeIs('viticulturist.field-activities*')],
        ];

        $menu['resources'] = [
            ['icon' => 'user-group',          'label' => 'Personal',                 'route' => 'viticulturist.personal.index',               'active' => request()->routeIs('viticulturist.personal*') || request()->routeIs('viticulturist.viticulturists.*')],
            ['icon' => 'adjustments-vertical','label' => 'Maquinaria',               'route' => 'viticulturist.machinery.index',              'active' => request()->routeIs('viticulturist.machinery*')],
            ['icon' => 'cube',                'label' => 'Contenedores',             'route' => 'viticulturist.containers.index',             'active' => request()->routeIs('viticulturist.containers.*')],
            ['icon' => 'building-storefront', 'label' => 'Almacén de Insumos',       'route' => 'viticulturist.almacen.index',                'active' => request()->routeIs('viticulturist.almacen.*') && !request()->routeIs('viticulturist.almacen.stock.analytics') && !request()->routeIs('viticulturist.almacen.stock.movements')],
            ['icon' => 'chart-bar-square',    'label' => 'Analítica de Stock',       'route' => 'viticulturist.almacen.stock.analytics',      'active' => request()->routeIs('viticulturist.almacen.stock.analytics')],
            ['icon' => 'beaker',              'label' => 'Productos Fitosanitarios', 'route' => 'viticulturist.phytosanitary-products.index',  'active' => request()->routeIs('viticulturist.phytosanitary-products.*')],
            ['icon' => 'user-plus',           'label' => 'Subcontratación',          'route' => 'viticulturist.subcontracting.index',         'active' => request()->routeIs('viticulturist.subcontracting*')],
        ];

        $menu['compliance'] = [
            ['icon' => 'building-office',   'label' => 'Explotación SIEX/REA',      'route' => 'viticulturist.exploitations.index',             'active' => request()->routeIs('viticulturist.exploitations.*')],
            ['icon' => 'shield-check',      'label' => 'Autorizaciones Comerciales', 'route' => 'viticulturist.commercial-authorizations.index', 'active' => request()->routeIs('viticulturist.commercial-authorizations.*')],
            ['icon' => 'user',              'label' => 'Asesorías Técnicas',         'route' => 'viticulturist.advisory-memberships.index',      'active' => request()->routeIs('viticulturist.advisory-memberships.*')],
            ['icon' => 'identification',    'label' => 'Aplicadores ROPO',           'route' => 'viticulturist.field-applicators.index',         'active' => request()->routeIs('viticulturist.field-applicators.*')],
            ['icon' => 'cog-8-tooth',        'label' => 'Equipos ITB/ITEA',           'route' => 'viticulturist.field-equipment.index',           'active' => request()->routeIs('viticulturist.field-equipment.*')],
            ['icon' => 'lifebuoy',          'label' => 'Seguros Agrarios',           'route' => 'viticulturist.agri-insurance.index',            'active' => request()->routeIs('viticulturist.agri-insurance*')],
        ];

        $menu['pac'] = [
            ['icon' => 'chart-pie',    'label' => 'Resumen PAC',          'route' => 'viticulturist.pac.dashboard',          'active' => request()->routeIs('viticulturist.pac.dashboard')],
            ['icon' => 'check-circle', 'label' => 'Superficies Elegibles','route' => 'viticulturist.pac.surfaces.index',     'active' => request()->routeIs('viticulturist.pac.surfaces.*')],
            ['icon' => 'document-text','label' => 'Declaraciones',        'route' => 'viticulturist.pac.declarations.index', 'active' => request()->routeIs('viticulturist.pac.declarations.*')],
            ['icon' => 'sparkles',     'label' => 'Eco-regímenes',        'route' => 'viticulturist.pac.eco-schemes.index',  'active' => request()->routeIs('viticulturist.pac.eco-schemes.*')],
            ['icon' => 'banknotes',    'label' => 'Historial de Ayudas',  'route' => 'viticulturist.pac.payments.index',     'active' => request()->routeIs('viticulturist.pac.payments.*')],
        ];

        $menu['billing'] = [
            ['icon' => 'calculator',             'label' => 'Facturas',                 'route' => 'viticulturist.invoices.index',               'active' => request()->routeIs('viticulturist.invoices.*')],
            ['icon' => 'document-check',         'label' => 'VeriFactu',                'route' => 'viticulturist.verifactu.index',              'active' => request()->routeIs('viticulturist.verifactu*'),  'wip' => true, 'new' => true],
            ['icon' => 'document-arrow-up',      'label' => 'Facturas Venta Cosecha',   'route' => 'viticulturist.invoices.harvest-sale.index',  'active' => request()->routeIs('viticulturist.invoices.harvest-sale*')],
            ['icon' => 'document-arrow-down',    'label' => 'Liquidaciones de Bodega',  'route' => 'viticulturist.invoices.grape-purchase.index','active' => request()->routeIs('viticulturist.invoices.grape-purchase*')],
            ['icon' => 'shopping-cart',          'label' => 'Cosecha Comercializada',   'route' => 'viticulturist.marketed-harvests.index',      'active' => request()->routeIs('viticulturist.marketed-harvests.*')],
            ['icon' => 'table-cells',            'label' => 'Costes por Parcela',       'route' => 'viticulturist.plot-costs.index',             'active' => request()->routeIs('viticulturist.plot-costs*')],
            ['icon' => 'users',                  'label' => 'Clientes',                 'route' => 'viticulturist.clients.index',                'active' => request()->routeIs('viticulturist.clients.*')],
            ['icon' => 'presentation-chart-bar', 'label' => 'Estadísticas Financieras', 'route' => 'viticulturist.financial-stats',              'active' => request()->routeIs('viticulturist.financial-stats')],
        ];

        $menu['rail_bottom'] = [
            [
                'icon'   => 'cog-6-tooth',
                'label'  => 'Configuración',
                'route'  => 'viticulturist.settings',
                'active' => request()->routeIs('viticulturist.settings'),
            ],
            [
                'icon'   => 'question-mark-circle',
                'label'  => 'Soporte',
                'route'  => 'viticulturist.support.index',
                'active' => request()->routeIs('viticulturist.support.*'),
                'badge'  => Cache::remember("nav_badge_support_{$user->id}", 120, fn() => $user->supportTickets()->open()->count()),
            ],
        ];

        return $menu;
    }
}
