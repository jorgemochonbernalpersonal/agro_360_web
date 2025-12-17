<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar limpieza de usuarios no verificados cada hora
Schedule::command('users:cleanup-unverified')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Programar limpieza de logs antiguos diariamente a las 2 AM
Schedule::command('logs:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
