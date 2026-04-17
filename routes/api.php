<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\RemoteSensingController;
use App\Http\Controllers\Api\Winery\ContainerController;
use App\Http\Controllers\Api\Winery\TraceabilityController;
use App\Http\Controllers\Api\Winery\PlotController as WineryPlotController;
use App\Http\Controllers\Api\Winery\DashboardController as WineryDashboard;
use App\Http\Controllers\Api\Winery\FermentationControlController;
use App\Http\Controllers\Api\Winery\GrapeReceptionController;
use App\Http\Controllers\Api\Winery\ViticulturistController;
use App\Http\Controllers\Api\Winery\WineController;
use App\Http\Controllers\Api\Winery\WineProcessController;
use App\Http\Controllers\Api\Winery\InvoiceController;
use App\Http\Controllers\Api\Winery\ClientController;
use App\Http\Controllers\Api\Winery\WineAnalysisController;
use App\Http\Controllers\Api\Winery\WineProcessStepController;
use App\Http\Controllers\Api\Winery\ProductLotController;
use App\Http\Controllers\Api\Winery\BottlingController;
use App\Http\Controllers\Api\Winery\GrapePurchaseInvoiceController;
use App\Http\Controllers\Api\Winery\SilicieController;
use App\Http\Controllers\Api\Winery\InfoviController;
use App\Http\Controllers\Api\Winery\ContainerMaintenanceController;
use App\Http\Controllers\Api\Winery\OenologistController;
use App\Http\Controllers\Api\Winery\SupplierController;
use App\Http\Controllers\Api\Winery\WinerySupplyController;
use App\Http\Controllers\Api\Winery\ContainerRoomController;
use App\Http\Controllers\Api\Winery\WineAdditiveController;
use App\Http\Controllers\Api\Winery\TastingNoteController;
use App\Http\Controllers\Api\Winery\WineSubproductController;
use App\Http\Controllers\Api\Winery\LabelBatchController;
use App\Http\Controllers\Api\Winery\LabelingController;
use App\Http\Controllers\Api\Winery\SanitaryRegistrationController;
use App\Http\Controllers\Api\Winery\BottlingAuthorizationController;
use App\Http\Controllers\Api\Winery\WineryDocumentController;
use App\Http\Controllers\Api\Winery\CellarOperationController;
use App\Http\Controllers\Api\Winery\YieldForecastController;
use App\Http\Controllers\Api\Winery\EcoCertificationController;
use App\Http\Controllers\Api\Winery\DisputeController;
use App\Http\Controllers\Api\Winery\StatsController as WineryStatsController;
use App\Http\Controllers\Api\Winery\WineryCampaignController;
use App\Http\Controllers\Api\Winery\FieldActivityController;
use App\Http\Controllers\Api\Winery\ContainerHistoryController;
use App\Http\Controllers\Api\Winery\ExternalGrapePurchaseController;
use App\Http\Controllers\Api\Winery\WineryAlertController;
use App\Http\Controllers\Api\Winery\WinerySettingController;
use App\Http\Controllers\Api\Winery\DenominationOfOriginController;
use App\Http\Controllers\Api\Winery\VerifactuController;
use App\Http\Controllers\Api\Winery\EconomicSummaryController;
use App\Http\Controllers\Api\Winery\HarvestSummaryController;
use App\Http\Controllers\Api\Viticulturist\CampaignController;
use App\Http\Controllers\Api\Viticulturist\ComplianceController;
use App\Http\Controllers\Api\Viticulturist\DashboardController as ViticulturistDashboard;
use App\Http\Controllers\Api\Viticulturist\EstimatedYieldController;
use App\Http\Controllers\Api\Viticulturist\NotebookController;
use App\Http\Controllers\Api\Viticulturist\PestController;
use App\Http\Controllers\Api\Viticulturist\PlotController;
use App\Http\Controllers\Api\Viticulturist\PhenologyObservationController;
use App\Http\Controllers\Api\Viticulturist\PlotCostController;
use App\Http\Controllers\Api\Viticulturist\EnergyUsageController;
use App\Http\Controllers\Api\Viticulturist\HarvestDeclarationController;
use App\Http\Controllers\Api\Viticulturist\CampaignDocumentController;
use App\Http\Controllers\Api\Viticulturist\SoilAnalysisController;
use App\Http\Controllers\Api\Viticulturist\BiodiversityRecordController;
use App\Http\Controllers\Api\Viticulturist\ResidueManagementController;
use App\Http\Controllers\Api\Viticulturist\ResidueAnalysisController;
use App\Http\Controllers\Api\Viticulturist\PlannedWorkController;
use App\Http\Controllers\Api\Viticulturist\FertilizationPlanController;
use App\Http\Controllers\Api\Viticulturist\PhytosanitaryAlertController;
use App\Http\Controllers\Api\Viticulturist\WaterConcessionController;
use App\Http\Controllers\Api\Viticulturist\SubcontractingController;
use App\Http\Controllers\Api\Viticulturist\PlotEnvironmentController;
use App\Http\Controllers\Api\Viticulturist\WarehouseController;
use App\Http\Controllers\Api\Viticulturist\MachineryController;
use App\Http\Controllers\Api\Viticulturist\PhytosanitaryProductController;
use App\Http\Controllers\Api\Viticulturist\CrewController;
use App\Http\Controllers\Api\Viticulturist\FieldApplicatorController;
use App\Http\Controllers\Api\Viticulturist\FieldEquipmentController;
use App\Http\Controllers\Api\Supervisor\DashboardController as SupervisorDashboard;
use App\Http\Controllers\Api\Supervisor\OversightController;
use Illuminate\Support\Facades\Route;

