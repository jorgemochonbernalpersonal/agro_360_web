<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('config')
    ->name('config.')
    ->group(function () {
        Route::get('/', function () {
            return view('config.index');
        })->name('index');
    });

