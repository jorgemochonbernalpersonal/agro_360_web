<?php

namespace App\Helpers\Navigation;

use App\Models\SupervisorRequest;
use Illuminate\Support\Facades\Cache;

class DOMenu
{
    public static function build($user): array
    {
        $menu = [];

        $inboxCount = Cache::remember(
            "supervisor:{$user->id}:inbox_count",
            60,
            fn () => SupervisorRequest::forSupervisor($user->id)
                ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
                ->count()
        );

        $menu['main'] = [
            ['icon' => 'squares-2x2', 'label' => 'Dashboard',          'route' => 'supervisor.dashboard',      'active' => request()->routeIs('supervisor.dashboard')],
            ['icon' => 'inbox',        'label' => 'Solicitudes / Actas', 'route' => 'supervisor.requests.index', 'active' => request()->routeIs('supervisor.requests.*'),
                'badge' => $inboxCount ?: null],
        ];

        $menu['do_census'] = [
            ['icon' => 'users', 'label' => 'Censo DO', 'route' => 'supervisor.census.index', 'active' => request()->routeIs('supervisor.census.*')],
        ];

        $menu['do_growers'] = [
            ['icon' => 'user-group', 'label' => 'Viticultores DO',  'route' => 'supervisor.growers.index', 'active' => request()->routeIs('supervisor.growers.*')],
            ['icon' => 'map-pin',    'label' => 'SIGPAC',           'route' => 'sigpac.codes',             'active' => request()->routeIs('sigpac.*')],
        ];

        $menu['do_campaigns'] = [
            ['icon' => 'flag', 'label' => 'Campañas de Vendimia', 'route' => 'supervisor.campaigns.index', 'active' => request()->routeIs('supervisor.campaigns.*')],
        ];

        $menu['do_oversight_wineries'] = [
            ['icon' => 'building-office-2', 'label' => 'Panel de Bodegas', 'route' => 'supervisor.oversight.wineries.index', 'active' => request()->routeIs('supervisor.oversight.wineries.*')],
        ];

        $menu['do_oversight_growers'] = [
            ['icon' => 'eye',                     'label' => 'Panel de viticultores',   'route' => 'supervisor.oversight.growers.index',       'active' => request()->routeIs('supervisor.oversight.growers.index')],
            ['icon' => 'map',                     'label' => 'Parcelas DO',             'route' => 'supervisor.oversight.plots.index',          'active' => request()->routeIs('supervisor.oversight.plots.*')],
            ['icon' => 'book-open',               'label' => 'Cuaderno de campo',       'route' => 'supervisor.oversight.notebook.index',       'active' => request()->routeIs('supervisor.oversight.notebook.*')],
            ['icon' => 'lock-open',               'label' => 'Acceso al Cuaderno',      'route' => 'supervisor.notebook.index',                 'active' => request()->routeIs('supervisor.notebook.*')],
            ['icon' => 'document-check',          'label' => 'Cumplimiento PAC',        'route' => 'supervisor.oversight.pac.index',            'active' => request()->routeIs('supervisor.oversight.pac.*')],
            ['icon' => 'check-badge',             'label' => 'Certificaciones',         'route' => 'supervisor.oversight.certifications.index', 'active' => request()->routeIs('supervisor.oversight.certifications.*')],
            ['icon' => 'clipboard-document-list', 'label' => 'Stream de actividad',     'route' => 'supervisor.oversight.activity.index',       'active' => request()->routeIs('supervisor.oversight.activity.*')],
        ];

        $menu['do_qualification'] = [
            ['icon' => 'star', 'label' => 'Calificación de Vinos', 'route' => 'supervisor.qualification.index', 'active' => request()->routeIs('supervisor.qualification.*')],
        ];

        $menu['do_labels'] = [
            ['icon' => 'tag', 'label' => 'Contraetiquetas', 'route' => 'supervisor.labels.index', 'active' => request()->routeIs('supervisor.labels.*')],
        ];

        $menu['do_inspection'] = [
            ['icon' => 'shield-check', 'label' => 'Control e Inspecciones', 'route' => 'supervisor.inspection.index', 'active' => request()->routeIs('supervisor.inspection.*')],
        ];

        $menu['do_regulation'] = [
            ['icon' => 'document-text', 'label' => 'Normativa y Autorizaciones', 'route' => 'supervisor.regulation.index', 'active' => request()->routeIs('supervisor.regulation.*')],
        ];

        $menu['do_territory'] = [
            ['icon' => 'map', 'label' => 'Territorio DO', 'route' => 'supervisor.territory.index', 'active' => request()->routeIs('supervisor.territory.*')],
        ];

        $menu['do_statistics'] = [
            ['icon' => 'chart-bar', 'label' => 'Estadísticas e Informes', 'route' => 'supervisor.statistics.index', 'active' => request()->routeIs('supervisor.statistics.*')],
        ];

        $menu['do_finance'] = [
            ['icon' => 'banknotes', 'label' => 'Finanzas DO', 'route' => 'supervisor.finance.index', 'active' => request()->routeIs('supervisor.finance.*')],
        ];

        $menu['do_settings'] = [
            ['icon' => 'cog-6-tooth', 'label' => 'Sistema y Configuración', 'route' => 'supervisor.settings.index', 'active' => request()->routeIs('supervisor.settings.*')],
        ];

        return $menu;
    }
}
