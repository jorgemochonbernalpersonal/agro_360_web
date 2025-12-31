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
                'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'label' => 'Dashboard',
                'route' => 'viticulturist.dashboard',
                'active' => request()->routeIs('viticulturist.dashboard'),
            ];

            // GRUPO: OPERACIONES
            $menu['operations'] = [
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
                    'label' => 'Campaña',
                    'route' => 'viticulturist.campaign.index',
                    'active' => request()->routeIs('viticulturist.campaign*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                    'label' => 'Calendario',
                    'route' => 'viticulturist.calendar',
                    'active' => request()->routeIs('viticulturist.calendar'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'label' => 'Cuaderno Digital',
                    'route' => 'viticulturist.digital-notebook',
                    'active' => request()->routeIs('viticulturist.digital-notebook*') || request()->routeIs('viticulturist.phytosanitary-products.*'),
                    'submenu' => [
                        ['label' => 'Actividades', 'route' => 'viticulturist.digital-notebook', 'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*')],
                        ['label' => 'Rendimientos', 'route' => 'viticulturist.digital-notebook.estimated-yields.index', 'active' => request()->routeIs('viticulturist.digital-notebook.estimated-yields.*')],
                        ['label' => 'Contenedores', 'route' => 'viticulturist.digital-notebook.containers.index', 'active' => request()->routeIs('viticulturist.digital-notebook.containers.*')],
                        ['label' => 'Fitosanitarios', 'route' => 'viticulturist.phytosanitary-products.index', 'active' => request()->routeIs('viticulturist.phytosanitary-products.*')],
                        ['label' => 'Nuevo Tratamiento', 'route' => 'viticulturist.digital-notebook.treatment.create', 'active' => request()->routeIs('viticulturist.digital-notebook.treatment.*')],
                        ['label' => 'Nueva Fertilización', 'route' => 'viticulturist.digital-notebook.fertilization.create', 'active' => request()->routeIs('viticulturist.digital-notebook.fertilization.*')],
                        ['label' => 'Nuevo Riego', 'route' => 'viticulturist.digital-notebook.irrigation.create', 'active' => request()->routeIs('viticulturist.digital-notebook.irrigation.*')],
                        ['label' => 'Labor Cultural', 'route' => 'viticulturist.digital-notebook.cultural.create', 'active' => request()->routeIs('viticulturist.digital-notebook.cultural.*')],
                        ['label' => 'Observación', 'route' => 'viticulturist.digital-notebook.observation.create', 'active' => request()->routeIs('viticulturist.digital-notebook.observation.*')],
                        ['label' => 'Nueva Cosecha', 'route' => 'viticulturist.digital-notebook.harvest.create', 'active' => request()->routeIs('viticulturist.digital-notebook.harvest.create')],
                    ],
                ],
            ];

            // GRUPO: PARCELAS Y ANÁLISIS
            $menu['plots_analysis'] = [
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>',
                    'label' => 'Parcelas',
                    'route' => 'plots.index',
                    'active' => request()->routeIs('plots.*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'label' => 'SIGPAC',
                    'route' => 'sigpac.codes',
                    'active' => request()->routeIs('sigpac.*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    'label' => 'Teledetección',
                    'route' => 'remote-sensing.dashboard',
                    'active' => request()->routeIs('remote-sensing.*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                    'label' => 'Cumplimiento PAC',
                    'route' => 'viticulturist.pac-compliance',
                    'active' => request()->routeIs('viticulturist.pac-compliance'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                    'label' => 'Gestión de Plagas',
                    'route' => 'viticulturist.pest-management.index',
                    'active' => request()->routeIs('viticulturist.pest-management.*'),
                ],
            ];

            // GRUPO: RECURSOS
            $menu['resources'] = [
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                    'label' => 'Equipos y Personal',
                    'route' => 'viticulturist.personal.index',
                    'active' => request()->routeIs('viticulturist.personal*') || request()->routeIs('viticulturist.viticulturists.*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                    'label' => 'Maquinaria',
                    'route' => 'viticulturist.machinery.index',
                    'active' => request()->routeIs('viticulturist.machinery*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                    'label' => 'Almacenes',
                    'route' => 'viticulturist.warehouses.index',
                    'active' => request()->routeIs('viticulturist.warehouses.*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
                    'label' => 'Inventario',
                    'route' => 'viticulturist.inventory.index',
                    'active' => request()->routeIs('viticulturist.inventory.*'),
                ],
            ];

            // GRUPO: FACTURACIÓN
            $menu['billing'] = [
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                    'label' => 'Facturas',
                    'route' => 'viticulturist.invoices.index',
                    'active' => request()->routeIs('viticulturist.invoices.*'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                    'label' => 'Estadísticas Financieras',
                    'route' => 'viticulturist.financial-stats',
                    'active' => request()->routeIs('viticulturist.financial-stats'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'label' => 'Informes Oficiales',
                    'route' => 'viticulturist.official-reports.index',
                    'active' => request()->routeIs('viticulturist.official-reports.*'),
                ],
            ];

            // GRUPO: CLIENTES
            $menu['clients'] = [
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                    'label' => 'Clientes',
                    'route' => 'viticulturist.clients.index',
                    'active' => request()->routeIs('viticulturist.clients.*'),
                ],
            ];

            // GRUPO: SISTEMA
            $menu['system'] = [
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'label' => 'Configuración',
                    'route' => 'viticulturist.settings',
                    'active' => request()->routeIs('viticulturist.settings'),
                ],
                [
                    'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    'label' => 'Soporte',
                    'route' => 'viticulturist.support.index',
                    'active' => request()->routeIs('viticulturist.support.*'),
                    'badge' => $user->supportTickets()->open()->count(),
                ],
            ];
        }

        if ($role === 'admin') {
            $menu['main'][] = [
                'icon_svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
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
