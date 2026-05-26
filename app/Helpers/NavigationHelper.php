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
                    'label'  => __('Usuarios'),
                    'route'  => 'admin.users.index',
                    'active' => request()->routeIs('admin.users.index') || request()->routeIs('admin.users.show'),
                ],
                [
                    'icon'   => 'building-office-2',
                    'label'  => __('Organizaciones'),
                    'route'  => 'admin.organizations.index',
                    'active' => request()->routeIs('admin.organizations.*'),
                ],
                [
                    'icon'   => 'lifebuoy',
                    'label'  => __('Soporte'),
                    'route'  => 'admin.support.index',
                    'active' => request()->routeIs('admin.support.*'),
                    'badge'  => Cache::remember('nav_badge_admin_support', 60, fn() => \App\Models\SupportTicket::open()->count()),
                ],
                [
                    'icon'   => 'credit-card',
                    'label'  => __('Suscripciones'),
                    'route'  => 'admin.subscriptions.index',
                    'active' => request()->routeIs('admin.subscriptions.*'),
                ],
                [
                    'icon'   => 'megaphone',
                    'label'  => __('Notificaciones'),
                    'route'  => 'admin.notifications.index',
                    'active' => request()->routeIs('admin.notifications.*'),
                ],
                [
                    'icon'   => 'shield-exclamation',
                    'label'  => __('Log de seguridad'),
                    'route'  => 'admin.security-log.index',
                    'active' => request()->routeIs('admin.security-log.*'),
                ],
                [
                    'icon'   => 'exclamation-triangle',
                    'label'  => __('Jobs fallidos'),
                    'route'  => 'admin.failed-jobs.index',
                    'active' => request()->routeIs('admin.failed-jobs.*'),
                    'badge'  => Cache::remember('nav_badge_admin_failed_jobs', 120, fn() => \DB::table('failed_jobs')->count()),
                ],
                [
                    'icon'   => 'clock',
                    'label'  => __('Scheduler'),
                    'route'  => 'admin.scheduler.index',
                    'active' => request()->routeIs('admin.scheduler.*'),
                ],
                [
                    'icon'   => 'map',
                    'label'  => __('Parcelas'),
                    'route'  => 'admin.plots.index',
                    'active' => request()->routeIs('admin.plots.*'),
                ],
                [
                    'icon'   => 'map-pin',
                    'label'  => 'SIGPAC',
                    'route'  => 'admin.sigpac.index',
                    'active' => request()->routeIs('admin.sigpac.*'),
                ],
                [
                    'icon'   => 'document-duplicate',
                    'label'  => __('Duplicados'),
                    'route'  => 'admin.users.duplicates',
                    'active' => request()->routeIs('admin.users.duplicates'),
                ],
                [
                    'icon'   => 'user-plus',
                    'label'  => __('Aprobaciones'),
                    'route'  => 'admin.users.approvals',
                    'active' => request()->routeIs('admin.users.approvals'),
                    'badge'  => Cache::remember('nav_badge_admin_approvals', 300, fn() => \App\Models\User::where('can_login', false)->whereNotNull('email_verified_at')->where('role', '!=', 'admin')->count()),
                ],
                [
                    'icon'   => 'bell-alert',
                    'label'  => __('Anuncios'),
                    'route'  => 'admin.announcements.index',
                    'active' => request()->routeIs('admin.announcements.*'),
                ],
                [
                    'icon'   => 'chat-bubble-left-right',
                    'label'  => __('Resp. rápidas'),
                    'route'  => 'admin.canned-responses.index',
                    'active' => request()->routeIs('admin.canned-responses.*'),
                ],
                [
                    'icon'   => 'cog-6-tooth',
                    'label'  => __('Configuración'),
                    'route'  => 'admin.settings.index',
                    'active' => request()->routeIs('admin.settings.*'),
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
            'admin'                  => __('Administrador'),
            'supervisor'             => __('Denominación de Origen'),
            'denomination_of_origin' => __('Denominación de Origen'),
            'winery'                 => __('Bodega'),
            'viticulturist'          => __('Viticultor'),
            'producer'               => __('Productor'),
            default                  => ucfirst($role),
        };
    }
}
