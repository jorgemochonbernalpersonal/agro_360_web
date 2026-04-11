<?php

namespace App\Helpers\Navigation;

use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use Illuminate\Support\Facades\Cache;

class ViticulturistMenu
{
    public static function build($user): array
    {
        $menu = [];

        // ── Main ─────────────────────────────────────────────────────────────
        $menu['main'] = [
            ['icon' => 'home',         'label' => 'Dashboard',      'route' => 'viticulturist.dashboard',           'active' => request()->routeIs('viticulturist.dashboard')],
            ['icon' => 'calendar-days','label' => 'Calendario',     'route' => 'viticulturist.calendar',            'active' => request()->routeIs('viticulturist.calendar')],
            ['icon' => 'bell',         'label' => 'Notificaciones', 'route' => 'viticulturist.notifications.index', 'active' => request()->routeIs('viticulturist.notifications*'),
             'badge' => Cache::remember("nav_badge_notifications_{$user->id}", 60, fn() => $user->unreadNotifications()->count())],
        ];

        // ── Campaña ───────────────────────────────────────────────────────────
        $menu['campaigns'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Campañas',              'route' => 'viticulturist.campaign.index',           'active' => request()->routeIs('viticulturist.campaign.*')],
            ['icon' => 'folder-open',             'label' => 'Documentos de Campaña', 'route' => 'viticulturist.campaign-documents.index', 'active' => request()->routeIs('viticulturist.campaign-documents.*')],
            ['icon' => 'check-badge',             'label' => 'Firma y Cierre',        'route' => 'viticulturist.campaign-sign.index',       'active' => request()->routeIs('viticulturist.campaign-sign.*')],
        ];

        // ── Relación con Bodega ───────────────────────────────────────────────
        $menu['bodega_rel'] = [
            ['icon' => 'megaphone',              'label' => 'Avisos de Bodegas',          'route' => 'viticulturist.announcements',          'active' => request()->routeIs('viticulturist.announcements')],
            ['icon' => 'chat-bubble-left-right', 'label' => 'Comunicación con Bodega',    'route' => 'viticulturist.winery-messages.index',  'active' => request()->routeIs('viticulturist.winery-messages*')],
            ['icon' => 'lock-closed',            'label' => 'Acceso Bodegas al Cuaderno', 'route' => 'viticulturist.winery-access.index',   'active' => request()->routeIs('viticulturist.winery-access*'),
             'badge' => Cache::remember("nav_badge_notebook_access_{$user->id}", 120, fn() =>
                NotebookAccessRequest::where('viticulturist_id', $user->id)->where('status', NotebookAccessRequest::STATUS_PENDING)->count()
             )],
        ];

        // ── Denominación (solo si está adscrito) ──────────────────────────────
        $hasSupervisor = Cache::remember("viticulturist:{$user->id}:has_supervisor", 300,
            fn () => SupervisorViticulturist::where('viticulturist_id', $user->id)->exists()
        );

        if ($hasSupervisor) {
            $menu['denomination'] = [
                ['icon' => 'building-office-2', 'label' => 'Mi Denominación', 'route' => 'viticulturist.denomination.index', 'active' => request()->routeIs('viticulturist.denomination.*')],
            ];
        }

        // ── Cuaderno + Registros Oficiales ────────────────────────────────────
        $menu['cuaderno_inputs']     = self::cuadernoInputs('viticulturist');
        $menu['registros_oficiales'] = self::registrosOficiales('viticulturist');

        // ── Finca ─────────────────────────────────────────────────────────────
        $menu['finca'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'plots.index',                           'active' => request()->routeIs('plots.*') && !request()->routeIs('plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',                 'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',              'route' => 'sigpac.codes',                          'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',                       'active' => request()->routeIs('plots.territory')],
            ['divider' => true],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'viticulturist.field-activities.index',  'active' => request()->routeIs('viticulturist.field-activities*')],
            ['divider' => true],
            ['icon' => 'globe-alt',           'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',              'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'cloud',               'label' => 'Meteorología',        'route' => 'viticulturist.meteorology.index',       'active' => request()->routeIs('viticulturist.meteorology*')],
            ['icon' => 'viewfinder-circle',   'label' => 'Entorno de Parcelas', 'route' => 'viticulturist.plot-environments.index', 'active' => request()->routeIs('viticulturist.plot-environments.*')],
        ];

        // ── Recursos + Normativa + PAC ────────────────────────────────────────
        $menu['resources']  = self::resources('viticulturist', includeContainers: true);
        $menu['compliance'] = self::compliance('viticulturist');
        $menu['pac']        = self::pac('viticulturist');

        // ── Negocio ───────────────────────────────────────────────────────────
        $menu['billing'] = [
            ['icon' => 'document-arrow-up',      'label' => 'Facturas Venta Cosecha',   'route' => 'viticulturist.invoices.harvest-sale.index',   'active' => request()->routeIs('viticulturist.invoices.harvest-sale*')],
            ['icon' => 'document-arrow-down',    'label' => 'Liquidaciones de Bodega',  'route' => 'viticulturist.invoices.grape-purchase.index', 'active' => request()->routeIs('viticulturist.invoices.grape-purchase*')],
            ['divider' => true],
            ['icon' => 'shopping-cart',          'label' => 'Cosecha Comercializada',   'route' => 'viticulturist.marketed-harvests.index',       'active' => request()->routeIs('viticulturist.marketed-harvests.*')],
            ['icon' => 'table-cells',            'label' => 'Costes por Parcela',       'route' => 'viticulturist.plot-costs.index',              'active' => request()->routeIs('viticulturist.plot-costs*')],
            ['icon' => 'users',                  'label' => 'Clientes',                 'route' => 'viticulturist.clients.index',                 'active' => request()->routeIs('viticulturist.clients.*')],
            ['divider' => true],
            ['icon' => 'presentation-chart-bar', 'label' => 'Estadísticas Financieras', 'route' => 'viticulturist.financial-stats',               'active' => request()->routeIs('viticulturist.financial-stats')],
            ['icon' => 'document-check',         'label' => 'VeriFactu',                'route' => 'viticulturist.verifactu.index',               'active' => request()->routeIs('viticulturist.verifactu*'), 'wip' => true, 'new' => true],
        ];

        // ── Rail bottom ───────────────────────────────────────────────────────
        $menu['rail_bottom'] = [
            ['icon' => 'cog-6-tooth',         'label' => 'Configuración', 'route' => 'viticulturist.settings',      'active' => request()->routeIs('viticulturist.settings')],
            ['icon' => 'question-mark-circle','label' => 'Soporte',       'route' => 'viticulturist.support.index', 'active' => request()->routeIs('viticulturist.support.*'),
             'badge' => Cache::remember("nav_badge_support_{$user->id}", 120, fn() => $user->supportTickets()->open()->count())],
        ];

        return $menu;
    }

