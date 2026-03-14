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
    ->withoutOverlapping();

// 📊 Limpiar datos antiguos de remote sensing (cada lunes)
Schedule::command('remote-sensing:clean-old-data', ['--days' => 365])
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping();

// 🛢️ Alertas de mantenimiento de contenedores (diario a las 8 AM)
Schedule::command('containers:check-maintenance', ['--days' => 7])
    ->dailyAt('08:00')
    ->withoutOverlapping();

