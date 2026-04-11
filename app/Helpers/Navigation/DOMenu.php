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
            ['icon' => 'squares-2x2',  'label' => 'Dashboard',          'route' => 'supervisor.dashboard',         'active' => request()->routeIs('supervisor.dashboard')],
            ['icon' => 'inbox',         'label' => 'Solicitudes / Actas', 'route' => 'supervisor.requests.index',    'active' => request()->routeIs('supervisor.requests.*'),
                'badge' => $inboxCount ?: null],
        ];

        $menu['do_census'] = [
            ['icon' => 'users',              'label' => 'Bodegas registradas',    'route' => 'supervisor.census.index', 'active' => request()->routeIs('supervisor.census.*')],
            ['icon' => 'building-storefront','label' => 'Productores registrados','route' => 'supervisor.census.index', 'active' => false],
            ['icon' => 'check-circle',       'label' => 'Altas / bajas',         'route' => 'supervisor.census.index', 'active' => false],
            ['icon' => 'tag',                'label' => 'Variedades admitidas',   'route' => 'supervisor.census.index', 'active' => false],
            ['icon' => 'map',                'label' => 'Mapa territorial DO',    'route' => 'supervisor.census.index', 'active' => false],
        ];

        $menu['do_growers'] = [
            ['icon' => 'user-group',         'label' => 'Mis viticultores DO',  'route' => 'supervisor.growers.index', 'active' => request()->routeIs('supervisor.growers.*')],
            ['icon' => 'map',                'label' => 'Parcelas',             'route' => 'supervisor.growers.index', 'active' => false],
            ['icon' => 'book-open',          'label' => 'Plantaciones',         'route' => 'supervisor.growers.index', 'active' => false],
            ['icon' => 'map-pin',            'label' => 'SIGPAC',               'route' => 'sigpac.codes',             'active' => request()->routeIs('sigpac.*')],
            ['icon' => 'users',              'label' => 'Asignación a bodegas', 'route' => 'supervisor.growers.index', 'active' => false],
        ];

        $menu['do_campaigns'] = [
            ['icon' => 'flag',                    'label' => 'Campañas de vendimia',    'route' => 'supervisor.campaigns.index', 'active' => request()->routeIs('supervisor.campaigns.*')],
            ['icon' => 'clipboard-document-list', 'label' => 'Declaraciones de cosecha','route' => 'supervisor.campaigns.index', 'active' => false],
            ['icon' => 'chart-bar',               'label' => 'Aforos por bodega',       'route' => 'supervisor.campaigns.index', 'active' => false],
            ['icon' => 'chart-bar-square',        'label' => 'Cupos y límites',         'route' => 'supervisor.campaigns.index', 'active' => false],
        ];

        $menu['do_oversight_wineries'] = [
            ['icon' => 'building-office-2',      'label' => 'Panel de bodegas',   'route' => 'supervisor.oversight.wineries.index', 'active' => request()->routeIs('supervisor.oversight.wineries.*')],
            ['icon' => 'archive-box-arrow-down', 'label' => 'Recepciones de uva', 'route' => 'supervisor.oversight.wineries.index', 'active' => false],
            ['icon' => 'beaker',                 'label' => 'Elaboración y vinos','route' => 'supervisor.oversight.wineries.index', 'active' => false],
        ];

        $menu['do_oversight_growers'] = [
            ['icon' => 'eye',                     'label' => 'Panel de viticultores',   'route' => 'supervisor.oversight.growers.index',       'active' => request()->routeIs('supervisor.oversight.growers.index')],
            ['icon' => 'map',                     'label' => 'Parcelas DO',             'route' => 'supervisor.oversight.plots.index',          'active' => request()->routeIs('supervisor.oversight.plots.*')],
            ['icon' => 'book-open',               'label' => 'Cuaderno de campo',       'route' => 'supervisor.oversight.notebook.index',       'active' => request()->routeIs('supervisor.oversight.notebook.*')],
            ['icon' => 'document-check',          'label' => 'Cumplimiento PAC',        'route' => 'supervisor.oversight.pac.index',            'active' => request()->routeIs('supervisor.oversight.pac.*')],
            ['icon' => 'check-badge',             'label' => 'Certificaciones',         'route' => 'supervisor.oversight.certifications.index', 'active' => request()->routeIs('supervisor.oversight.certifications.*')],
            ['icon' => 'clipboard-document-list', 'label' => 'Stream de actividad',     'route' => 'supervisor.oversight.activity.index',       'active' => request()->routeIs('supervisor.oversight.activity.*')],
        ];

        $menu['do_qualification'] = [
            ['icon' => 'star',        'label' => 'Panel de cata',         'route' => 'supervisor.qualification.index', 'active' => request()->routeIs('supervisor.qualification.*')],
            ['icon' => 'beaker',      'label' => 'Análisis de laboratorio','route' => 'supervisor.qualification.index', 'active' => false],
            ['icon' => 'check-badge', 'label' => 'Calificación de vinos', 'route' => 'supervisor.qualification.index', 'active' => false],
            ['icon' => 'document-text','label' => 'Añadas',               'route' => 'supervisor.qualification.index', 'active' => false],
            ['icon' => 'clock',       'label' => 'Histórico',             'route' => 'supervisor.qualification.index', 'active' => false],
        ];

        $menu['do_labels'] = [
            ['icon' => 'tag',        'label' => 'Solicitudes',            'route' => 'supervisor.labels.index', 'active' => request()->routeIs('supervisor.labels.*')],
            ['icon' => 'hashtag',    'label' => 'Emisión y numeración',   'route' => 'supervisor.labels.index', 'active' => false],
            ['icon' => 'archive-box','label' => 'Stock de contraetiquetas','route' => 'supervisor.labels.index', 'active' => false],
        ];

        $menu['do_inspection'] = [
            ['icon' => 'clipboard-document-list','label' => 'Plan de control anual',   'route' => 'supervisor.inspection.index', 'active' => request()->routeIs('supervisor.inspection.*')],
            ['icon' => 'shield-check',           'label' => 'Inspecciones',            'route' => 'supervisor.inspection.index', 'active' => false],
            ['icon' => 'document-text',          'label' => 'Actas de inspección',     'route' => 'supervisor.inspection.index', 'active' => false],
            ['icon' => 'shield-exclamation',     'label' => 'Expedientes sancionadores','route' => 'supervisor.inspection.index', 'active' => false],
        ];

        $menu['do_regulation'] = [
            ['icon' => 'document-text',     'label' => 'Pliego de condiciones',       'route' => 'supervisor.regulation.index', 'active' => request()->routeIs('supervisor.regulation.*')],
            ['icon' => 'document-duplicate','label' => 'Reglamento interno',          'route' => 'supervisor.regulation.index', 'active' => false],
            ['icon' => 'check-circle',      'label' => 'Autorizaciones de plantación','route' => 'supervisor.regulation.index', 'active' => false],
            ['icon' => 'sparkles',          'label' => 'Certificaciones ecológicas',  'route' => 'supervisor.regulation.index', 'active' => false],
        ];

        $menu['do_territory'] = [
            ['icon' => 'map',                'label' => 'SIGPAC consolidado',    'route' => 'supervisor.territory.index', 'active' => request()->routeIs('supervisor.territory.*')],
            ['icon' => 'globe-europe-africa','label' => 'Teledetección regional','route' => 'supervisor.territory.index', 'active' => false],
            ['icon' => 'map-pin',            'label' => 'Zonificación DO',       'route' => 'supervisor.territory.index', 'active' => false],
        ];

        $menu['do_statistics'] = [
            ['icon' => 'chart-bar',         'label' => 'Dashboard general',    'route' => 'supervisor.statistics.index', 'active' => request()->routeIs('supervisor.statistics.*')],
            ['icon' => 'chart-pie',         'label' => 'Producción por campaña','route' => 'supervisor.statistics.index', 'active' => false],
            ['icon' => 'globe-alt',         'label' => 'Exportación',          'route' => 'supervisor.statistics.index', 'active' => false],
            ['icon' => 'document-chart-bar','label' => 'Informes para CCAA',   'route' => 'supervisor.statistics.index', 'active' => false],
        ];

        $menu['do_finance'] = [
            ['icon' => 'banknotes',              'label' => 'Cuotas de inscripción','route' => 'supervisor.finance.index', 'active' => request()->routeIs('supervisor.finance.*')],
            ['icon' => 'calculator',             'label' => 'Tasas y liquidaciones','route' => 'supervisor.finance.index', 'active' => false],
            ['icon' => 'document-text',          'label' => 'Facturas DO',          'route' => 'supervisor.finance.index', 'active' => false],
            ['icon' => 'presentation-chart-bar', 'label' => 'Presupuesto anual',    'route' => 'supervisor.finance.index', 'active' => false],
        ];

        $menu['do_settings'] = [
            ['icon' => 'users',       'label' => 'Usuarios y permisos',   'route' => 'supervisor.settings.index', 'active' => request()->routeIs('supervisor.settings.*')],
            ['icon' => 'folder-open', 'label' => 'Documentos DO',         'route' => 'supervisor.settings.index', 'active' => false],
            ['icon' => 'megaphone',   'label' => 'Comunicaciones masivas','route' => 'supervisor.settings.index', 'active' => false],
            ['icon' => 'cog-6-tooth', 'label' => 'Configuración',         'route' => 'supervisor.settings.index', 'active' => false],
        ];

        return $menu;
    }
}
