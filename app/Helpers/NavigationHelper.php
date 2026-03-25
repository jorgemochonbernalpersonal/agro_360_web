<?php

namespace App\Helpers;

use App\Helpers\Navigation\DOMenu;
use App\Helpers\Navigation\ProducerMenu;
use App\Helpers\Navigation\ViticulturistMenu;
use App\Helpers\Navigation\WineryMenu;
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

        return match ($user->role) {
            'viticulturist' => ViticulturistMenu::build($user),
            'winery'        => WineryMenu::build($user),
            'producer'      => ProducerMenu::build($user),
            'supervisor'    => DOMenu::build($user),
            'admin'         => static::buildAdminMenu(),
            default         => [],
        };
    }

    private static function buildAdminMenu(): array
    {
        return [
            'main' => [
                [
                    'icon'   => 'user-group',
                    'label'  => 'Usuarios',
                    'route'  => 'admin.users.index',
                    'active' => request()->routeIs('admin.users.*'),
                ],
                [
                    'icon'   => 'building-office-2',
                    'label'  => 'Organizaciones',
                    'route'  => 'admin.organizations.index',
                    'active' => request()->routeIs('admin.organizations.*'),
                ],
                [
                    'icon'   => 'lifebuoy',
                    'label'  => 'Soporte',
                    'route'  => 'admin.support.index',
                    'active' => request()->routeIs('admin.support.*'),
                    'badge'  => Cache::remember('nav_badge_admin_support', 60, fn() => \App\Models\SupportTicket::open()->count()),
                ],
            ],
        ];
    }

    /**
     * Obtener el nombre del rol en español
     */
    public static function getRoleName(string $role): string
    {
        return match ($role) {
            'admin'                  => 'Administrador',
            'supervisor'             => 'Denominación de Origen',
            'denomination_of_origin' => 'Denominación de Origen',
            'winery'                 => 'Bodega',
            'viticulturist'          => 'Viticultor',
            'producer'               => 'Productor',
            default                  => ucfirst($role),
        };
    }
}
