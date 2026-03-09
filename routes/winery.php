<?php

use Illuminate\Support\Facades\Route;

// Ruta helper: redirige al dashboard (usada por stubs mientras se implementa el módulo)
$stub = fn() => redirect()->route('winery.dashboard');

Route::middleware(['role:winery'])
    ->prefix('winery')
    ->name('winery.')
    ->group(function () use ($stub) {

        Route::get('/dashboard', \App\Livewire\Winery\Dashboard::class)->name('dashboard');

        // ── Viticultores ─────────────────────────────────────────────
        Route::get('/viticulturists', \App\Livewire\Winery\Viticulturists\Index::class)->name('viticulturists.index');
        Route::get('/viticulturists/create', \App\Livewire\Winery\Viticulturists\Create::class)->name('viticulturists.create');
        Route::get('/viticulturists/invite', \App\Livewire\Winery\Viticulturists\Invite::class)->name('viticulturists.invite');
        Route::get('/viticulturists/{viticulturist}', \App\Livewire\Winery\Viticulturists\Show::class)->name('viticulturists.show');
        Route::get('/viticulturists/{viticulturist}/edit', \App\Livewire\Winery\Viticulturists\Edit::class)->name('viticulturists.edit');

        // ── Parcelas (gestión completa de viticultores propios) ───────────
        Route::get('/plots', \App\Livewire\Plots\Index::class)->name('plots.index');
        Route::get('/plots/create', \App\Livewire\Plots\Create::class)->name('plots.create');
        Route::get('/plots/{plot}', \App\Livewire\Plots\Show::class)->name('plots.show');
        Route::get('/plots/{plot}/edit', \App\Livewire\Plots\Edit::class)->name('plots.edit');

        // ── Plantaciones de parcelas (gestión por bodega) ─────────────────
        Route::get('/plots/{plot}/plantings/create', \App\Livewire\Plots\Plantings\Create::class)->name('plots.plantings.create');
        Route::get('/plots/{plot}/plantings/{planting}/edit', \App\Livewire\Plots\Plantings\Edit::class)->name('plots.plantings.edit');

        // ── Campañas (gestionadas automáticamente por añada) ──────────
        Route::get('/campaigns', fn() => redirect()->route('winery.grape-reception.index'))->name('campaigns.index');
        Route::get('/campaigns/create', fn() => redirect()->route('winery.grape-reception.index'))->name('campaigns.create');

        // ── Cuadro de mando vendimia ──────────────────────────────────
        Route::get('/harvest-summary', \App\Livewire\Winery\Harvest\Summary\Index::class)->name('harvest-summary.index');

        // ── Aforos de viticultores (solo lectura) ─────────────────────
        Route::get('/vitic-estimates', \App\Livewire\Winery\Harvest\ViticulturistEstimates\Index::class)->name('vitic-estimates.index');

        // ── Análisis de calidad vendimia ───────────────────────────────
        Route::get('/harvest-quality', \App\Livewire\Winery\Harvest\QualityAnalysis\Index::class)->name('harvest-quality.index');
        Route::get('/harvest-quality/export/pdf', [\App\Http\Controllers\Winery\HarvestQualityController::class, 'exportPdf'])->name('harvest-quality.export-pdf');

        // ── Actividades de campo (solo lectura) ───────────────────────
        Route::get('/field-activities', \App\Livewire\Winery\FieldActivities\Index::class)->name('field-activities.index');

        // ── Previsiones de vendimia (aforo bodega) ───────────────────
        Route::get('/harvest-forecasts', \App\Livewire\Winery\Harvest\Forecasts\Index::class)->name('harvest-forecasts.index');
        Route::get('/harvest-forecasts/create', \App\Livewire\Winery\Harvest\Forecasts\Create::class)->name('harvest-forecasts.create');
        Route::get('/harvest-forecasts/{forecast}/edit', \App\Livewire\Winery\Harvest\Forecasts\Edit::class)->name('harvest-forecasts.edit');

        // ── Recepción de uva ──────────────────────────────────────────
        Route::get('/grape-reception', \App\Livewire\Winery\Harvest\Reception\Index::class)->name('grape-reception.index');
        Route::get('/grape-reception/create', \App\Livewire\Winery\Harvest\Reception\Create::class)->name('grape-reception.create');
        Route::get('/grape-reception/export/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdf'])->name('grape-reception.export-pdf');
        Route::get('/grape-reception/export/excel', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportExcel'])->name('grape-reception.export-excel');
        Route::get('/grape-reception/{harvest}', \App\Livewire\Winery\Harvest\Reception\Show::class)->name('grape-reception.show');
        Route::get('/grape-reception/{harvest}/edit', \App\Livewire\Winery\Harvest\Reception\Edit::class)->name('grape-reception.edit');
        Route::get('/grape-reception/{harvest}/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdfSingle'])->name('grape-reception.export-pdf-single');

        // ── Contenedores de bodega ────────────────────────────────────
        Route::get('/containers', \App\Livewire\Winery\Cellar\Containers\Index::class)->name('containers.index');
        Route::get('/containers/create', \App\Livewire\Winery\Cellar\Containers\Create::class)->name('containers.create');
        Route::get('/containers/{container}/edit', \App\Livewire\Winery\Cellar\Containers\Edit::class)->name('containers.edit');
        Route::get('/containers/{container}/maintenance', \App\Livewire\Winery\Cellar\Containers\Maintenance\Index::class)->name('containers.maintenance.index');
        Route::get('/containers/{container}/maintenance/create', \App\Livewire\Winery\Cellar\Containers\Maintenance\Create::class)->name('containers.maintenance.create');
        Route::get('/containers/{container}/maintenance/{maintenance}/edit', \App\Livewire\Winery\Cellar\Containers\Maintenance\Edit::class)->name('containers.maintenance.edit');
        Route::get('/containers/{container}/additives', \App\Livewire\Winery\Cellar\Containers\Additives\Index::class)->name('containers.additives.index');
        Route::get('/containers/{container}/additives/create', \App\Livewire\Winery\Cellar\Containers\Additives\Create::class)->name('containers.additives.create');

        // ── Insumos de bodega (en construcción) ──────────────────────
        Route::get('/winery-supplies', \App\Livewire\Winery\UnderConstruction::class)
            ->name('winery-supplies.index')
            ->defaults('module', 'Insumos de Bodega')
            ->defaults('icon', 'building-storefront');
        Route::get('/winery-supplies/create', fn() => redirect()->route('winery.winery-supplies.index'))->name('winery-supplies.create');
        Route::get('/winery-supplies/{winerySupply}/edit', fn() => redirect()->route('winery.winery-supplies.index'))->name('winery-supplies.edit');

        // ── Vinos (en construcción) ────────────────────────────────────
        Route::get('/wines', \App\Livewire\Winery\UnderConstruction::class)
            ->name('wines.index')
            ->defaults('module', 'Vinos')
            ->defaults('icon', 'arrows-right-left');
        Route::get('/wines/create', fn() => redirect()->route('winery.wines.index'))->name('wines.create');
        Route::get('/wines/{wine}/edit', fn() => redirect()->route('winery.wines.index'))->name('wines.edit');
        Route::get('/wines/{wine}/process/create', fn() => redirect()->route('winery.wines.index'))->name('wines.process.create');

        // ── Uva / mosto / vino externo (en construcción) ─────────────
        Route::get('/external-grape', \App\Livewire\Winery\UnderConstruction::class)
            ->name('external-grape.index')
            ->defaults('module', 'Uva / Mosto externo')
            ->defaults('icon', 'archive-box');
        Route::get('/external-grape/create', fn() => redirect()->route('winery.external-grape.index'))->name('external-grape.create');
        Route::get('/external-grape/{grape}/edit', fn() => redirect()->route('winery.external-grape.index'))->name('external-grape.edit');

        // ── Lotes de producto ─────────────────────────────────────────
        Route::get('/product-lots', \App\Livewire\Winery\Cellar\ProductLots\Index::class)->name('product-lots.index');
        Route::get('/product-lots/create', \App\Livewire\Winery\Cellar\ProductLots\Create::class)->name('product-lots.create');
        Route::get('/product-lots/{lot}/edit', \App\Livewire\Winery\Cellar\ProductLots\Edit::class)->name('product-lots.edit');
        // Compatibilidad URLs antiguas (wine-lots → product-lots)
        Route::redirect('/wine-lots', '/winery/product-lots')->name('wine-lots.index');
        Route::redirect('/wine-lots/create', '/winery/product-lots/create')->name('wine-lots.create');

        // ── Clientes ──────────────────────────────────────────────────
        Route::get('/clients', \App\Livewire\Winery\Clients\Index::class)->name('clients.index');
        Route::get('/clients/create', \App\Livewire\Winery\Clients\Create::class)->name('clients.create');
        Route::get('/clients/{client}/edit', \App\Livewire\Winery\Clients\Edit::class)->name('clients.edit');

        // ── Facturación: venta de productos ──────────────────────────
        Route::get('/invoices/products', \App\Livewire\Winery\Billing\ProductSale\Index::class)->name('invoices.products.index');
        Route::get('/invoices/products/create', \App\Livewire\Winery\Billing\ProductSale\Create::class)->name('invoices.products.create');
        Route::get('/invoices/products/{id}/edit', \App\Livewire\Winery\Billing\ProductSale\Edit::class)->name('invoices.products.edit');
        Route::get('/invoices/products/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->name('invoices.products.pdf');
        Route::get('/invoices/products/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->name('invoices.products.delivery-note-pdf');
        Route::get('/invoices/products/{id}/albaran-valorado-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'valoradoNote'])->name('invoices.products.valorado-pdf');
        // Compatibilidad URLs antiguas (wine-sale → products)
        Route::redirect('/invoices/wine-sale', '/winery/invoices/products')->name('invoices.wine-sale.index');
        Route::redirect('/invoices/wine-sale/create', '/winery/invoices/products/create')->name('invoices.wine-sale.create');

        // ── Facturación: liquidación de vendimia ──────────────────────
        Route::get('/invoices/grape-purchase', \App\Livewire\Winery\Billing\GrapePurchase\Index::class)->name('invoices.grape-purchase.index');
        Route::get('/invoices/grape-purchase/create', \App\Livewire\Winery\Billing\GrapePurchase\Create::class)->name('invoices.grape-purchase.create');
        Route::get('/invoices/grape-purchase/{id}/edit', \App\Livewire\Winery\Billing\GrapePurchase\Edit::class)->name('invoices.grape-purchase.edit');
        Route::get('/invoices/grape-purchase/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->name('invoices.grape-purchase.pdf');
        Route::get('/invoices/grape-purchase/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->name('invoices.grape-purchase.delivery-note-pdf');

        // ── Elaboración de vino ───────────────────────────────────────
        Route::get('/wine-process', fn() => redirect()->route('winery.wines.index'))->name('wine-process.index');

        // ── Análisis de laboratorio (en construcción) ────────────────
        Route::get('/wine-analysis', \App\Livewire\Winery\UnderConstruction::class)
            ->name('wine-analysis.index')
            ->defaults('module', 'Análisis de Lab.')
            ->defaults('icon', 'beaker');

        // ── Inventario de insumos de bodega ───────────────────────────
        Route::get('/inventory', fn() => redirect()->route('winery.winery-supplies.index'))->name('inventory.index');

        // ── Proveedores (en construcción) ─────────────────────────────
        Route::get('/suppliers', \App\Livewire\Winery\UnderConstruction::class)
            ->name('suppliers.index')
            ->defaults('module', 'Proveedores')
            ->defaults('icon', 'truck');

        // ── SILICIE (en construcción) ─────────────────────────────────
        Route::get('/silicie', \App\Livewire\Winery\UnderConstruction::class)
            ->name('silicie.dashboard')
            ->defaults('module', 'SILICIE')
            ->defaults('icon', 'document-chart-bar');
        Route::get('/silicie/movements', fn() => redirect()->route('winery.silicie.dashboard'))->name('silicie.movements.index');

        // ── Documentos Bodega (en construcción) ───────────────────────
        Route::get('/documents', \App\Livewire\Winery\UnderConstruction::class)
            ->name('documents.index')
            ->defaults('module', 'Documentos Bodega')
            ->defaults('icon', 'folder-open');

        // ── Configuración ─────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Winery\Settings::class)->name('settings');
    });
