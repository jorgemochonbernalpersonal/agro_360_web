<?php

namespace App\Helpers;

use App\Models\NotebookAccessRequest;
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

        if ($role === 'producer') {
            return static::buildProducerMenu($user);
        }

        if ($role === 'supervisor') {
            return static::buildDOMenu($user);
        }

        $menu = [];

        if (in_array($role, ['viticulturist', 'producer'])) {
            // DASHBOARD - viticulturist ve su dashboard; producer ve la vista general combinada
            if ($role === 'producer') {
                $menu['main'][] = [
                    'icon'   => 'squares-2x2',
                    'label'  => 'Vista general',
                    'route'  => 'producer.dashboard',
                    'active' => request()->routeIs('producer.dashboard'),
                ];
            } else {
                $menu['main'][] = [
                    'icon'   => 'home',
                    'label'  => 'Dashboard',
                    'route'  => 'viticulturist.dashboard',
                    'active' => request()->routeIs('viticulturist.dashboard'),
                ];
            }

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
                'badge'  => Cache::remember("nav_badge_notifications_{$user->id}", 60, fn() => $user->unreadNotifications()->count()),
            ];

            // GRUPO: CAMPAÑA — gestión de campaña
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

            // GRUPO: CUADERNO DE CAMPO — entradas diarias
            $menu['cuaderno_inputs'] = [
                [
                    'icon'   => 'book-open',
                    'label'  => 'Cuaderno Digital',
                    'route'  => 'viticulturist.digital-notebook',
                    'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*'),
                ],
                ['divider' => true],
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
                    'icon' => 'viewfinder-circle',
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
                    'icon' => 'cog-8-tooth',
                    'label' => 'Equipos ITB/ITEA',
                    'route' => 'viticulturist.field-equipment.index',
                    'active' => request()->routeIs('viticulturist.field-equipment.*'),
                ],
                [
                    'icon'   => 'lifebuoy',
                    'label'  => 'Seguros Agrarios',
                    'route'  => 'viticulturist.agri-insurance.index',
                    'active' => request()->routeIs('viticulturist.agri-insurance*'),
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
                ['divider' => true],
                [
                    'icon'   => 'chart-bar',
                    'label'  => 'Cumplimiento Cuaderno',
                    'route'  => 'viticulturist.pac-compliance',
                    'active' => request()->routeIs('viticulturist.pac-compliance'),
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
                    'icon'   => 'document-arrow-up',
                    'label'  => 'Facturas Venta Cosecha',
                    'route'  => 'viticulturist.invoices.harvest-sale.index',
                    'active' => request()->routeIs('viticulturist.invoices.harvest-sale*'),
                ],
                [
                    'icon'   => 'document-arrow-down',
                    'label'  => 'Liquidaciones de Bodega',
                    'route'  => 'viticulturist.invoices.grape-purchase.index',
                    'active' => request()->routeIs('viticulturist.invoices.grape-purchase*'),
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

        if (in_array($role, ['winery', 'producer'])) {
            // ── MAIN ─────────────────────────────────────────────────
            // Producer ya tiene su dashboard combinado; solo winery pura añade aquí
            if ($role !== 'producer') {
                $menu['main'][] = [
                    'icon'   => 'home',
                    'label'  => 'Dashboard',
                    'route'  => 'winery.dashboard',
                    'active' => request()->routeIs('winery.dashboard'),
                ];
            }

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

            // ── BODEGA — ELABORACIÓN ──────────────────────────────────
            $menu['cellar_elab'] = [
                [
                    'icon'   => 'beaker',
                    'label'  => 'Contenedores',
                    'route'  => 'winery.containers.index',
                    'active' => request()->routeIs('winery.containers.index') || request()->routeIs('winery.containers.create') || request()->routeIs('winery.containers.edit') || request()->routeIs('winery.containers.show'),
                ],
                [
                    'icon'   => 'map',
                    'label'  => 'Mapa de Bodega',
                    'route'  => 'winery.containers.map',
                    'active' => request()->routeIs('winery.containers.map'),
                    'new'    => true,
                ],
                [
                    'icon'   => 'building-office',
                    'label'  => 'Salas de Bodega',
                    'route'  => 'winery.container-rooms.index',
                    'active' => request()->routeIs('winery.container-rooms*'),
                ],
                [
                    'icon'   => 'archive-box',
                    'label'  => 'Uva / Mosto externo',
                    'route'  => 'winery.external-grape.index',
                    'active' => request()->routeIs('winery.external-grape*'),
                ],
                ['divider' => true],
                [
                    'icon'   => 'arrows-right-left',
                    'label'  => 'Vinos',
                    'route'  => 'winery.wines.index',
                    'active' => request()->routeIs('winery.wines.index') || request()->routeIs('winery.wines.create') || request()->routeIs('winery.wines.edit') || request()->routeIs('winery.wines.show'),
                ],
                [
                    'icon'   => 'queue-list',
                    'label'  => 'Timeline de Vinos',
                    'route'  => 'winery.wines.timeline',
                    'active' => request()->routeIs('winery.wines.timeline'),
                    'new'    => true,
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
                ],
                [
                    'icon'   => 'calendar-days',
                    'label'  => 'Operaciones de Bodega',
                    'route'  => 'winery.cellar-operations.index',
                    'active' => request()->routeIs('winery.cellar-operations*'),
                    'new'    => true,
                ],
            ];

            // ── BODEGA — SALIDA ───────────────────────────────────────
            $menu['cellar_salida'] = [
                [
                    'icon'   => 'archive-box',
                    'label'  => 'Productos',
                    'route'  => 'winery.product-lots.index',
                    'active' => request()->routeIs('winery.product-lots*') && !request()->routeIs('winery.product-lots.audit'),
                ],
                [
                    'icon'   => 'shield-check',
                    'label'  => 'Auditoría de Stock',
                    'route'  => 'winery.product-lots.audit',
                    'active' => request()->routeIs('winery.product-lots.audit'),
                ],
                [
                    'icon'   => 'magnifying-glass-circle',
                    'label'  => 'Trazabilidad',
                    'route'  => 'winery.traceability.index',
                    'active' => request()->routeIs('winery.traceability*'),
                    'new'    => true,
                ],
                ['divider' => true],
                [
                    'icon'   => 'archive-box-arrow-down',
                    'label'  => 'Embotellado y Etiquetado',
                    'route'  => 'winery.bottling.index',
                    'active' => request()->routeIs('winery.bottling*') || request()->routeIs('winery.label-batches*') || request()->routeIs('winery.labeling*'),
                ],
                [
                    'icon'   => 'document-text',
                    'label'  => 'Fichas Técnicas y Catas',
                    'route'  => 'winery.product-sheets.index',
                    'active' => request()->routeIs('winery.product-sheets*') || request()->routeIs('winery.tasting-notes*'),
                ],
                [
                    'icon'   => 'archive-box-x-mark',
                    'label'  => 'Subproductos',
                    'route'  => 'winery.subproducts.index',
                    'active' => request()->routeIs('winery.subproducts*'),
                ],
            ];


            // ── TERRITORIO ─── solo para winery pura; producer gestiona parcelas desde el lado campo
            if ($role !== 'producer') $menu['territory'] = [
                [
                    'icon'   => 'map',
                    'label'  => 'Parcelas',
                    'route'  => 'winery.plots.index',
                    'active' => request()->routeIs('winery.plots*') && !request()->routeIs('plots.plantings.*') || request()->routeIs('sigpac.*'),
                ],
                [
                    'icon'   => 'book-open',
                    'label'  => 'Plantaciones',
                    'route'  => 'plots.plantings.index',
                    'active' => request()->routeIs('plots.plantings.*'),
                ],
                [
                    'icon'   => 'globe-europe-africa',
                    'label'  => 'Gestión Territorial',
                    'route'  => 'plots.territory',
                    'active' => request()->routeIs('plots.territory'),
                ],
                ['divider' => true],
                [
                    'icon'   => 'globe-alt',
                    'label'  => 'Teledetección',
                    'route'  => 'remote-sensing.dashboard',
                    'active' => request()->routeIs('remote-sensing.*'),
                ],
                [
                    'icon'   => 'cloud',
                    'label'  => 'Meteorología',
                    'route'  => 'winery.meteorology.index',
                    'active' => request()->routeIs('winery.meteorology*'),
                    'wip'    => true,
                    'new'    => true,
                ],
                ['divider' => true],
                [
                    'icon'   => 'pencil-square',
                    'label'  => 'Actividades de Campo',
                    'route'  => 'winery.field-activities.index',
                    'active' => request()->routeIs('winery.field-activities*'),
                ],
            ];

            // ── RECURSOS ─── prefijo winery_ para producer (evita solapar recursos del viticultor)
            $wineryResourcesKey = $role === 'producer' ? 'winery_resources' : 'resources';
            $menu[$wineryResourcesKey] = [
                [
                    'icon'   => 'building-storefront',
                    'label'  => 'Insumos de Bodega',
                    'route'  => 'winery.winery-supplies.index',
                    'active' => request()->routeIs('winery.winery-supplies*'),
                ],
                [
                    'icon'   => 'truck',
                    'label'  => 'Proveedores',
                    'route'  => 'winery.suppliers.index',
                    'active' => request()->routeIs('winery.suppliers*'),
                ],
            ];

            // ── FACTURACIÓN Y CLIENTES ─── prefijo winery_ para producer
            $wineryBillingKey = $role === 'producer' ? 'winery_billing' : 'billing';
            $menu[$wineryBillingKey] = [
                [
                    'icon'   => 'chart-bar-square',
                    'label'  => 'Resumen Económico',
                    'route'  => 'winery.financial-summary.index',
                    'active' => request()->routeIs('winery.financial-summary*'),
                    'new'    => true,
                ],
                [
                    'icon'   => 'presentation-chart-bar',
                    'label'  => 'Estadísticas Financieras',
                    'route'  => 'winery.financial-stats.index',
                    'active' => request()->routeIs('winery.financial-stats*'),
                    'new'    => true,
                ],
                ['divider' => true],
                [
                    'icon'   => 'arrow-down-tray',
                    'label'  => 'Compra de Uva',
                    'route'  => 'winery.invoices.grape-purchase.index',
                    'active' => request()->routeIs('winery.invoices.grape-purchase*'),
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
                    'active' => request()->routeIs('winery.silicie.dashboard') || request()->routeIs('winery.silicie.movements*'),
                ],
                [
                    'icon'   => 'chart-bar',
                    'label'  => 'INFOVI (AICA)',
                    'route'  => 'winery.silicie.infovi',
                    'active' => request()->routeIs('winery.silicie.infovi'),
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
                    'new'    => true,
                ],
                [
                    'icon'   => 'identification',
                    'label'  => 'Autorizaciones de Embotellado',
                    'route'  => 'winery.bottling-authorizations.index',
                    'active' => request()->routeIs('winery.bottling-authorizations*'),
                    'new'    => true,
                ],
                [
                    'icon'   => 'sparkles',
                    'label'  => 'Certificaciones Ecológicas',
                    'route'  => 'winery.eco-certifications.index',
                    'active' => request()->routeIs('winery.eco-certifications*'),
                    'new'    => true,
                ],
            ];

            // ── REGISTRO OFICIAL (Sistema) ─── prefijo winery_ para producer
            $wineryComplianceKey = $role === 'producer' ? 'winery_compliance' : 'compliance';
            $menu[$wineryComplianceKey] = [
                [
                    'icon'   => 'folder-open',
                    'label'  => 'Documentos Bodega',
                    'route'  => 'winery.documents.index',
                    'active' => request()->routeIs('winery.documents*'),
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
                [
                    'icon'   => 'bell-alert',
                    'label'  => 'Centro de Alertas',
                    'route'  => 'winery.alerts.index',
                    'active' => request()->routeIs('winery.alerts*'),
                    'new'    => true,
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
     * Menú completo para el rol producer (usa rutas producer.*)
     */
    private static function buildProducerMenu($user): array
    {
        $menu = [];

        // ── MAIN ──────────────────────────────────────────────────────────
        $menu['main'] = [
            [
                'icon'   => 'squares-2x2',
                'label'  => 'Vista general',
                'route'  => 'producer.dashboard',
                'active' => request()->routeIs('producer.dashboard'),
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
                'new'    => true,
            ],
        ];

        // ── CAMPAÑA ───────────────────────────────────────────────────────
        $menu['operations'] = [
            [
                'icon'   => 'clipboard-document-list',
                'label'  => 'Campañas',
                'route'  => 'producer.campaign.index',
                'active' => request()->routeIs('producer.campaign*') && !request()->routeIs('producer.campaign-documents.*') && !request()->routeIs('producer.campaign-sign.*'),
            ],
            [
                'icon'   => 'folder-open',
                'label'  => 'Documentos de Campaña',
                'route'  => 'producer.campaign-documents.index',
                'active' => request()->routeIs('producer.campaign-documents.*'),
            ],
            [
                'icon'   => 'check-badge',
                'label'  => 'Firma y Cierre',
                'route'  => 'producer.campaign-sign.index',
                'active' => request()->routeIs('producer.campaign-sign.*'),
            ],
            ['divider' => true],
            [
                'icon'   => 'chart-bar-square',
                'label'  => 'Rendimientos Estimados',
                'route'  => 'producer.digital-notebook.estimated-yields.index',
                'active' => request()->routeIs('producer.digital-notebook.estimated-yields.*'),
            ],
        ];

        // ── CUADERNO DE CAMPO ─────────────────────────────────────────────
        $menu['cuaderno_inputs'] = [
            ['icon' => 'shield-exclamation',     'label' => 'Tratamientos',       'route' => 'producer.digital-notebook.treatment.index',    'active' => request()->routeIs('producer.digital-notebook.treatment.*')],
            ['icon' => 'funnel',                 'label' => 'Fertilizaciones',    'route' => 'producer.digital-notebook.fertilization.index', 'active' => request()->routeIs('producer.digital-notebook.fertilization.*')],
            ['icon' => 'cloud',                  'label' => 'Riegos',             'route' => 'producer.digital-notebook.irrigation.index',    'active' => request()->routeIs('producer.digital-notebook.irrigation.*')],
            ['icon' => 'wrench-screwdriver',     'label' => 'Labores Culturales', 'route' => 'producer.digital-notebook.cultural.index',      'active' => request()->routeIs('producer.digital-notebook.cultural.*')],
            ['icon' => 'eye',                    'label' => 'Observaciones',      'route' => 'producer.digital-notebook.observation.index',   'active' => request()->routeIs('producer.digital-notebook.observation.*')],
            ['icon' => 'sun',                    'label' => 'Fenología',          'route' => 'producer.phenology.index',                      'active' => request()->routeIs('producer.phenology.*')],
            ['icon' => 'scissors',               'label' => 'Podas',              'route' => 'producer.digital-notebook.pruning.index',       'active' => request()->routeIs('producer.digital-notebook.pruning.*')],
            ['icon' => 'archive-box',            'label' => 'Post-Vendimia',      'route' => 'producer.digital-notebook.post-harvest.index',  'active' => request()->routeIs('producer.digital-notebook.post-harvest.*')],
            ['divider' => true],
            ['icon' => 'archive-box-arrow-down', 'label' => 'Vendimia Campo',     'route' => 'producer.harvests.index',                       'active' => request()->routeIs('producer.harvests.*')],
            ['icon' => 'bug-ant',                'label' => 'Gestión de Plagas',  'route' => 'producer.pest-management.index',                'active' => request()->routeIs('producer.pest-management.*')],
        ];

        // ── REGISTROS OFICIALES ───────────────────────────────────────────
        $menu['registros_oficiales'] = [
            ['icon' => 'chart-bar',                'label' => 'Cumplimiento Cuaderno', 'route' => 'producer.pac-compliance',             'active' => request()->routeIs('producer.pac-compliance')],
            ['icon' => 'clipboard-document-check', 'label' => 'Análisis de Residuos',  'route' => 'producer.residue-analyses.index',      'active' => request()->routeIs('producer.residue-analyses.*')],
            ['icon' => 'trash',                    'label' => 'Gestión de Residuos',   'route' => 'producer.residue-managements.index',   'active' => request()->routeIs('producer.residue-managements.*')],
            ['icon' => 'bolt',                     'label' => 'Consumo Energético',    'route' => 'producer.energy-usages.index',         'active' => request()->routeIs('producer.energy-usages.*')],
            ['icon' => 'arrow-up-tray',            'label' => 'Exportaciones CUE',     'route' => 'producer.cue-exports.index',           'active' => request()->routeIs('producer.cue-exports.*')],
            ['icon' => 'document',                 'label' => 'Informes Oficiales',    'route' => 'producer.official-reports.index',      'active' => request()->routeIs('producer.official-reports.*')],
        ];

        // ── PARCELAS Y TERRITORIO ─────────────────────────────────────────
        $menu['plots_analysis'] = [
            ['icon' => 'map',                 'label' => 'Parcelas',           'route' => 'producer.plots.index',              'active' => request()->routeIs('producer.plots.*') && !request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'book-open',           'label' => 'Plantaciones',       'route' => 'plots.plantings.index',             'active' => request()->routeIs('plots.plantings.*') || request()->routeIs('producer.plots.plantings.*')],
            ['icon' => 'map-pin',             'label' => 'SIGPAC',             'route' => 'sigpac.codes',                      'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'globe-alt',           'label' => 'Teledetección',      'route' => 'remote-sensing.dashboard',          'active' => request()->routeIs('remote-sensing.*')],
            ['icon' => 'globe-europe-africa', 'label' => 'Gestión Territorial','route' => 'plots.territory',                   'active' => request()->routeIs('plots.territory')],
            ['icon' => 'beaker',              'label' => 'Entorno de Parcelas','route' => 'producer.plot-environments.index',  'active' => request()->routeIs('producer.plot-environments.*')],
            ['icon' => 'pencil-square',       'label' => 'Actividades de Campo','route' => 'producer.field-activities.index', 'active' => request()->routeIs('producer.field-activities*')],
        ];

        // ── RECURSOS ──────────────────────────────────────────────────────
        $menu['resources'] = [
            ['icon' => 'user-group',          'label' => 'Personal',                 'route' => 'producer.personal.index',               'active' => request()->routeIs('producer.personal*')],
            ['icon' => 'adjustments-vertical','label' => 'Maquinaria',               'route' => 'producer.machinery.index',              'active' => request()->routeIs('producer.machinery*')],
            ['icon' => 'building-storefront', 'label' => 'Almacén de Insumos',       'route' => 'producer.almacen.index',                'active' => request()->routeIs('producer.almacen.*')],
            ['icon' => 'beaker',              'label' => 'Productos Fitosanitarios', 'route' => 'producer.phytosanitary-products.index', 'active' => request()->routeIs('producer.phytosanitary-products.*')],
            ['icon' => 'user-plus',           'label' => 'Subcontratación',          'route' => 'producer.subcontracting.index',         'active' => request()->routeIs('producer.subcontracting*'), 'new' => true],
        ];

        // ── NORMATIVA ─────────────────────────────────────────────────────
        $menu['compliance'] = [
            ['icon' => 'building-office',   'label' => 'Explotación SIEX/REA',      'route' => 'producer.exploitations.index',             'active' => request()->routeIs('producer.exploitations.*')],
            ['icon' => 'shield-check',      'label' => 'Autorizaciones Comerciales', 'route' => 'producer.commercial-authorizations.index', 'active' => request()->routeIs('producer.commercial-authorizations.*')],
            ['icon' => 'user',              'label' => 'Asesorías Técnicas',         'route' => 'producer.advisory-memberships.index',      'active' => request()->routeIs('producer.advisory-memberships.*')],
            ['icon' => 'identification',    'label' => 'Aplicadores ROPO',           'route' => 'producer.field-applicators.index',         'active' => request()->routeIs('producer.field-applicators.*')],
            ['icon' => 'cog-6-tooth',       'label' => 'Equipos ITB/ITEA',           'route' => 'producer.field-equipment.index',           'active' => request()->routeIs('producer.field-equipment.*')],
            ['icon' => 'shield-exclamation','label' => 'Seguros Agrarios',           'route' => 'producer.agri-insurance.index',            'active' => request()->routeIs('producer.agri-insurance*'), 'new' => true],
        ];

        // ── PAC ───────────────────────────────────────────────────────────
        $menu['pac'] = [
            ['icon' => 'chart-pie',    'label' => 'Resumen PAC',          'route' => 'producer.pac.dashboard',          'active' => request()->routeIs('producer.pac.dashboard')],
            ['icon' => 'check-circle', 'label' => 'Superficies Elegibles','route' => 'producer.pac.surfaces.index',     'active' => request()->routeIs('producer.pac.surfaces.*')],
            ['icon' => 'document-text','label' => 'Declaraciones',        'route' => 'producer.pac.declarations.index', 'active' => request()->routeIs('producer.pac.declarations.*')],
            ['icon' => 'sparkles',     'label' => 'Eco-regímenes',        'route' => 'producer.pac.eco-schemes.index',  'active' => request()->routeIs('producer.pac.eco-schemes.*')],
            ['icon' => 'banknotes',    'label' => 'Historial de Ayudas',  'route' => 'producer.pac.payments.index',     'active' => request()->routeIs('producer.pac.payments.*')],
        ];

        // ── NEGOCIO VITICULTOR ────────────────────────────────────────────
        $menu['billing'] = [
            ['icon' => 'calculator',             'label' => 'Facturas',               'route' => 'producer.invoices.index',          'active' => request()->routeIs('producer.invoices.*') && !request()->routeIs('producer.invoices.products.*') && !request()->routeIs('producer.invoices.grape-purchase.*')],
            ['icon' => 'document-check',         'label' => 'VeriFactu',              'route' => 'producer.verifactu.index',         'active' => request()->routeIs('producer.verifactu*'), 'new' => true],
            ['icon' => 'shopping-cart',          'label' => 'Cosecha Comercializada', 'route' => 'producer.marketed-harvests.index', 'active' => request()->routeIs('producer.marketed-harvests.*')],
            ['icon' => 'table-cells',            'label' => 'Costes por Parcela',     'route' => 'producer.plot-costs.index',        'active' => request()->routeIs('producer.plot-costs*'), 'new' => true],
            ['icon' => 'users',                  'label' => 'Clientes',               'route' => 'producer.clients.index',           'active' => request()->routeIs('producer.clients.*')],
            ['icon' => 'presentation-chart-bar', 'label' => 'Estadísticas Viñedo',     'route' => 'producer.financial-stats',        'active' => request()->routeIs('producer.financial-stats')],
        ];

        // ── VENDIMIA (BODEGA) ─────────────────────────────────────────────
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

        // ── BODEGA (CELLAR) ───────────────────────────────────────────────
        $menu['cellar'] = [
            ['icon' => 'beaker',                  'label' => 'Contenedores',               'route' => 'producer.containers.index',       'active' => request()->routeIs('producer.containers.index') || request()->routeIs('producer.containers.create') || request()->routeIs('producer.containers.edit') || request()->routeIs('producer.containers.show')],
            ['icon' => 'map',                     'label' => 'Mapa de Bodega',             'route' => 'producer.containers.map',         'active' => request()->routeIs('producer.containers.map'), 'new' => true],
            ['icon' => 'home-modern',             'label' => 'Salas de Bodega',            'route' => 'producer.container-rooms.index',  'active' => request()->routeIs('producer.container-rooms*'), 'new' => true],
            ['icon' => 'cog-6-tooth',             'label' => 'Operaciones de Bodega',      'route' => 'producer.cellar-operations.index','active' => request()->routeIs('producer.cellar-operations*'), 'new' => true],
            ['icon' => 'archive-box',             'label' => 'Uva / Mosto externo',        'route' => 'producer.external-grape.index',   'active' => request()->routeIs('producer.external-grape*')],
            ['icon' => 'arrows-right-left',       'label' => 'Vinos',                      'route' => 'producer.wines.index',            'active' => request()->routeIs('producer.wines.index') || request()->routeIs('producer.wines.create') || request()->routeIs('producer.wines.edit') || request()->routeIs('producer.wines.show')],
            ['icon' => 'queue-list',              'label' => 'Timeline de Vinos',          'route' => 'producer.wines.timeline',         'active' => request()->routeIs('producer.wines.timeline'), 'new' => true],
            ['icon' => 'user-circle',             'label' => 'Enólogos',                   'route' => 'producer.oenologists.index',      'active' => request()->routeIs('producer.oenologists*')],
            ['icon' => 'magnifying-glass',        'label' => 'Análisis de Lab.',            'route' => 'producer.wine-analysis.index',   'active' => request()->routeIs('producer.wine-analysis*')],
            ['icon' => 'archive-box',             'label' => 'Productos',                   'route' => 'producer.product-lots.index',    'active' => request()->routeIs('producer.product-lots*') && !request()->routeIs('producer.product-lots.audit')],
            ['icon' => 'magnifying-glass-circle', 'label' => 'Trazabilidad',                'route' => 'producer.traceability.index',    'active' => request()->routeIs('producer.traceability*'), 'new' => true],
            ['icon' => 'archive-box-arrow-down',  'label' => 'Embotellado y Expediciones', 'route' => 'producer.bottling.index',         'active' => request()->routeIs('producer.bottling*'), 'new' => true],
            ['icon' => 'tag',                     'label' => 'Etiquetado',                 'route' => 'producer.label-batches.index',    'active' => request()->routeIs('producer.label-batches*') || request()->routeIs('producer.labeling*'), 'new' => true],
            ['icon' => 'document-text',           'label' => 'Fichas Técnicas y Catas',    'route' => 'producer.product-sheets.index',  'active' => request()->routeIs('producer.product-sheets*') || request()->routeIs('producer.tasting-notes*'), 'new' => true],
            ['icon' => 'archive-box-x-mark',      'label' => 'Subproductos',               'route' => 'producer.subproducts.index',     'active' => request()->routeIs('producer.subproducts*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'building-storefront',     'label' => 'Insumos de Bodega',           'route' => 'producer.winery-supplies.index', 'active' => request()->routeIs('producer.winery-supplies*')],
            ['icon' => 'truck',                   'label' => 'Proveedores',                 'route' => 'producer.suppliers.index',       'active' => request()->routeIs('producer.suppliers*')],
            ['divider' => true],
            ['icon' => 'bell-alert',              'label' => 'Centro de Alertas',           'route' => 'producer.alerts.index',          'active' => request()->routeIs('producer.alerts*'), 'new' => true],
        ];

        // ── NORMATIVA BODEGA ──────────────────────────────────────────────
        $menu['winery_normativa'] = [
            ['icon' => 'document-chart-bar','label' => 'SILICIE',                      'route' => 'producer.silicie.index',                 'active' => request()->routeIs('producer.silicie*'), 'wip' => true],
            ['divider' => true],
            ['icon' => 'document-text',    'label' => 'AICA',                          'route' => 'producer.aica.index',                    'active' => request()->routeIs('producer.aica*'), 'wip' => true, 'new' => true],
            ['icon' => 'shield-check',     'label' => 'Registros Sanitarios',          'route' => 'producer.sanitary-registrations.index',  'active' => request()->routeIs('producer.sanitary-registrations*'), 'new' => true],
            ['icon' => 'identification',   'label' => 'Autorizaciones de Embotellado', 'route' => 'producer.bottling-authorizations.index', 'active' => request()->routeIs('producer.bottling-authorizations*'), 'new' => true],
            ['icon' => 'sparkles',         'label' => 'Certificaciones Ecológicas',    'route' => 'producer.eco-certifications.index',      'active' => request()->routeIs('producer.eco-certifications*'), 'new' => true],
            ['divider' => true],
            ['icon' => 'folder-open',      'label' => 'Documentos Bodega',             'route' => 'producer.documents.index',               'active' => request()->routeIs('producer.documents*'), 'new' => true],
        ];

        // ── NEGOCIO BODEGA ────────────────────────────────────────────────
        $menu['winery_billing'] = [
            ['icon' => 'arrow-up-tray',           'label' => 'Venta de Productos',    'route' => 'producer.invoices.products.index',       'active' => request()->routeIs('producer.invoices.products*')],
            ['icon' => 'arrow-down-tray',         'label' => 'Compra de Uva',         'route' => 'producer.invoices.grape-purchase.index', 'active' => request()->routeIs('producer.invoices.grape-purchase*')],
            ['icon' => 'users',                   'label' => 'Clientes Bodega',        'route' => 'producer.winery-clients.index',          'active' => request()->routeIs('producer.winery-clients*'), 'new' => true],
            ['icon' => 'chart-bar',               'label' => 'Resumen Económico',      'route' => 'producer.financial-summary.index',       'active' => request()->routeIs('producer.financial-summary*'), 'new' => true],
            ['icon' => 'presentation-chart-line', 'label' => 'Estadísticas Bodega',   'route' => 'producer.financial-stats-winery',        'active' => request()->routeIs('producer.financial-stats-winery'), 'new' => true],
            ['divider' => true],
            ['icon' => 'globe-alt',               'label' => 'Exportación',            'route' => 'producer.exports.index',                 'active' => request()->routeIs('producer.exports*'), 'wip' => true, 'new' => true],
            ['icon' => 'sparkles',                'label' => 'Enoturismo',             'route' => 'producer.enotourism.index',              'active' => request()->routeIs('producer.enotourism*'), 'wip' => true, 'new' => true],
        ];

        // ── RAIL BOTTOM ───────────────────────────────────────────────────
        $menu['rail_bottom'] = [
            [
                'icon'   => 'cog-6-tooth',
                'label'  => 'Configuración',
                'route'  => 'producer.settings',
                'active' => request()->routeIs('producer.settings'),
            ],
        ];

        return $menu;
    }

    /**
     * Construir menú para el rol DO (Denominación de Origen)
     */
    private static function buildDOMenu($user): array
    {
        $menu = [];

        // ── MAIN ──────────────────────────────────────────────────────
        $menu['main'] = [
            [
                'icon'   => 'squares-2x2',
                'label'  => 'Dashboard',
                'route'  => 'supervisor.dashboard',
                'active' => request()->routeIs('supervisor.dashboard'),
            ],
        ];

        // ── CENSO ─────────────────────────────────────────────────────
        $menu['do_census'] = [
            ['icon' => 'users',              'label' => 'Bodegas registradas',         'route' => 'supervisor.census.index', 'active' => request()->routeIs('supervisor.census.*')],
            ['icon' => 'building-storefront','label' => 'Productores registrados',     'route' => 'supervisor.census.index', 'active' => false],
            ['icon' => 'check-circle',       'label' => 'Altas / bajas',              'route' => 'supervisor.census.index', 'active' => false],
            ['icon' => 'tag',                'label' => 'Variedades admitidas',        'route' => 'supervisor.census.index', 'active' => false],
            ['icon' => 'map',                'label' => 'Mapa territorial DO',         'route' => 'supervisor.census.index', 'active' => false],
        ];

        // ── VITICULTORES DO ───────────────────────────────────────────
        $menu['do_growers'] = [
            ['icon' => 'user-group',         'label' => 'Mis viticultores DO',         'route' => 'supervisor.growers.index', 'active' => request()->routeIs('supervisor.growers.*')],
            ['icon' => 'map',                'label' => 'Parcelas',                    'route' => 'supervisor.growers.index', 'active' => false],
            ['icon' => 'book-open',          'label' => 'Plantaciones',               'route' => 'supervisor.growers.index', 'active' => false],
            ['icon' => 'globe-europe-africa','label' => 'SIGPAC',                     'route' => 'supervisor.growers.index', 'active' => false],
            ['icon' => 'users',              'label' => 'Asignación a bodegas',       'route' => 'supervisor.growers.index', 'active' => false],
        ];

        // ── CAMPAÑAS DO ───────────────────────────────────────────────
        $menu['do_campaigns'] = [
            ['icon' => 'flag',               'label' => 'Campañas de vendimia',       'route' => 'supervisor.campaigns.index', 'active' => request()->routeIs('supervisor.campaigns.*')],
            ['icon' => 'clipboard-document-list', 'label' => 'Declaraciones de cosecha', 'route' => 'supervisor.campaigns.index', 'active' => false],
            ['icon' => 'chart-bar',          'label' => 'Aforos por bodega',          'route' => 'supervisor.campaigns.index', 'active' => false],
            ['icon' => 'chart-bar-square',   'label' => 'Cupos y límites',            'route' => 'supervisor.campaigns.index', 'active' => false],
        ];

        // ── SUPERVISIÓN BODEGAS ────────────────────────────────────────
        $menu['do_oversight_wineries'] = [
            ['icon' => 'building-office-2',  'label' => 'Panel de bodegas',           'route' => 'supervisor.oversight.wineries.index', 'active' => request()->routeIs('supervisor.oversight.wineries.*')],
            ['icon' => 'archive-box-arrow-down', 'label' => 'Recepciones de uva',     'route' => 'supervisor.oversight.wineries.index', 'active' => false],
            ['icon' => 'beaker',             'label' => 'Elaboración y vinos',        'route' => 'supervisor.oversight.wineries.index', 'active' => false],
        ];

        // ── SUPERVISIÓN VITICULTORES ───────────────────────────────────
        $menu['do_oversight_growers'] = [
            ['icon' => 'eye',                'label' => 'Panel de viticultores',      'route' => 'supervisor.oversight.growers.index', 'active' => request()->routeIs('supervisor.oversight.growers.*')],
            ['icon' => 'map',                'label' => 'Parcelas y plantaciones',    'route' => 'supervisor.oversight.growers.index', 'active' => false],
            ['icon' => 'clipboard-document-check', 'label' => 'Registros y cumplimiento', 'route' => 'supervisor.oversight.growers.index', 'active' => false],
        ];

        // ── CALIFICACIÓN ───────────────────────────────────────────────
        $menu['do_qualification'] = [
            ['icon' => 'star',               'label' => 'Panel de cata',              'route' => 'supervisor.qualification.index', 'active' => request()->routeIs('supervisor.qualification.*')],
            ['icon' => 'beaker',             'label' => 'Análisis de laboratorio',   'route' => 'supervisor.qualification.index', 'active' => false],
            ['icon' => 'check-badge',        'label' => 'Calificación de vinos',     'route' => 'supervisor.qualification.index', 'active' => false],
            ['icon' => 'document-text',      'label' => 'Añadas',                    'route' => 'supervisor.qualification.index', 'active' => false],
            ['icon' => 'clock',              'label' => 'Histórico',                 'route' => 'supervisor.qualification.index', 'active' => false],
        ];

        // ── CONTRAETIQUETAS ───────────────────────────────────────────
        $menu['do_labels'] = [
            ['icon' => 'tag',                'label' => 'Solicitudes',                'route' => 'supervisor.labels.index', 'active' => request()->routeIs('supervisor.labels.*')],
            ['icon' => 'hashtag',            'label' => 'Emisión y numeración',       'route' => 'supervisor.labels.index', 'active' => false],
            ['icon' => 'archive-box',        'label' => 'Stock de contraetiquetas',  'route' => 'supervisor.labels.index', 'active' => false],
        ];

        // ── CONTROL E INSPECCIÓN ──────────────────────────────────────
        $menu['do_inspection'] = [
            ['icon' => 'clipboard-document-list', 'label' => 'Plan de control anual', 'route' => 'supervisor.inspection.index', 'active' => request()->routeIs('supervisor.inspection.*')],
            ['icon' => 'shield-check',       'label' => 'Inspecciones',              'route' => 'supervisor.inspection.index', 'active' => false],
            ['icon' => 'document-text',      'label' => 'Actas de inspección',       'route' => 'supervisor.inspection.index', 'active' => false],
            ['icon' => 'shield-exclamation', 'label' => 'Expedientes sancionadores', 'route' => 'supervisor.inspection.index', 'active' => false],
        ];

        // ── NORMATIVA DO ──────────────────────────────────────────────
        $menu['do_regulation'] = [
            ['icon' => 'document-text',      'label' => 'Pliego de condiciones',     'route' => 'supervisor.regulation.index', 'active' => request()->routeIs('supervisor.regulation.*')],
            ['icon' => 'document-duplicate', 'label' => 'Reglamento interno',        'route' => 'supervisor.regulation.index', 'active' => false],
            ['icon' => 'check-circle',       'label' => 'Autorizaciones de plantación', 'route' => 'supervisor.regulation.index', 'active' => false],
            ['icon' => 'sparkles',           'label' => 'Certificaciones ecológicas', 'route' => 'supervisor.regulation.index', 'active' => false],
        ];

        // ── TERRITORIO ────────────────────────────────────────────────
        $menu['do_territory'] = [
            ['icon' => 'map',                'label' => 'SIGPAC consolidado',         'route' => 'supervisor.territory.index', 'active' => request()->routeIs('supervisor.territory.*')],
            ['icon' => 'globe-europe-africa','label' => 'Teledetección regional',    'route' => 'supervisor.territory.index', 'active' => false],
            ['icon' => 'map-pin',            'label' => 'Zonificación DO',            'route' => 'supervisor.territory.index', 'active' => false],
        ];

        // ── ESTADÍSTICAS ──────────────────────────────────────────────
        $menu['do_statistics'] = [
            ['icon' => 'chart-bar',          'label' => 'Dashboard general',          'route' => 'supervisor.statistics.index', 'active' => request()->routeIs('supervisor.statistics.*')],
            ['icon' => 'chart-pie',          'label' => 'Producción por campaña',    'route' => 'supervisor.statistics.index', 'active' => false],
            ['icon' => 'globe-alt',          'label' => 'Exportación',               'route' => 'supervisor.statistics.index', 'active' => false],
            ['icon' => 'document-chart-bar', 'label' => 'Informes para CCAA',        'route' => 'supervisor.statistics.index', 'active' => false],
        ];

        // ── NEGOCIO DO ────────────────────────────────────────────────
        $menu['do_finance'] = [
            ['icon' => 'banknotes',          'label' => 'Cuotas de inscripción',     'route' => 'supervisor.finance.index', 'active' => request()->routeIs('supervisor.finance.*')],
            ['icon' => 'calculator',         'label' => 'Tasas y liquidaciones',     'route' => 'supervisor.finance.index', 'active' => false],
            ['icon' => 'document-text',      'label' => 'Facturas DO',               'route' => 'supervisor.finance.index', 'active' => false],
            ['icon' => 'presentation-chart-bar', 'label' => 'Presupuesto anual',    'route' => 'supervisor.finance.index', 'active' => false],
        ];

        // ── SISTEMA ───────────────────────────────────────────────────
        $menu['do_settings'] = [
            ['icon' => 'users',              'label' => 'Usuarios y permisos',       'route' => 'supervisor.settings.index', 'active' => request()->routeIs('supervisor.settings.*')],
            ['icon' => 'folder-open',        'label' => 'Documentos DO',             'route' => 'supervisor.settings.index', 'active' => false],
            ['icon' => 'megaphone',          'label' => 'Comunicaciones masivas',    'route' => 'supervisor.settings.index', 'active' => false],
            ['icon' => 'cog-6-tooth',        'label' => 'Configuración',             'route' => 'supervisor.settings.index', 'active' => false],
        ];

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
            'producer'      => 'Productor',
            'supervisor'    => 'Denominación de Origen',
            default         => ucfirst($role),
        };
    }
}