    // ── Secciones compartidas (usadas también por ProducerMenu) ───────────────

    public static function cuadernoInputs(string $prefix, string $vendimiaLabel = 'Vendimia'): array
    {
        return [
            ['icon' => 'book-open',          'label' => 'Cuaderno Digital',    'route' => "{$prefix}.digital-notebook",                     'active' => request()->routeIs("{$prefix}.digital-notebook") && !request()->routeIs("{$prefix}.digital-notebook.*")],
            ['divider' => true],
            ['icon' => 'shield-exclamation', 'label' => 'Tratamientos',        'route' => "{$prefix}.digital-notebook.treatment.index",     'active' => request()->routeIs("{$prefix}.digital-notebook.treatment.*")],
            ['icon' => 'funnel',             'label' => 'Fertilizaciones',     'route' => "{$prefix}.digital-notebook.fertilization.index", 'active' => request()->routeIs("{$prefix}.digital-notebook.fertilization.*")],
            ['icon' => 'cloud',              'label' => 'Riegos',              'route' => "{$prefix}.digital-notebook.irrigation.index",    'active' => request()->routeIs("{$prefix}.digital-notebook.irrigation.*")],
            ['icon' => 'wrench-screwdriver', 'label' => 'Labores Culturales',  'route' => "{$prefix}.digital-notebook.cultural.index",      'active' => request()->routeIs("{$prefix}.digital-notebook.cultural.*")],
            ['icon' => 'eye',                'label' => 'Observaciones',       'route' => "{$prefix}.digital-notebook.observation.index",   'active' => request()->routeIs("{$prefix}.digital-notebook.observation.*")],
            ['icon' => 'sun',                'label' => 'Fenología',           'route' => "{$prefix}.phenology.index",                      'active' => request()->routeIs("{$prefix}.phenology.*")],
            ['icon' => 'scissors',           'label' => 'Podas',               'route' => "{$prefix}.digital-notebook.pruning.index",       'active' => request()->routeIs("{$prefix}.digital-notebook.pruning.*")],
            ['icon' => 'archive-box',        'label' => 'Post-Vendimia',       'route' => "{$prefix}.digital-notebook.post-harvest.index",  'active' => request()->routeIs("{$prefix}.digital-notebook.post-harvest.*")],
            ['icon' => 'chart-bar-square',   'label' => 'Rendimientos Estimados', 'route' => "{$prefix}.digital-notebook.estimated-yields.index", 'active' => request()->routeIs("{$prefix}.digital-notebook.estimated-yields.*")],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down', 'label' => $vendimiaLabel,      'route' => "{$prefix}.harvests.index",        'active' => request()->routeIs("{$prefix}.harvests.*")],
            ['icon' => 'bug-ant',                'label' => 'Gestión de Plagas', 'route' => "{$prefix}.pest-management.index", 'active' => request()->routeIs("{$prefix}.pest-management.*")],
        ];
    }

