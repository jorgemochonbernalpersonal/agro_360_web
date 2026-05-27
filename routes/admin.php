<?php

use Illuminate\Support\Facades\Route;

// Ruta de stop-impersonate FUERA del middleware role:admin
// porque durante la impersonación el usuario actual NO es admin
Route::middleware(['auth'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::post('/stop-impersonate', \App\Http\Controllers\Admin\StopImpersonationController::class)->name('stop-impersonate');
    });

Route::middleware(['role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

        // Usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',             \App\Livewire\Admin\Users\Index::class)->name('index');
            Route::get('/duplicates',   \App\Livewire\Admin\Users\Duplicates::class)->name('duplicates');
            Route::get('/approvals',    \App\Livewire\Admin\Users\Approvals::class)->name('approvals');
            Route::get('/{user}',       \App\Livewire\Admin\Users\Show::class)->name('show');
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

        // Salud del sistema
        Route::get('/health', \App\Livewire\Admin\Health\Index::class)->name('health.index');

        // Notificaciones masivas
        Route::get('/notifications', \App\Livewire\Admin\Notifications\Index::class)->name('notifications.index');

        // Suscripciones y pagos
        Route::get('/subscriptions', \App\Livewire\Admin\Subscriptions\Index::class)->name('subscriptions.index');

        // Organizaciones (bodegas y DOs)
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Organizations\Index::class)->name('index');
        });

        // Jobs fallidos
        Route::get('/failed-jobs', \App\Livewire\Admin\FailedJobs\Index::class)->name('failed-jobs.index');

        // Tareas programadas
        Route::get('/scheduler', \App\Livewire\Admin\Scheduler\Index::class)->name('scheduler.index');

        // Anuncios
        Route::get('/announcements', \App\Livewire\Admin\Announcements\Index::class)->name('announcements.index');

        // Respuestas rápidas de soporte
        Route::get('/canned-responses', \App\Livewire\Admin\CannedResponses\Index::class)->name('canned-responses.index');

        // Configuración global
        Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');

        // Catálogos multilingüe
        Route::get('/catalogs', \App\Livewire\Admin\Catalogs\Index::class)->name('catalogs.index');

        // Búsqueda global (componente inline, no necesita ruta propia)
    });
