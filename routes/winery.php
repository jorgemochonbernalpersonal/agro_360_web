<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:winery'])
    ->prefix('winery')
    ->name('winery.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('winery.dashboard');
        })->name('dashboard');
    });