    public static function registrosOficiales(string $prefix): array
    {
        return [
            ['icon' => 'chart-bar',                'label' => 'Cumplimiento Cuaderno',    'route' => "{$prefix}.pac-compliance",                'active' => request()->routeIs("{$prefix}.pac-compliance")],
            ['divider' => true],
            ['icon' => 'clipboard-document-check', 'label' => 'Análisis de Residuos',     'route' => "{$prefix}.residue-analyses.index",        'active' => request()->routeIs("{$prefix}.residue-analyses.*")],
            ['icon' => 'trash',                    'label' => 'Gestión de Residuos',      'route' => "{$prefix}.residue-managements.index",     'active' => request()->routeIs("{$prefix}.residue-managements.*")],
            ['icon' => 'bolt',                     'label' => 'Consumo Energético',       'route' => "{$prefix}.energy-usages.index",           'active' => request()->routeIs("{$prefix}.energy-usages.*")],
            ['icon' => 'beaker',                   'label' => 'Registro de Agua',         'route' => "{$prefix}.water-concessions.index",       'active' => request()->routeIs("{$prefix}.water-concessions.*")],
            ['icon' => 'funnel',                   'label' => 'Plan de Fertilización',    'route' => "{$prefix}.fertilization-plans.index",     'active' => request()->routeIs("{$prefix}.fertilization-plans.*")],
            ['divider' => true],
            ['icon' => 'archive-box-x-mark',       'label' => 'Envases Fitosanitarios',   'route' => "{$prefix}.container-returns.index",       'active' => request()->routeIs("{$prefix}.container-returns.*")],
            ['icon' => 'document-arrow-up',        'label' => 'Declaración de Vendimia',  'route' => "{$prefix}.harvest-declarations.index",    'active' => request()->routeIs("{$prefix}.harvest-declarations.*")],
            ['icon' => 'cube-transparent',         'label' => 'Subproductos Vendimia',    'route' => "{$prefix}.harvest-byproducts.index",      'active' => request()->routeIs("{$prefix}.harvest-byproducts.*")],
            ['divider' => true],
            ['icon' => 'shield-check',             'label' => 'Certificaciones y Sellos', 'route' => "{$prefix}.certifications.index",          'active' => request()->routeIs("{$prefix}.certifications.*")],
            ['icon' => 'arrow-up-tray',            'label' => 'Exportaciones CUE',        'route' => "{$prefix}.cue-exports.index",             'active' => request()->routeIs("{$prefix}.cue-exports.*")],
            ['icon' => 'document',                 'label' => 'Informes Oficiales',       'route' => "{$prefix}.official-reports.index",        'active' => request()->routeIs("{$prefix}.official-reports.*")],
        ];
    }

