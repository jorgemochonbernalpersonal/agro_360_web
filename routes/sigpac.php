<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Sigpac\CodesIndex;
use App\Livewire\Sigpac\UsesIndex;

Route::middleware(['auth', 'verified'])
    ->prefix('sigpac')
    ->name('sigpac.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('sigpac.codes');
        })->name('index');
        
        Route::get('/codes', CodesIndex::class)->name('codes');
        Route::get('/uses', UsesIndex::class)->name('uses');
    });

