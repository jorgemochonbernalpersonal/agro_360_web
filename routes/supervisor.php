<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:supervisor'])
    ->prefix('supervisor')
    ->name('supervisor.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('supervisor.dashboard');
        })->name('dashboard');
    });
