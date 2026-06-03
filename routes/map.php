<?php

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'check.beta'])->group(function () {
    // Mapa de municipio (sin plot_id) — debe ir antes del wildcard
    Route::get('/map/municipality/{municipalityId}', [MapController::class, 'showMunicipality'])
        ->name('map.municipality')
        ->whereNumber('municipalityId');

    // Ruta de mapa unificada (detecta automáticamente Plot o SigpacCode)
    Route::get('/map/{id}', [MapController::class, 'show'])
        ->name('map');
});
