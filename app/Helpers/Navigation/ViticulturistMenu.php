<?php

namespace App\Helpers\Navigation;

use App\Models\NotebookAccessRequest;
use App\Models\OnboardingProgress;
use App\Models\Plot;
use Illuminate\Support\Facades\Cache;

class ViticulturistMenu
{
    public static function build($user): array
    {
        $menu = [];

        // ── Determinar acceso al plan ─────────────────────────────────────────
        $isLocked   = !$user->hasActiveAccess();
        $hasWinery  = $user->hasWinery();

        // Ocultar secciones avanzadas hasta que el usuario tenga al menos una parcela
        $hasPlots = Cache::remember("nav_has_plots_{$user->id}", 120, fn () =>
            Plot::forUser($user)->exists()
        );

        // ── Main ─────────────────────────────────────────────────────────────
        $menu['main'] = [
            ['icon' => 'home',         'label' => 'Dashboard',      'route' => 'viticulturist.dashboard',           'active' => request()->routeIs('viticulturist.dashboard')],
            ['icon' => 'calendar-days','label' => 'Calendario',     'route' => 'viticulturist.calendar',            'active' => request()->routeIs('viticulturist.calendar')],
            ['icon' => 'bolt',         'label' => 'Entrada Rápida', 'route' => 'viticulturist.quick-entry',         'active' => request()->routeIs('viticulturist.quick-entry')],
        ];

        // ── Campaña ───────────────────────────────────────────────────────────
        $menu['campaigns'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Campañas',              'route' => 'viticulturist.campaign.index',           'active' => request()->routeIs('viticulturist.campaign.*')],
            ['icon' => 'folder-open',             'label' => 'Documentos de Campaña', 'route' => 'viticulturist.campaign-documents.index', 'active' => request()->routeIs('viticulturist.campaign-documents.*'), 'locked' => $isLocked],
            ['icon' => 'check-badge',             'label' => 'Firma y Cierre',        'route' => 'viticulturist.campaign-sign.index',       'active' => request()->routeIs('viticulturist.campaign-sign.*'),       'locked' => $isLocked],
            ['divider' => true],
            ['icon' => 'queue-list',              'label' => 'Plan de Trabajos',        'route' => 'viticulturist.planned-works.index',       'active' => request()->routeIs('viticulturist.planned-works.*'),       'locked' => $isLocked],
            ['icon' => 'chart-bar-square',        'label' => 'Comparativa de Campañas','route' => 'viticulturist.campaign-comparison',        'active' => request()->routeIs('viticulturist.campaign-comparison'),   'locked' => $isLocked],
        ];

        // ── Relación con Bodega (solo si tiene bodegas vinculadas) ───────────
        if ($hasWinery) {
            $menu['winery_rel'] = [
                ['icon' => 'megaphone',              'label' => 'Avisos de Bodegas',          'route' => 'viticulturist.announcements',         'active' => request()->routeIs('viticulturist.announcements')],
                ['icon' => 'chat-bubble-left-right', 'label' => 'Comunicación con Bodega',    'route' => 'viticulturist.winery-messages.index', 'active' => request()->routeIs('viticulturist.winery-messages*'), 'locked' => $isLocked],
                ['icon' => 'lock-closed',            'label' => 'Acceso Bodegas al Cuaderno', 'route' => 'viticulturist.winery-access.index',  'active' => request()->routeIs('viticulturist.winery-access*'),
                 'badge' => Cache::remember("nav_badge_notebook_access_{$user->id}", 120, fn() =>
                    NotebookAccessRequest::where('viticulturist_id', $user->id)->where('status', NotebookAccessRequest::STATUS_PENDING)->count()
                 )],
            ];
        }

        // ── Denominación (solo si está adscrito) ──────────────────────────────
        if ($user->hasSupervisor()) {
            $menu['denomination'] = [
                ['icon' => 'building-office-2', 'label' => 'Mi Denominación', 'route' => 'viticulturist.denomination.index', 'active' => request()->routeIs('viticulturist.denomination.*')],
            ];
        }

        // ── Finca (geografía + actividades) ───────────────────────────────────
        $menu['estate'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',            'route' => 'plots.index',                          'active' => request()->routeIs('plots.*') && !request()->routeIs('plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',        'route' => 'plots.plantings.index',                'active' => request()->routeIs('plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',              'route' => 'sigpac.codes',                         'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial', 'route' => 'plots.territory',                      'active' => request()->routeIs('plots.territory')],
            ['divider' => true],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'viticulturist.field-activities.index', 'active' => request()->routeIs('viticulturist.field-activities*'), 'locked' => $isLocked],
        ];

