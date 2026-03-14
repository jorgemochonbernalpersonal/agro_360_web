<?php

use Illuminate\Support\Facades\Route;

// Ruta helper: redirige al dashboard (usada por stubs mientras se implementa el módulo)
$stub = fn() => redirect()->route('winery.dashboard');

Route::middleware(['role:winery,producer'])
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
        Route::get('/grape-reception/disputes', \App\Livewire\Winery\Harvest\Reception\Disputes::class)->name('grape-reception.disputes');
        Route::get('/grape-reception/export/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdf'])->name('grape-reception.export-pdf');
        Route::get('/grape-reception/export/excel', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportExcel'])->name('grape-reception.export-excel');
        Route::get('/grape-reception/{harvest}', \App\Livewire\Winery\Harvest\Reception\Show::class)->name('grape-reception.show');
        Route::get('/grape-reception/{harvest}/edit', \App\Livewire\Winery\Harvest\Reception\Edit::class)->name('grape-reception.edit');
        Route::get('/grape-reception/{harvest}/assign', \App\Livewire\Winery\Harvest\Reception\Assign::class)->name('grape-reception.assign');
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
        Route::get('/containers/{container}/additives/{additive}/edit', \App\Livewire\Winery\Cellar\Containers\Additives\Edit::class)->name('containers.additives.edit');

        // ── Salas de bodega ───────────────────────────────────────────
        Route::get('/container-rooms', \App\Livewire\Winery\Cellar\ContainerRooms\Index::class)->name('container-rooms.index');
        Route::get('/container-rooms/create', \App\Livewire\Winery\Cellar\ContainerRooms\Create::class)->name('container-rooms.create');
        Route::get('/container-rooms/{room}/edit', \App\Livewire\Winery\Cellar\ContainerRooms\Edit::class)->name('container-rooms.edit');

        // ── Insumos de bodega ─────────────────────────────────────────
        Route::get('/winery-supplies', \App\Livewire\Winery\WinerySupplies\Index::class)->name('winery-supplies.index');
        Route::get('/winery-supplies/create', \App\Livewire\Winery\WinerySupplies\Create::class)->name('winery-supplies.create');
        Route::get('/winery-supplies/{winerySupply}/edit', \App\Livewire\Winery\WinerySupplies\Edit::class)->name('winery-supplies.edit');

        // ── Vinos ─────────────────────────────────────────────────────────────
        Route::get('/wines', \App\Livewire\Winery\Wines\Index::class)->name('wines.index');
        Route::get('/wines/create', \App\Livewire\Winery\Wines\Create::class)->name('wines.create');
        Route::get('/wines/{wine}', \App\Livewire\Winery\Wines\Show::class)->name('wines.show');
        Route::get('/wines/{wine}/edit', \App\Livewire\Winery\Wines\Edit::class)->name('wines.edit');
        Route::get('/wines/{wine}/process/create', \App\Livewire\Winery\Wines\Process\Create::class)->name('wines.process.create');
        Route::get('/wines/{wine}/process/{process}/edit', \App\Livewire\Winery\Wines\Process\Edit::class)->name('wines.process.edit');

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
        Route::get('/clients', \App\Livewire\Winery\Clients\Index::class)->name('clients.index');
        Route::get('/clients/insights', \App\Livewire\Winery\Clients\Insights::class)->name('clients.insights');
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

        // ── Resumen Económico (en construcción) ───────────────────────
        Route::get('/financial-summary', \App\Livewire\Winery\UnderConstruction::class)
            ->name('financial-summary.index')
            ->defaults('module', 'Resumen Económico')
            ->defaults('icon', 'chart-bar-square');

        // ── Estadísticas Financieras (en construcción) ────────────────
        Route::get('/financial-stats', \App\Livewire\Winery\UnderConstruction::class)
            ->name('financial-stats.index')
            ->defaults('module', 'Estadísticas Financieras')
            ->defaults('icon', 'presentation-chart-bar');

        // ── Embotellado ───────────────────────────────────────────────
        Route::get('/bottling', \App\Livewire\Winery\Bottling\Index::class)->name('bottling.index');
        Route::get('/bottling/create', \App\Livewire\Winery\Bottling\Create::class)->name('bottling.create');
        Route::get('/bottling/{bottling}/edit', \App\Livewire\Winery\Bottling\Edit::class)->name('bottling.edit');

        // ── Lotes de etiquetas ────────────────────────────────────────
        Route::get('/label-batches', \App\Livewire\Winery\LabelBatches\Index::class)->name('label-batches.index');
        Route::get('/label-batches/create', \App\Livewire\Winery\LabelBatches\Create::class)->name('label-batches.create');
        Route::get('/label-batches/{labelBatch}/edit', \App\Livewire\Winery\LabelBatches\Edit::class)->name('label-batches.edit');
        Route::get('/label-batches/{labelBatch}/waste', \App\Livewire\Winery\LabelBatches\Waste\Index::class)->name('label-batches.waste.index');
        Route::get('/label-batches/{labelBatch}/waste/create', \App\Livewire\Winery\LabelBatches\Waste\Create::class)->name('label-batches.waste.create');

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

        // ── Exportación (en construcción) ─────────────────────────────
        Route::get('/exports', \App\Livewire\Winery\UnderConstruction::class)
            ->name('exports.index')
            ->defaults('module', 'Exportación')
            ->defaults('icon', 'globe-alt');

        // ── Enoturismo (en construcción) ──────────────────────────────
        Route::get('/enotourism', \App\Livewire\Winery\UnderConstruction::class)
            ->name('enotourism.index')
            ->defaults('module', 'Enoturismo')
            ->defaults('icon', 'sparkles');

        // ── Trazabilidad (en construcción) ───────────────────────────
        Route::get('/traceability', \App\Livewire\Winery\UnderConstruction::class)
            ->name('traceability.index')
            ->defaults('module', 'Trazabilidad')
            ->defaults('icon', 'magnifying-glass-circle');

        // ── Operaciones de Bodega (en construcción) ───────────────────
        Route::get('/cellar-operations', \App\Livewire\Winery\UnderConstruction::class)
            ->name('cellar-operations.index')
            ->defaults('module', 'Operaciones de Bodega')
            ->defaults('icon', 'calendar-days');

        // ── Meteorología (en construcción) ────────────────────────────
        Route::get('/meteorology', \App\Livewire\Winery\UnderConstruction::class)
            ->name('meteorology.index')
            ->defaults('module', 'Meteorología')
            ->defaults('icon', 'cloud');

        // ── Centro de Alertas (en construcción) ───────────────────────
        Route::get('/alerts', \App\Livewire\Winery\UnderConstruction::class)
            ->name('alerts.index')
            ->defaults('module', 'Centro de Alertas')
            ->defaults('icon', 'bell-alert');

        // ── VeriFactu / facturación electrónica ───────────────────────
        Route::get('/verifactu', \App\Livewire\Winery\Verifactu\Dashboard::class)->name('verifactu.index');

        // ── Normativa bodega ──────────────────────────────────────────
        Route::get('/aica', \App\Livewire\Winery\UnderConstruction::class)
            ->name('aica.index')
            ->defaults('module', 'AICA')
            ->defaults('icon', 'document-text');

        Route::get('/sanitary-registrations', \App\Livewire\Winery\UnderConstruction::class)
            ->name('sanitary-registrations.index')
            ->defaults('module', 'Registros Sanitarios')
            ->defaults('icon', 'shield-check');

        Route::get('/bottling-authorizations', \App\Livewire\Winery\UnderConstruction::class)
            ->name('bottling-authorizations.index')
            ->defaults('module', 'Autorizaciones de Embotellado')
            ->defaults('icon', 'identification');

        Route::get('/eco-certifications', \App\Livewire\Winery\UnderConstruction::class)
            ->name('eco-certifications.index')
            ->defaults('module', 'Certificaciones Ecológicas')
            ->defaults('icon', 'sparkles');

        // ── Configuración ─────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Winery\Settings::class)->name('settings');
    });
