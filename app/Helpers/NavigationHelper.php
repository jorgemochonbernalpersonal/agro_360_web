<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        // Cachear el menú por usuario durante 1 hora
        return Cache::remember('menu_' . $user->id . '_' . request()->path(), 3600, function() use ($user) {
            return static::buildMenu($user);
        });
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

            // GRUPO: OPERACIONES
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
                    'active' => request()->routeIs('viticulturist.digital-notebook*'),
                    'submenu' => [
                        ['label' => 'Actividades', 'route' => 'viticulturist.digital-notebook', 'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*')],
                        ['label' => 'Rendimientos', 'route' => 'viticulturist.digital-notebook.estimated-yields.index', 'active' => request()->routeIs('viticulturist.digital-notebook.estimated-yields.*')],
                    ],
                ],
            ];

            // GRUPO: PARCELAS Y ANÁLISIS
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
                    'icon' => 'bug-ant',
                    'label' => 'Gestión de Plagas',
                    'route' => 'viticulturist.pest-management.index',
                    'active' => request()->routeIs('viticulturist.pest-management.*'),
                ],
            ];

            // GRUPO: REGISTRO OFICIAL
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
                    'icon' => 'arrow-up-tray',
                    'label' => 'Exportaciones CUE',
                    'route' => 'viticulturist.cue-exports.index',
                    'active' => request()->routeIs('viticulturist.cue-exports.*'),
                ],
                [
                    'icon' => 'chart-bar',
                    'label' => 'Cumplimiento PAC',
                    'route' => 'viticulturist.pac-compliance',
                    'active' => request()->routeIs('viticulturist.pac-compliance'),
                ],
                [
                    'icon' => 'document',
                    'label' => 'Informes Oficiales',
                    'route' => 'viticulturist.official-reports.index',
                    'active' => request()->routeIs('viticulturist.official-reports.*'),
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
                [
                    'icon' => 'bolt',
                    'label' => 'Consumo Energético',
                    'route' => 'viticulturist.energy-usages.index',
                    'active' => request()->routeIs('viticulturist.energy-usages.*'),
                ],
            ];

            // GRUPO: FACTURACIÓN
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
                    'icon' => 'presentation-chart-bar',
                    'label' => 'Estadísticas Financieras',
                    'route' => 'viticulturist.financial-stats',
                    'active' => request()->routeIs('viticulturist.financial-stats'),
                ],
            ];

            // GRUPO: CLIENTES
            $menu['clients'] = [
                [
                    'icon' => 'users',
                    'label' => 'Clientes',
                    'route' => 'viticulturist.clients.index',
                    'active' => request()->routeIs('viticulturist.clients.*'),
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
