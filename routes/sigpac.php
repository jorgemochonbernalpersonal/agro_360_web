<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Sigpac\CodesIndex;
use App\Livewire\Sigpac\UsesIndex;
use App\Livewire\Sigpac\EditGeometry;
use App\Livewire\Sigpac\Create;
use App\Livewire\Sigpac\Edit;

Route::middleware(['auth', 'verified', 'check.beta'])
    ->prefix('sigpac')
    ->name('sigpac.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('sigpac.codes');
        })->name('index');
        
        Route::get('/codes', CodesIndex::class)->name('codes');
        Route::get('/codes/create', Create::class)->name('codes.create');
        Route::get('/codes/{code}/edit', Edit::class)->name('codes.edit');
        Route::get('/uses', UsesIndex::class)->name('uses');
        Route::get('/geometry/{sigpacId}', EditGeometry::class)->name('geometry.edit');
        Route::get('/geometry/{sigpacId}/{plotId}', EditGeometry::class)->name('geometry.edit-plot');
    });

