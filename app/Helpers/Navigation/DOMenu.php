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
            ['icon' => 'squares-2x2', 'label' => __('Dashboard'),          'route' => 'supervisor.dashboard',      'active' => request()->routeIs('supervisor.dashboard')],
            ['icon' => 'inbox',        'label' => __('Solicitudes / Actas'), 'route' => 'supervisor.requests.index', 'active' => request()->routeIs('supervisor.requests.*'),
                'badge' => $inboxCount ?: null],
        ];

        $menu['do_census'] = [
            ['icon' => 'users', 'label' => __('Censo DO'), 'route' => 'supervisor.census.index', 'active' => request()->routeIs('supervisor.census.*')],
        ];

        $menu['do_growers'] = [
            ['icon' => 'user-group', 'label' => __('Viticultores DO'),  'route' => 'supervisor.growers.index', 'active' => request()->routeIs('supervisor.growers.*')],
            ['icon' => 'map-pin',    'label' => __('SIGPAC'),           'route' => 'sigpac.codes',             'active' => request()->routeIs('sigpac.*')],
        ];

        $menu['do_campaigns'] = [
            ['icon' => 'flag', 'label' => __('Campañas de Vendimia'), 'route' => 'supervisor.campaigns.index', 'active' => request()->routeIs('supervisor.campaigns.*')],
        ];

        $menu['do_oversight_wineries'] = [
            ['icon' => 'building-office-2', 'label' => __('Panel de Bodegas'), 'route' => 'supervisor.oversight.wineries.index', 'active' => request()->routeIs('supervisor.oversight.wineries.*')],
        ];

        $menu['do_oversight_growers'] = [
            ['icon' => 'eye',                     'label' => __('Panel de viticultores'),   'route' => 'supervisor.oversight.growers.index',       'active' => request()->routeIs('supervisor.oversight.growers.index')],
            ['icon' => 'map',                     'label' => __('Parcelas DO'),             'route' => 'supervisor.oversight.plots.index',          'active' => request()->routeIs('supervisor.oversight.plots.*')],
            ['icon' => 'book-open',               'label' => __('Cuaderno de campo'),       'route' => 'supervisor.oversight.notebook.index',       'active' => request()->routeIs('supervisor.oversight.notebook.*')],
            ['icon' => 'lock-open',               'label' => __('Acceso al Cuaderno'),      'route' => 'supervisor.notebook.index',                 'active' => request()->routeIs('supervisor.notebook.*')],
            ['icon' => 'document-check',          'label' => __('Cumplimiento PAC'),        'route' => 'supervisor.oversight.pac.index',            'active' => request()->routeIs('supervisor.oversight.pac.*')],
            ['icon' => 'check-badge',             'label' => __('Certificaciones'),         'route' => 'supervisor.oversight.certifications.index', 'active' => request()->routeIs('supervisor.oversight.certifications.*')],
            ['icon' => 'clipboard-document-list', 'label' => __('Stream de actividad'),     'route' => 'supervisor.oversight.activity.index',       'active' => request()->routeIs('supervisor.oversight.activity.*')],
        ];

        $menu['do_qualification'] = [
            ['icon' => 'star', 'label' => __('Calificación de Vinos'), 'route' => 'supervisor.qualification.index', 'active' => request()->routeIs('supervisor.qualification.*')],
        ];

        $menu['do_labels'] = [
            ['icon' => 'tag', 'label' => __('Contraetiquetas'), 'route' => 'supervisor.labels.index', 'active' => request()->routeIs('supervisor.labels.*')],
        ];

        $menu['do_inspection'] = [
            ['icon' => 'shield-check', 'label' => __('Control e Inspecciones'), 'route' => 'supervisor.inspection.index', 'active' => request()->routeIs('supervisor.inspection.*')],
        ];

        $menu['do_regulation'] = [
            ['icon' => 'document-text', 'label' => __('Normativa y Autorizaciones'), 'route' => 'supervisor.regulation.index', 'active' => request()->routeIs('supervisor.regulation.*')],
        ];

        $menu['do_documents'] = [
            ['icon' => 'document-duplicate', 'label' => __('Pliegos y Reglamentos'), 'route' => 'supervisor.documents.index', 'active' => request()->routeIs('supervisor.documents.*')],
        ];

        $menu['do_territory'] = [
            ['icon' => 'map', 'label' => __('Territorio DO'), 'route' => 'supervisor.territory.index', 'active' => request()->routeIs('supervisor.territory.*')],
        ];

        $menu['do_statistics'] = [
            ['icon' => 'chart-bar', 'label' => __('Estadísticas e Informes'), 'route' => 'supervisor.statistics.index', 'active' => request()->routeIs('supervisor.statistics.*')],
        ];

        $menu['do_finance'] = [
            ['icon' => 'banknotes', 'label' => __('Finanzas DO'), 'route' => 'supervisor.finance.index', 'active' => request()->routeIs('supervisor.finance.*')],
        ];

        $menu['do_settings'] = [
            ['icon' => 'cog-6-tooth', 'label' => __('Sistema y Configuración'), 'route' => 'supervisor.settings.index', 'active' => request()->routeIs('supervisor.settings.*')],
        ];

        return $menu;
    }
}
