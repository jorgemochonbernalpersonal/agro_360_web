<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Subscription\Manage;

Route::middleware(['auth', 'verified'])
    ->prefix('subscription')
    ->name('subscription.')
    ->group(function () {
        Route::get('/', Manage::class)->name('manage');
    });

