<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar eliminación de usuarios no verificados diariamente a las 3 AM
Schedule::command('users:delete-unverified', ['--hours' => 24])
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();

// Programar limpieza de logs antiguos diariamente a las 2 AM
Schedule::command('logs:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
