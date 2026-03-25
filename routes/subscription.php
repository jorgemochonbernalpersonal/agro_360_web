<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Subscription\Manage;

Route::middleware(['auth', 'verified'])
    ->prefix('subscription')
    ->name('subscription.')
    ->group(function () {
        Route::get('/', Manage::class)->name('manage');
    });

// Alias for pricing page (redirects to subscription management)
Route::middleware(['auth', 'verified'])
    ->get('/pricing', fn () => redirect()->route('subscription.manage'))
    ->name('pricing');