        if ($hasPlots) {
            // ── Cuaderno de Campo ─────────────────────────────────────────────────
            $menu['notebook_inputs'] = self::notebookInputs('viticulturist', 'Vendimia', $isLocked);

            // ── Seguimiento (cumplimiento + plagas) ───────────────────────────────
            $menu['monitoring'] = self::monitoring('viticulturist', $isLocked);

            // ── Análisis de Finca ─────────────────────────────────────────────────
            $menu['analytics'] = [
                ['icon' => 'globe-alt',         'label' => 'Teledetección',       'route' => 'remote-sensing.dashboard',              'active' => request()->routeIs('remote-sensing.*')],
                ['icon' => 'cloud',             'label' => 'Meteorología',        'route' => 'viticulturist.meteorology.index',       'active' => request()->routeIs('viticulturist.meteorology*'),       'locked' => $isLocked],
                ['icon' => 'viewfinder-circle', 'label' => 'Entorno de Parcelas', 'route' => 'viticulturist.plot-environments.index', 'active' => request()->routeIs('viticulturist.plot-environments.*'), 'locked' => $isLocked],
                ['divider' => true],
                ['icon' => 'beaker',            'label' => 'Análisis de Suelo',   'route' => 'viticulturist.soil-analyses.index',     'active' => request()->routeIs('viticulturist.soil-analyses.*'),     'locked' => $isLocked],
            ];

            // ── Recursos ──────────────────────────────────────────────────────────
            $menu['resources'] = self::resources('viticulturist', includeContainers: $hasWinery, locked: $isLocked);

            // ── Registros Medioambientales ────────────────────────────────────────
            $menu['environmental'] = self::environmental('viticulturist', $isLocked);

            // ── Declaraciones y Certificaciones ───────────────────────────────────
            $declarations = self::officialDeclarations('viticulturist', $isLocked);
            if (!$hasWinery) {
                $declarations = array_values(array_filter($declarations, fn($item) =>
                    !isset($item['label']) || $item['label'] !== 'Trazabilidad de Uva'
                ));
            }
            $menu['declarations'] = $declarations;

            // ── Normativa regulatoria ─────────────────────────────────────────────
            $menu['compliance'] = self::compliance('viticulturist', $isLocked);

            // ── PAC ───────────────────────────────────────────────────────────────
            $menu['pac'] = self::pac('viticulturist', $isLocked);
        }

        // ── Negocio (solo si tiene parcelas — sin cosecha no hay facturas) ────────
        if ($hasPlots) {
            $billingItems = [
                ['icon' => 'document-arrow-up', 'label' => 'Facturas Venta Cosecha', 'route' => 'viticulturist.invoices.harvest-sale.index', 'active' => request()->routeIs('viticulturist.invoices.harvest-sale*'), 'locked' => $isLocked],
            ];

            if ($hasWinery) {
                $billingItems[] = ['icon' => 'document-arrow-down', 'label' => 'Liquidaciones de Bodega', 'route' => 'viticulturist.invoices.grape-purchase.index', 'active' => request()->routeIs('viticulturist.invoices.grape-purchase*'), 'locked' => $isLocked];
            }

            $billingItems[] = ['divider' => true];

            if ($hasWinery) {
                $billingItems[] = ['icon' => 'shopping-cart', 'label' => 'Cosecha Comercializada', 'route' => 'viticulturist.marketed-harvests.index', 'active' => request()->routeIs('viticulturist.marketed-harvests.*'), 'locked' => $isLocked];
            }

            $billingItems = array_merge($billingItems, [
                ['icon' => 'table-cells',            'label' => 'Costes por Parcela',       'route' => 'viticulturist.plot-costs.index',  'active' => request()->routeIs('viticulturist.plot-costs*'),         'locked' => $isLocked],
                ['icon' => 'users',                  'label' => 'Clientes',                 'route' => 'viticulturist.clients.index',     'active' => request()->routeIs('viticulturist.clients.*'),           'locked' => $isLocked],
                ['divider' => true],
                ['icon' => 'presentation-chart-bar', 'label' => 'Estadísticas Financieras', 'route' => 'viticulturist.financial-stats',   'active' => request()->routeIs('viticulturist.financial-stats'),     'locked' => $isLocked],
                ['icon' => 'document-check',         'label' => 'VeriFactu',                'route' => 'viticulturist.verifactu.index',   'active' => request()->routeIs('viticulturist.verifactu*'), 'wip' => true],
            ]);

            $menu['billing'] = $billingItems;
        }

