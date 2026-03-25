<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

        // Usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Users\Index::class)->name('index');
            Route::get('/{user}', \App\Livewire\Admin\Users\Show::class)->name('show');
            Route::post('/stop-impersonate', \App\Http\Controllers\Admin\StopImpersonationController::class)->name('stop-impersonate');
        });

        // Soporte
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Support\Index::class)->name('index');
        });

        // Parcelas (solo lectura)
        Route::prefix('plots')->name('plots.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Plots\Index::class)->name('index');
        });

        // SIGPACs
        Route::prefix('sigpac')->name('sigpac.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Sigpac\Index::class)->name('index');
        });

        // Log de seguridad
        Route::get('/security-log', \App\Livewire\Admin\SecurityLog\Index::class)->name('security-log.index');

        // Notificaciones masivas
        Route::get('/notifications', \App\Livewire\Admin\Notifications\Index::class)->name('notifications.index');

        // Suscripciones y pagos
        Route::get('/subscriptions', \App\Livewire\Admin\Subscriptions\Index::class)->name('subscriptions.index');

        // Organizaciones (bodegas y DOs)
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Organizations\Index::class)->name('index');
        });
    });

