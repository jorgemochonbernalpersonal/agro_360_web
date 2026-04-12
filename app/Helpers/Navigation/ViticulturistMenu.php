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
        ];

        // ── Campaña ───────────────────────────────────────────────────────────
        $menu['campaigns'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Campañas',              'route' => 'viticulturist.campaign.index',           'active' => request()->routeIs('viticulturist.campaign.*')],
            ['icon' => 'folder-open',             'label' => 'Documentos de Campaña', 'route' => 'viticulturist.campaign-documents.index', 'active' => request()->routeIs('viticulturist.campaign-documents.*')],
            ['icon' => 'check-badge',             'label' => 'Firma y Cierre',        'route' => 'viticulturist.campaign-sign.index',       'active' => request()->routeIs('viticulturist.campaign-sign.*')],
            ['divider' => true],
            ['icon' => 'queue-list',              'label' => 'Plan de Trabajos',         'route' => 'viticulturist.planned-works.index',       'active' => request()->routeIs('viticulturist.planned-works.*'), 'new' => true],
            ['icon' => 'chart-bar-square',        'label' => 'Comparativa de Campañas', 'route' => 'viticulturist.campaign-comparison',        'active' => request()->routeIs('viticulturist.campaign-comparison'), 'new' => true],
        ];

        // ── Relación con Bodega ───────────────────────────────────────────────
        $menu['winery_rel'] = [
            ['icon' => 'megaphone',              'label' => 'Avisos de Bodegas',          'route' => 'viticulturist.announcements',         'active' => request()->routeIs('viticulturist.announcements')],
            ['icon' => 'chat-bubble-left-right', 'label' => 'Comunicación con Bodega',    'route' => 'viticulturist.winery-messages.index', 'active' => request()->routeIs('viticulturist.winery-messages*')],
            ['icon' => 'lock-closed',            'label' => 'Acceso Bodegas al Cuaderno', 'route' => 'viticulturist.winery-access.index',  'active' => request()->routeIs('viticulturist.winery-access*'),
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

        // ── Cuaderno de Campo ─────────────────────────────────────────────────
        $menu['notebook_inputs'] = self::notebookInputs('viticulturist');

        // ── Seguimiento (cumplimiento + plagas) ───────────────────────────────
        $menu['monitoring'] = self::monitoring('viticulturist');

        // ── Registros Medioambientales ────────────────────────────────────────
        $menu['environmental'] = self::environmental('viticulturist');

        // ── Declaraciones y Certificaciones ───────────────────────────────────
        $menu['declarations'] = self::officialDeclarations('viticulturist');

        // ── Finca (geografía + actividades) ───────────────────────────────────
        $menu['estate'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'plots.index',                          'active' => request()->routeIs('plots.*') && !request()->routeIs('plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',                'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',              'route' => 'sigpac.codes',                         'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',                      'active' => request()->routeIs('plots.territory')],
            ['divider' => true],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'viticulturist.field-activities.index', 'active' => request()->routeIs('viticulturist.field-activities*')],
        ];

        // ── Análisis de Finca ─────────────────────────────────────────────────
        $menu['analytics'] = [
            ['icon' => 'globe-alt',         'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',              'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'cloud',             'label' => 'Meteorología',        'route' => 'viticulturist.meteorology.index',       'active' => request()->routeIs('viticulturist.meteorology*')],
            ['icon' => 'viewfinder-circle', 'label' => 'Entorno de Parcelas', 'route' => 'viticulturist.plot-environments.index', 'active' => request()->routeIs('viticulturist.plot-environments.*')],
            ['divider' => true],
            ['icon' => 'beaker',            'label' => 'Análisis de Suelo',   'route' => 'viticulturist.soil-analyses.index',     'active' => request()->routeIs('viticulturist.soil-analyses.*'), 'new' => true],
        ];

        // ── Recursos ──────────────────────────────────────────────────────────
        $menu['resources'] = self::resources('viticulturist', includeContainers: true);

        // ── Normativa regulatoria ─────────────────────────────────────────────
        $menu['compliance'] = self::compliance('viticulturist');

        // ── PAC ───────────────────────────────────────────────────────────────
        $menu['pac'] = self::pac('viticulturist');

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

    public static function notebookInputs(string $prefix, string $harvestLabel = 'Vendimia'): array
    {
        return [
            ['icon' => 'book-open',          'label' => 'Cuaderno Digital',       'route' => "{$prefix}.digital-notebook",                          'active' => request()->routeIs("{$prefix}.digital-notebook") && !request()->routeIs("{$prefix}.digital-notebook.*")],
            ['divider' => true],
            ['icon' => 'shield-exclamation', 'label' => 'Tratamientos',           'route' => "{$prefix}.digital-notebook.treatment.index",          'active' => request()->routeIs("{$prefix}.digital-notebook.treatment.*")],
            ['icon' => 'funnel',             'label' => 'Fertilizaciones',        'route' => "{$prefix}.digital-notebook.fertilization.index",      'active' => request()->routeIs("{$prefix}.digital-notebook.fertilization.*")],
            ['icon' => 'arrows-pointing-in', 'label' => 'Riegos',                 'route' => "{$prefix}.digital-notebook.irrigation.index",         'active' => request()->routeIs("{$prefix}.digital-notebook.irrigation.*")],
            ['icon' => 'wrench-screwdriver', 'label' => 'Labores Culturales',     'route' => "{$prefix}.digital-notebook.cultural.index",           'active' => request()->routeIs("{$prefix}.digital-notebook.cultural.*")],
            ['icon' => 'eye',                'label' => 'Observaciones',          'route' => "{$prefix}.digital-notebook.observation.index",        'active' => request()->routeIs("{$prefix}.digital-notebook.observation.*")],
            ['icon' => 'sun',                'label' => 'Fenología',              'route' => "{$prefix}.phenology.index",                           'active' => request()->routeIs("{$prefix}.phenology.*")],
            ['icon' => 'scissors',           'label' => 'Podas',                  'route' => "{$prefix}.digital-notebook.pruning.index",            'active' => request()->routeIs("{$prefix}.digital-notebook.pruning.*")],
            ['icon' => 'archive-box',        'label' => 'Post-Vendimia',          'route' => "{$prefix}.digital-notebook.post-harvest.index",       'active' => request()->routeIs("{$prefix}.digital-notebook.post-harvest.*")],
            ['icon' => 'chart-bar-square',   'label' => 'Rendimientos Estimados', 'route' => "{$prefix}.digital-notebook.estimated-yields.index",   'active' => request()->routeIs("{$prefix}.digital-notebook.estimated-yields.*")],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down', 'label' => $harvestLabel, 'route' => "{$prefix}.harvests.index", 'active' => request()->routeIs("{$prefix}.harvests.*")],
        ];
    }

    /**
     * Seguimiento: cumplimiento del cuaderno + gestión de plagas.
     */
    public static function monitoring(string $prefix): array
    {
        return [
            ['icon' => 'chart-bar',  'label' => 'Cumplimiento Cuaderno',   'route' => "{$prefix}.pac-compliance",              'active' => request()->routeIs("{$prefix}.pac-compliance")],
            ['icon' => 'bug-ant',    'label' => 'Gestión de Plagas',       'route' => "{$prefix}.pest-management.index",       'active' => request()->routeIs("{$prefix}.pest-management.*")],
            ['icon' => 'bell-alert', 'label' => 'Alertas Fitosanitarias',  'route' => "{$prefix}.phytosanitary-alerts.index",  'active' => request()->routeIs("{$prefix}.phytosanitary-alerts.*"), 'new' => true],
        ];
    }

    /**
     * Registros medioambientales: residuos, energía, agua, fertilización, envases.
     */
    public static function environmental(string $prefix): array
    {
        return [
            ['icon' => 'clipboard-document-check', 'label' => 'Análisis de Residuos',      'route' => "{$prefix}.residue-analyses.index",       'active' => request()->routeIs("{$prefix}.residue-analyses.*")],
            ['icon' => 'trash',                    'label' => 'Gestión de Residuos',       'route' => "{$prefix}.residue-managements.index",    'active' => request()->routeIs("{$prefix}.residue-managements.*")],
            ['icon' => 'bolt',                     'label' => 'Consumo Energético',        'route' => "{$prefix}.energy-usages.index",          'active' => request()->routeIs("{$prefix}.energy-usages.*")],
            ['icon' => 'academic-cap',             'label' => 'Registro de Agua',          'route' => "{$prefix}.water-concessions.index",      'active' => request()->routeIs("{$prefix}.water-concessions.*")],
            ['icon' => 'calculator',               'label' => 'Plan de Fertilización',     'route' => "{$prefix}.fertilization-plans.index",    'active' => request()->routeIs("{$prefix}.fertilization-plans.*")],
            ['icon' => 'archive-box-x-mark',       'label' => 'Envases Fitosanitarios',    'route' => "{$prefix}.container-returns.index",      'active' => request()->routeIs("{$prefix}.container-returns.*")],
            ['divider' => true],
            ['icon' => 'sparkles',                 'label' => 'Biodiversidad y Cubiertas', 'route' => "{$prefix}.biodiversity-records.index",   'active' => request()->routeIs("{$prefix}.biodiversity-records.*"), 'new' => true],
        ];
    }

    /**
     * Declaraciones oficiales, certificaciones y exportaciones.
     */
    public static function officialDeclarations(string $prefix): array
    {
        return [
            ['icon' => 'inbox-arrow-down',   'label' => 'Declaración de Vendimia',  'route' => "{$prefix}.harvest-declarations.index", 'active' => request()->routeIs("{$prefix}.harvest-declarations.*")],
            ['icon' => 'cube-transparent',   'label' => 'Subproductos Vendimia',    'route' => "{$prefix}.harvest-byproducts.index",   'active' => request()->routeIs("{$prefix}.harvest-byproducts.*")],
            ['icon' => 'arrow-trending-up',  'label' => 'Trazabilidad de Uva',      'route' => "{$prefix}.grape-traceability",         'active' => request()->routeIs("{$prefix}.grape-traceability"), 'new' => true],
            ['divider' => true],
            ['icon' => 'star',               'label' => 'Certificaciones y Sellos', 'route' => "{$prefix}.certifications.index",       'active' => request()->routeIs("{$prefix}.certifications.*")],
            ['icon' => 'arrow-up-tray',      'label' => 'Exportaciones CUE',        'route' => "{$prefix}.cue-exports.index",          'active' => request()->routeIs("{$prefix}.cue-exports.*")],
            ['icon' => 'document',           'label' => 'Informes Oficiales',       'route' => "{$prefix}.official-reports.index",     'active' => request()->routeIs("{$prefix}.official-reports.*")],
        ];
    }

    /**
     * Todos los registros oficiales combinados (usado por ProducerMenu).
     */
    public static function officialRecords(string $prefix): array
    {
        return array_merge(
            self::monitoring($prefix),
            [['divider' => true]],
            self::environmental($prefix),
            [['divider' => true]],
            self::officialDeclarations($prefix),
        );
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
            ['icon' => 'building-storefront', 'label' => 'Almacén de Insumos',       'route' => "{$prefix}.warehouse.index",              'active' => request()->routeIs("{$prefix}.warehouse.*")],
            ['icon' => 'beaker',              'label' => 'Productos Fitosanitarios', 'route' => "{$prefix}.phytosanitary-products.index", 'active' => request()->routeIs("{$prefix}.phytosanitary-products.*")],
            ['icon' => 'user-plus',           'label' => 'Subcontratación',          'route' => "{$prefix}.subcontracting.index",         'active' => request()->routeIs("{$prefix}.subcontracting*")],
        ]);
    }

    public static function normativa(string $prefix): array
    {
        return array_merge(
            self::compliance($prefix),
            [['divider' => true]],
            self::pac($prefix),
        );
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
