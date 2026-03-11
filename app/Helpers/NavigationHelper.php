<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NavigationHelper
{
    /**
     * Obtener el menú de navegación según el rol del usuario.
     * La estructura del menú se reconstruye por request (contiene active states via routeIs).
     * Solo los contadores de BD (badges) se cachean brevemente.
     */
    public static function getMenu(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        return static::buildMenu($user);
    }

    /**
     * Construir el menú (método interno)
     */
    private static function buildMenu($user): array
    {
        $role = $user->role;

        // Producer delegates to viticulturist or winery based on active context
        if ($role === 'producer') {
            $role = session('producer_context', 'viticulturist');
        }

        $menu = [];

        // Producer always gets a home item pointing to the combined dashboard
        if ($user->role === 'producer') {
            $menu['main'][] = [
                'icon'   => 'squares-2x2',
                'label'  => 'Vista general',
                'route'  => 'producer.dashboard',
                'active' => request()->routeIs('producer.dashboard'),
            ];
        }

        if ($role === 'viticulturist') {
            // DASHBOARD - Siempre visible
            $menu['main'][] = [
                'icon' => 'home',
                'label' => 'Dashboard',
                'route' => 'viticulturist.dashboard',
                'active' => request()->routeIs('viticulturist.dashboard'),
            ];

            $menu['main'][] = [
                'icon'   => 'calendar-days',
                'label'  => 'Calendario',
                'route'  => 'viticulturist.calendar',
                'active' => request()->routeIs('viticulturist.calendar'),
            ];

            $menu['main'][] = [
                'icon'   => 'bell',
                'label'  => 'Notificaciones',
                'route'  => 'viticulturist.notifications.index',
                'active' => request()->routeIs('viticulturist.notifications*'),
                'wip'    => true,
                'new'    => true,
            ];

            // GRUPO: CAMPAÑA — gestión de campaña
            $menu['operations'] = [
                [
                    'icon'   => 'clipboard-document-list',
                    'label'  => 'Campañas',
                    'route'  => 'viticulturist.campaign.index',
                    'active' => request()->routeIs('viticulturist.campaign*') && !request()->routeIs('viticulturist.campaign-documents.*') && !request()->routeIs('viticulturist.campaign-sign.*'),
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
                    'wip'    => true,
                    'new'    => true,
                ],
            ];

            // GRUPO: CUADERNO DE CAMPO — entradas diarias
            $menu['cuaderno_inputs'] = [
                [
                    'icon'   => 'shield-exclamation',
                    'label'  => 'Tratamientos',
                    'route'  => 'viticulturist.digital-notebook.treatment.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.treatment.*'),
                ],
                [
                    'icon'   => 'funnel',
                    'label'  => 'Fertilizaciones',
                    'route'  => 'viticulturist.digital-notebook.fertilization.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.fertilization.*'),
                ],
                [
                    'icon'   => 'cloud',
                    'label'  => 'Riegos',
                    'route'  => 'viticulturist.digital-notebook.irrigation.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.irrigation.*'),
                ],
                [
                    'icon'   => 'wrench-screwdriver',
                    'label'  => 'Labores Culturales',
                    'route'  => 'viticulturist.digital-notebook.cultural.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.cultural.*'),
                ],
                [
                    'icon'   => 'eye',
                    'label'  => 'Observaciones',
                    'route'  => 'viticulturist.digital-notebook.observation.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.observation.*'),
                ],
                [
                    'icon'   => 'sun',
                    'label'  => 'Fenología',
                    'route'  => 'viticulturist.phenology.index',
                    'active' => request()->routeIs('viticulturist.phenology.*'),
                ],
                [
                    'icon'   => 'scissors',
                    'label'  => 'Podas',
                    'route'  => 'viticulturist.digital-notebook.pruning.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.pruning.*'),
                ],
                [
                    'icon'   => 'archive-box',
                    'label'  => 'Post-Vendimia',
                    'route'  => 'viticulturist.digital-notebook.post-harvest.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.post-harvest.*'),
                ],
                ['divider' => true],
                [
                    'icon'   => 'archive-box-arrow-down',
                    'label'  => 'Vendimia',
                    'route'  => 'viticulturist.harvests.index',
                    'active' => request()->routeIs('viticulturist.harvests.*'),
                ],
                [
                    'icon'   => 'bug-ant',
                    'label'  => 'Gestión de Plagas',
                    'route'  => 'viticulturist.pest-management.index',
                    'active' => request()->routeIs('viticulturist.pest-management.*'),
                ],
            ];

            // GRUPO: REGISTROS OFICIALES — outputs y compliance del cuaderno
            $menu['registros_oficiales'] = [
                [
                    'icon'   => 'chart-bar',
                    'label'  => 'Cumplimiento Cuaderno',
                    'route'  => 'viticulturist.pac-compliance',
                    'active' => request()->routeIs('viticulturist.pac-compliance'),
                ],
                [
                    'icon'   => 'clipboard-document-check',
                    'label'  => 'Análisis de Residuos',
                    'route'  => 'viticulturist.residue-analyses.index',
                    'active' => request()->routeIs('viticulturist.residue-analyses.*'),
                ],
                [
                    'icon'   => 'trash',
                    'label'  => 'Gestión de Residuos',
                    'route'  => 'viticulturist.residue-managements.index',
                    'active' => request()->routeIs('viticulturist.residue-managements.*'),
                ],
                [
                    'icon'   => 'bolt',
                    'label'  => 'Consumo Energético',
                    'route'  => 'viticulturist.energy-usages.index',
                    'active' => request()->routeIs('viticulturist.energy-usages.*'),
                ],
                [
                    'icon'   => 'arrow-up-tray',
                    'label'  => 'Exportaciones CUE',
                    'route'  => 'viticulturist.cue-exports.index',
                    'active' => request()->routeIs('viticulturist.cue-exports.*'),
                ],
                [
                    'icon'   => 'document',
                    'label'  => 'Informes Oficiales',
                    'route'  => 'viticulturist.official-reports.index',
                    'active' => request()->routeIs('viticulturist.official-reports.*'),
                ],
            ];

            // GRUPO: PARCELAS Y TERRITORIO
            $menu['plots_analysis'] = [
                [
                    'icon' => 'map',
                    'label' => 'Parcelas',
                    'route' => 'plots.index',
                    'active' => request()->routeIs('plots.*') && !request()->routeIs('plots.plantings.*'),
                ],
                [
                    'icon' => 'book-open',
                    'label' => 'Plantaciones',
                    'route' => 'plots.plantings.index',
                    'active' => request()->routeIs('plots.plantings.*'),
                ],
                [
                    'icon' => 'map-pin',
                    'label' => 'SIGPAC',
                    'route' => 'sigpac.codes',
                    'active' => request()->routeIs('sigpac.*'),
                ],
                [
                    'icon' => 'globe-alt',
                    'label' => 'Teledetección',
                    'route' => 'remote-sensing.dashboard',
                    'active' => request()->routeIs('remote-sensing.*'),
                ],
                [
                    'icon' => 'globe-europe-africa',
                    'label' => 'Gestión Territorial',
                    'route' => 'plots.territory',
                    'active' => request()->routeIs('plots.territory'),
                ],
                [
                    'icon' => 'beaker',
                    'label' => 'Entorno de Parcelas',
                    'route' => 'viticulturist.plot-environments.index',
                    'active' => request()->routeIs('viticulturist.plot-environments.*'),
                ],
            ];

            // GRUPO: NORMATIVA (solo registros legales fijos)
            $menu['compliance'] = [
                [
                    'icon' => 'building-office',
                    'label' => 'Explotación SIEX/REA',
                    'route' => 'viticulturist.exploitations.index',
                    'active' => request()->routeIs('viticulturist.exploitations.*'),
                ],
                [
                    'icon' => 'shield-check',
                    'label' => 'Autorizaciones Comerciales',
                    'route' => 'viticulturist.commercial-authorizations.index',
                    'active' => request()->routeIs('viticulturist.commercial-authorizations.*'),
                ],
                [
                    'icon' => 'user',
                    'label' => 'Asesorías Técnicas',
                    'route' => 'viticulturist.advisory-memberships.index',
                    'active' => request()->routeIs('viticulturist.advisory-memberships.*'),
                ],
                [
                    'icon' => 'identification',
                    'label' => 'Aplicadores ROPO',
                    'route' => 'viticulturist.field-applicators.index',
                    'active' => request()->routeIs('viticulturist.field-applicators.*'),
                ],
                [
                    'icon' => 'cog-6-tooth',
                    'label' => 'Equipos ITB/ITEA',
                    'route' => 'viticulturist.field-equipment.index',
                    'active' => request()->routeIs('viticulturist.field-equipment.*'),
                ],
                [
                    'icon'   => 'shield-exclamation',
                    'label'  => 'Seguros Agrarios',
                    'route'  => 'viticulturist.agri-insurance.index',
                    'active' => request()->routeIs('viticulturist.agri-insurance*'),
                    'wip'    => true,
                    'new'    => true,
                ],
            ];

            // GRUPO: PAC — Política Agrícola Común
            $menu['pac'] = [
                [
                    'icon'   => 'chart-pie',
                    'label'  => 'Resumen PAC',
                    'route'  => 'viticulturist.pac.dashboard',
                    'active' => request()->routeIs('viticulturist.pac.dashboard'),
                ],
                [
                    'icon'   => 'check-circle',
                    'label'  => 'Superficies Elegibles',
                    'route'  => 'viticulturist.pac.surfaces.index',
                    'active' => request()->routeIs('viticulturist.pac.surfaces.*'),
                ],
                [
                    'icon'   => 'document-text',
                    'label'  => 'Declaraciones',
                    'route'  => 'viticulturist.pac.declarations.index',
                    'active' => request()->routeIs('viticulturist.pac.declarations.*'),
                ],
                [
                    'icon'   => 'sparkles',
                    'label'  => 'Eco-regímenes',
                    'route'  => 'viticulturist.pac.eco-schemes.index',
                    'active' => request()->routeIs('viticulturist.pac.eco-schemes.*'),
                ],
                [
                    'icon'   => 'banknotes',
                    'label'  => 'Historial de Ayudas',
                    'route'  => 'viticulturist.pac.payments.index',
                    'active' => request()->routeIs('viticulturist.pac.payments.*'),
                ],
            ];

            // GRUPO: RECURSOS
            $menu['resources'] = [
                [
                    'icon' => 'user-group',
                    'label' => 'Personal',
                    'route' => 'viticulturist.personal.index',
                    'active' => request()->routeIs('viticulturist.personal*') || request()->routeIs('viticulturist.viticulturists.*'),
                ],
                [
                    'icon' => 'adjustments-vertical',
                    'label' => 'Maquinaria',
                    'route' => 'viticulturist.machinery.index',
                    'active' => request()->routeIs('viticulturist.machinery*'),
                ],
                [
                    'icon' => 'cube',
                    'label' => 'Contenedores',
                    'route' => 'viticulturist.containers.index',
                    'active' => request()->routeIs('viticulturist.containers.*'),
                ],
                [
                    'icon' => 'building-storefront',
                    'label' => 'Almacén de Insumos',
                    'route' => 'viticulturist.almacen.index',
                    'active' => request()->routeIs('viticulturist.almacen.*'),
                ],
                [
                    'icon' => 'beaker',
                    'label' => 'Productos Fitosanitarios',
                    'route' => 'viticulturist.phytosanitary-products.index',
                    'active' => request()->routeIs('viticulturist.phytosanitary-products.*'),
                ],
                [
                    'icon'   => 'user-plus',
                    'label'  => 'Subcontratación',
                    'route'  => 'viticulturist.subcontracting.index',
                    'active' => request()->routeIs('viticulturist.subcontracting*'),
                    'wip'    => true,
                    'new'    => true,
                ],
            ];

            // GRUPO: FACTURACIÓN Y CLIENTES
            $menu['billing'] = [
                [
                    'icon' => 'calculator',
                    'label' => 'Facturas',
                    'route' => 'viticulturist.invoices.index',
                    'active' => request()->routeIs('viticulturist.invoices.*'),
                ],
                [
                    'icon'   => 'document-check',
                    'label'  => 'VeriFactu',
                    'route'  => 'viticulturist.verifactu.index',
                    'active' => request()->routeIs('viticulturist.verifactu*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon' => 'shopping-cart',
                    'label' => 'Cosecha Comercializada',
                    'route' => 'viticulturist.marketed-harvests.index',
                    'active' => request()->routeIs('viticulturist.marketed-harvests.*'),
                ],
                [
                    'icon'   => 'table-cells',
                    'label'  => 'Costes por Parcela',
                    'route'  => 'viticulturist.plot-costs.index',
                    'active' => request()->routeIs('viticulturist.plot-costs*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon' => 'users',
                    'label' => 'Clientes',
                    'route' => 'viticulturist.clients.index',
                    'active' => request()->routeIs('viticulturist.clients.*'),
                ],
                [
                    'icon' => 'presentation-chart-bar',
                    'label' => 'Estadísticas Financieras',
                    'route' => 'viticulturist.financial-stats',
                    'active' => request()->routeIs('viticulturist.financial-stats'),
                ],
            ];

            // RAIL BOTTOM — acceso directo desde el rail (sin flyout)
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
        }

        if ($role === 'winery') {
            // ── MAIN ─────────────────────────────────────────────────
            $menu['main'][] = [
                'icon'   => 'home',
                'label'  => 'Dashboard',
                'route'  => 'winery.dashboard',
                'active' => request()->routeIs('winery.dashboard'),
            ];

            // ── VENDIMIA ─────────────────────────────────────────────
            $menu['harvest'] = [
                [
                    'icon'   => 'clipboard-document-list',
                    'label'  => 'Campañas de Vendimia',
                    'route'  => 'winery.campaigns.index',
                    'active' => request()->routeIs('winery.campaigns*'),
                ],
                [
                    'icon'   => 'user-group',
                    'label'  => 'Mis Viticultores',
                    'route'  => 'winery.viticulturists.index',
                    'active' => request()->routeIs('winery.viticulturists*'),
                ],
                ['divider' => true],
                [
                    'icon'   => 'chart-bar',
                    'label'  => 'Cuadro de Mando',
                    'route'  => 'winery.harvest-summary.index',
                    'active' => request()->routeIs('winery.harvest-summary*'),
                ],
                [
                    'icon'   => 'calculator',
                    'label'  => 'Aforos viticultores',
                    'route'  => 'winery.vitic-estimates.index',
                    'active' => request()->routeIs('winery.vitic-estimates*'),
                ],
                [
                    'icon'   => 'clipboard-document-list',
                    'label'  => 'Previsiones',
                    'route'  => 'winery.harvest-forecasts.index',
                    'active' => request()->routeIs('winery.harvest-forecasts*'),
                ],
                [
                    'icon'   => 'archive-box-arrow-down',
                    'label'  => 'Recepciones',
                    'route'  => 'winery.grape-reception.index',
                    'active' => request()->routeIs('winery.grape-reception*'),
                ],
                [
                    'icon'   => 'exclamation-triangle',
                    'label'  => 'Disputas',
                    'route'  => 'winery.grape-reception.disputes',
                    'active' => request()->routeIs('winery.grape-reception.disputes'),
                ],
                [
                    'icon'   => 'beaker',
                    'label'  => 'Análisis de Calidad',
                    'route'  => 'winery.harvest-quality.index',
                    'active' => request()->routeIs('winery.harvest-quality*'),
                ],
            ];

            // ── BODEGA ───────────────────────────────────────────────
            $menu['cellar'] = [
                [
                    'icon'   => 'beaker',
                    'label'  => 'Contenedores',
                    'route'  => 'winery.containers.index',
                    'active' => request()->routeIs('winery.containers*'),
                ],
                [
                    'icon'   => 'archive-box',
                    'label'  => 'Uva / Mosto externo',
                    'route'  => 'winery.external-grape.index',
                    'active' => request()->routeIs('winery.external-grape*'),
                ],
                [
                    'icon'   => 'arrows-right-left',
                    'label'  => 'Vinos',
                    'route'  => 'winery.wines.index',
                    'active' => request()->routeIs('winery.wines*'),
                ],
                [
                    'icon'   => 'user-circle',
                    'label'  => 'Enólogos',
                    'route'  => 'winery.oenologists.index',
                    'active' => request()->routeIs('winery.oenologists*'),
                ],
                [
                    'icon'   => 'magnifying-glass',
                    'label'  => 'Análisis de Lab.',
                    'route'  => 'winery.wine-analysis.index',
                    'active' => request()->routeIs('winery.wine-analysis*'),
                    'wip'    => true,
                ],
                [
                    'icon'   => 'archive-box',
                    'label'  => 'Productos',
                    'route'  => 'winery.product-lots.index',
                    'active' => request()->routeIs('winery.product-lots*'),
                ],
                [
                    'icon'   => 'magnifying-glass-circle',
                    'label'  => 'Trazabilidad',
                    'route'  => 'winery.traceability.index',
                    'active' => request()->routeIs('winery.traceability*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'archive-box-arrow-down',
                    'label'  => 'Embotellado y Expediciones',
                    'route'  => 'winery.bottling.index',
                    'active' => request()->routeIs('winery.bottling*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'document-text',
                    'label'  => 'Fichas Técnicas y Catas',
                    'route'  => 'winery.product-sheets.index',
                    'active' => request()->routeIs('winery.product-sheets*'),
                    'wip'    => true,
                    'new'    => true,
                ],
            ];


            // ── TERRITORIO ───────────────────────────────────────────
            $menu['territory'] = [
                [
                    'icon'   => 'map',
                    'label'  => 'Parcelas',
                    'route'  => 'winery.plots.index',
                    'active' => request()->routeIs('winery.plots*') && !request()->routeIs('plots.plantings.*'),
                ],
                [
                    'icon'   => 'book-open',
                    'label'  => 'Plantaciones',
                    'route'  => 'plots.plantings.index',
                    'active' => request()->routeIs('plots.plantings.*'),
                ],
                [
                    'icon'   => 'map-pin',
                    'label'  => 'SIGPAC',
                    'route'  => 'sigpac.codes',
                    'active' => request()->routeIs('sigpac.*'),
                ],
                [
                    'icon'   => 'globe-europe-africa',
                    'label'  => 'Gestión Territorial',
                    'route'  => 'plots.territory',
                    'active' => request()->routeIs('plots.territory'),
                ],
                [
                    'icon'   => 'globe-alt',
                    'label'  => 'Teledetección',
                    'route'  => 'remote-sensing.dashboard',
                    'active' => request()->routeIs('remote-sensing.*'),
                ],
                ['divider' => true],
                [
                    'icon'   => 'pencil-square',
                    'label'  => 'Actividades de Campo',
                    'route'  => 'winery.field-activities.index',
                    'active' => request()->routeIs('winery.field-activities*'),
                ],
            ];

            // ── RECURSOS ─────────────────────────────────────────────
            $menu['resources'] = [
                [
                    'icon'   => 'building-storefront',
                    'label'  => 'Insumos de Bodega',
                    'route'  => 'winery.winery-supplies.index',
                    'active' => request()->routeIs('winery.winery-supplies*'),
                    'wip'    => true,
                ],
                [
                    'icon'   => 'truck',
                    'label'  => 'Proveedores',
                    'route'  => 'winery.suppliers.index',
                    'active' => request()->routeIs('winery.suppliers*'),
                    'wip'    => true,
                ],
            ];

            // ── FACTURACIÓN Y CLIENTES ───────────────────────────────
            $menu['billing'] = [
                [
                    'icon'   => 'chart-bar-square',
                    'label'  => 'Resumen Económico',
                    'route'  => 'winery.financial-summary.index',
                    'active' => request()->routeIs('winery.financial-summary*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'presentation-chart-bar',
                    'label'  => 'Estadísticas Financieras',
                    'route'  => 'winery.financial-stats.index',
                    'active' => request()->routeIs('winery.financial-stats*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                ['divider' => true],
                [
                    'icon'   => 'arrow-down-tray',
                    'label'  => 'Compra de Uva',
                    'route'  => 'winery.invoices.grape-purchase.index',
                    'active' => request()->routeIs('winery.invoices.grape-purchase*'),
                    'wip'    => true,
                ],
                [
                    'icon'   => 'arrow-up-tray',
                    'label'  => 'Venta de Productos',
                    'route'  => 'winery.invoices.products.index',
                    'active' => request()->routeIs('winery.invoices.products*'),
                ],
                [
                    'icon'   => 'document-check',
                    'label'  => 'VeriFactu',
                    'route'  => 'winery.verifactu.index',
                    'active' => request()->routeIs('winery.verifactu*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'users',
                    'label'  => 'Clientes y Canales',
                    'route'  => 'winery.clients.index',
                    'active' => request()->routeIs('winery.clients*'),
                ],
                [
                    'icon'   => 'globe-alt',
                    'label'  => 'Exportación',
                    'route'  => 'winery.exports.index',
                    'active' => request()->routeIs('winery.exports*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'sparkles',
                    'label'  => 'Enoturismo',
                    'route'  => 'winery.enotourism.index',
                    'active' => request()->routeIs('winery.enotourism*'),
                    'wip'    => true,
                    'new'    => true,
                ],
            ];

            // ── NORMATIVA BODEGA ─────────────────────────────────────
            $menu['winery_normativa'] = [
                [
                    'icon'   => 'document-chart-bar',
                    'label'  => 'SILICIE',
                    'route'  => 'winery.silicie.dashboard',
                    'active' => request()->routeIs('winery.silicie*'),
                    'wip'    => true,
                ],
                ['divider' => true],
                [
                    'icon'   => 'document-text',
                    'label'  => 'AICA',
                    'route'  => 'winery.aica.index',
                    'active' => request()->routeIs('winery.aica*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'shield-check',
                    'label'  => 'Registros Sanitarios',
                    'route'  => 'winery.sanitary-registrations.index',
                    'active' => request()->routeIs('winery.sanitary-registrations*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'identification',
                    'label'  => 'Autorizaciones de Embotellado',
                    'route'  => 'winery.bottling-authorizations.index',
                    'active' => request()->routeIs('winery.bottling-authorizations*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                [
                    'icon'   => 'sparkles',
                    'label'  => 'Certificaciones Ecológicas',
                    'route'  => 'winery.eco-certifications.index',
                    'active' => request()->routeIs('winery.eco-certifications*'),
                    'wip'    => true,
                    'new'    => true,
                ],
            ];

            // ── REGISTRO OFICIAL (Sistema) ────────────────────────────
            $menu['compliance'] = [
                [
                    'icon'   => 'folder-open',
                    'label'  => 'Documentos Bodega',
                    'route'  => 'winery.documents.index',
                    'active' => request()->routeIs('winery.documents*'),
                    'wip'    => true,
                ],
            ];

            // ── SISTEMA ──────────────────────────────────────────────
            $menu['system'] = [
                [
                    'icon'   => 'cog-6-tooth',
                    'label'  => 'Configuración',
                    'route'  => 'winery.settings',
                    'active' => request()->routeIs('winery.settings'),
                ],
            ];
        }

        if ($role === 'admin') {
            $menu['main'][] = [
                'icon'   => 'user-group',
                'label'  => 'Usuarios',
                'route'  => 'admin.users.index',
                'active' => request()->routeIs('admin.users.*'),
            ];
            $menu['main'][] = [
                'icon'   => 'lifebuoy',
                'label'  => 'Soporte',
                'route'  => 'admin.support.index',
                'active' => request()->routeIs('admin.support.*'),
                'badge'  => Cache::remember('nav_badge_admin_support', 60, fn() => \App\Models\SupportTicket::open()->count()),
            ];
        }

        return $menu;
    }

    /**
     * Obtener el nombre del rol en español
     */
    public static function getRoleName(string $role): string
    {
        return match($role) {
            'admin'         => 'Administrador',
            'supervisor'    => 'Supervisor',
            'winery'        => 'Bodega',
            'viticulturist' => 'Viticultor',
            'producer'      => session('producer_context', 'viticulturist') === 'winery'
                                ? 'Productor · Bodega'
                                : 'Productor · Campo',
            default         => ucfirst($role),
        };
    }
}
