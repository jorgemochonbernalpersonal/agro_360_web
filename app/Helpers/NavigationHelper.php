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

        $menu = [
            'main' => [
                [
                    'icon' => '🏠',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                    'label' => 'Dashboard',
                    'route' => $user->role . '.dashboard',
                    'active' => request()->routeIs($user->role . '.dashboard'),
                ],
                // Informes Oficiales solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '📄',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'label' => 'Informes Oficiales',
                    'route' => 'viticulturist.official-reports.index',
                    'active' => request()->routeIs('viticulturist.official-reports.*'),
                    'submenu' => [
                        [
                            'label' => 'Ver Informes',
                            'route' => 'viticulturist.official-reports.index',
                            'active' => request()->routeIs('viticulturist.official-reports.index'),
                        ],
                        [
                            'label' => 'Generar Nuevo Informe',
                            'route' => 'viticulturist.official-reports.create',
                            'active' => request()->routeIs('viticulturist.official-reports.create'),
                        ],
                    ],
                ]] : []),
                // Campaña solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '📅',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                    'label' => 'Campaña',
                    'route' => 'viticulturist.campaign.index',
                    'active' => request()->routeIs('viticulturist.campaign*'),
                ]] : []),
                [
                    'icon' => '🌾',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>',
                    'label' => 'Parcelas',
                    'route' => 'plots.index',
                    'active' => request()->routeIs('plots.*'),
                    'submenu' => [
                        [
                            'label' => 'Ver Parcelas',
                            'route' => 'plots.index',
                            'active' => request()->routeIs('plots.index'),
                        ],
                        [
                            'label' => 'Ver Plantaciones',
                            'route' => 'plots.plantings.index',
                            'active' => request()->routeIs('plots.plantings.index'),
                        ],
                        [
                            'label' => 'Crear Parcela',
                            'route' => 'plots.create',
                            'active' => request()->routeIs('plots.create'),
                        ],
                    ],
                ],
                [
                    'icon' => '📋',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'label' => 'SIGPACs',
                    'route' => 'sigpac.index',
                    'active' => request()->routeIs('sigpac.*'),
                    'submenu' => [
                        [
                            'label' => 'Códigos SIGPAC',
                            'route' => 'sigpac.codes',
                            'active' => request()->routeIs('sigpac.codes'),
                        ],
                        [
                            'label' => 'Crear Código SIGPAC',
                            'route' => 'sigpac.codes.create',
                            'active' => request()->routeIs('sigpac.codes.create'),
                        ],
                        [
                            'label' => 'Usos SIGPAC',
                            'route' => 'sigpac.uses',
                            'active' => request()->routeIs('sigpac.uses'),
                        ],
                    ],
                ],
                // Cuaderno Digital solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '📔',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'label' => 'Cuaderno Digital',
                    'route' => 'viticulturist.digital-notebook',
                    'active' => request()->routeIs('viticulturist.digital-notebook') 
                        || request()->routeIs('viticulturist.digital-notebook.treatment.*')
                        || request()->routeIs('viticulturist.digital-notebook.fertilization.*')
                        || request()->routeIs('viticulturist.digital-notebook.irrigation.*')
                        || request()->routeIs('viticulturist.digital-notebook.cultural.*')
                        || request()->routeIs('viticulturist.digital-notebook.observation.*')
                        || request()->routeIs('viticulturist.digital-notebook.harvest.*')
                        || request()->routeIs('viticulturist.digital-notebook.estimated-yields.*')
                        || request()->routeIs('viticulturist.phytosanitary-products.*'),
                    'submenu' => [
                        [
                            'label' => 'Ver Actividades',
                            'route' => 'viticulturist.digital-notebook',
                            'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*'),
                        ],
                        [
                            'label' => 'Productos fitosanitarios',
                            'route' => 'viticulturist.phytosanitary-products.index',
                            'active' => request()->routeIs('viticulturist.phytosanitary-products.*'),
                        ],
                        [
                            'label' => 'Registrar Tratamiento',
                            'route' => 'viticulturist.digital-notebook.treatment.create',
                            'active' => request()->routeIs('viticulturist.digital-notebook.treatment.*'),
                        ],
                        [
                            'label' => 'Registrar Fertilización',
                            'route' => 'viticulturist.digital-notebook.fertilization.create',
                            'active' => request()->routeIs('viticulturist.digital-notebook.fertilization.*'),
                        ],
                        [
                            'label' => 'Registrar Riego',
                            'route' => 'viticulturist.digital-notebook.irrigation.create',
                            'active' => request()->routeIs('viticulturist.digital-notebook.irrigation.*'),
                        ],
                        [
                            'label' => 'Registrar Labor',
                            'route' => 'viticulturist.digital-notebook.cultural.create',
                            'active' => request()->routeIs('viticulturist.digital-notebook.cultural.*'),
                        ],
                        [
                            'label' => 'Registrar Observación',
                            'route' => 'viticulturist.digital-notebook.observation.create',
                            'active' => request()->routeIs('viticulturist.digital-notebook.observation.*'),
                        ],
                        [
                            'label' => 'Registrar Cosecha',
                            'route' => 'viticulturist.digital-notebook.harvest.create',
                            'active' => request()->routeIs('viticulturist.digital-notebook.harvest.create'),
                        ],
                        [
                            'label' => 'Rendimientos Estimados',
                            'route' => 'viticulturist.digital-notebook.estimated-yields.index',
                            'active' => request()->routeIs('viticulturist.digital-notebook.estimated-yields.*'),
                        ],
                    ],
                ]] : []),
                // Contenedores solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '📦',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                    'label' => 'Contenedores',
                    'route' => 'viticulturist.digital-notebook.containers.index',
                    'active' => request()->routeIs('viticulturist.digital-notebook.containers.*'),
                ]] : []),
                // Facturación solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '🧾',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'label' => 'Facturación',
                    'route' => 'viticulturist.invoices.index',
                    'active' => request()->routeIs('viticulturist.invoices.*') || request()->routeIs('viticulturist.invoices.harvest.*'),
                    'submenu' => [
                        [
                            'label' => 'Pedidos',
                            'route' => 'viticulturist.invoices.index',
                            'active' => request()->routeIs('viticulturist.invoices.index') || request()->routeIs('viticulturist.invoices.create') || request()->routeIs('viticulturist.invoices.edit') || request()->routeIs('viticulturist.invoices.show'),
                        ],
                        [
                            'label' => 'Facturar Cosecha',
                            'route' => 'viticulturist.invoices.harvest.index',
                            'active' => request()->routeIs('viticulturist.invoices.harvest.*'),
                        ],
                    ],
                ]] : []),
                // Clientes solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '👤',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                    'label' => 'Clientes',
                    'route' => 'viticulturist.clients.index',
                    'active' => request()->routeIs('viticulturist.clients.*'),
                    'submenu' => [
                        [
                            'label' => 'Ver Clientes',
                            'route' => 'viticulturist.clients.index',
                            'active' => request()->routeIs('viticulturist.clients.index'),
                        ],
                        [
                            'label' => 'Crear Cliente',
                            'route' => 'viticulturist.clients.create',
                            'active' => request()->routeIs('viticulturist.clients.create'),
                        ],
                    ],
                ]] : []),
                // Personal y Equipos (unificado) solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '👥',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                    'label' => 'Equipos y Personal',
                    'route' => 'viticulturist.personal.index',
                    'active' => request()->routeIs('viticulturist.personal*') || request()->routeIs('viticulturist.viticulturists.*'),
                    'submenu' => [
                        [
                            'label' => 'Crear Equipo',
                            'route' => 'viticulturist.personal.create',
                            'active' => request()->routeIs('viticulturist.personal.create'),
                        ],
                        [
                            'label' => 'Crear Viticultor',
                            'route' => 'viticulturist.viticulturists.create',
                            'active' => request()->routeIs('viticulturist.viticulturists.create'),
                        ],
                    ],
                ]] : []),
                // Maquinaria solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '🚜',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'label' => 'Maquinaria',
                    'route' => 'viticulturist.machinery.index',
                    'active' => request()->routeIs('viticulturist.machinery*'),
                    'submenu' => [
                        [
                            'label' => 'Ver Maquinaria',
                            'route' => 'viticulturist.machinery.index',
                            'active' => request()->routeIs('viticulturist.machinery.index'),
                        ],
                        [
                            'label' => 'Crear Maquinaria',
                            'route' => 'viticulturist.machinery.create',
                            'active' => request()->routeIs('viticulturist.machinery.create'),
                        ],
                    ],
                ]] : []),
                // Calendario solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'icon' => '📆',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                    'label' => 'Calendario',
                    'route' => 'viticulturist.calendar',
                    'active' => request()->routeIs('viticulturist.calendar*'),
                ]] : []),
                // Configuración solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'label' => 'Configuración',
                    'route' => 'viticulturist.settings',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'active' => request()->routeIs('viticulturist.settings'),
                    'submenu' => [
                        [
                            'label' => 'Impuestos',
                            'route' => 'viticulturist.settings',
                            'query' => ['tab' => 'taxes'],
                            'active' => request()->routeIs('viticulturist.settings') && (request()->query('tab') === 'taxes' || !request()->query('tab')),
                        ],
                        [
                            'label' => 'Numeración',
                            'route' => 'viticulturist.settings',
                            'query' => ['tab' => 'invoicing'],
                            'active' => request()->routeIs('viticulturist.settings') && request()->query('tab') === 'invoicing',
                        ],
                        [
                            'label' => 'Firma Digital',
                            'route' => 'viticulturist.settings',
                            'query' => ['tab' => 'signature'],
                            'active' => request()->routeIs('viticulturist.settings') && request()->query('tab') === 'signature',
                        ],
                    ],
                ]] : []),
                // Soporte solo para viticultor
                ...($user->role === 'viticulturist' ? [[
                    'label' => 'Soporte',
                    'route' => 'viticulturist.support.index',
                    'icon_svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    'active' => request()->routeIs('viticulturist.support.*'),
                    'badge' => $user->supportTickets()->open()->count(),
                ]] : []),
            ],
        ];

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

