<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Profile\Edit;

Route::middleware(['auth', 'verified'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::get('/', function () {
            return view('profile.show');
        })->name('show');
        
        Route::get('/edit', Edit::class)->name('edit');
    });

