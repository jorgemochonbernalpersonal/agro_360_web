<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('payment')
    ->name('payment.')
    ->group(function () {
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    });
