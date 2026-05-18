<?php

use Illuminate\Support\Facades\Route;

// Ruta helper: redirige al dashboard (usada por stubs mientras se implementa el módulo)
$stub = fn() => redirect()->route('winery.dashboard');

Route::middleware(['role:winery,producer'])
    ->prefix('winery')
    ->name('winery.')
    ->group(function () use ($stub) {

        Route::get('/dashboard', \App\Livewire\Winery\Dashboard::class)->name('dashboard');
        Route::get('/visual', \App\Livewire\Winery\VisualDashboard::class)->name('visual');

        // ── Denominación de Origen ───────────────────────────────────
        Route::get('/denomination', \App\Livewire\Winery\Denomination\Index::class)->name('denomination.index');
        Route::get('/denomination/requests', \App\Livewire\Winery\Denomination\Requests\Index::class)->name('denomination.requests.index');
        Route::get('/denomination/labels', \App\Livewire\Winery\Denomination\Labels\Index::class)->name('denomination.labels.index');
        Route::get('/denomination/inspections', \App\Livewire\Winery\Denomination\Inspections\Index::class)->name('denomination.inspections.index');
        Route::get('/denomination/qualifications', \App\Livewire\Winery\Denomination\Qualifications\Index::class)->name('denomination.qualifications.index');

        // ── Viticultores ─────────────────────────────────────────────
        Route::get('/viticulturists', \App\Livewire\Winery\Viticulturists\Index::class)->name('viticulturists.index');
        Route::get('/viticulturists/create', \App\Livewire\Winery\Viticulturists\Create::class)->name('viticulturists.create');
        Route::get('/viticulturists/invite', \App\Livewire\Winery\Viticulturists\Invite::class)->name('viticulturists.invite');
        Route::get('/viticulturists/{viticulturist}', \App\Livewire\Winery\Viticulturists\Show::class)->name('viticulturists.show');
        Route::get('/viticulturists/{viticulturist}/edit', \App\Livewire\Winery\Viticulturists\Edit::class)->name('viticulturists.edit');

        // ── Parcelas y Plantaciones ───────────────────────────────────────
        // Producer usa siempre el flujo viticultor (con cuaderno, fenología, etc.)
        Route::middleware(\App\Http\Middleware\RedirectProducerToViticulturistPlots::class)->group(function () {
            Route::get('/plots', \App\Livewire\Plots\Index::class)->name('plots.index');
            Route::get('/plots/create', \App\Livewire\Plots\Create::class)->name('plots.create');
            Route::get('/plots/{plot}', \App\Livewire\Plots\Show::class)->name('plots.show');
            Route::get('/plots/{plot}/edit', \App\Livewire\Plots\Edit::class)->name('plots.edit');
            Route::get('/plots/{plot}/plantings/create', \App\Livewire\Plots\Plantings\Create::class)->name('plots.plantings.create');
            Route::get('/plots/{plot}/plantings/{planting}/edit', \App\Livewire\Plots\Plantings\Edit::class)->name('plots.plantings.edit');
        });

        // ── Campañas ──────────────────────────────────────────────────
        Route::get('/campaigns', \App\Livewire\Winery\Harvest\Campaigns\Index::class)->name('campaigns.index');
        Route::get('/campaigns/create', \App\Livewire\Winery\Harvest\Campaigns\Create::class)->name('campaigns.create');
        Route::get('/campaigns/{campaign}/edit', \App\Livewire\Winery\Harvest\Campaigns\Edit::class)->name('campaigns.edit');

        // ── Cuadro de mando vendimia ──────────────────────────────────
        Route::get('/harvest-summary', \App\Livewire\Winery\Harvest\Summary\Index::class)->name('harvest-summary.index');

        // ── Aforos de viticultores (solo lectura) ─────────────────────
        Route::middleware('winery.ability:yield_forecasts')->group(function () {
            Route::get('/vitic-estimates', \App\Livewire\Winery\Harvest\ViticulturistEstimates\Index::class)->name('vitic-estimates.index');
        });

        // ── Análisis de calidad vendimia ───────────────────────────────
        Route::middleware('winery.ability:quality_analysis')->group(function () {
            Route::get('/harvest-quality', \App\Livewire\Winery\Harvest\QualityAnalysis\Index::class)->name('harvest-quality.index');
            Route::get('/harvest-quality/export/pdf', [\App\Http\Controllers\Winery\HarvestQualityController::class, 'exportPdf'])->name('harvest-quality.export-pdf');
        });

        // ── Actividades de campo (solo lectura) ───────────────────────
        Route::get('/field-activities', \App\Livewire\Winery\FieldActivities\Index::class)->name('field-activities.index');

        // ── Entorno de parcelas (solo lectura — la bodega no puede crear/editar) ─
        Route::get('/plot-environments', \App\Livewire\Viticulturist\PlotEnvironments\Index::class)->name('plot-environments.index');

        // ── Previsiones de vendimia (aforo bodega) ───────────────────
        Route::middleware('winery.ability:yield_forecasts')->group(function () {
            Route::get('/harvest-forecasts', \App\Livewire\Winery\Harvest\Forecasts\Index::class)->name('harvest-forecasts.index');
            Route::get('/harvest-forecasts/create', \App\Livewire\Winery\Harvest\Forecasts\Create::class)->name('harvest-forecasts.create');
            Route::get('/harvest-forecasts/{forecast}/edit', \App\Livewire\Winery\Harvest\Forecasts\Edit::class)->name('harvest-forecasts.edit');
        });

        // ── Recepción de uva ──────────────────────────────────────────
        Route::middleware('winery.ability:harvest_reception')->group(function () {
            Route::get('/grape-reception', \App\Livewire\Winery\Harvest\Reception\Index::class)->name('grape-reception.index');
            Route::get('/grape-reception/create', \App\Livewire\Winery\Harvest\Reception\Create::class)->name('grape-reception.create');
            Route::get('/grape-reception/disputes', \App\Livewire\Winery\Harvest\Reception\Disputes::class)->name('grape-reception.disputes');
            Route::get('/grape-reception/export/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdf'])->name('grape-reception.export-pdf');
            Route::get('/grape-reception/export/excel', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportExcel'])->name('grape-reception.export-excel');
            Route::get('/grape-reception/{harvest}', \App\Livewire\Winery\Harvest\Reception\Show::class)->name('grape-reception.show');
            Route::get('/grape-reception/{harvest}/edit', \App\Livewire\Winery\Harvest\Reception\Edit::class)->name('grape-reception.edit');
            Route::get('/grape-reception/{harvest}/assign', \App\Livewire\Winery\Harvest\Reception\Assign::class)->name('grape-reception.assign');
            Route::get('/grape-reception/{harvest}/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdfSingle'])->name('grape-reception.export-pdf-single');
        });

        // ── Contenedores de bodega ────────────────────────────────────
        Route::middleware('winery.ability:cellar_management')->group(function () {
            Route::get('/containers', \App\Livewire\Winery\Cellar\Containers\Index::class)->name('containers.index');
            Route::get('/containers/analytics', \App\Livewire\Winery\Cellar\Containers\Analytics::class)->name('containers.analytics');
            Route::get('/containers/map', \App\Livewire\Winery\Cellar\Containers\Map::class)->name('containers.map');
            Route::get('/containers/create', \App\Livewire\Winery\Cellar\Containers\Create::class)->name('containers.create');
            Route::get('/containers/{container}', \App\Livewire\Winery\Cellar\Containers\Show::class)->name('containers.show');
            Route::get('/containers/{container}/edit', \App\Livewire\Winery\Cellar\Containers\Edit::class)->name('containers.edit');
            Route::get('/containers/{container}/maintenance', \App\Livewire\Winery\Cellar\Containers\Maintenance\Index::class)->name('containers.maintenance.index');
            Route::get('/containers/{container}/maintenance/create', \App\Livewire\Winery\Cellar\Containers\Maintenance\Create::class)->name('containers.maintenance.create');
            Route::get('/containers/{container}/maintenance/{maintenance}/edit', \App\Livewire\Winery\Cellar\Containers\Maintenance\Edit::class)->name('containers.maintenance.edit');
            Route::get('/containers/{container}/additives', \App\Livewire\Winery\Cellar\Containers\Additives\Index::class)->name('containers.additives.index');
            Route::get('/containers/{container}/additives/create', \App\Livewire\Winery\Cellar\Containers\Additives\Create::class)->name('containers.additives.create');
            Route::get('/containers/{container}/additives/{additive}/edit', \App\Livewire\Winery\Cellar\Containers\Additives\Edit::class)->name('containers.additives.edit');

            // ── Mantenimientos globales ───────────────────────────────────
            Route::get('/container-maintenances', \App\Livewire\Winery\Cellar\Containers\MaintenanceGlobal::class)->name('container-maintenances.index');

            // ── Salas de bodega ───────────────────────────────────────────
            Route::get('/container-rooms', \App\Livewire\Winery\Cellar\ContainerRooms\Index::class)->name('container-rooms.index');
            Route::get('/container-rooms/create', \App\Livewire\Winery\Cellar\ContainerRooms\Create::class)->name('container-rooms.create');
            Route::get('/container-rooms/{room}/edit', \App\Livewire\Winery\Cellar\ContainerRooms\Edit::class)->name('container-rooms.edit');
        });

        // ── Insumos de bodega ─────────────────────────────────────────
        Route::get('/winery-supplies', \App\Livewire\Winery\WinerySupplies\Index::class)->name('winery-supplies.index');
        Route::get('/winery-supplies/create', \App\Livewire\Winery\WinerySupplies\Create::class)->name('winery-supplies.create');
        Route::get('/winery-supplies/{winerySupply}/edit', \App\Livewire\Winery\WinerySupplies\Edit::class)->name('winery-supplies.edit');

        // ── Vinos ─────────────────────────────────────────────────────────────
        Route::middleware('winery.ability:wine_process')->group(function () {
            Route::get('/wines', \App\Livewire\Winery\Wines\Index::class)->name('wines.index');
            Route::get('/wines/timeline', \App\Livewire\Winery\Wines\Timeline::class)->name('wines.timeline');
            Route::get('/wines/create', \App\Livewire\Winery\Wines\Create::class)->name('wines.create');
            Route::get('/wines/{wine}', \App\Livewire\Winery\Wines\Show::class)->name('wines.show');
            Route::get('/wines/{wine}/edit', \App\Livewire\Winery\Wines\Edit::class)->name('wines.edit');
            Route::get('/wines/{wine}/process/create', \App\Livewire\Winery\Wines\Process\Create::class)->name('wines.process.create');
            Route::get('/wines/{wine}/process/{process}/edit', \App\Livewire\Winery\Wines\Process\Edit::class)->name('wines.process.edit');
            Route::get('/wines/{wine}/traceability-pdf', [\App\Http\Controllers\Winery\WineTraceabilityController::class, 'exportPdf'])->name('wines.traceability-pdf');
        });

        // ── Uva / mosto / vino externo (en construcción) ─────────────
        Route::get('/external-grape', \App\Livewire\Winery\ExternalGrape\Index::class)->name('external-grape.index');
        Route::get('/external-grape/create', \App\Livewire\Winery\ExternalGrape\Create::class)->name('external-grape.create');
        Route::get('/external-grape/{externalGrape}/edit', \App\Livewire\Winery\ExternalGrape\Edit::class)->name('external-grape.edit');

        // ── Lotes de producto ─────────────────────────────────────────
        Route::get('/product-lots', \App\Livewire\Winery\Cellar\ProductLots\Index::class)->name('product-lots.index');
        Route::get('/product-lots/insights', \App\Livewire\Winery\Cellar\ProductLots\Insights::class)->name('product-lots.insights');
        Route::get('/product-lots/audit', \App\Livewire\Winery\Cellar\ProductLots\Audit::class)->name('product-lots.audit');
        Route::get('/product-lots/create', \App\Livewire\Winery\Cellar\ProductLots\Create::class)->name('product-lots.create');
        Route::get('/product-lots/{lot}/sales', \App\Livewire\Winery\Cellar\ProductLots\Sales::class)->name('product-lots.sales');
        Route::get('/product-lots/{lot}/edit', \App\Livewire\Winery\Cellar\ProductLots\Edit::class)->name('product-lots.edit');
        // Compatibilidad URLs antiguas (wine-lots → product-lots)
        Route::redirect('/wine-lots', '/winery/product-lots')->name('wine-lots.index');
        Route::redirect('/wine-lots/create', '/winery/product-lots/create')->name('wine-lots.create');

        // ── Enólogos ──────────────────────────────────────────────────
        Route::get('/oenologists', \App\Livewire\Winery\Oenologists\Index::class)->name('oenologists.index');
        Route::get('/oenologists/create', \App\Livewire\Winery\Oenologists\Create::class)->name('oenologists.create');
        Route::get('/oenologists/{oenologist}/edit', \App\Livewire\Winery\Oenologists\Edit::class)->name('oenologists.edit');

        // ── Clientes ──────────────────────────────────────────────────
        Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients.index');
        Route::get('/clients/insights', \App\Livewire\Clients\Insights::class)->name('clients.insights');
        Route::get('/clients/create', \App\Livewire\Clients\Create::class)->name('clients.create');
        Route::get('/clients/{client}', \App\Livewire\Clients\Show::class)->name('clients.show');
        Route::get('/clients/{client}/edit', \App\Livewire\Clients\Edit::class)->name('clients.edit');

        // ── Facturación: venta de productos ──────────────────────────
        Route::middleware('winery.ability:product_sales')->group(function () {
            Route::get('/invoices/products', \App\Livewire\Winery\Billing\ProductSale\Index::class)->name('invoices.products.index');
            Route::get('/invoices/products/create', \App\Livewire\Winery\Billing\ProductSale\Create::class)->name('invoices.products.create');
            Route::get('/invoices/products/{id}/edit', \App\Livewire\Winery\Billing\ProductSale\Edit::class)->name('invoices.products.edit');
            Route::get('/invoices/products/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->name('invoices.products.pdf');
            Route::get('/invoices/products/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->name('invoices.products.delivery-note-pdf');
            Route::get('/invoices/products/{id}/albaran-valorado-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'valoradoNote'])->name('invoices.products.valorado-pdf');
        });
        // Compatibilidad URLs antiguas (wine-sale → products)
        Route::redirect('/invoices/wine-sale', '/winery/invoices/products')->name('invoices.wine-sale.index');
        Route::redirect('/invoices/wine-sale/create', '/winery/invoices/products/create')->name('invoices.wine-sale.create');

        // ── Facturación: liquidación de vendimia ──────────────────────
        Route::middleware('winery.ability:grape_purchase_invoice')->group(function () {
            Route::get('/invoices/grape-purchase', \App\Livewire\Winery\Billing\GrapePurchase\Index::class)->name('invoices.grape-purchase.index');
            Route::get('/invoices/grape-purchase/create', \App\Livewire\Winery\Billing\GrapePurchase\Create::class)->name('invoices.grape-purchase.create');
            Route::get('/invoices/grape-purchase/{id}/edit', \App\Livewire\Winery\Billing\GrapePurchase\Edit::class)->name('invoices.grape-purchase.edit');
            Route::get('/invoices/grape-purchase/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->name('invoices.grape-purchase.pdf');
            Route::get('/invoices/grape-purchase/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->name('invoices.grape-purchase.delivery-note-pdf');
        });

        // ── Elaboración de vino ───────────────────────────────────────
        Route::get('/wine-process', fn() => redirect()->route('winery.wines.index'))->name('wine-process.index');

        // ── Análisis de laboratorio ───────────────────────────────────
        Route::middleware('winery.ability:wine_process')->group(function () {
            Route::get('/wine-analysis', \App\Livewire\Winery\WineAnalysis\Index::class)->name('wine-analysis.index');
            Route::get('/wine-analysis/create', \App\Livewire\Winery\WineAnalysis\Create::class)->name('wine-analysis.create');
            Route::get('/wine-analysis/{analysis}/edit', \App\Livewire\Winery\WineAnalysis\Edit::class)->name('wine-analysis.edit');
        });

        // ── Controles de Fermentación ─────────────────────────────────
        Route::get('/fermentation-controls', \App\Livewire\Winery\FermentationControls\Index::class)->name('fermentation-controls.index');
        Route::get('/fermentation-controls/create', \App\Livewire\Winery\FermentationControls\Create::class)->name('fermentation-controls.create');
        Route::get('/fermentation-controls/{control}/edit', \App\Livewire\Winery\FermentationControls\Edit::class)->name('fermentation-controls.edit');

        // ── Trasvases ──────────────────────────────────────────────────
        Route::get('/wine-transfers', \App\Livewire\Winery\WineTransfers\Index::class)->name('wine-transfers.index');
        Route::get('/wine-transfers/create', \App\Livewire\Winery\WineTransfers\Create::class)->name('wine-transfers.create');
        Route::get('/wine-transfers/{transfer}/edit', \App\Livewire\Winery\WineTransfers\Edit::class)->name('wine-transfers.edit');

        // ── Coupage ────────────────────────────────────────────────────
        Route::prefix('coupage')->name('coupage.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Coupage\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Coupage\Create::class)->name('create');
        });

        // ── Mermas y Pérdidas ─────────────────────────────────────────
        Route::get('/wine-losses', \App\Livewire\Winery\WineLosses\Index::class)->name('wine-losses.index');
        Route::get('/wine-losses/create', \App\Livewire\Winery\WineLosses\Create::class)->name('wine-losses.create');
        Route::get('/wine-losses/{loss}/edit', \App\Livewire\Winery\WineLosses\Edit::class)->name('wine-losses.edit');

        // ── Aditivos Enológicos ───────────────────────────────────────
        Route::get('/wine-additives', \App\Livewire\Winery\WineAdditives\Index::class)->name('wine-additives.index');

        // ── Inventario de insumos de bodega ───────────────────────────
        Route::get('/inventory', fn() => redirect()->route('winery.winery-supplies.index'))->name('inventory.index');

        // ── Proveedores ───────────────────────────────────────────────
        Route::get('/suppliers', \App\Livewire\Winery\Suppliers\Index::class)->name('suppliers.index');
        Route::get('/suppliers/create', \App\Livewire\Winery\Suppliers\Create::class)->name('suppliers.create');
        Route::get('/suppliers/{supplier}/edit', \App\Livewire\Winery\Suppliers\Edit::class)->name('suppliers.edit');

        // ── SILICIE ───────────────────────────────────────────────────
        Route::get('/silicie', \App\Livewire\Winery\Silicie\Dashboard::class)
            ->name('silicie.dashboard');
        Route::get('/silicie/movements', fn() => redirect()->route('winery.silicie.dashboard'))->name('silicie.movements.index');
        Route::get('/silicie/infovi', \App\Livewire\Winery\Silicie\Infovi::class)
            ->name('silicie.infovi');
        Route::get('/silicie/infovi/pdf', [\App\Http\Controllers\Winery\InfoviController::class, 'exportPdf'])
            ->name('silicie.infovi.pdf');

        // ── Documentos Bodega ─────────────────────────────────────────
        Route::get('/documents', \App\Livewire\Winery\Documents\Index::class)->name('documents.index');
        Route::get('/documents/create', \App\Livewire\Winery\Documents\Create::class)->name('documents.create');
        Route::get('/documents/{wineryDocument}/edit', \App\Livewire\Winery\Documents\Edit::class)->name('documents.edit');

        // ── Resumen Económico ─────────────────────────────────────────
        Route::get('/financial-summary', \App\Livewire\Winery\Financial\Summary::class)->name('financial-summary.index');

        // ── Estadísticas Financieras ──────────────────────────────────
        Route::get('/financial-stats', \App\Livewire\Winery\Financial\Stats::class)->name('financial-stats.index');

        // ── Embotellado ───────────────────────────────────────────────
        Route::get('/bottling', \App\Livewire\Winery\Bottling\Index::class)->name('bottling.index');
        Route::get('/bottling/create', \App\Livewire\Winery\Bottling\Create::class)->name('bottling.create');
        Route::get('/bottling/{bottling}/edit', \App\Livewire\Winery\Bottling\Edit::class)->name('bottling.edit');

        // ── Lotes de etiquetas ────────────────────────────────────────
        Route::middleware('winery.ability:label_batches')->group(function () {
            Route::get('/label-batches', \App\Livewire\Winery\LabelBatches\Index::class)->name('label-batches.index');
            Route::get('/label-batches/create', \App\Livewire\Winery\LabelBatches\Create::class)->name('label-batches.create');
            Route::get('/label-batches/{labelBatch}/edit', \App\Livewire\Winery\LabelBatches\Edit::class)->name('label-batches.edit');
            Route::get('/label-batches/{labelBatch}/waste', \App\Livewire\Winery\LabelBatches\Waste\Index::class)->name('label-batches.waste.index');
            Route::get('/label-batches/{labelBatch}/waste/create', \App\Livewire\Winery\LabelBatches\Waste\Create::class)->name('label-batches.waste.create');
        });

        // ── Etiquetado ────────────────────────────────────────────────
        Route::get('/labeling', \App\Livewire\Winery\Labeling\Index::class)->name('labeling.index');
        Route::get('/labeling/create', \App\Livewire\Winery\Labeling\Create::class)->name('labeling.create');
        Route::get('/labeling/{labeling}/edit', \App\Livewire\Winery\Labeling\Edit::class)->name('labeling.edit');

        // ── Fichas Técnicas y Catas ────────────────────────────────────
        Route::get('/product-sheets', \App\Livewire\Winery\ProductSheets\Index::class)->name('product-sheets.index');
        Route::get('/tasting-notes', \App\Livewire\Winery\TastingNotes\Index::class)->name('tasting-notes.index');
        Route::get('/tasting-notes/create', \App\Livewire\Winery\TastingNotes\Create::class)->name('tasting-notes.create');
        Route::get('/tasting-notes/{tastingNote}/edit', \App\Livewire\Winery\TastingNotes\Edit::class)->name('tasting-notes.edit');

        // ── Subproductos ───────────────────────────────────────────────
        Route::get('/subproducts', \App\Livewire\Winery\Subproducts\Index::class)->name('subproducts.index');
        Route::get('/subproducts/create', \App\Livewire\Winery\Subproducts\Create::class)->name('subproducts.create');
        Route::get('/subproducts/{subproduct}/edit', \App\Livewire\Winery\Subproducts\Edit::class)->name('subproducts.edit');

        // ── Trazabilidad ──────────────────────────────────────────────
        Route::get('/traceability', \App\Livewire\Winery\Traceability\Index::class)
            ->name('traceability.index');

        // ── Operaciones de Bodega ─────────────────────────────────────
        Route::prefix('cellar-operations')->name('cellar-operations.')->group(function () {
            Route::get('/', \App\Livewire\Winery\CellarOperations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\CellarOperations\Create::class)->name('create');
            Route::get('/{operation}/edit', \App\Livewire\Winery\CellarOperations\Edit::class)->name('edit');
        });

        // ── Meteorología ──────────────────────────────────────────────
        Route::get('/meteorology', \App\Livewire\Winery\Meteorology\Index::class)->name('meteorology.index');

        // ── Avisos a Viticultores ────────────────────────────────────
        Route::get('/announcements', \App\Livewire\Winery\Announcements\Index::class)->name('announcements.index');

        // ── Centro de Alertas ─────────────────────────────────────────
        Route::get('/alerts', \App\Livewire\Winery\Alerts\Dashboard::class)->name('alerts.index');

        // ── VeriFactu / facturación electrónica ───────────────────────
        Route::middleware('winery.ability:verifaktu')->group(function () {
            Route::get('/verifactu', \App\Livewire\Winery\Verifactu\Dashboard::class)->name('verifactu.index');
        });

        // ── Registros Sanitarios ──────────────────────────────────────
        Route::get('/sanitary-registrations', \App\Livewire\Winery\SanitaryRegistrations\Index::class)->name('sanitary-registrations.index');
        Route::get('/sanitary-registrations/create', \App\Livewire\Winery\SanitaryRegistrations\Create::class)->name('sanitary-registrations.create');
        Route::get('/sanitary-registrations/{sanitaryRegistration}/edit', \App\Livewire\Winery\SanitaryRegistrations\Edit::class)->name('sanitary-registrations.edit');

        // ── Autorizaciones de Embotellado ─────────────────────────────
        Route::get('/bottling-authorizations', \App\Livewire\Winery\BottlingAuthorizations\Index::class)->name('bottling-authorizations.index');
        Route::get('/bottling-authorizations/create', \App\Livewire\Winery\BottlingAuthorizations\Create::class)->name('bottling-authorizations.create');
        Route::get('/bottling-authorizations/{bottlingAuthorization}/edit', \App\Livewire\Winery\BottlingAuthorizations\Edit::class)->name('bottling-authorizations.edit');

        // ── Certificaciones Ecológicas ────────────────────────────────
        Route::get('/eco-certifications', \App\Livewire\Winery\EcoCertifications\Index::class)->name('eco-certifications.index');
        Route::get('/eco-certifications/create', \App\Livewire\Winery\EcoCertifications\Create::class)->name('eco-certifications.create');
        Route::get('/eco-certifications/{ecoCertification}/edit', \App\Livewire\Winery\EcoCertifications\Edit::class)->name('eco-certifications.edit');

        // ── Costes de Producción ──────────────────────────────────────
        Route::prefix('production-costs')->name('production-costs.')->group(function () {
            Route::get('/', \App\Livewire\Winery\ProductionCosts\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\ProductionCosts\Create::class)->name('create');
            Route::get('/{cost}/edit', \App\Livewire\Winery\ProductionCosts\Edit::class)->name('edit');
        });

        // ── Configuración ─────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Winery\Settings::class)->name('settings');
    });
