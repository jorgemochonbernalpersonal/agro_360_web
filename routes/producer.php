<?php

use App\Livewire\Viticulturist\Campaign\Create as CampaignCreate;
use App\Livewire\Viticulturist\Campaign\Edit as CampaignEdit;
use App\Livewire\Viticulturist\Campaign\Index as CampaignIndex;
use App\Livewire\Viticulturist\Campaign\Show as CampaignShow;
use App\Livewire\Viticulturist\DigitalNotebook;
use App\Livewire\Viticulturist\DigitalNotebook\CreateCulturalWork;
use App\Livewire\Viticulturist\DigitalNotebook\CreateFertilization;
use App\Livewire\Viticulturist\DigitalNotebook\CreateHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\EditHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\ShowHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\CreateIrrigation;
use App\Livewire\Viticulturist\DigitalNotebook\CreateObservation;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePhytosanitaryTreatment;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePruning;
use App\Livewire\Viticulturist\DigitalNotebook\EditPruning;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePostHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\EditPostHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\TreatmentIndex;
use App\Livewire\Viticulturist\DigitalNotebook\FertilizationIndex;
use App\Livewire\Viticulturist\DigitalNotebook\IrrigationIndex;
use App\Livewire\Viticulturist\DigitalNotebook\CulturalWorkIndex;
use App\Livewire\Viticulturist\DigitalNotebook\ObservationIndex;
use App\Livewire\Viticulturist\DigitalNotebook\PruningIndex;
use App\Livewire\Viticulturist\DigitalNotebook\PostHarvestIndex;
use App\Livewire\Viticulturist\Machinery\Create as MachineryCreate;
use App\Livewire\Viticulturist\Machinery\Edit as MachineryEdit;
use App\Livewire\Viticulturist\Machinery\Index as MachineryIndex;
use App\Livewire\Viticulturist\Machinery\Show as MachineryShow;
use App\Livewire\Viticulturist\PhytosanitaryProducts\Index as PhytosanitaryProductsIndex;
use App\Livewire\Viticulturist\PhytosanitaryProducts\Create as PhytosanitaryProductsCreate;
use App\Livewire\Viticulturist\PhytosanitaryProducts\Edit as PhytosanitaryProductsEdit;
use App\Livewire\Viticulturist\Personal\Create as PersonalCreate;
use App\Livewire\Viticulturist\Personal\Edit as PersonalEdit;
use App\Livewire\Viticulturist\Personal\UnifiedIndex as PersonalUnifiedIndex;
use App\Livewire\Viticulturist\Personal\Show as PersonalShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:producer', 'check.beta'])
    ->prefix('producer')
    ->name('producer.')
    ->group(function () {

        // Dashboard combinado
        Route::get('/dashboard', \App\Livewire\Producer\Dashboard::class)->name('dashboard');

        // ══════════════════════════════════════════════════════════════════
        // MÓDULOS UNIFICADOS — misma entidad de datos en bodega y viñedo
        // ══════════════════════════════════════════════════════════════════

        // ── Campañas (línea temporal única: vendimia + cuaderno de campo) ─
        Route::prefix('campaign')->name('campaign.')->group(function () {
            Route::get('/', CampaignIndex::class)->name('index');
            Route::get('/create', CampaignCreate::class)->name('create');
            Route::get('/{campaign}', CampaignShow::class)->name('show');
            Route::get('/{campaign}/edit', CampaignEdit::class)->name('edit');
        });

        // ── Plantaciones (versión viticulturist — más completa) ───────────
        Route::prefix('plots/{plot}/plantings')->name('plots.plantings.')->group(function () {
            Route::get('/', \App\Livewire\Plots\Plantings\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Plots\Plantings\Create::class)->name('create');
            Route::get('/{planting}/edit', \App\Livewire\Plots\Plantings\Edit::class)->name('edit');
        });

        // ── SIGPAC ────────────────────────────────────────────────────────
        Route::get('/sigpac', fn() => redirect()->route('sigpac.codes'))->name('sigpac.index');

        // ── Gestión Territorial ───────────────────────────────────────────
        Route::get('/territory', fn() => redirect()->route('plots.territory'))->name('territory');

        // ── Teledetección ─────────────────────────────────────────────────
        Route::get('/remote-sensing', fn() => redirect()->route('remote-sensing.dashboard'))->name('remote-sensing');

        // ── Contenedores (unificado bodega + campo) ───────────────────────
        Route::prefix('containers')->name('containers.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Cellar\Containers\Index::class)->name('index');
            Route::get('/analytics', \App\Livewire\Winery\Cellar\Containers\Analytics::class)->name('analytics');
            Route::get('/map', \App\Livewire\Winery\Cellar\Containers\Map::class)->name('map');
            Route::get('/create', \App\Livewire\Winery\Cellar\Containers\Create::class)->name('create');
            Route::get('/{container}', \App\Livewire\Winery\Cellar\Containers\Show::class)->name('show');
            Route::get('/{container}/edit', \App\Livewire\Winery\Cellar\Containers\Edit::class)->name('edit');
            Route::get('/{container}/maintenance', \App\Livewire\Winery\Cellar\Containers\Maintenance\Index::class)->name('maintenance.index');
            Route::get('/{container}/maintenance/create', \App\Livewire\Winery\Cellar\Containers\Maintenance\Create::class)->name('maintenance.create');
            Route::get('/{container}/maintenance/{maintenance}/edit', \App\Livewire\Winery\Cellar\Containers\Maintenance\Edit::class)->name('maintenance.edit');
            Route::get('/{container}/additives', \App\Livewire\Winery\Cellar\Containers\Additives\Index::class)->name('additives.index');
            Route::get('/{container}/additives/create', \App\Livewire\Winery\Cellar\Containers\Additives\Create::class)->name('additives.create');
            Route::get('/{container}/additives/{additive}/edit', \App\Livewire\Winery\Cellar\Containers\Additives\Edit::class)->name('additives.edit');
        });

        // ── Clientes viticultor (uva) ─────────────────────────────────────
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', \App\Livewire\Clients\Index::class)->name('index');
            Route::get('/insights', \App\Livewire\Clients\Insights::class)->name('insights');
            Route::get('/create', \App\Livewire\Clients\Create::class)->name('create');
            Route::get('/{client}', \App\Livewire\Clients\Show::class)->name('show');
            Route::get('/{client}/edit', \App\Livewire\Clients\Edit::class)->name('edit');
        });

        // ── Clientes bodega (vino) ────────────────────────────────────────
        Route::prefix('winery-clients')->name('winery-clients.')->group(function () {
            Route::get('/', \App\Livewire\Clients\Index::class)->name('index');
            Route::get('/insights', \App\Livewire\Clients\Insights::class)->name('insights');
            Route::get('/create', \App\Livewire\Clients\Create::class)->name('create');
            Route::get('/{client}', \App\Livewire\Clients\Show::class)->name('show');
            Route::get('/{client}/edit', \App\Livewire\Clients\Edit::class)->name('edit');
        });

        // ── Facturas — sub-rutas específicas ANTES del wildcard {invoice} ─

        // Facturas mixtas (cosechas + vinos en el mismo albarán)
        Route::prefix('invoices/mixed')->name('invoices.mixed.')->group(function () {
            Route::get('/', \App\Livewire\Producer\Invoices\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Producer\Invoices\Create::class)->name('create');
            Route::get('/{invoice}/edit', \App\Livewire\Producer\Invoices\Edit::class)->name('edit');
        });

        Route::prefix('invoices/products')->name('invoices.products.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Billing\ProductSale\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Billing\ProductSale\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\Winery\Billing\ProductSale\Edit::class)->name('edit');
            Route::get('/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->name('pdf');
            Route::get('/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->name('delivery-note-pdf');
            Route::get('/{id}/albaran-valorado-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'valoradoNote'])->name('valorado-pdf');
        });

        Route::prefix('invoices/grape-purchase')->name('invoices.grape-purchase.')
            ->middleware(\App\Http\Middleware\EnsureProducerBuysExternalGrape::class)
            ->group(function () {
                Route::get('/', \App\Livewire\Winery\Billing\GrapePurchase\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Winery\Billing\GrapePurchase\Create::class)->name('create');
                Route::get('/{id}/edit', \App\Livewire\Winery\Billing\GrapePurchase\Edit::class)->name('edit');
                Route::get('/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->name('pdf');
            });

        // ── Facturas viticultor (wildcard al final) ────────────────────────
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Invoices\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Invoices\Create::class)->name('create');
            Route::get('/{invoice}/edit', \App\Livewire\Viticulturist\Invoices\Edit::class)->name('edit');
            Route::get('/{invoice}/pdf', [\App\Http\Controllers\Viticulturist\InvoicePdfController::class, 'invoice'])->name('pdf');
            Route::get('/{invoice}/albaran-pdf', [\App\Http\Controllers\Viticulturist\InvoicePdfController::class, 'deliveryNote'])->name('delivery-note-pdf');
            Route::get('/{invoice}', \App\Livewire\Viticulturist\Invoices\Show::class)->name('show');
        });

        // ── VeriFactu ─────────────────────────────────────────────────────
        Route::get('/verifactu', \App\Livewire\Winery\Verifactu\Dashboard::class)->name('verifactu.index');

        // ── Estadísticas Financieras ──────────────────────────────────────
        Route::get('/financial-stats', \App\Livewire\Viticulturist\FinancialStats::class)->name('financial-stats.index');
        Route::get('/financial-stats-winery', \App\Livewire\Winery\Financial\Stats::class)->name('financial-stats-winery');

        // ── Configuración ─────────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Producer\Settings::class)->name('settings');


        // ══════════════════════════════════════════════════════════════════
        // MÓDULOS PROPIOS DE BODEGA
        // ══════════════════════════════════════════════════════════════════

        // ── Cuadro de mando vendimia ──────────────────────────────────────
        Route::get('/harvest-summary', \App\Livewire\Winery\Harvest\Summary\Index::class)->name('harvest-summary.index');

        // ── Previsiones de vendimia ───────────────────────────────────────
        Route::prefix('harvest-forecasts')->name('harvest-forecasts.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Harvest\Forecasts\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Harvest\Forecasts\Create::class)->name('create');
            Route::get('/{forecast}/edit', \App\Livewire\Winery\Harvest\Forecasts\Edit::class)->name('edit');
        });

        // ── Recepciones de uva ────────────────────────────────────────────
        Route::prefix('grape-reception')->name('grape-reception.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Harvest\Reception\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Harvest\Reception\Create::class)->name('create');
            Route::get('/export/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export/excel', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportExcel'])->name('export-excel');
            // disputes antes del wildcard {harvest}
            Route::get('/disputes', \App\Livewire\Winery\Harvest\Reception\Disputes::class)
                ->middleware(\App\Http\Middleware\EnsureProducerBuysExternalGrape::class)
                ->name('disputes');
            Route::get('/{harvest}', \App\Livewire\Winery\Harvest\Reception\Show::class)->name('show');
            Route::get('/{harvest}/edit', \App\Livewire\Winery\Harvest\Reception\Edit::class)->name('edit');
            Route::get('/{harvest}/assign', \App\Livewire\Winery\Harvest\Reception\Assign::class)->name('assign');
            Route::get('/{harvest}/pdf', [\App\Http\Controllers\Winery\HarvestReceptionController::class, 'exportPdfSingle'])->name('export-pdf-single');
        });

        // ── Análisis de calidad vendimia ──────────────────────────────────
        Route::get('/harvest-quality', \App\Livewire\Winery\Harvest\QualityAnalysis\Index::class)->name('harvest-quality.index');
        Route::get('/harvest-quality/export/pdf', [\App\Http\Controllers\Winery\HarvestQualityController::class, 'exportPdf'])->name('harvest-quality.export-pdf');

        // ── Vinos ─────────────────────────────────────────────────────────
        Route::prefix('wines')->name('wines.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Wines\Index::class)->name('index');
            Route::get('/timeline', \App\Livewire\Winery\Wines\Timeline::class)->name('timeline');
            Route::get('/create', \App\Livewire\Winery\Wines\Create::class)->name('create');
            Route::get('/{wine}', \App\Livewire\Winery\Wines\Show::class)->name('show');
            Route::get('/{wine}/edit', \App\Livewire\Winery\Wines\Edit::class)->name('edit');
            Route::get('/{wine}/process/create', \App\Livewire\Winery\Wines\Process\Create::class)->name('process.create');
            Route::get('/{wine}/process/{process}/edit', \App\Livewire\Winery\Wines\Process\Edit::class)->name('process.edit');
            Route::get('/{wine}/traceability-pdf', [\App\Http\Controllers\Winery\WineTraceabilityController::class, 'exportPdf'])->name('traceability-pdf');
        });

        // ── Enólogos ──────────────────────────────────────────────────────
        Route::prefix('oenologists')->name('oenologists.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Oenologists\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Oenologists\Create::class)->name('create');
            Route::get('/{oenologist}/edit', \App\Livewire\Winery\Oenologists\Edit::class)->name('edit');
        });

        // ── Análisis de laboratorio ───────────────────────────────────────
        Route::prefix('wine-analysis')->name('wine-analysis.')->group(function () {
            Route::get('/', \App\Livewire\Winery\WineAnalysis\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\WineAnalysis\Create::class)->name('create');
            Route::get('/{analysis}/edit', \App\Livewire\Winery\WineAnalysis\Edit::class)->name('edit');
        });

        // ── Controles de Fermentación ─────────────────────────────────────
        Route::prefix('fermentation-controls')->name('fermentation-controls.')->group(function () {
            Route::get('/', \App\Livewire\Winery\FermentationControls\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\FermentationControls\Create::class)->name('create');
            Route::get('/{control}/edit', \App\Livewire\Winery\FermentationControls\Edit::class)->name('edit');
        });

        // ── Trasvases y Coupage ───────────────────────────────────────────
        Route::prefix('wine-transfers')->name('wine-transfers.')->group(function () {
            Route::get('/', \App\Livewire\Winery\WineTransfers\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\WineTransfers\Create::class)->name('create');
            Route::get('/{transfer}/edit', \App\Livewire\Winery\WineTransfers\Edit::class)->name('edit');
        });

        // ── Mermas y Pérdidas ─────────────────────────────────────────────
        Route::prefix('wine-losses')->name('wine-losses.')->group(function () {
            Route::get('/', \App\Livewire\Winery\WineLosses\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\WineLosses\Create::class)->name('create');
            Route::get('/{loss}/edit', \App\Livewire\Winery\WineLosses\Edit::class)->name('edit');
        });

        // ── Lotes de producto ─────────────────────────────────────────────
        Route::prefix('product-lots')->name('product-lots.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Cellar\ProductLots\Index::class)->name('index');
            Route::get('/insights', \App\Livewire\Winery\Cellar\ProductLots\Insights::class)->name('insights');
            Route::get('/audit', \App\Livewire\Winery\Cellar\ProductLots\Audit::class)->name('audit');
            Route::get('/create', \App\Livewire\Winery\Cellar\ProductLots\Create::class)->name('create');
            Route::get('/{lot}/sales', \App\Livewire\Winery\Cellar\ProductLots\Sales::class)->name('sales');
            Route::get('/{lot}/edit', \App\Livewire\Winery\Cellar\ProductLots\Edit::class)->name('edit');
        });

        // ── Trazabilidad ──────────────────────────────────────────────────
        Route::get('/traceability', \App\Livewire\Winery\Traceability\Index::class)
            ->name('traceability.index');

        // ── Embotellado ───────────────────────────────────────────────────
        Route::get('/bottling', \App\Livewire\Winery\Bottling\Index::class)->name('bottling.index');
        Route::get('/bottling/create', \App\Livewire\Winery\Bottling\Create::class)->name('bottling.create');
        Route::get('/bottling/{bottling}/edit', \App\Livewire\Winery\Bottling\Edit::class)->name('bottling.edit');

        // ── Lotes de etiquetas ────────────────────────────────────────────
        Route::get('/label-batches', \App\Livewire\Winery\LabelBatches\Index::class)->name('label-batches.index');
        Route::get('/label-batches/create', \App\Livewire\Winery\LabelBatches\Create::class)->name('label-batches.create');
        Route::get('/label-batches/{labelBatch}/edit', \App\Livewire\Winery\LabelBatches\Edit::class)->name('label-batches.edit');
        Route::get('/label-batches/{labelBatch}/waste', \App\Livewire\Winery\LabelBatches\Waste\Index::class)->name('label-batches.waste.index');
        Route::get('/label-batches/{labelBatch}/waste/create', \App\Livewire\Winery\LabelBatches\Waste\Create::class)->name('label-batches.waste.create');

        // ── Etiquetado ────────────────────────────────────────────────────
        Route::get('/labeling', \App\Livewire\Winery\Labeling\Index::class)->name('labeling.index');
        Route::get('/labeling/create', \App\Livewire\Winery\Labeling\Create::class)->name('labeling.create');
        Route::get('/labeling/{labeling}/edit', \App\Livewire\Winery\Labeling\Edit::class)->name('labeling.edit');

        // ── Fichas Técnicas y Catas ───────────────────────────────────────
        Route::get('/product-sheets', \App\Livewire\Winery\ProductSheets\Index::class)->name('product-sheets.index');
        Route::get('/tasting-notes', \App\Livewire\Winery\TastingNotes\Index::class)->name('tasting-notes.index');
        Route::get('/tasting-notes/create', \App\Livewire\Winery\TastingNotes\Create::class)->name('tasting-notes.create');
        Route::get('/tasting-notes/{tastingNote}/edit', \App\Livewire\Winery\TastingNotes\Edit::class)->name('tasting-notes.edit');

        // ── Subproductos ──────────────────────────────────────────────────
        Route::get('/subproducts', \App\Livewire\Winery\Subproducts\Index::class)->name('subproducts.index');
        Route::get('/subproducts/create', \App\Livewire\Winery\Subproducts\Create::class)->name('subproducts.create');
        Route::get('/subproducts/{subproduct}/edit', \App\Livewire\Winery\Subproducts\Edit::class)->name('subproducts.edit');

        // ── Uva / Mosto externo ───────────────────────────────────────────
        Route::prefix('external-grape')->name('external-grape.')->group(function () {
            Route::get('/', \App\Livewire\Winery\ExternalGrape\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\ExternalGrape\Create::class)->name('create');
            Route::get('/{externalGrape}/edit', \App\Livewire\Winery\ExternalGrape\Edit::class)->name('edit');
        });

        // ── Salas de bodega ───────────────────────────────────────────────
        Route::prefix('container-rooms')->name('container-rooms.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Cellar\ContainerRooms\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Cellar\ContainerRooms\Create::class)->name('create');
            Route::get('/{room}/edit', \App\Livewire\Winery\Cellar\ContainerRooms\Edit::class)->name('edit');
        });

        // ── Operaciones de bodega ─────────────────────────────────────────
        Route::prefix('cellar-operations')->name('cellar-operations.')->group(function () {
            Route::get('/', \App\Livewire\Winery\CellarOperations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\CellarOperations\Create::class)->name('create');
            Route::get('/{operation}/edit', \App\Livewire\Winery\CellarOperations\Edit::class)->name('edit');
        });

        // ── Resumen Económico ─────────────────────────────────────────────
        Route::get('/financial-summary', \App\Livewire\Winery\Financial\Summary::class)->name('financial-summary.index');

        // ── Documentos Bodega ─────────────────────────────────────────────
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Documents\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Documents\Create::class)->name('create');
            Route::get('/{wineryDocument}/edit', \App\Livewire\Winery\Documents\Edit::class)->name('edit');
        });

        // ── Centro de Alertas ─────────────────────────────────────────────
        Route::get('/alerts', \App\Livewire\Winery\Alerts\Dashboard::class)->name('alerts.index');

        // ── Meteorología (en construcción) ────────────────────────────────
        Route::get('/meteorology', \App\Livewire\Winery\UnderConstruction::class)
            ->name('meteorology.index')
            ->defaults('module', 'Meteorología')
            ->defaults('icon', 'cloud');

        // ── Normativa bodega ──────────────────────────────────────────────
        Route::get('/silicie', \App\Livewire\Winery\UnderConstruction::class)
            ->name('silicie.dashboard')
            ->defaults('module', 'SILICIE')
            ->defaults('icon', 'document-chart-bar');

        Route::get('/silicie/infovi', \App\Livewire\Winery\UnderConstruction::class)
            ->name('silicie.infovi')
            ->defaults('module', 'INFOVI (AICA)')
            ->defaults('icon', 'chart-bar');

        Route::get('/aica', \App\Livewire\Winery\UnderConstruction::class)
            ->name('aica.index')
            ->defaults('module', 'AICA')
            ->defaults('icon', 'document-text');

        Route::prefix('sanitary-registrations')->name('sanitary-registrations.')->group(function () {
            Route::get('/', \App\Livewire\Winery\SanitaryRegistrations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\SanitaryRegistrations\Create::class)->name('create');
            Route::get('/{sanitaryRegistration}/edit', \App\Livewire\Winery\SanitaryRegistrations\Edit::class)->name('edit');
        });

        Route::prefix('bottling-authorizations')->name('bottling-authorizations.')->group(function () {
            Route::get('/', \App\Livewire\Winery\BottlingAuthorizations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\BottlingAuthorizations\Create::class)->name('create');
            Route::get('/{bottlingAuthorization}/edit', \App\Livewire\Winery\BottlingAuthorizations\Edit::class)->name('edit');
        });

        Route::prefix('eco-certifications')->name('eco-certifications.')->group(function () {
            Route::get('/', \App\Livewire\Winery\EcoCertifications\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\EcoCertifications\Create::class)->name('create');
            Route::get('/{ecoCertification}/edit', \App\Livewire\Winery\EcoCertifications\Edit::class)->name('edit');
        });

        // ── Negocio bodega ────────────────────────────────────────────────

        Route::get('/exports', \App\Livewire\Winery\UnderConstruction::class)
            ->name('exports.index')
            ->defaults('module', 'Exportación')
            ->defaults('icon', 'globe-alt');

        Route::get('/enotourism', \App\Livewire\Winery\UnderConstruction::class)
            ->name('enotourism.index')
            ->defaults('module', 'Enoturismo')
            ->defaults('icon', 'sparkles');

        // ── Sistema bodega ────────────────────────────────────────────────
        Route::prefix('winery-supplies')->name('winery-supplies.')->group(function () {
            Route::get('/', \App\Livewire\Winery\WinerySupplies\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\WinerySupplies\Create::class)->name('create');
            Route::get('/{winerySupply}/edit', \App\Livewire\Winery\WinerySupplies\Edit::class)->name('edit');
        });

        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Suppliers\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Suppliers\Create::class)->name('create');
            Route::get('/{supplier}/edit', \App\Livewire\Winery\Suppliers\Edit::class)->name('edit');
        });

        // ── Módulos condicionales (compra_uva_externa = true) ─────────────
        Route::middleware(\App\Http\Middleware\EnsureProducerBuysExternalGrape::class)->group(function () {
            Route::get('/viticulturists', \App\Livewire\Winery\Viticulturists\Index::class)->name('viticulturists.index');
            Route::get('/viticulturists/create', \App\Livewire\Winery\Viticulturists\Create::class)->name('viticulturists.create');
            Route::get('/viticulturists/invite', \App\Livewire\Winery\Viticulturists\Invite::class)->name('viticulturists.invite');
            Route::get('/viticulturists/{viticulturist}', \App\Livewire\Winery\Viticulturists\Show::class)->name('viticulturists.show');
            Route::get('/viticulturists/{viticulturist}/edit', \App\Livewire\Winery\Viticulturists\Edit::class)->name('viticulturists.edit');

            Route::get('/vitic-estimates', \App\Livewire\Winery\Harvest\ViticulturistEstimates\Index::class)->name('vitic-estimates.index');

            Route::get('/announcements', \App\Livewire\Winery\Announcements\Index::class)->name('announcements.index');
        });


        // ══════════════════════════════════════════════════════════════════
        // MÓDULOS PROPIOS DE VITICULTOR
        // ══════════════════════════════════════════════════════════════════

        // ── Documentos de Campaña ─────────────────────────────────────────
        Route::prefix('campaign-documents')->name('campaign-documents.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\CampaignDocuments\Index::class)->name('index');
            Route::get('/{document}/download', function (\App\Models\CampaignDocument $document) {
                if ($document->viticulturist_id !== auth()->id()) abort(403);
                if (!$document->file_path || !\Storage::disk('private')->exists($document->file_path)) abort(404);
                return \Storage::disk('private')->download(
                    $document->file_path,
                    $document->original_filename ?? basename($document->file_path)
                );
            })->name('download');
        });

        // ── Firma y Cierre de Campaña ─────────────────────────────────────
        Route::get('/campaign-sign', \App\Livewire\Viticulturist\CampaignSign\Index::class)->name('campaign-sign.index');

        // ── Rendimientos Estimados ────────────────────────────────────────
        Route::prefix('digital-notebook/estimated-yields')->name('digital-notebook.estimated-yields.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Create::class)->name('create');
            Route::get('/{estimatedYield}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Edit::class)->name('edit');
        });

        // ── Cuaderno de Campo ─────────────────────────────────────────────
        Route::get('/digital-notebook', DigitalNotebook::class)->name('digital-notebook');
        Route::prefix('digital-notebook')->name('digital-notebook.')->group(function () {
            Route::get('/treatment', TreatmentIndex::class)->name('treatment.index');
            Route::get('/treatment/create', CreatePhytosanitaryTreatment::class)->name('treatment.create');
            Route::get('/treatment/{activity}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EditPhytosanitaryTreatment::class)->name('treatment.edit');

            Route::get('/fertilization', FertilizationIndex::class)->name('fertilization.index');
            Route::get('/fertilization/create', CreateFertilization::class)->name('fertilization.create');
            Route::get('/fertilization/{activity}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EditFertilization::class)->name('fertilization.edit');

            Route::get('/irrigation', IrrigationIndex::class)->name('irrigation.index');
            Route::get('/irrigation/create', CreateIrrigation::class)->name('irrigation.create');
            Route::get('/irrigation/{activity}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EditIrrigation::class)->name('irrigation.edit');

            Route::get('/cultural', CulturalWorkIndex::class)->name('cultural.index');
            Route::get('/cultural/create', CreateCulturalWork::class)->name('cultural.create');
            Route::get('/cultural/{activity}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EditCulturalWork::class)->name('cultural.edit');

            Route::get('/observation', ObservationIndex::class)->name('observation.index');
            Route::get('/observation/create', CreateObservation::class)->name('observation.create');
            Route::get('/observation/{activity}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EditObservation::class)->name('observation.edit');

            Route::get('/pruning', PruningIndex::class)->name('pruning.index');
            Route::get('/pruning/create', CreatePruning::class)->name('pruning.create');
            Route::get('/pruning/{activity}/edit', EditPruning::class)->name('pruning.edit');

            Route::get('/post-harvest', PostHarvestIndex::class)->name('post-harvest.index');
            Route::get('/post-harvest/create', CreatePostHarvest::class)->name('post-harvest.create');
            Route::get('/post-harvest/{activity}/edit', EditPostHarvest::class)->name('post-harvest.edit');

            Route::get('/harvest/create', CreateHarvest::class)->name('harvest.create');
            Route::get('/harvest/{harvest}', ShowHarvest::class)->name('harvest.show');
            Route::get('/harvest/{harvest}/edit', EditHarvest::class)->name('harvest.edit');
            Route::get('/harvest/{harvest}/promote', \App\Livewire\Producer\Harvest\PromoteToReception::class)->name('harvest.promote');
        });

        // ── Fenología ─────────────────────────────────────────────────────
        Route::prefix('phenology')->name('phenology.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Phenology\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Phenology\Create::class)->name('create');
            Route::get('/{observation}/edit', \App\Livewire\Viticulturist\Phenology\Edit::class)->name('edit');
        });

        // ── Vendimia campo (entregas a bodega) ────────────────────────────
        Route::prefix('harvests')->name('harvests.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Harvests\Index::class)->name('index');
            Route::get('/export/pdf', [\App\Http\Controllers\Viticulturist\HarvestsPdfController::class, 'export'])->name('export-pdf');
            Route::get('/planting/{planting}', \App\Livewire\Viticulturist\Harvests\Show::class)->name('show');
            Route::get('/create-delivery', \App\Livewire\Viticulturist\Harvests\CreateDelivery::class)->name('delivery.create');
            Route::get('/{delivery}/edit-delivery', \App\Livewire\Viticulturist\Harvests\EditDelivery::class)->name('delivery.edit');
            Route::get('/{delivery}/albaran', \App\Http\Controllers\Viticulturist\HarvestDeliveryAlbaranController::class)->name('delivery.albaran');
        });

        // ── Gestión de Plagas ─────────────────────────────────────────────
        Route::prefix('pest-management')->name('pest-management.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\PestManagement\Index::class)->name('index');
            Route::get('/{pest}', \App\Livewire\Viticulturist\PestManagement\Show::class)->name('show');
        });

        // ── Registros Oficiales ───────────────────────────────────────────
        Route::get('/pac-compliance', \App\Livewire\Viticulturist\PacComplianceDashboard::class)->name('pac-compliance');

        Route::prefix('residue-analyses')->name('residue-analyses.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\ResidueAnalyses\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\ResidueAnalyses\Create::class)->name('create');
            Route::get('/{residueAnalysis}/edit', \App\Livewire\Viticulturist\ResidueAnalyses\Edit::class)->name('edit');
        });

        Route::prefix('residue-managements')->name('residue-managements.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\ResidueManagements\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\ResidueManagements\Create::class)->name('create');
            Route::get('/{residueManagement}/edit', \App\Livewire\Viticulturist\ResidueManagements\Edit::class)->name('edit');
        });

        Route::prefix('energy-usages')->name('energy-usages.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\EnergyUsages\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\EnergyUsages\Create::class)->name('create');
            Route::get('/{energyUsage}/edit', \App\Livewire\Viticulturist\EnergyUsages\Edit::class)->name('edit');
        });

        Route::prefix('cue-exports')->name('cue-exports.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\CueExports\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\CueExports\Create::class)->name('create');
            Route::get('/{cueExport}/edit', \App\Livewire\Viticulturist\CueExports\Edit::class)->name('edit');
        });

        // Gestión de Envases Fitosanitarios (SIGFITO / FIELD)
        Route::prefix('container-returns')->name('container-returns.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\ContainerReturns\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\ContainerReturns\Create::class)->name('create');
            Route::get('/{containerReturn}/edit', \App\Livewire\Viticulturist\ContainerReturns\Edit::class)->name('edit');
        });

        // Declaraciones de Vendimia
        Route::prefix('harvest-declarations')->name('harvest-declarations.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\HarvestDeclarations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\HarvestDeclarations\Create::class)->name('create');
            Route::get('/{harvestDeclaration}/edit', \App\Livewire\Viticulturist\HarvestDeclarations\Edit::class)->name('edit');
        });

        // Subproductos de Vendimia
        Route::prefix('harvest-byproducts')->name('harvest-byproducts.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\HarvestByproducts\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\HarvestByproducts\Create::class)->name('create');
            Route::get('/{byproduct}/edit', \App\Livewire\Viticulturist\HarvestByproducts\Edit::class)->name('edit');
        });

        // Registro de Agua / Concesiones de Riego
        Route::prefix('water-concessions')->name('water-concessions.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\WaterConcessions\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\WaterConcessions\Create::class)->name('create');
            Route::get('/{concession}/edit', \App\Livewire\Viticulturist\WaterConcessions\Edit::class)->name('edit');
        });

        // Plan de Fertilización
        Route::prefix('fertilization-plans')->name('fertilization-plans.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\FertilizationPlans\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\FertilizationPlans\Create::class)->name('create');
            Route::get('/{plan}/edit', \App\Livewire\Viticulturist\FertilizationPlans\Edit::class)->name('edit');
        });

        // Certificaciones y Sellos
        Route::prefix('certifications')->name('certifications.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Certifications\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Certifications\Create::class)->name('create');
            Route::get('/{certification}/edit', \App\Livewire\Viticulturist\Certifications\Edit::class)->name('edit');
        });

        Route::get('/official-reports', \App\Livewire\Viticulturist\OfficialReports\Index::class)->name('official-reports.index');
        Route::get('/official-reports/create', \App\Livewire\Viticulturist\OfficialReports\Create::class)->name('official-reports.create');
        Route::get('/official-reports/{report}/download', function (\App\Models\OfficialReport $report) {
            if ($report->user_id !== auth()->id()) abort(403);
            return (new \App\Services\OfficialReportService())->downloadReport($report);
        })->name('official-reports.download');
        Route::get('/official-reports/{report}/preview', function (\App\Models\OfficialReport $report) {
            if ($report->user_id !== auth()->id()) abort(403);
            if (!$report->pdfExists()) abort(404);
            $pdfPath = str_starts_with($report->pdf_path, storage_path())
                ? $report->pdf_path
                : \Storage::disk('local')->path($report->pdf_path);
            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($report->pdf_filename ?? 'informe.pdf') . '"',
            ]);
        })->name('official-reports.preview');

        // ── Parcelas y territorio ─────────────────────────────────────────
        Route::prefix('plots')->name('plots.')->group(function () {
            Route::get('/', \App\Livewire\Plots\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Plots\Create::class)->name('create');
            Route::get('/{plot}', \App\Livewire\Plots\Show::class)->name('show');
            Route::get('/{plot}/edit', \App\Livewire\Plots\Edit::class)->name('edit');
        });

        Route::prefix('plot-environments')->name('plot-environments.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\PlotEnvironments\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\PlotEnvironments\Create::class)->name('create');
            Route::get('/{plotEnvironment}/edit', \App\Livewire\Viticulturist\PlotEnvironments\Edit::class)->name('edit');
        });

        Route::get('/field-activities', \App\Livewire\Winery\FieldActivities\Index::class)->name('field-activities.index');

        // ── Recursos ──────────────────────────────────────────────────────
        Route::prefix('personal')->name('personal.')->group(function () {
            Route::get('/', PersonalUnifiedIndex::class)->name('index');
            Route::get('/create', PersonalCreate::class)->name('create');
            Route::get('/{crew}', PersonalShow::class)->name('show');
            Route::get('/{crew}/edit', PersonalEdit::class)->name('edit');
        });

        Route::prefix('machinery')->name('machinery.')->group(function () {
            Route::get('/', MachineryIndex::class)->name('index');
            Route::get('/create', MachineryCreate::class)->name('create');
            Route::get('/{machinery}', MachineryShow::class)->name('show');
            Route::get('/{machinery}/edit', MachineryEdit::class)->name('edit');
        });

        Route::prefix('warehouse')->name('warehouse.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Warehouse\Index::class)->name('index');
            Route::get('/stock/analytics', \App\Livewire\Viticulturist\Inventory\Analytics::class)->name('stock.analytics');
            Route::get('/stock/create', \App\Livewire\Viticulturist\Inventory\CreateStock::class)->name('stock.create');
            Route::get('/stock/{stock}/edit', \App\Livewire\Viticulturist\Inventory\EditStock::class)->name('stock.edit');
            Route::get('/stock/{stock}/consume', \App\Livewire\Viticulturist\Inventory\ConsumeStock::class)->name('stock.consume');
            Route::get('/stock/{stock}/movements', \App\Livewire\Viticulturist\Inventory\Movements::class)->name('stock.movements');
            Route::get('/supplies/create', \App\Livewire\Viticulturist\Supplies\Create::class)->name('supplies.create');
            Route::get('/supplies/{supply}/edit', \App\Livewire\Viticulturist\Supplies\Edit::class)->name('supplies.edit');
            Route::get('/warehouses/create', \App\Livewire\Viticulturist\Warehouses\Create::class)->name('warehouses.create');
            Route::get('/warehouses/{warehouse}/edit', \App\Livewire\Viticulturist\Warehouses\Edit::class)->name('warehouses.edit');
        });

        Route::prefix('phytosanitary-products')->name('phytosanitary-products.')->group(function () {
            Route::get('/', PhytosanitaryProductsIndex::class)->name('index');
            Route::get('/create', PhytosanitaryProductsCreate::class)->name('create');
            Route::get('/{product}/edit', PhytosanitaryProductsEdit::class)->name('edit');
        });

        Route::prefix('subcontracting')->name('subcontracting.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Subcontracting\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Subcontracting\Create::class)->name('create');
            Route::get('/{record}/edit', \App\Livewire\Viticulturist\Subcontracting\Edit::class)->name('edit');
        });

        // ── Normativa viticultor ──────────────────────────────────────────
        Route::prefix('exploitations')->name('exploitations.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Exploitations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Exploitations\Create::class)->name('create');
            Route::get('/{exploitation}/edit', \App\Livewire\Viticulturist\Exploitations\Edit::class)->name('edit');
        });

        Route::prefix('commercial-authorizations')->name('commercial-authorizations.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\CommercialAuthorizations\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\CommercialAuthorizations\Create::class)->name('create');
            Route::get('/{commercialAuthorization}/edit', \App\Livewire\Viticulturist\CommercialAuthorizations\Edit::class)->name('edit');
        });

        Route::prefix('advisory-memberships')->name('advisory-memberships.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\AdvisoryMemberships\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\AdvisoryMemberships\Create::class)->name('create');
            Route::get('/{advisoryMembership}/edit', \App\Livewire\Viticulturist\AdvisoryMemberships\Edit::class)->name('edit');
        });

        Route::prefix('field-applicators')->name('field-applicators.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\FieldApplicators\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\FieldApplicators\Create::class)->name('create');
            Route::get('/{fieldApplicator}/edit', \App\Livewire\Viticulturist\FieldApplicators\Edit::class)->name('edit');
        });

        Route::prefix('field-equipment')->name('field-equipment.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\FieldEquipment\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\FieldEquipment\Create::class)->name('create');
            Route::get('/{fieldEquipment}/edit', \App\Livewire\Viticulturist\FieldEquipment\Edit::class)->name('edit');
        });

        Route::prefix('agri-insurance')->name('agri-insurance.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\AgriInsurance\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\AgriInsurance\Create::class)->name('create');
            Route::get('/{record}/edit', \App\Livewire\Viticulturist\AgriInsurance\Edit::class)->name('edit');
        });

        // ── PAC ───────────────────────────────────────────────────────────
        Route::prefix('pac')->name('pac.')->group(function () {
            Route::get('/dashboard', \App\Livewire\Viticulturist\Pac\Dashboard::class)->name('dashboard');
            Route::get('/superficies', \App\Livewire\Viticulturist\Pac\Surfaces\Index::class)->name('surfaces.index');
            Route::get('/eco-regimenes', \App\Livewire\Viticulturist\Pac\EcoSchemes\Index::class)->name('eco-schemes.index');
            Route::get('/ayudas', \App\Livewire\Viticulturist\Pac\Payments\Index::class)->name('payments.index');
            Route::prefix('declaraciones')->name('declarations.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Pac\Declarations\Index::class)->name('index');
                Route::get('/nueva', \App\Livewire\Viticulturist\Pac\Declarations\Create::class)->name('create');
                Route::get('/{declaration}/editar', \App\Livewire\Viticulturist\Pac\Declarations\Edit::class)->name('edit');
                Route::get('/{declaration}/pdf', [\App\Http\Controllers\Viticulturist\PacDeclarationPdfController::class, 'download'])->name('pdf');
                Route::get('/{declaration}', \App\Livewire\Viticulturist\Pac\Declarations\Show::class)->name('show');
            });
        });

        // ── Negocio viticultor ────────────────────────────────────────────
        Route::prefix('marketed-harvests')->name('marketed-harvests.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\MarketedHarvests\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\MarketedHarvests\Create::class)->name('create');
            Route::get('/{marketedHarvest}/edit', \App\Livewire\Viticulturist\MarketedHarvests\Edit::class)->name('edit');
        });

        Route::prefix('plot-costs')->name('plot-costs.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\PlotCosts\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\PlotCosts\Create::class)->name('create');
            Route::get('/{record}/edit', \App\Livewire\Viticulturist\PlotCosts\Edit::class)->name('edit');
        });

        // ── Soporte ───────────────────────────────────────────────────────
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Support\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Support\CreateTicket::class)->name('create');
        });

        // ══════════════════════════════════════════════════════════════════
        // RUTAS ADICIONALES — heredadas de viticulturist + winery
        // ══════════════════════════════════════════════════════════════════

        // ── Calendario de actividades (viticulturist) ────────────────────
        Route::get('/calendar', \App\Livewire\Viticulturist\Calendar::class)->name('calendar');

        // ── Entrada rápida de actividades (viticulturist) ────────────────
        Route::get('/quick-entry', \App\Livewire\Viticulturist\QuickEntry::class)->name('quick-entry');

        // ── Página completa de notificaciones (viticulturist) ────────────
        Route::get('/notifications', \App\Livewire\Viticulturist\Notifications\Index::class)->name('notifications.index');

        // ── Dashboard visual bodega (winery) ─────────────────────────────
        Route::get('/visual', \App\Livewire\Winery\VisualDashboard::class)->name('visual');

        // ── Campañas bodega (winery — CRUD completo) ─────────────────────
        Route::prefix('winery-campaigns')->name('winery-campaigns.')->group(function () {
            Route::get('/', \App\Livewire\Winery\Harvest\Campaigns\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Winery\Harvest\Campaigns\Create::class)->name('create');
            Route::get('/{campaign}/edit', \App\Livewire\Winery\Harvest\Campaigns\Edit::class)->name('edit');
        });

        // ── Inventario (redirect a winery-supplies) ──────────────────────
        Route::get('/inventory', fn() => redirect()->route('producer.winery-supplies.index'))->name('inventory.index');

    });
