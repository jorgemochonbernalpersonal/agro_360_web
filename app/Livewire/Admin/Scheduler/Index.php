<?php

namespace App\Livewire\Admin\Scheduler;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Index extends Component
{
    // Tasks defined in routes/console.php
    private const TASKS = [
        ['command' => 'queue:work',                        'schedule' => __('Cada minuto'),       'desc' => __('Procesa jobs de la cola (remote-sensing + default)'),        'icon' => 'queue-list',          'color' => 'blue'],
        ['command' => 'users:delete-unverified',           'schedule' => __('Diario 03:00'),      'desc' => __('Elimina usuarios sin verificar en más de 24h'),              'icon' => 'user-minus',          'color' => 'red'],
        ['command' => 'logs:cleanup',                      'schedule' => __('Diario 02:00'),      'desc' => __('Limpia logs antiguos del sistema'),                          'icon' => 'trash',               'color' => 'zinc'],
        ['command' => 'remote-sensing:update-all',         'schedule' => __('Diario 02:00'),      'desc' => __('Actualiza NDVI de todas las parcelas'),                      'icon' => 'globe-alt',           'color' => 'agro'],
        ['command' => 'remote-sensing:update-enriched',    'schedule' => __('Diario 02:30'),      'desc' => __('Actualiza GNDVI, LAI, SMAP, ET (datos enriquecidos)'),        'icon' => 'signal',              'color' => 'agro'],
        ['command' => 'remote-sensing:clean-old-data',     'schedule' => __('Lunes 03:00'),       'desc' => __('Elimina datos de teledetección con más de 365 días'),        'icon' => 'archive-box-x-mark',  'color' => 'orange'],
        ['command' => 'containers:check-maintenance',      'schedule' => __('Diario 08:00'),      'desc' => __('Alertas de mantenimiento de depósitos (próximos 7 días)'),   'icon' => 'beaker',              'color' => 'purple'],
        ['command' => 'viticulturists:clean-stale-invitations', 'schedule' => __('Lunes 04:00'), 'desc' => __('Limpia invitaciones de viticultores caducadas (>30 días)'),   'icon' => 'envelope-open',       'color' => 'zinc'],
        ['command' => 'agro:regulatory-reminders',         'schedule' => __('Diario 08:30'),      'desc' => __('Recordatorios de plazos INFOVI (7 días y 1 día antes)'),     'icon' => 'bell-alert',          'color' => 'yellow'],
        ['command' => 'agro:notebook-access-reminders',    'schedule' => __('Lunes 09:00'),       'desc' => __('Recordatorios de solicitudes de cuaderno sin respuesta'),    'icon' => 'book-open',           'color' => 'blue'],
        ['command' => 'alerts:check',                      'schedule' => __('Diario 09:00'),      'desc' => __('Alertas de NDVI bajo umbral configurado'),                   'icon' => 'exclamation-triangle','color' => 'red'],
        ['command' => 'treatments:send-withdrawal-alerts', 'schedule' => __('Diario 08:15'),      'desc' => __('Alertas de plazo de seguridad fitosanitario'),               'icon' => 'shield-check',        'color' => 'green'],
        ['command' => 'notifications:send-digest',         'schedule' => __('Diario 08:00'),      'desc' => __('Digest diario de notificaciones (modo digest)'),             'icon' => 'inbox',               'color' => 'blue'],
        ['command' => 'supervisor-requests:due-reminders', 'schedule' => __('Diario 09:00'),      'desc' => __('Avisos de vencimiento de solicitudes DO'),                   'icon' => 'clock',               'color' => 'purple'],
    ];

    public function runNow(string $command): void
    {
        try {
            Artisan::call($command);
            Cache::put("scheduler.last_run.{$command}", now()->timestamp, now()->addDays(7));
            $this->dispatch('toast', message: __('Comando ejecutado.'), type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: __('Error: :message', ['message' => $e->getMessage()]), type: 'error');
        }
    }

    public function render()
    {
        $tasks = collect(self::TASKS)->map(function ($task) {
            $cacheKey = "scheduler.last_run.{$task['command']}";
            $lastRun  = Cache::get($cacheKey);
            $task['last_run'] = $lastRun
                ? \Carbon\Carbon::createFromTimestamp($lastRun)->diffForHumans()
                : null;
            return $task;
        });

        $failedCount = 0;
        try {
            $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        } catch (\Throwable) {}

        return view('livewire.admin.scheduler.index', compact('tasks', 'failedCount'))
            ->layout('layouts.app', [
                'title'       => __('Scheduler - Admin - Agro365'),
                'description' => __('Monitor de tareas programadas del sistema'),
            ]);
    }
}
