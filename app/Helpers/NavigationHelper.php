<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class NavigationHelper
{
    /**
     * Obtener el menú de navegación según el rol del usuario
     * Cacheado por 1 hora para mejorar rendimiento
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
        $menu = [];

        if ($role === 'viticulturist') {
            // DASHBOARD - Siempre visible
            $menu['main'][] = [
                'icon' => 'home',
                'label' => 'Dashboard',
                'route' => 'viticulturist.dashboard',
                'active' => request()->routeIs('viticulturist.dashboard'),
            ];

            $menu['main'][] = [
                'icon' => 'calendar',
                'label' => 'Calendario',
                'route' => 'viticulturist.calendar',
                'active' => request()->routeIs('viticulturist.calendar'),
            ];

            // GRUPO: CAMPAÑA Y CUADERNO
            $menu['operations'] = [
                [
                    'icon' => 'clipboard-document-list',
                    'label' => 'Campaña',
                    'route' => 'viticulturist.campaign.index',
                    'active' => request()->routeIs('viticulturist.campaign*') || request()->routeIs('viticulturist.campaign-documents.*') || request()->routeIs('viticulturist.campaign-sign.*'),
                    'submenu' => [
                        ['label' => 'Campañas', 'route' => 'viticulturist.campaign.index', 'active' => request()->routeIs('viticulturist.campaign*') && !request()->routeIs('viticulturist.campaign-documents.*') && !request()->routeIs('viticulturist.campaign-sign.*')],
                        ['label' => 'Documentos', 'route' => 'viticulturist.campaign-documents.index', 'active' => request()->routeIs('viticulturist.campaign-documents.*')],
                        ['label' => 'Firma y Cierre', 'route' => 'viticulturist.campaign-sign.index', 'active' => request()->routeIs('viticulturist.campaign-sign.*')],
                    ],
                ],
                [
                    'icon' => 'pencil-square',
                    'label' => 'Cuaderno Digital',
                    'route' => 'viticulturist.digital-notebook',
                    'active' => request()->routeIs('viticulturist.digital-notebook*') || request()->routeIs('viticulturist.vendimia.*'),
                    'submenu' => [
                        ['label' => 'Actividades', 'route' => 'viticulturist.digital-notebook', 'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*')],
                        ['label' => 'Rendimientos', 'route' => 'viticulturist.digital-notebook.estimated-yields.index', 'active' => request()->routeIs('viticulturist.digital-notebook.estimated-yields.*')],
                        ['label' => 'Entregas a Bodega', 'route' => 'viticulturist.vendimia.index', 'active' => request()->routeIs('viticulturist.vendimia.*')],
                    ],
                ],
                [
                    'icon' => 'bug-ant',
                    'label' => 'Gestión de Plagas',
                    'route' => 'viticulturist.pest-management.index',
                    'active' => request()->routeIs('viticulturist.pest-management.*'),
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
                    'active' => request()->routeIs('plots.plantings.*') || request()->routeIs('viticulturist.phenology.*'),
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
            ];

            // GRUPO: NORMATIVA (registros y certificaciones)
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
            ];

            // GRUPO: CUADERNO OFICIAL (compliance del cuaderno de campo)
            $menu['cuaderno_official'] = [
                [
                    'icon' => 'chart-bar',
                    'label' => 'Cumplimiento Cuaderno',
                    'route' => 'viticulturist.pac-compliance',
                    'active' => request()->routeIs('viticulturist.pac-compliance'),
                ],
                [
                    'icon' => 'clipboard-document-check',
                    'label' => 'Análisis de Residuos',
                    'route' => 'viticulturist.residue-analyses.index',
                    'active' => request()->routeIs('viticulturist.residue-analyses.*'),
                ],
                [
                    'icon' => 'trash',
                    'label' => 'Gestión de Residuos',
                    'route' => 'viticulturist.residue-managements.index',
                    'active' => request()->routeIs('viticulturist.residue-managements.*'),
                ],
                [
                    'icon' => 'bolt',
                    'label' => 'Consumo Energético',
                    'route' => 'viticulturist.energy-usages.index',
                    'active' => request()->routeIs('viticulturist.energy-usages.*'),
                ],
                [
                    'icon' => 'arrow-up-tray',
                    'label' => 'Exportaciones CUE',
                    'route' => 'viticulturist.cue-exports.index',
                    'active' => request()->routeIs('viticulturist.cue-exports.*'),
                ],
                [
                    'icon' => 'document',
                    'label' => 'Informes Oficiales',
                    'route' => 'viticulturist.official-reports.index',
                    'active' => request()->routeIs('viticulturist.official-reports.*'),
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
                    'label' => 'Equipos y Personal',
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
                    'icon' => 'shopping-cart',
                    'label' => 'Cosecha Comercializada',
                    'route' => 'viticulturist.marketed-harvests.index',
                    'active' => request()->routeIs('viticulturist.marketed-harvests.*'),
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

            // GRUPO: SISTEMA
            $menu['system'] = [
                [
                    'icon' => 'cog-6-tooth',
                    'label' => 'Configuración',
                    'route' => 'viticulturist.settings',
                    'active' => request()->routeIs('viticulturist.settings'),
                ],
                [
                    'icon' => 'question-mark-circle',
                    'label' => 'Soporte',
                    'route' => 'viticulturist.support.index',
                    'active' => request()->routeIs('viticulturist.support.*'),
                    'badge' => $user->supportTickets()->open()->count(),
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
            $menu['main'][] = [
                'icon'   => 'clipboard-document-list',
                'label'  => 'Campañas de Vendimia',
                'route'  => 'winery.campaigns.index',
                'active' => request()->routeIs('winery.campaigns*'),
            ];
            $menu['main'][] = [
                'icon'   => 'user-group',
                'label'  => 'Mis Viticultores',
                'route'  => 'winery.viticulturists.index',
                'active' => request()->routeIs('winery.viticulturists*'),
            ];

            // ── VENDIMIA ─────────────────────────────────────────────
            $menu['harvest'] = [
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
                    'icon'   => 'beaker',
                    'label'  => 'Análisis de Calidad',
                    'route'  => 'winery.harvest-quality.index',
                    'active' => request()->routeIs('winery.harvest-quality*'),
                ],
                [
                    'icon'   => 'clipboard-document-list',
                    'label'  => 'Actividades de campo',
                    'route'  => 'winery.field-activities.index',
                    'active' => request()->routeIs('winery.field-activities*'),
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
                    'icon'   => 'magnifying-glass',
                    'label'  => 'Análisis de Lab.',
                    'route'  => 'winery.wine-analysis.index',
                    'active' => request()->routeIs('winery.wine-analysis*'),
                ],
                [
                    'icon'   => 'archive-box',
                    'label'  => 'Lotes de Vino',
                    'route'  => 'winery.wine-lots.index',
                    'active' => request()->routeIs('winery.wine-lots*'),
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
            ];

            // ── RECURSOS ─────────────────────────────────────────────
            $menu['resources'] = [
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

            // ── FACTURACIÓN Y CLIENTES ───────────────────────────────
            $menu['billing'] = [
                [
                    'icon'   => 'arrow-down-tray',
                    'label'  => 'Compra de Uva',
                    'route'  => 'winery.invoices.grape-purchase.index',
                    'active' => request()->routeIs('winery.invoices.grape-purchase*'),
                ],
                [
                    'icon'   => 'arrow-up-tray',
                    'label'  => 'Venta de Vino',
                    'route'  => 'winery.invoices.wine-sale.index',
                    'active' => request()->routeIs('winery.invoices.wine-sale*'),
                ],
                [
                    'icon'   => 'users',
                    'label'  => 'Clientes',
                    'route'  => 'winery.clients.index',
                    'active' => request()->routeIs('winery.clients*'),
                ],
            ];

            // ── REGISTRO OFICIAL ─────────────────────────────────────
            $menu['compliance'] = [
                [
                    'icon'   => 'document-chart-bar',
                    'label'  => 'SILICIE',
                    'route'  => 'winery.silicie.dashboard',
                    'active' => request()->routeIs('winery.silicie*'),
                    'submenu' => [
                        ['label' => 'Panel', 'route' => 'winery.silicie.dashboard', 'active' => request()->routeIs('winery.silicie.dashboard')],
                        ['label' => 'Movimientos', 'route' => 'winery.silicie.movements.index', 'active' => request()->routeIs('winery.silicie.movements*')],
                    ],
                ],
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
            ];
        }

        if ($role === 'admin') {
            $menu['main'][] = [
                'icon' => 'user-group',
                'label' => 'Usuarios',
                'route' => 'admin.users.index',
                'active' => request()->routeIs('admin.users.*'),
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
            'admin' => 'Administrador',
            'supervisor' => 'Supervisor',
            'winery' => 'Bodega',
            'viticulturist' => 'Viticultor',
            default => ucfirst($role),
        };
    }
}
