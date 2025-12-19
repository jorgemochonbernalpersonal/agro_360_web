<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Sigpac\CodesIndex;
use App\Livewire\Sigpac\UsesIndex;
use App\Livewire\Sigpac\EditGeometry;

Route::middleware(['auth', 'verified'])
    ->prefix('sigpac')
    ->name('sigpac.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('sigpac.codes');
        })->name('index');
        
        Route::get('/codes', CodesIndex::class)->name('codes');
        Route::get('/uses', UsesIndex::class)->name('uses');
        Route::get('/geometry/{sigpacId}', EditGeometry::class)->name('geometry.edit');
        Route::get('/geometry/{sigpacId}/{plotId}', EditGeometry::class)->name('geometry.edit-plot');
    });