    public static function resources(string $prefix, bool $includeContainers = false): array
    {
        $items = [
            ['icon' => 'user-group',           'label' => 'Personal',   'route' => "{$prefix}.personal.index",  'active' => request()->routeIs("{$prefix}.personal*")],
            ['icon' => 'adjustments-vertical', 'label' => 'Maquinaria', 'route' => "{$prefix}.machinery.index", 'active' => request()->routeIs("{$prefix}.machinery*")],
        ];

        if ($includeContainers) {
            $items[] = ['icon' => 'cube', 'label' => 'Contenedores', 'route' => "{$prefix}.containers.index", 'active' => request()->routeIs("{$prefix}.containers.*")];
        }

        return array_merge($items, [
            ['icon' => 'building-storefront', 'label' => 'Almacén de Insumos',       'route' => "{$prefix}.warehouse.index",               'active' => request()->routeIs("{$prefix}.warehouse.*")],
            ['icon' => 'beaker',              'label' => 'Productos Fitosanitarios', 'route' => "{$prefix}.phytosanitary-products.index", 'active' => request()->routeIs("{$prefix}.phytosanitary-products.*")],
            ['icon' => 'user-plus',           'label' => 'Subcontratación',          'route' => "{$prefix}.subcontracting.index",         'active' => request()->routeIs("{$prefix}.subcontracting*")],
        ]);
    }

    public static function compliance(string $prefix): array
    {
        return [
            ['icon' => 'building-office', 'label' => 'Explotación SIEX/REA',       'route' => "{$prefix}.exploitations.index",             'active' => request()->routeIs("{$prefix}.exploitations.*")],
            ['icon' => 'shield-check',    'label' => 'Autorizaciones Comerciales',  'route' => "{$prefix}.commercial-authorizations.index", 'active' => request()->routeIs("{$prefix}.commercial-authorizations.*")],
            ['icon' => 'user',            'label' => 'Asesorías Técnicas',          'route' => "{$prefix}.advisory-memberships.index",      'active' => request()->routeIs("{$prefix}.advisory-memberships.*")],
            ['icon' => 'identification',  'label' => 'Aplicadores ROPO',            'route' => "{$prefix}.field-applicators.index",         'active' => request()->routeIs("{$prefix}.field-applicators.*")],
            ['icon' => 'cog-8-tooth',     'label' => 'Equipos ITB/ITEA',            'route' => "{$prefix}.field-equipment.index",           'active' => request()->routeIs("{$prefix}.field-equipment.*")],
            ['icon' => 'lifebuoy',        'label' => 'Seguros Agrarios',            'route' => "{$prefix}.agri-insurance.index",            'active' => request()->routeIs("{$prefix}.agri-insurance*"), 'new' => true],
        ];
    }

    public static function pac(string $prefix): array
    {
        return [
            ['icon' => 'chart-pie',    'label' => 'Resumen PAC',           'route' => "{$prefix}.pac.dashboard",          'active' => request()->routeIs("{$prefix}.pac.dashboard")],
            ['icon' => 'check-circle', 'label' => 'Superficies Elegibles', 'route' => "{$prefix}.pac.surfaces.index",     'active' => request()->routeIs("{$prefix}.pac.surfaces.*")],
            ['icon' => 'document-text','label' => 'Declaraciones',         'route' => "{$prefix}.pac.declarations.index", 'active' => request()->routeIs("{$prefix}.pac.declarations.*")],
            ['icon' => 'sparkles',     'label' => 'Eco-regímenes',         'route' => "{$prefix}.pac.eco-schemes.index",  'active' => request()->routeIs("{$prefix}.pac.eco-schemes.*")],
            ['icon' => 'banknotes',    'label' => 'Historial de Ayudas',   'route' => "{$prefix}.pac.payments.index",     'active' => request()->routeIs("{$prefix}.pac.payments.*")],
        ];
    }
}
