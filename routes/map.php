<?php

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

// Ruta de mapa unificada (detecta automáticamente Plot o SigpacCode)
Route::middleware(['auth', 'verified', 'check.beta'])
    ->get('/map/{id}', [MapController::class, 'show'])
    ->name('map');
