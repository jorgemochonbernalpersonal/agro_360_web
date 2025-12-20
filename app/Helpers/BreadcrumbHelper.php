<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    /**
     * Generar breadcrumbs basado en la ruta actual
     */
    public static function generate(): array
    {
        $breadcrumbs = [];
        $user = auth()->user();
        
        if (!$user) {
            return $breadcrumbs;
        }

        // Parcelas
        if (request()->routeIs('plots.*')) {
            $breadcrumbs[] = [
                'label' => 'Parcelas',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>',
                'route' => 'plots.index',
                'active' => request()->routeIs('plots.index'),
            ];

            if (request()->routeIs('plots.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nueva Parcela',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('plots.edit')) {
                $plotName = request()->route('plot') ? 'Editar' : 'Editar Parcela';
                $breadcrumbs[] = [
                    'label' => $plotName,
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('plots.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // SIGPAC
        if (request()->routeIs('sigpac.*')) {
            $breadcrumbs[] = [
                'label' => 'SIGPAC',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'route' => 'sigpac.index',
                'active' => request()->routeIs('sigpac.index'),
            ];
        }

        // Configuración
        if (request()->routeIs('config.*')) {
            $breadcrumbs[] = [
                'label' => 'Configuración',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'route' => null,
                'active' => true,
            ];
        }

        // Perfil
        if (request()->routeIs('profile.*')) {
            $breadcrumbs[] = [
                'label' => 'Mi Perfil',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'route' => 'profile.show',
                'active' => request()->routeIs('profile.show'),
            ];

            if (request()->routeIs('profile.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Campañas
        if (request()->routeIs('viticulturist.campaign.*')) {
            $breadcrumbs[] = [
                'label' => 'Campañas',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                'route' => 'viticulturist.campaign.index',
                'active' => request()->routeIs('viticulturist.campaign.index'),
            ];

            if (request()->routeIs('viticulturist.campaign.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nueva Campaña',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.campaign.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.campaign.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Cuaderno Digital
        if (request()->routeIs('viticulturist.digital-notebook*') && !request()->routeIs('viticulturist.digital-notebook.containers.*') && !request()->routeIs('viticulturist.digital-notebook.estimated-yields.*')) {
            $breadcrumbs[] = [
                'label' => 'Cuaderno Digital',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'route' => 'viticulturist.digital-notebook',
                'active' => request()->routeIs('viticulturist.digital-notebook') && !request()->routeIs('viticulturist.digital-notebook.*'),
            ];

            if (request()->routeIs('viticulturist.digital-notebook.harvest.create')) {
                $breadcrumbs[] = [
                    'label' => 'Registrar Cosecha',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.digital-notebook.harvest.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles de Cosecha',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.digital-notebook.harvest.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar Cosecha',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Contenedores
        if (request()->routeIs('viticulturist.digital-notebook.containers.*')) {
            $breadcrumbs[] = [
                'label' => 'Contenedores',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                'route' => 'viticulturist.digital-notebook.containers.index',
                'active' => request()->routeIs('viticulturist.digital-notebook.containers.index'),
            ];

            if (request()->routeIs('viticulturist.digital-notebook.containers.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nuevo Contenedor',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.digital-notebook.containers.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar Contenedor',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Rendimientos Estimados
        if (request()->routeIs('viticulturist.digital-notebook.estimated-yields.*')) {
            $breadcrumbs[] = [
                'label' => 'Rendimientos Estimados',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                'route' => 'viticulturist.digital-notebook.estimated-yields.index',
                'active' => request()->routeIs('viticulturist.digital-notebook.estimated-yields.index'),
            ];

            if (request()->routeIs('viticulturist.digital-notebook.estimated-yields.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nuevo Rendimiento',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.digital-notebook.estimated-yields.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar Rendimiento',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Equipos y Personal
        if (request()->routeIs('viticulturist.personal.*') || request()->routeIs('viticulturist.viticulturists.*')) {
            $breadcrumbs[] = [
                'label' => 'Equipos y Personal',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'route' => 'viticulturist.personal.index',
                'active' => request()->routeIs('viticulturist.personal.index'),
            ];

            if (request()->routeIs('viticulturist.personal.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nuevo Equipo',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.viticulturists.create') || request()->routeIs('viticulturist.personal.viticulturist.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nuevo Viticultor',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.personal.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.personal.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Maquinaria
        if (request()->routeIs('viticulturist.machinery.*')) {
            $breadcrumbs[] = [
                'label' => 'Maquinaria',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'route' => 'viticulturist.machinery.index',
                'active' => request()->routeIs('viticulturist.machinery.index'),
            ];

            if (request()->routeIs('viticulturist.machinery.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nueva Maquinaria',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.machinery.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.machinery.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Productos Fitosanitarios
        if (request()->routeIs('viticulturist.phytosanitary-products.*')) {
            $breadcrumbs[] = [
                'label' => 'Productos Fitosanitarios',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
                'route' => 'viticulturist.phytosanitary-products.index',
                'active' => request()->routeIs('viticulturist.phytosanitary-products.index'),
            ];

            if (request()->routeIs('viticulturist.phytosanitary-products.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nuevo Producto',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.phytosanitary-products.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Facturación
        if (request()->routeIs('viticulturist.invoices.*')) {
            $breadcrumbs[] = [
                'label' => 'Facturación',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'route' => 'viticulturist.invoices.index',
                'active' => request()->routeIs('viticulturist.invoices.index'),
            ];

            if (request()->routeIs('viticulturist.invoices.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nueva Factura',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.invoices.harvest.*')) {
                $breadcrumbs[] = [
                    'label' => 'Facturar Cosecha',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'route' => 'viticulturist.invoices.harvest.index',
                    'active' => request()->routeIs('viticulturist.invoices.harvest.index'),
                ];
            } elseif (request()->routeIs('viticulturist.invoices.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.invoices.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Clientes
        if (request()->routeIs('viticulturist.clients.*')) {
            $breadcrumbs[] = [
                'label' => 'Equipos y Personal',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'route' => 'viticulturist.personal.index',
                'active' => false,
            ];
            $breadcrumbs[] = [
                'label' => 'Clientes',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                'route' => 'viticulturist.clients.index',
                'active' => request()->routeIs('viticulturist.clients.index'),
            ];

            if (request()->routeIs('viticulturist.clients.create')) {
                $breadcrumbs[] = [
                    'label' => 'Nuevo Cliente',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.clients.show')) {
                $breadcrumbs[] = [
                    'label' => 'Detalles',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            } elseif (request()->routeIs('viticulturist.clients.edit')) {
                $breadcrumbs[] = [
                    'label' => 'Editar',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Configuración
        if (request()->routeIs('viticulturist.settings.*')) {
            $breadcrumbs[] = [
                'label' => 'Configuración',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'route' => null,
                'active' => false,
            ];

            if (request()->routeIs('viticulturist.settings.taxes')) {
                $breadcrumbs[] = [
                    'label' => 'Impuestos',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'route' => null,
                    'active' => true,
                ];
            }
        }

        // Calendario
        if (request()->routeIs('viticulturist.calendar')) {
            $breadcrumbs[] = [
                'label' => 'Calendario',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                'route' => null,
                'active' => true,
            ];
        }

        return $breadcrumbs;
    }
}
