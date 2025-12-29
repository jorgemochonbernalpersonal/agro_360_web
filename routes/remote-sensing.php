<?php

use App\Http\Controllers\Api\RemoteSensingController;
use App\Http\Controllers\RemoteSensingExportController;
use App\Livewire\Viticulturist\RemoteSensing\Dashboard;
use App\Livewire\Viticulturist\RemoteSensing\PlotAnalysis;
use App\Services\RemoteSensing\RemoteSensingReportService;
use App\Models\Plot;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Remote Sensing Routes
|--------------------------------------------------------------------------
|
| Routes for the remote sensing module (Sentinel-2 satellite data).
|
*/

Route::middleware(['auth', 'verified', 'check.beta'])->group(function () {
    Route::prefix('remote-sensing')->name('remote-sensing.')->group(function () {
        // Dashboard principal de teledetección
        Route::get('/', Dashboard::class)->name('dashboard');
        
        // Análisis unificado por parcela
        Route::get('/plot/{plot}', PlotAnalysis::class)->name('plot');
        
        // API endpoints para datos de teledetección
        Route::get('/api/plot/{plot}/ndvi-colors', [RemoteSensingController::class, 'getPlotNdviColors'])
            ->name('api.plot.ndvi-colors');
        Route::get('/api/plots/ndvi', [RemoteSensingController::class, 'getAllPlotsNdvi'])
            ->name('api.plots.ndvi');
        
        // PDF Reports
        Route::get('/report/plot/{plot}', function (Plot $plot) {
            $service = new RemoteSensingReportService();
            $result = $service->generatePlotReport($plot);
            
            if ($result['success']) {
                return $service->downloadReport($result['pdf_path']);
            }
            
            return back()->with('error', 'No se pudo generar el informe');
        })->name('report.plot');
        
        Route::get('/report/global', function () {
            $service = new RemoteSensingReportService();
            $result = $service->generateGlobalReport(auth()->user());
            
            if ($result['success']) {
                return $service->downloadReport($result['pdf_path']);
            }
            
            return back()->with('error', 'No se pudo generar el informe');
        })->name('report.global');
        
        // Export routes (PDF/Excel)
        Route::get('/export/{plot}/pdf', [RemoteSensingExportController::class, 'exportPdf'])
            ->name('export.pdf');
        Route::get('/export/{plot}/excel', [RemoteSensingExportController::class, 'exportExcel'])
            ->name('export.excel');
    });
});

