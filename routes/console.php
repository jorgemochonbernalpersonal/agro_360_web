<?php

use App\Jobs\UpdateAllPlotsNdviJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Ejecutar cola automáticamente cada minuto (para producción con Cron)
// Escucha remote-sensing y default para procesar todos los jobs
Schedule::command('queue:work --stop-when-empty --max-time=50 --queue=remote-sensing,default')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar eliminación de usuarios no verificados diariamente a las 3 AM
Schedule::command('users:delete-unverified', ['--hours' => 24])
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::command('logs:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// 🛰️ Actualizar NDVI de todas las parcelas diariamente a las 2 AM
// Usa queue para no bloquear, con delay entre requests para respetar rate limits
Schedule::command('remote-sensing:update-all', [
    '--queue' => 'remote-sensing',
    '--delay' => 2,
])
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address'));

// 🌌 Actualizar datos enriquecidos (GNDVI, LAI, SMAP, ET) a las 2:30 AM
// Se ejecuta 30 min después del update-all para que el NDVI básico ya esté guardado
Schedule::command('remote-sensing:update-enriched --ultra')
    ->dailyAt('02:30')
    ->withoutOverlapping();

// 📊 Limpiar datos antiguos de remote sensing (cada lunes)
Schedule::command('remote-sensing:clean-old-data', ['--days' => 365])
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping();

// 🛢️ Alertas de mantenimiento de contenedores (diario a las 8 AM)
Schedule::command('containers:check-maintenance', ['--days' => 7])
    ->dailyAt('08:00')
    ->withoutOverlapping();

// 👻 Limpiar tokens de invitación caducados de viticultores ghost (cada lunes)
Schedule::command('viticulturists:clean-stale-invitations', ['--days' => 30])
    ->weeklyOn(1, '04:00')
    ->withoutOverlapping();

// 📋 Recordatorios de plazos regulatorios INFOVI (diario a las 8:30 AM)
// Envía aviso 7 días y 1 día antes del vencimiento; con deduplicación por BD
Schedule::command('agro:regulatory-reminders')
    ->dailyAt('08:30')
    ->withoutOverlapping();

// 📓 Recordatorios de solicitudes de cuaderno de campo sin respuesta (lunes 9 AM)
Schedule::command('agro:notebook-access-reminders')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping();

// 🛰️ Alertas de NDVI bajo umbral (diario a las 9 AM, tras actualización de datos)
Schedule::command('alerts:check')
    ->dailyAt('09:00')
    ->withoutOverlapping();

// 🧪 Alertas de plazos de seguridad fitosanitarios (diario a las 8:15 AM)
Schedule::command('treatments:send-withdrawal-alerts')
    ->dailyAt('08:15')
    ->withoutOverlapping();

// 📬 Digest diario de notificaciones (8:00 AM para usuarios con modo digest)
Schedule::command('notifications:send-digest')
    ->dailyAt('08:00')
    ->withoutOverlapping();

// ⏰ Avisos de vencimiento de solicitudes DO (9:00 AM — 3 días y 1 día antes)
Schedule::command('supervisor-requests:due-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

