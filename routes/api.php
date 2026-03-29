<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RemoteSensingController;
use App\Http\Controllers\Api\Winery\ContainerController;
use App\Http\Controllers\Api\Winery\PlotController as WineryPlotController;
use App\Http\Controllers\Api\Winery\DashboardController as WineryDashboard;
use App\Http\Controllers\Api\Winery\FermentationControlController;
use App\Http\Controllers\Api\Winery\GrapeReceptionController;
use App\Http\Controllers\Api\Winery\WineController;
use App\Http\Controllers\Api\Winery\WineProcessController;
use App\Http\Controllers\Api\Winery\InvoiceController;
use App\Http\Controllers\Api\Winery\ClientController;
use App\Http\Controllers\Api\Winery\WineAnalysisController;
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
    Route::get('/me',               [AuthController::class, 'me'])->middleware('throttle:60,1');
    Route::put('/me',               [AuthController::class, 'updateMe'])->middleware('throttle:30,1');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:10,1');
    Route::post('/logout',          [AuthController::class, 'logout'])->middleware('throttle:10,1');
    Route::post('/logout-all',      [AuthController::class, 'logoutAll'])->middleware('throttle:10,1');
    Route::delete('/account',       [AuthController::class, 'deleteAccount'])->middleware('throttle:5,1');
    Route::post('/refresh',         [AuthController::class, 'refresh'])->middleware('throttle:30,1');
    Route::post('/email/resend',    [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');

    // ── Winery / Producer ─────────────────────────────────────────────────────
    Route::prefix('winery')->middleware('api.role:winery,producer')->group(function () {

        Route::get('/dashboard', WineryDashboard::class)->middleware('throttle:60,1');

        // Contenedores
        Route::get('/containers',         [ContainerController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/containers',        [ContainerController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/containers/{id}',    [ContainerController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/containers/{id}',    [ContainerController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/containers/{id}', [ContainerController::class, 'destroy'])->middleware('throttle:60,1');

        // Vinos
        Route::get('/wines',                                [WineController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/wines',                               [WineController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/wines/{id}',                           [WineController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/wines/{id}',                           [WineController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/wines/{id}',                        [WineController::class, 'destroy'])->middleware('throttle:60,1');
        Route::get('/wines/{id}/fermentation-controls',     [WineController::class, 'fermentationControls'])->middleware('throttle:60,1');

        // Controles de fermentación
        Route::get('/fermentation-controls',         [FermentationControlController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/fermentation-controls',        [FermentationControlController::class, 'store'])->middleware('throttle:60,1');
        Route::delete('/fermentation-controls/{id}', [FermentationControlController::class, 'destroy'])->middleware('throttle:60,1');

        // Recepción de uva
        Route::get('/grape-receptions',         [GrapeReceptionController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/grape-receptions',        [GrapeReceptionController::class, 'store'])->middleware('throttle:60,1');
        Route::delete('/grape-receptions/{id}', [GrapeReceptionController::class, 'destroy'])->middleware('throttle:60,1');
        Route::get('/viticulturists',           [GrapeReceptionController::class, 'viticulturists'])->middleware('throttle:60,1');

        // Trasvases y mermas
        Route::get('/transfers',         [WineProcessController::class, 'indexTransfers'])->middleware('throttle:60,1');
        Route::post('/transfers',        [WineProcessController::class, 'storeTransfer'])->middleware('throttle:60,1');
        Route::delete('/transfers/{id}', [WineProcessController::class, 'destroyTransfer'])->middleware('throttle:60,1');
        Route::get('/losses',            [WineProcessController::class, 'indexLosses'])->middleware('throttle:60,1');
        Route::post('/losses',           [WineProcessController::class, 'storeLoss'])->middleware('throttle:60,1');
        Route::delete('/losses/{id}',    [WineProcessController::class, 'destroyLoss'])->middleware('throttle:60,1');

        // Facturas
        Route::get('/invoices',      [InvoiceController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->middleware('throttle:60,1');

        // Clientes
        Route::get('/clients',      [ClientController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/clients/{id}', [ClientController::class, 'show'])->middleware('throttle:60,1');

        // Análisis de vino
        Route::get('/wine-analysis',      [WineAnalysisController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/wine-analysis/{id}', [WineAnalysisController::class, 'show'])->middleware('throttle:60,1');

        // Parcelas de los viticultores de la bodega
        Route::get('/plots/centroids',            [WineryPlotController::class, 'centroids'])->middleware('throttle:30,1');
        Route::get('/plots/geometries',           [WineryPlotController::class, 'allGeometries'])->middleware('throttle:30,1');
        Route::get('/plots',                      [WineryPlotController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/plots/{id}',                 [WineryPlotController::class, 'show'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/geometries',      [WineryPlotController::class, 'geometries'])->middleware('throttle:30,1');
        Route::get('/plots/{id}/harvest-quality', [WineryPlotController::class, 'harvestQuality'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/notebook',        [WineryPlotController::class, 'notebook'])->middleware('throttle:60,1');
    });

    // ── Viticulturist / Producer ───────────────────────────────────────────────
    Route::prefix('viticulturist')->middleware('api.role:viticulturist,producer')->group(function () {

        Route::get('/dashboard', ViticulturistDashboard::class)->middleware('throttle:60,1');

        // Parcelas
        Route::get('/plots/centroids',       [PlotController::class, 'centroids'])->middleware('throttle:30,1');
        Route::get('/plots/geometries',      [PlotController::class, 'allGeometries'])->middleware('throttle:30,1');
        Route::get('/plots',                 [PlotController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/plots/{id}',            [PlotController::class, 'show'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/geometries', [PlotController::class, 'geometries'])->middleware('throttle:30,1');
        Route::put('/plots/{id}',            [PlotController::class, 'update'])->middleware('throttle:60,1');

        // Campañas
        Route::get('/campaigns',                 [CampaignController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/campaigns/active',          [CampaignController::class, 'active'])->middleware('throttle:60,1');
        Route::get('/campaigns/{id}/activities', [CampaignController::class, 'activities'])->middleware('throttle:60,1');

        // Cuaderno de campo
        Route::get('/notebook',         [NotebookController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/notebook',        [NotebookController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/notebook/{id}',    [NotebookController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/notebook/{id}',    [NotebookController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/notebook/{id}', [NotebookController::class, 'destroy'])->middleware('throttle:30,1');

        // Teledetección NDVI
        Route::get('/plots/{plot}/ndvi', [RemoteSensingController::class, 'getPlotNdviColors'])->middleware('throttle:30,1');
        Route::get('/ndvi',              [RemoteSensingController::class, 'getAllPlotsNdvi'])->middleware('throttle:30,1');
    });

    // ── Supervisor ────────────────────────────────────────────────────────────
    Route::prefix('supervisor')->middleware('api.role:supervisor')->group(function () {

        Route::get('/dashboard', SupervisorDashboard::class)->middleware('throttle:60,1');

        Route::get('/wineries',            [OversightController::class, 'wineries'])->middleware('throttle:60,1');
        Route::get('/wineries/{id}',       [OversightController::class, 'winery'])->middleware('throttle:60,1');
        Route::get('/viticulturists',      [OversightController::class, 'viticulturists'])->middleware('throttle:60,1');
        Route::get('/viticulturists/{id}', [OversightController::class, 'viticulturist'])->middleware('throttle:60,1');
    });
});