        // ── Onboarding (solo mientras no esté completo) ───────────────────────
        $onboardingItems = self::onboardingSection($user);
        if (!empty($onboardingItems)) {
            $menu['onboarding'] = $onboardingItems;
        }

        // ── Rail bottom ───────────────────────────────────────────────────────
        $menu['rail_bottom'] = [
            ['icon' => 'cog-6-tooth',         'label' => 'Configuración', 'route' => 'viticulturist.settings',      'active' => request()->routeIs('viticulturist.settings')],
            ['icon' => 'question-mark-circle','label' => 'Soporte',       'route' => 'viticulturist.support.index', 'active' => request()->routeIs('viticulturist.support.*'),
             'badge' => Cache::remember("nav_badge_support_{$user->id}", 120, fn() => $user->supportTickets()->open()->count())],
        ];

        return $menu;
    }

    // ── Sección onboarding ────────────────────────────────────────────────────

    /**
     * Devuelve los ítems de onboarding pendientes.
     * Retorna [] cuando el onboarding está completo → el capítulo desaparece solo.
     */
    private static function onboardingSection($user): array
    {
        // Cache de 60 s: se invalida cuando OnboardingChecklist llama a loadProgress()
        // (el componente hace forget del cache al completar pasos)
        $pendingSteps = Cache::remember(
            "nav_onboarding_pending_{$user->id}",
            60,
            function () use ($user) {
                if (OnboardingProgress::isOnboardingComplete($user->id)) {
                    return null; // null = onboarding completo
                }

                // Una sola query para obtener todos los registros existentes
                $progresses = OnboardingProgress::where('user_id', $user->id)
                    ->whereIn('step', OnboardingProgress::ALL_STEPS)
                    ->get()
                    ->keyBy('step');

                $pending = [];
                foreach (OnboardingProgress::ALL_STEPS as $step) {
                    $record = $progresses->get($step);
                    if (!$record || !$record->isCompleted()) {
                        $pending[] = $step;
                    }
                }

                return $pending;
            }
        );

        if ($pendingSteps === null || empty($pendingSteps)) {
            return [];
        }

        $prefix = 'viticulturist';
        $stepMap = [
            OnboardingProgress::STEP_REVIEW_CAMPAIGN   => ['icon' => 'calendar-days', 'label' => 'Revisa tu campaña',        'route' => "{$prefix}.campaign.index"],
            OnboardingProgress::STEP_CREATE_PLOT       => ['icon' => 'map',           'label' => 'Añade tus parcelas',       'route' => 'plots.create'],
            OnboardingProgress::STEP_REGISTER_ACTIVITY => ['icon' => 'bolt',          'label' => 'Registra una actividad',   'route' => "{$prefix}.quick-entry"],
            OnboardingProgress::STEP_ADD_PRODUCTS      => ['icon' => 'beaker',        'label' => 'Añade productos fitosan.', 'route' => "{$prefix}.phytosanitary-products.index"],
        ];

        $items = [];
        foreach ($pendingSteps as $step) {
            $cfg = $stepMap[$step] ?? null;
            if (!$cfg) continue;
            $items[] = [
                'icon'   => $cfg['icon'],
                'label'  => $cfg['label'],
                'route'  => $cfg['route'],
                'active' => request()->routeIs($cfg['route']),
            ];
        }

        return $items;
    }

    // ── Secciones compartidas (usadas también por ProducerMenu) ───────────────

    public static function notebookInputs(string $prefix, string $harvestLabel = 'Vendimia', bool $locked = false): array
    {
        return [
            ['icon' => 'book-open',          'label' => 'Cuaderno Digital',       'route' => "{$prefix}.digital-notebook",                          'active' => request()->routeIs("{$prefix}.digital-notebook") && !request()->routeIs("{$prefix}.digital-notebook.*")],
            ['divider' => true, 'label' => 'Día a día'],
            ['icon' => 'shield-exclamation', 'label' => 'Tratamientos',           'route' => "{$prefix}.digital-notebook.treatment.index",          'active' => request()->routeIs("{$prefix}.digital-notebook.treatment.*")],
            ['icon' => 'funnel',             'label' => 'Fertilizaciones',        'route' => "{$prefix}.digital-notebook.fertilization.index",      'active' => request()->routeIs("{$prefix}.digital-notebook.fertilization.*")],
            ['icon' => 'arrows-pointing-in', 'label' => 'Riegos',                 'route' => "{$prefix}.digital-notebook.irrigation.index",         'active' => request()->routeIs("{$prefix}.digital-notebook.irrigation.*")],
            ['icon' => 'wrench-screwdriver', 'label' => 'Labores Culturales',     'route' => "{$prefix}.digital-notebook.cultural.index",           'active' => request()->routeIs("{$prefix}.digital-notebook.cultural.*")],
            ['divider' => true, 'label' => 'Seguimiento'],
            ['icon' => 'eye',                'label' => 'Observaciones',          'route' => "{$prefix}.digital-notebook.observation.index",        'active' => request()->routeIs("{$prefix}.digital-notebook.observation.*")],
            ['icon' => 'sun',                'label' => 'Fenología',              'route' => "{$prefix}.phenology.index",                           'active' => request()->routeIs("{$prefix}.phenology.*")],
            ['divider' => true, 'label' => 'Ciclo'],
            ['icon' => 'scissors',           'label' => 'Podas',                  'route' => "{$prefix}.digital-notebook.pruning.index",            'active' => request()->routeIs("{$prefix}.digital-notebook.pruning.*")],
            ['icon' => 'archive-box',        'label' => 'Post-Vendimia',          'route' => "{$prefix}.digital-notebook.post-harvest.index",       'active' => request()->routeIs("{$prefix}.digital-notebook.post-harvest.*")],
            ['icon' => 'chart-bar-square',   'label' => 'Rendimientos Estimados', 'route' => "{$prefix}.digital-notebook.estimated-yields.index",   'active' => request()->routeIs("{$prefix}.digital-notebook.estimated-yields.*")],
            ['divider' => true, 'label' => 'Cosecha'],
            ['icon' => 'archive-box-arrow-down', 'label' => $harvestLabel, 'route' => "{$prefix}.harvests.index", 'active' => request()->routeIs("{$prefix}.harvests.*")],
        ];
    }

    /**
     * Seguimiento: cumplimiento del cuaderno + gestión de plagas.
     */
    public static function monitoring(string $prefix, bool $locked = false): array
    {
        return [
            ['icon' => 'chart-bar',  'label' => 'Cumplimiento Cuaderno',   'route' => "{$prefix}.pac-compliance",              'active' => request()->routeIs("{$prefix}.pac-compliance"),             'locked' => $locked],
            ['icon' => 'bug-ant',    'label' => 'Gestión de Plagas',       'route' => "{$prefix}.pest-management.index",       'active' => request()->routeIs("{$prefix}.pest-management.*")],
            ['icon' => 'bell-alert', 'label' => 'Alertas Fitosanitarias',  'route' => "{$prefix}.phytosanitary-alerts.index",  'active' => request()->routeIs("{$prefix}.phytosanitary-alerts.*"),     'locked' => $locked],
        ];
    }

    /**
     * Registros medioambientales: residuos, energía, agua, fertilización, envases.
     */
    public static function environmental(string $prefix, bool $locked = false): array
    {
        return [
            ['icon' => 'clipboard-document-check', 'label' => 'Análisis de Residuos',      'route' => "{$prefix}.residue-analyses.index",       'active' => request()->routeIs("{$prefix}.residue-analyses.*"),      'locked' => $locked],
            ['icon' => 'trash',                    'label' => 'Gestión de Residuos',       'route' => "{$prefix}.residue-managements.index",    'active' => request()->routeIs("{$prefix}.residue-managements.*"),   'locked' => $locked],
            ['icon' => 'archive-box-x-mark',       'label' => 'Envases Fitosanitarios',    'route' => "{$prefix}.container-returns.index",      'active' => request()->routeIs("{$prefix}.container-returns.*"),     'locked' => $locked],
            ['divider' => true, 'label' => 'Consumos'],
            ['icon' => 'bolt',                     'label' => 'Consumo Energético',        'route' => "{$prefix}.energy-usages.index",          'active' => request()->routeIs("{$prefix}.energy-usages.*"),         'locked' => $locked],
            ['icon' => 'academic-cap',             'label' => 'Registro de Agua',          'route' => "{$prefix}.water-concessions.index",      'active' => request()->routeIs("{$prefix}.water-concessions.*"),     'locked' => $locked],
            ['icon' => 'calculator',               'label' => 'Plan de Fertilización',     'route' => "{$prefix}.fertilization-plans.index",    'active' => request()->routeIs("{$prefix}.fertilization-plans.*"),   'locked' => $locked],
            ['divider' => true, 'label' => 'Ecosistema'],
            ['icon' => 'sparkles',                 'label' => 'Biodiversidad y Cubiertas', 'route' => "{$prefix}.biodiversity-records.index",   'active' => request()->routeIs("{$prefix}.biodiversity-records.*"),  'locked' => $locked],
        ];
    }

    /**
     * Declaraciones oficiales, certificaciones y exportaciones.
     */
    public static function officialDeclarations(string $prefix, bool $locked = false): array
    {
        return [
            ['icon' => 'inbox-arrow-down',   'label' => 'Declaración de Vendimia',  'route' => "{$prefix}.harvest-declarations.index", 'active' => request()->routeIs("{$prefix}.harvest-declarations.*"), 'locked' => $locked],
            ['icon' => 'cube-transparent',   'label' => 'Subproductos Vendimia',    'route' => "{$prefix}.harvest-byproducts.index",   'active' => request()->routeIs("{$prefix}.harvest-byproducts.*"),   'locked' => $locked],
            ['icon' => 'arrow-trending-up',  'label' => 'Trazabilidad de Uva',      'route' => "{$prefix}.grape-traceability",         'active' => request()->routeIs("{$prefix}.grape-traceability"),     'locked' => $locked],
            ['divider' => true],
            ['icon' => 'star',               'label' => 'Certificaciones y Sellos', 'route' => "{$prefix}.certifications.index",       'active' => request()->routeIs("{$prefix}.certifications.*"),       'locked' => $locked],
            ['icon' => 'arrow-up-tray',      'label' => 'Exportaciones CUE',        'route' => "{$prefix}.cue-exports.index",          'active' => request()->routeIs("{$prefix}.cue-exports.*"),          'locked' => $locked],
            ['icon' => 'document',           'label' => 'Informes Oficiales',       'route' => "{$prefix}.official-reports.index",     'active' => request()->routeIs("{$prefix}.official-reports.*"),     'locked' => $locked],
        ];
    }

    /**
     * Todos los registros oficiales combinados (usado por ProducerMenu).
     */
    public static function officialRecords(string $prefix, bool $locked = false): array
    {
        return array_merge(
            self::monitoring($prefix, $locked),
            [['divider' => true]],
            self::environmental($prefix, $locked),
            [['divider' => true]],
            self::officialDeclarations($prefix, $locked),
        );
    }

    public static function resources(string $prefix, bool $includeContainers = false, bool $locked = false): array
    {
        $items = [
            ['icon' => 'user-group',           'label' => 'Personal',   'route' => "{$prefix}.personal.index",  'active' => request()->routeIs("{$prefix}.personal*"),   'locked' => $locked],
            ['icon' => 'adjustments-vertical', 'label' => 'Maquinaria', 'route' => "{$prefix}.machinery.index", 'active' => request()->routeIs("{$prefix}.machinery*"),   'locked' => $locked],
        ];

        if ($includeContainers) {
            $items[] = ['icon' => 'cube', 'label' => 'Contenedores', 'route' => "{$prefix}.containers.index", 'active' => request()->routeIs("{$prefix}.containers.*"), 'locked' => $locked];
        }

        return array_merge($items, [
            ['icon' => 'building-storefront', 'label' => 'Almacén de Insumos',       'route' => "{$prefix}.warehouse.index",              'active' => request()->routeIs("{$prefix}.warehouse.*"),              'locked' => $locked],
            ['icon' => 'beaker',              'label' => 'Productos Fitosanitarios', 'route' => "{$prefix}.phytosanitary-products.index", 'active' => request()->routeIs("{$prefix}.phytosanitary-products.*"), 'locked' => $locked],
            ['icon' => 'user-plus',           'label' => 'Subcontratación',          'route' => "{$prefix}.subcontracting.index",         'active' => request()->routeIs("{$prefix}.subcontracting*"),          'locked' => $locked],
        ]);
    }

    public static function normativa(string $prefix, bool $locked = false): array
    {
        return array_merge(
            self::compliance($prefix, $locked),
            [['divider' => true]],
            self::pac($prefix, $locked),
        );
    }

    public static function compliance(string $prefix, bool $locked = false): array
    {
        return [
            ['icon' => 'building-office', 'label' => 'Explotación SIEX/REA',       'route' => "{$prefix}.exploitations.index",             'active' => request()->routeIs("{$prefix}.exploitations.*"),             'locked' => $locked],
            ['icon' => 'shield-check',    'label' => 'Autorizaciones Comerciales',  'route' => "{$prefix}.commercial-authorizations.index", 'active' => request()->routeIs("{$prefix}.commercial-authorizations.*"), 'locked' => $locked],
            ['icon' => 'user',            'label' => 'Asesorías Técnicas',          'route' => "{$prefix}.advisory-memberships.index",      'active' => request()->routeIs("{$prefix}.advisory-memberships.*"),      'locked' => $locked],
            ['icon' => 'identification',  'label' => 'Aplicadores ROPO',            'route' => "{$prefix}.field-applicators.index",         'active' => request()->routeIs("{$prefix}.field-applicators.*"),         'locked' => $locked],
            ['icon' => 'cog-8-tooth',     'label' => 'Equipos ITB/ITEA',            'route' => "{$prefix}.field-equipment.index",           'active' => request()->routeIs("{$prefix}.field-equipment.*"),           'locked' => $locked],
            ['icon' => 'lifebuoy',        'label' => 'Seguros Agrarios',            'route' => "{$prefix}.agri-insurance.index",            'active' => request()->routeIs("{$prefix}.agri-insurance*"),             'locked' => $locked],
        ];
    }

    public static function pac(string $prefix, bool $locked = false): array
    {
        return [
            ['icon' => 'chart-pie',    'label' => 'Resumen PAC',           'route' => "{$prefix}.pac.dashboard",          'active' => request()->routeIs("{$prefix}.pac.dashboard"),          'locked' => $locked],
            ['icon' => 'check-circle', 'label' => 'Superficies Elegibles', 'route' => "{$prefix}.pac.surfaces.index",     'active' => request()->routeIs("{$prefix}.pac.surfaces.*"),          'locked' => $locked],
            ['icon' => 'document-text','label' => 'Declaraciones',         'route' => "{$prefix}.pac.declarations.index", 'active' => request()->routeIs("{$prefix}.pac.declarations.*"),      'locked' => $locked],
            ['icon' => 'sparkles',     'label' => 'Eco-regímenes',         'route' => "{$prefix}.pac.eco-schemes.index",  'active' => request()->routeIs("{$prefix}.pac.eco-schemes.*"),       'locked' => $locked],
            ['icon' => 'banknotes',    'label' => 'Historial de Ayudas',   'route' => "{$prefix}.pac.payments.index",     'active' => request()->routeIs("{$prefix}.pac.payments.*"),          'locked' => $locked],
        ];
    }
}
