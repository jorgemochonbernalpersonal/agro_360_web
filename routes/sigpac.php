<?php

use App\Livewire\Sigpac\CodesIndex;
use App\Livewire\Sigpac\Create;
use App\Livewire\Sigpac\Edit;
use App\Livewire\Sigpac\UsesIndex;
use Illuminate\Support\Facades\Route;

// Lectura: todos los roles autenticados con beta
Route::middleware(['auth', 'verified', 'check.beta'])
    ->prefix('sigpac')
    ->name('sigpac.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('sigpac.codes');
        })->name('index');

        Route::get('/codes', CodesIndex::class)->name('codes');
        Route::get('/uses', UsesIndex::class)->name('uses');

        // Vista de todos los mapas de un municipio
        Route::get('/municipality-map/{municipalityId}', [App\Http\Controllers\MunicipalityMapController::class, 'show'])
            ->name('municipality-map');
    });

// Escritura: solo administradores (datos maestros del sistema)
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('sigpac')
    ->name('sigpac.')
    ->group(function () {
        Route::get('/codes/create', Create::class)->name('codes.create');
        Route::get('/codes/{code}/edit', Edit::class)->name('codes.edit');
    });
