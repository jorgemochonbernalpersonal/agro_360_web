<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clients Routes (shared across roles)
|--------------------------------------------------------------------------
| Registered under /clients prefix with role-based middleware.
| Same pattern as routes/plots.php — shared namespace, role filtering in code.
*/

Route::middleware(['auth', 'role:admin,supervisor,winery,viticulturist,producer', 'check.beta'])
    ->prefix('clients')
    ->name('clients.')
    ->group(function () {
        Route::get('/', \App\Livewire\Clients\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Clients\Create::class)->name('create');
        Route::get('/insights', \App\Livewire\Clients\Insights::class)->name('insights');
        Route::get('/{client}', \App\Livewire\Clients\Show::class)->name('show');
        Route::get('/{client}/edit', \App\Livewire\Clients\Edit::class)->name('edit');
    });
