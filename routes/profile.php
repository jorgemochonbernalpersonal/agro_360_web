<?php

use App\Livewire\Profile\Edit;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::get('/', function () {
            return view('profile.show');
        })->name('show');

        Route::get('/edit', Edit::class)->name('edit');
    });
