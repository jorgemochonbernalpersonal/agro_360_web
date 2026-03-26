<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Winery\ContainerController;
use App\Http\Controllers\Api\Winery\DashboardController as WineryDashboard;
use App\Http\Controllers\Api\Winery\FermentationControlController;
use App\Http\Controllers\Api\Winery\GrapeReceptionController;
use App\Http\Controllers\Api\Winery\WineController;
use App\Http\Controllers\Api\Winery\WineProcessController;
use App\Http\Controllers\Api\Viticulturist\CampaignController;
use App\Http\Controllers\Api\Viticulturist\DashboardController as ViticulturistDashboard;
use App\Http\Controllers\Api\Viticulturist\NotebookController;
use App\Http\Controllers\Api\Viticulturist\PlotController;
use App\Http\Controllers\Api\Supervisor\DashboardController as SupervisorDashboard;
use App\Http\Controllers\Api\Supervisor\OversightController;
use Illuminate\Support\Facades\Route;

// ─── Public: Auth (rate limited) ──────────────────────────────────────────────

Route::post('/register',        [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login',           [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::post('/claim-account',   [AuthController::class, 'claimAccount'])->middleware('throttle:5,1');

// ─── Protected ────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'check.can_login'])->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────────────
    Route::get('/me',               [AuthController::class, 'me']);
    Route::put('/me',               [AuthController::class, 'updateMe']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout',          [AuthController::class, 'logout']);
    Route::post('/logout-all',      [AuthController::class, 'logoutAll']);
    Route::post('/refresh',         [AuthController::class, 'refresh']);
    Route::post('/email/resend',    [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');

    // ── Winery / Producer ─────────────────────────────────────────────────────
    Route::prefix('winery')->middleware('api.role:winery,producer')->group(function () {

        Route::get('/dashboard', WineryDashboard::class);

        // Contenedores
        Route::get('/containers',      [ContainerController::class, 'index']);
        Route::get('/containers/{id}', [ContainerController::class, 'show']);

        // Vinos
        Route::get('/wines',                                [WineController::class, 'index']);
        Route::get('/wines/{id}',                           [WineController::class, 'show']);
        Route::get('/wines/{id}/fermentation-controls',     [WineController::class, 'fermentationControls']);

        // Controles de fermentación
        Route::get('/fermentation-controls',  [FermentationControlController::class, 'index']);
        Route::post('/fermentation-controls', [FermentationControlController::class, 'store']);

        // Recepción de uva
        Route::get('/grape-receptions',  [GrapeReceptionController::class, 'index']);
        Route::post('/grape-receptions', [GrapeReceptionController::class, 'store']);
        Route::get('/viticulturists',    [GrapeReceptionController::class, 'viticulturists']);

        // Trasvases y mermas
        Route::post('/transfers', [WineProcessController::class, 'storeTransfer']);
        Route::post('/losses',    [WineProcessController::class, 'storeLoss']);
    });

    // ── Viticulturist / Producer ───────────────────────────────────────────────
    Route::prefix('viticulturist')->middleware('api.role:viticulturist,producer')->group(function () {

        Route::get('/dashboard', ViticulturistDashboard::class);

        // Parcelas
        Route::get('/plots',      [PlotController::class, 'index']);
        Route::get('/plots/{id}', [PlotController::class, 'show']);

        // Campañas
        Route::get('/campaigns',               [CampaignController::class, 'index']);
        Route::get('/campaigns/active',        [CampaignController::class, 'active']);
        Route::get('/campaigns/{id}/activities', [CampaignController::class, 'activities']);

        // Cuaderno de campo
        Route::get('/notebook',  [NotebookController::class, 'index']);
        Route::post('/notebook', [NotebookController::class, 'store']);
    });

    // ── Supervisor ────────────────────────────────────────────────────────────
    Route::prefix('supervisor')->middleware('api.role:supervisor')->group(function () {

        Route::get('/dashboard', SupervisorDashboard::class);

        Route::get('/wineries',                [OversightController::class, 'wineries']);
        Route::get('/wineries/{id}',           [OversightController::class, 'winery']);
        Route::get('/viticulturists',          [OversightController::class, 'viticulturists']);
        Route::get('/viticulturists/{id}',     [OversightController::class, 'viticulturist']);
    });
});