// ─── Public: Auth (rate limited) ──────────────────────────────────────────────

Route::post('/register',        [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login',           [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/auth/google',     [AuthController::class, 'loginWithGoogle'])->middleware('throttle:10,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::post('/claim-account',   [AuthController::class, 'claimAccount'])->middleware('throttle:5,1');

// ─── Protected ────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'check.can_login'])->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────────────
    // ── Push notifications ────────────────────────────────────────────────────
    Route::post('/push/register',   [PushController::class, 'register'])->middleware('throttle:30,1');
    Route::delete('/push/token',    [PushController::class, 'unregister'])->middleware('throttle:30,1');

    Route::get('/me',               [AuthController::class, 'me'])->middleware('throttle:60,1');
    Route::put('/me',               [AuthController::class, 'updateMe'])->middleware('throttle:30,1');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:10,1');
    Route::post('/logout',          [AuthController::class, 'logout'])->middleware('throttle:10,1');
    Route::post('/logout-all',      [AuthController::class, 'logoutAll'])->middleware('throttle:10,1');
    Route::delete('/account',       [AuthController::class, 'deleteAccount'])->middleware('throttle:5,1');
    Route::post('/refresh',         [AuthController::class, 'refresh'])->middleware('throttle:10,1');
    Route::post('/email/resend',    [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');

    // ── Winery / Producer ─────────────────────────────────────────────────────
    Route::prefix('winery')->middleware('api.role:winery,producer')->group(function () {

        Route::get('/dashboard', WineryDashboard::class)->middleware('throttle:60,1');
        Route::get('/stats',     WineryStatsController::class)->middleware('throttle:30,1');

        // Campañas de viticultores vinculados
        Route::get('/campaigns',         [WineryCampaignController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/campaigns',        [WineryCampaignController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/campaigns/{id}',    [WineryCampaignController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/campaigns/{id}',    [WineryCampaignController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/campaigns/{id}', [WineryCampaignController::class, 'destroy'])->middleware('throttle:60,1');

        // Actividades de campo de viticultores vinculados (lectura)
        Route::get('/field-activities',      [FieldActivityController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/field-activities/{id}', [FieldActivityController::class, 'show'])->middleware('throttle:60,1');

        // Contenedores
        Route::middleware('winery.ability:cellar_management')->group(function () {
            Route::get('/containers',                           [ContainerController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/containers',                          [ContainerController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/containers/fleet-analytics',           [ContainerHistoryController::class, 'fleetAnalytics'])->middleware('throttle:30,1');
            Route::get('/containers/{id}',                      [ContainerController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/containers/{id}',                      [ContainerController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/containers/{id}',                   [ContainerController::class, 'destroy'])->middleware('throttle:60,1');
            Route::get('/containers/{id}/history',              [ContainerHistoryController::class, 'index'])->middleware('throttle:60,1');
            Route::get('/containers/{id}/analytics',            [ContainerHistoryController::class, 'analytics'])->middleware('throttle:30,1');
        });

        // Vinos
        Route::middleware('winery.ability:wine_process')->group(function () {
            Route::get('/wines',                                [WineController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/wines',                               [WineController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/wines/{id}',                           [WineController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/wines/{id}',                           [WineController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/wines/{id}',                        [WineController::class, 'destroy'])->middleware('throttle:60,1');
            Route::get('/wines/{id}/fermentation-controls',     [WineController::class, 'fermentationControls'])->middleware('throttle:60,1');

            // Controles de fermentación
            Route::get('/fermentation-controls',         [FermentationControlController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/fermentation-controls',        [FermentationControlController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/fermentation-controls/{id}',    [FermentationControlController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/fermentation-controls/{id}',    [FermentationControlController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/fermentation-controls/{id}', [FermentationControlController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Recepción de uva
        Route::middleware('winery.ability:harvest_reception')->group(function () {
            Route::get('/grape-receptions',         [GrapeReceptionController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/grape-receptions',        [GrapeReceptionController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/grape-receptions/{id}',    [GrapeReceptionController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/grape-receptions/{id}',    [GrapeReceptionController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/grape-receptions/{id}', [GrapeReceptionController::class, 'destroy'])->middleware('throttle:60,1');
        });
        // Viticultores
        Route::get('/viticulturists',                [ViticulturistController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/viticulturists',               [ViticulturistController::class, 'store'])->middleware('throttle:30,1');
        Route::get('/viticulturists/search',         [ViticulturistController::class, 'search'])->middleware('throttle:30,1');
        Route::post('/viticulturists/link',          [ViticulturistController::class, 'link'])->middleware('throttle:30,1');
        Route::get('/viticulturists/{id}',           [ViticulturistController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/viticulturists/{id}',           [ViticulturistController::class, 'update'])->middleware('throttle:30,1');
        Route::post('/viticulturists/{id}/invite',   [ViticulturistController::class, 'invite'])->middleware('throttle:5,1');
        Route::delete('/viticulturists/{id}/invite', [ViticulturistController::class, 'revokeInvite'])->middleware('throttle:10,1');

        // Trasvases y mermas
        Route::middleware('winery.ability:wine_process')->group(function () {
            Route::get('/transfers',         [WineProcessController::class, 'indexTransfers'])->middleware('throttle:60,1');
            Route::post('/transfers',        [WineProcessController::class, 'storeTransfer'])->middleware('throttle:60,1');
            Route::put('/transfers/{id}',    [WineProcessController::class, 'updateTransfer'])->middleware('throttle:60,1');
            Route::delete('/transfers/{id}', [WineProcessController::class, 'destroyTransfer'])->middleware('throttle:60,1');
            Route::get('/losses',            [WineProcessController::class, 'indexLosses'])->middleware('throttle:60,1');
            Route::post('/losses',           [WineProcessController::class, 'storeLoss'])->middleware('throttle:60,1');
            Route::put('/losses/{id}',       [WineProcessController::class, 'updateLoss'])->middleware('throttle:60,1');
            Route::delete('/losses/{id}',    [WineProcessController::class, 'destroyLoss'])->middleware('throttle:60,1');
        });

        // Facturas
        Route::middleware('winery.ability:product_sales')->group(function () {
            Route::get('/invoices',         [InvoiceController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/invoices',        [InvoiceController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/invoices/{id}',    [InvoiceController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/invoices/{id}',    [InvoiceController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Clientes
        Route::get('/clients',         [ClientController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/clients',        [ClientController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/clients/{id}',    [ClientController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/clients/{id}',    [ClientController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->middleware('throttle:60,1');

        // Mantenimiento de contenedores
        Route::get('/maintenances',                    [ContainerMaintenanceController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/maintenances',                   [ContainerMaintenanceController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/maintenances/{id}',               [ContainerMaintenanceController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/maintenances/{id}',               [ContainerMaintenanceController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/maintenances/{id}',            [ContainerMaintenanceController::class, 'destroy'])->middleware('throttle:60,1');
        Route::post('/maintenances/{id}/complete',     [ContainerMaintenanceController::class, 'complete'])->middleware('throttle:60,1');
        Route::get('/containers/{id}/maintenances',    [ContainerMaintenanceController::class, 'byContainer'])->middleware('throttle:60,1');

        // SILICIE
        Route::get('/silicie',                [SilicieController::class, 'index'])->middleware('throttle:30,1');
        Route::get('/silicie/entries',       [SilicieController::class, 'entries'])->middleware('throttle:30,1');
        Route::get('/silicie/elaboration',    [SilicieController::class, 'elaboration'])->middleware('throttle:30,1');
        Route::get('/silicie/inventory',    [SilicieController::class, 'inventory'])->middleware('throttle:30,1');
        Route::get('/silicie/outputs',        [SilicieController::class, 'outputs'])->middleware('throttle:30,1');
        Route::get('/silicie/opening',       [SilicieController::class, 'opening'])->middleware('throttle:30,1');
        Route::post('/silicie/snapshot',      [SilicieController::class, 'snapshot'])->middleware('throttle:10,1');
        Route::get('/silicie/export',         [SilicieController::class, 'export'])->middleware('throttle:10,1');

        // INFOVI / AICA
        Route::get('/infovi',           [InfoviController::class, 'index'])->middleware('throttle:30,1');
        Route::get('/infovi/threshold', [InfoviController::class, 'threshold'])->middleware('throttle:30,1');

        // Liquidaciones (facturas de compra de uva)
        Route::middleware('winery.ability:grape_purchase_invoice')->group(function () {
            Route::get('/grape-invoices',                                    [GrapePurchaseInvoiceController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/grape-invoices',                                   [GrapePurchaseInvoiceController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/grape-invoices/{id}',                               [GrapePurchaseInvoiceController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/grape-invoices/{id}',                               [GrapePurchaseInvoiceController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/grape-invoices/{id}',                            [GrapePurchaseInvoiceController::class, 'destroy'])->middleware('throttle:60,1');
            Route::post('/grape-invoices/{id}/items',                        [GrapePurchaseInvoiceController::class, 'addItem'])->middleware('throttle:60,1');
            Route::put('/grape-invoices/{id}/items/{itemId}',                [GrapePurchaseInvoiceController::class, 'updateItem'])->middleware('throttle:60,1');
            Route::delete('/grape-invoices/{id}/items/{itemId}',             [GrapePurchaseInvoiceController::class, 'removeItem'])->middleware('throttle:60,1');
            Route::post('/grape-invoices/{id}/confirm',                      [GrapePurchaseInvoiceController::class, 'confirm'])->middleware('throttle:30,1');
            Route::post('/grape-invoices/{id}/mark-paid',                    [GrapePurchaseInvoiceController::class, 'markPaid'])->middleware('throttle:30,1');
        });

        // Lotes de producto
        Route::middleware('winery.ability:product_sales')->group(function () {
            Route::get('/product-lots',         [ProductLotController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/product-lots',        [ProductLotController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/product-lots/{id}',    [ProductLotController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/product-lots/{id}',    [ProductLotController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/product-lots/{id}', [ProductLotController::class, 'destroy'])->middleware('throttle:60,1');

            // Embotellado
            Route::get('/bottlings',         [BottlingController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/bottlings',        [BottlingController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/bottlings/{id}',    [BottlingController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/bottlings/{id}',    [BottlingController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/bottlings/{id}', [BottlingController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Pasos de proceso de vino
        Route::get('/wines/{id}/process',          [WineProcessStepController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/wines/{id}/process',         [WineProcessStepController::class, 'store'])->middleware('throttle:60,1');
        Route::put('/process/{id}',                [WineProcessStepController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/process/{id}',             [WineProcessStepController::class, 'destroy'])->middleware('throttle:60,1');
        Route::post('/process/{id}/complete',      [WineProcessStepController::class, 'complete'])->middleware('throttle:60,1');

        // Análisis de vino
        Route::middleware('winery.ability:quality_analysis')->group(function () {
            Route::get('/wine-analysis',         [WineAnalysisController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/wine-analysis',        [WineAnalysisController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/wine-analysis/{id}',    [WineAnalysisController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/wine-analysis/{id}',    [WineAnalysisController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/wine-analysis/{id}', [WineAnalysisController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Enólogos
        Route::get('/oenologists',         [OenologistController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/oenologists',        [OenologistController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/oenologists/{id}',    [OenologistController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/oenologists/{id}',    [OenologistController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/oenologists/{id}', [OenologistController::class, 'destroy'])->middleware('throttle:60,1');

        // Proveedores
        Route::get('/suppliers',         [SupplierController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/suppliers',        [SupplierController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/suppliers/{id}',    [SupplierController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/suppliers/{id}',    [SupplierController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->middleware('throttle:60,1');

        // Insumos de bodega
        Route::get('/supplies',         [WinerySupplyController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/supplies',        [WinerySupplyController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/supplies/{id}',    [WinerySupplyController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/supplies/{id}',    [WinerySupplyController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/supplies/{id}', [WinerySupplyController::class, 'destroy'])->middleware('throttle:60,1');

        // Salas / zonas de contenedores
        Route::get('/rooms',         [ContainerRoomController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/rooms',        [ContainerRoomController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/rooms/{id}',    [ContainerRoomController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/rooms/{id}',    [ContainerRoomController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/rooms/{id}', [ContainerRoomController::class, 'destroy'])->middleware('throttle:60,1');

        // Aditivos de vino
        Route::get('/additives',         [WineAdditiveController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/additives',        [WineAdditiveController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/additives/{id}',    [WineAdditiveController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/additives/{id}',    [WineAdditiveController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/additives/{id}', [WineAdditiveController::class, 'destroy'])->middleware('throttle:60,1');

        // Notas de cata
        Route::get('/tasting-notes',         [TastingNoteController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/tasting-notes',        [TastingNoteController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/tasting-notes/{id}',    [TastingNoteController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/tasting-notes/{id}',    [TastingNoteController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/tasting-notes/{id}', [TastingNoteController::class, 'destroy'])->middleware('throttle:60,1');

        // Subproductos
        Route::get('/subproducts',         [WineSubproductController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/subproducts',        [WineSubproductController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/subproducts/{id}',    [WineSubproductController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/subproducts/{id}',    [WineSubproductController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/subproducts/{id}', [WineSubproductController::class, 'destroy'])->middleware('throttle:60,1');

        // Lotes de etiquetas
        Route::middleware('winery.ability:label_batches')->group(function () {
            Route::get('/label-batches',         [LabelBatchController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/label-batches',        [LabelBatchController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/label-batches/{id}',    [LabelBatchController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/label-batches/{id}',    [LabelBatchController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/label-batches/{id}', [LabelBatchController::class, 'destroy'])->middleware('throttle:60,1');

            // Etiquetado
            Route::get('/labelings',         [LabelingController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/labelings',        [LabelingController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/labelings/{id}',    [LabelingController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/labelings/{id}',    [LabelingController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/labelings/{id}', [LabelingController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Registros sanitarios
        Route::get('/sanitary-registrations',         [SanitaryRegistrationController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/sanitary-registrations',        [SanitaryRegistrationController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/sanitary-registrations/{id}',    [SanitaryRegistrationController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/sanitary-registrations/{id}',    [SanitaryRegistrationController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/sanitary-registrations/{id}', [SanitaryRegistrationController::class, 'destroy'])->middleware('throttle:60,1');

        // Autorizaciones de embotellado
        Route::get('/bottling-authorizations',         [BottlingAuthorizationController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/bottling-authorizations',        [BottlingAuthorizationController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/bottling-authorizations/{id}',    [BottlingAuthorizationController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/bottling-authorizations/{id}',    [BottlingAuthorizationController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/bottling-authorizations/{id}', [BottlingAuthorizationController::class, 'destroy'])->middleware('throttle:60,1');

        // Documentos de bodega
        Route::get('/documents',         [WineryDocumentController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/documents',        [WineryDocumentController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/documents/{id}',    [WineryDocumentController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/documents/{id}',    [WineryDocumentController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/documents/{id}', [WineryDocumentController::class, 'destroy'])->middleware('throttle:60,1');

        // Operaciones de bodega
        Route::get('/cellar-operations',                [CellarOperationController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/cellar-operations',               [CellarOperationController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/cellar-operations/{id}',           [CellarOperationController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/cellar-operations/{id}',           [CellarOperationController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/cellar-operations/{id}',        [CellarOperationController::class, 'destroy'])->middleware('throttle:60,1');
        Route::post('/cellar-operations/{id}/complete', [CellarOperationController::class, 'complete'])->middleware('throttle:60,1');

        // Aforos / Previsiones de rendimiento
        Route::middleware('winery.ability:yield_forecasts')->group(function () {
            Route::get('/yield-forecasts',         [YieldForecastController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/yield-forecasts',        [YieldForecastController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/yield-forecasts/{id}',    [YieldForecastController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/yield-forecasts/{id}',    [YieldForecastController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/yield-forecasts/{id}', [YieldForecastController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Certificaciones ecológicas
        Route::get('/eco-certifications',         [EcoCertificationController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/eco-certifications',        [EcoCertificationController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/eco-certifications/{id}',    [EcoCertificationController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/eco-certifications/{id}',    [EcoCertificationController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/eco-certifications/{id}', [EcoCertificationController::class, 'destroy'])->middleware('throttle:60,1');

        // Uva / mosto externo
        Route::get('/external-purchases',         [ExternalGrapePurchaseController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/external-purchases',        [ExternalGrapePurchaseController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/external-purchases/{id}',    [ExternalGrapePurchaseController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/external-purchases/{id}',    [ExternalGrapePurchaseController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/external-purchases/{id}', [ExternalGrapePurchaseController::class, 'destroy'])->middleware('throttle:60,1');

        // Centro de alertas
        Route::get('/alerts',                   [WineryAlertController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/alerts',                  [WineryAlertController::class, 'store'])->middleware('throttle:60,1');
        Route::post('/alerts/{id}/read',        [WineryAlertController::class, 'markRead'])->middleware('throttle:60,1');
        Route::post('/alerts/read-all',         [WineryAlertController::class, 'markAllRead'])->middleware('throttle:30,1');
        Route::delete('/alerts/{id}',           [WineryAlertController::class, 'destroy'])->middleware('throttle:60,1');

        // Disputas en recepciones de uva
        Route::get('/disputes',                  [DisputeController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/disputes/{id}',             [DisputeController::class, 'show'])->middleware('throttle:60,1');
        Route::post('/disputes/{id}/resolve',    [DisputeController::class, 'resolve'])->middleware('throttle:30,1');

        // Trazabilidad uva → vino
        Route::get('/grape-receptions/{id}/traceability', [TraceabilityController::class, 'receptionWines'])->middleware('throttle:60,1');
        Route::get('/wines/{id}/traceability',            [TraceabilityController::class, 'wineReceptions'])->middleware('throttle:60,1');

        // Parcelas de los viticultores de la bodega
        Route::get('/plots/centroids',            [WineryPlotController::class, 'centroids'])->middleware('throttle:30,1');
        Route::get('/plots/geometries',           [WineryPlotController::class, 'allGeometries'])->middleware('throttle:30,1');
        Route::get('/plots',                      [WineryPlotController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/plots/{id}',                 [WineryPlotController::class, 'show'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/geometries',      [WineryPlotController::class, 'geometries'])->middleware('throttle:30,1');
        Route::get('/plots/{id}/harvest-quality', [WineryPlotController::class, 'harvestQuality'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/notebook',        [WineryPlotController::class, 'notebook'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/plantings',       [WineryPlotController::class, 'plantings'])->middleware('throttle:60,1');

        // Fichas técnicas de vino (datos agregados)
        Route::get('/wines/{id}/technical-sheet', [WineController::class, 'technicalSheet'])->middleware('throttle:60,1');

        // Resumen económico
        Route::get('/economic-summary', EconomicSummaryController::class)->middleware('throttle:30,1');

        // Configuración de bodega (numeración de facturas)
        Route::get('/settings',  [WinerySettingController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/settings',  [WinerySettingController::class, 'update'])->middleware('throttle:30,1');

        // Denominación de Origen
        Route::get('/do/qualifications',      [DenominationOfOriginController::class, 'qualifications'])->middleware('throttle:60,1');
        Route::get('/do/qualifications/{id}', [DenominationOfOriginController::class, 'showQualification'])->middleware('throttle:60,1');
        Route::get('/do/labels',              [DenominationOfOriginController::class, 'labels'])->middleware('throttle:60,1');
        Route::get('/do/labels/{id}',         [DenominationOfOriginController::class, 'showLabel'])->middleware('throttle:60,1');
        Route::get('/do/inspections',         [DenominationOfOriginController::class, 'inspections'])->middleware('throttle:60,1');
        Route::get('/do/documents',           [DenominationOfOriginController::class, 'documents'])->middleware('throttle:60,1');

        // VeriFactu (facturación electrónica AEAT)
        Route::middleware('winery.ability:verifaktu')->group(function () {
            Route::get('/verifactu',                   [VerifactuController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/verifactu/submit',           [VerifactuController::class, 'submit'])->middleware('throttle:30,1');
            Route::get('/verifactu/{id}',              [VerifactuController::class, 'show'])->middleware('throttle:60,1');
            Route::post('/verifactu/{id}/cancel',      [VerifactuController::class, 'cancel'])->middleware('throttle:30,1');
        });

        // ── Alias routes (nombres distintos entre web y API móvil) ─────────────

        // Cuadro de mando de vendimia
        Route::get('/harvest-summary', HarvestSummaryController::class)->middleware('throttle:30,1');

        // Aforos / estimaciones de viticultores
        Route::middleware('winery.ability:yield_forecasts')->group(function () {
            Route::get('/vitic-estimates',      [YieldForecastController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/vitic-estimates',     [YieldForecastController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/vitic-estimates/{id}', [YieldForecastController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/vitic-estimates/{id}', [YieldForecastController::class, 'update'])->middleware('throttle:60,1');
        });

        // Análisis de calidad de vendimia (alias de wine-analysis)
        Route::middleware('winery.ability:quality_analysis')->group(function () {
            Route::get('/harvest-quality',         [WineAnalysisController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/harvest-quality',        [WineAnalysisController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/harvest-quality/{id}',    [WineAnalysisController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/harvest-quality/{id}',    [WineAnalysisController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/harvest-quality/{id}', [WineAnalysisController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Previsiones de vendimia (alias de yield-forecasts)
        Route::middleware('winery.ability:yield_forecasts')->group(function () {
            Route::get('/harvest-forecasts',         [YieldForecastController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/harvest-forecasts',        [YieldForecastController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/harvest-forecasts/{id}',    [YieldForecastController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/harvest-forecasts/{id}',    [YieldForecastController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/harvest-forecasts/{id}', [YieldForecastController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Mantenimiento por contenedor (alias nombrado en singular, ya existe el plural)
        Route::get('/containers/{id}/maintenance', [ContainerMaintenanceController::class, 'byContainer'])->middleware('throttle:60,1');

        // Aditivos por contenedor
        Route::get('/containers/{id}/additives', [WineAdditiveController::class, 'byContainer'])->middleware('throttle:60,1');

        // Salas de bodega (alias container-rooms)
        Route::get('/container-rooms',         [ContainerRoomController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/container-rooms',        [ContainerRoomController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/container-rooms/{id}',    [ContainerRoomController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/container-rooms/{id}',    [ContainerRoomController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/container-rooms/{id}', [ContainerRoomController::class, 'destroy'])->middleware('throttle:60,1');

        // Insumos de bodega (alias winery-supplies)
        Route::get('/winery-supplies',         [WinerySupplyController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/winery-supplies',        [WinerySupplyController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/winery-supplies/{id}',    [WinerySupplyController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/winery-supplies/{id}',    [WinerySupplyController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/winery-supplies/{id}', [WinerySupplyController::class, 'destroy'])->middleware('throttle:60,1');

        // Uva/mosto externo (alias external-grape)
        Route::get('/external-grape',         [ExternalGrapePurchaseController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/external-grape',        [ExternalGrapePurchaseController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/external-grape/{id}',    [ExternalGrapePurchaseController::class, 'show'])->middleware('throttle:60,1');
        Route::put('/external-grape/{id}',    [ExternalGrapePurchaseController::class, 'update'])->middleware('throttle:60,1');
        Route::delete('/external-grape/{id}', [ExternalGrapePurchaseController::class, 'destroy'])->middleware('throttle:60,1');

        // Embotellado (alias singular)
        Route::middleware('winery.ability:product_sales')->group(function () {
            Route::get('/bottling',         [BottlingController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/bottling',        [BottlingController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/bottling/{id}',    [BottlingController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/bottling/{id}',    [BottlingController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/bottling/{id}', [BottlingController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Etiquetado (alias singular)
        Route::middleware('winery.ability:label_batches')->group(function () {
            Route::get('/labeling',         [LabelingController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/labeling',        [LabelingController::class, 'store'])->middleware('throttle:60,1');
            Route::put('/labeling/{id}',    [LabelingController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/labeling/{id}', [LabelingController::class, 'destroy'])->middleware('throttle:60,1');
        });

        // Facturas de compra de uva (alias ruta web)
        Route::middleware('winery.ability:grape_purchase_invoice')->group(function () {
            Route::get('/invoices/grape-purchase',                    [GrapePurchaseInvoiceController::class, 'index'])->middleware('throttle:60,1');
            Route::post('/invoices/grape-purchase',                   [GrapePurchaseInvoiceController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/invoices/grape-purchase/{id}',               [GrapePurchaseInvoiceController::class, 'show'])->middleware('throttle:60,1');
            Route::put('/invoices/grape-purchase/{id}',               [GrapePurchaseInvoiceController::class, 'update'])->middleware('throttle:60,1');
            Route::delete('/invoices/grape-purchase/{id}',            [GrapePurchaseInvoiceController::class, 'destroy'])->middleware('throttle:60,1');
            Route::post('/invoices/grape-purchase/{id}/confirm',      [GrapePurchaseInvoiceController::class, 'confirm'])->middleware('throttle:30,1');
            Route::post('/invoices/grape-purchase/{id}/mark-paid',    [GrapePurchaseInvoiceController::class, 'markPaid'])->middleware('throttle:30,1');
        });

        // Resumen y estadísticas económicas (alias)
        Route::get('/financial-summary', EconomicSummaryController::class)->middleware('throttle:30,1');
        Route::get('/financial-stats',   WineryStatsController::class)->middleware('throttle:30,1');
    });

    // ── Viticulturist / Producer ───────────────────────────────────────────────
    Route::prefix('viticulturist')->middleware(['api.role:viticulturist,producer', 'api.check.beta'])->group(function () {

        Route::get('/dashboard', ViticulturistDashboard::class)->middleware('throttle:60,1');

        // Parcelas
        Route::get('/plots/centroids',       [PlotController::class, 'centroids'])->middleware('throttle:30,1');
        Route::get('/plots/geometries',      [PlotController::class, 'allGeometries'])->middleware('throttle:30,1');
        Route::get('/plots',                 [PlotController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/plots/{id}',            [PlotController::class, 'show'])->middleware('throttle:60,1');
        Route::get('/plots/{id}/geometries', [PlotController::class, 'geometries'])->middleware('throttle:30,1');
        Route::get('/plots/{id}/plantings',  [PlotController::class, 'plantings'])->middleware('throttle:60,1');
        Route::put('/plots/{id}',            [PlotController::class, 'update'])->middleware('throttle:60,1');

        // Campañas
        Route::get('/campaigns',                 [CampaignController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/campaigns/active',          [CampaignController::class, 'active'])->middleware('throttle:60,1');
        Route::get('/campaigns/{id}/activities', [CampaignController::class, 'activities'])->middleware('throttle:60,1');

        // Cuaderno — estimated-yields (ruta estática ANTES de los wildcards)
        Route::get('/notebook/estimated-yields',  [EstimatedYieldController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/notebook/estimated-yields', [EstimatedYieldController::class, 'store'])->middleware('throttle:30,1');

        // Cuaderno — listados por tipo usando parámetro con where() (cacheable, sin closures)
        Route::get('/notebook/{notebook_type}', [NotebookController::class, 'indexOfType'])
            ->where('notebook_type', 'treatments|fertilizations|irrigations|observations|harvests|cultural-works|pruning|post-harvest-treatments')
            ->middleware('throttle:60,1');

        // Cuaderno — creación por tipo extendido (pruning, cultural-works, post-harvest)
        Route::post('/notebook/{notebook_type}', [NotebookController::class, 'storeTyped'])
            ->where('notebook_type', 'pruning|cultural-works|post-harvest-treatments')
            ->middleware('throttle:30,1');

        // Cuaderno — CRUD genérico (wildcard numérico DESPUÉS de los tipados)
        Route::get('/notebook',         [NotebookController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/notebook',        [NotebookController::class, 'store'])->middleware('throttle:30,1');
        Route::get('/notebook/{id}',    [NotebookController::class, 'show'])->where('id', '[0-9]+')->middleware('throttle:60,1');
        Route::put('/notebook/{id}',    [NotebookController::class, 'update'])->where('id', '[0-9]+')->middleware('throttle:30,1');
        Route::delete('/notebook/{id}', [NotebookController::class, 'destroy'])->where('id', '[0-9]+')->middleware('throttle:30,1');

        // Gestión de plagas
        Route::get('/pests',      [PestController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/pests/{id}', [PestController::class, 'show'])->middleware('throttle:60,1');

        // Cumplimiento PAC
        Route::get('/compliance', [ComplianceController::class, 'index'])->middleware('throttle:30,1');

        // Teledetección NDVI
        Route::get('/plots/{plot}/ndvi', [RemoteSensingController::class, 'getPlotNdviColors'])->middleware('throttle:30,1');
        Route::get('/ndvi',              [RemoteSensingController::class, 'getAllPlotsNdvi'])->middleware('throttle:30,1');

        // ── Fenología ────────────────────────────────────────────────────────
        Route::get('/phenology', [PhenologyObservationController::class, 'index'])->middleware('throttle:60,1');

        // ── Costes parcela ───────────────────────────────────────────────────
        Route::get('/plot-costs', [PlotCostController::class, 'index'])->middleware('throttle:60,1');

        // ── Consumo energía ──────────────────────────────────────────────────
        Route::get('/energy-usages', [EnergyUsageController::class, 'index'])->middleware('throttle:60,1');

        // ── Declaraciones cosecha ────────────────────────────────────────────
        Route::get('/harvest-declarations', [HarvestDeclarationController::class, 'index'])->middleware('throttle:60,1');

        // ── Documentos campaña ───────────────────────────────────────────────
        Route::get('/campaign-documents', [CampaignDocumentController::class, 'index'])->middleware('throttle:60,1');

        // ── Análisis suelo ───────────────────────────────────────────────────
        Route::get('/soil-analyses', [SoilAnalysisController::class, 'index'])->middleware('throttle:60,1');

        // ── Biodiversidad ────────────────────────────────────────────────────
        Route::get('/biodiversity-records', [BiodiversityRecordController::class, 'index'])->middleware('throttle:60,1');

        // ── Gestión residuos ─────────────────────────────────────────────────
        Route::get('/residue-managements', [ResidueManagementController::class, 'index'])->middleware('throttle:60,1');

        // ── Análisis residuos ────────────────────────────────────────────────
        Route::get('/residue-analyses', [ResidueAnalysisController::class, 'index'])->middleware('throttle:60,1');

        // ── Trabajos planeados ───────────────────────────────────────────────
        Route::get('/planned-works', [PlannedWorkController::class, 'index'])->middleware('throttle:60,1');

        // ── Planes fertilización ─────────────────────────────────────────────
        Route::get('/fertilization-plans', [FertilizationPlanController::class, 'index'])->middleware('throttle:60,1');

        // ── Alertas fitosanitarias ───────────────────────────────────────────
        Route::get('/phytosanitary-alerts', [PhytosanitaryAlertController::class, 'index'])->middleware('throttle:60,1');

        // ── Concesiones agua ─────────────────────────────────────────────────
        Route::get('/water-concessions', [WaterConcessionController::class, 'index'])->middleware('throttle:60,1');

        // ── Subcontratación ──────────────────────────────────────────────────
        Route::get('/subcontracting', [SubcontractingController::class, 'index'])->middleware('throttle:60,1');

        // ── Entorno parcelas ─────────────────────────────────────────────────
        Route::get('/plot-environments', [PlotEnvironmentController::class, 'index'])->middleware('throttle:60,1');

        // ── Almacén / Stock ──────────────────────────────────────────────────
        Route::get('/warehouse', [WarehouseController::class, 'index'])->middleware('throttle:60,1');

        // ── Maquinaria ───────────────────────────────────────────────────────
        Route::get('/machinery', [MachineryController::class, 'index'])->middleware('throttle:60,1');

        // ── Productos fitosanitarios ─────────────────────────────────────────
        Route::get('/phytosanitary-products', [PhytosanitaryProductController::class, 'index'])->middleware('throttle:60,1');

        // ── Cuadrillas / Personal ────────────────────────────────────────────
        Route::get('/crews', [CrewController::class, 'index'])->middleware('throttle:60,1');

        // ── Aplicadores ROPO ─────────────────────────────────────────────────
        Route::get('/field-applicators', [FieldApplicatorController::class, 'index'])->middleware('throttle:60,1');

        // ── Equipos campo ────────────────────────────────────────────────────
        Route::get('/field-equipment', [FieldEquipmentController::class, 'index'])->middleware('throttle:60,1');
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
