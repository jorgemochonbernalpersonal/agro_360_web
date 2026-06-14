<?php

use App\Livewire\Viticulturist\Calendar;
use App\Livewire\Viticulturist\Campaign\Create as CampaignCreate;
use App\Livewire\Viticulturist\Campaign\Edit as CampaignEdit;
use App\Livewire\Viticulturist\Campaign\Index as CampaignIndex;
use App\Livewire\Viticulturist\Campaign\Show as CampaignShow;
use App\Livewire\Viticulturist\DigitalNotebook;
use App\Livewire\Viticulturist\DigitalNotebook\CreateCulturalWork;
use App\Livewire\Viticulturist\DigitalNotebook\CreateFertilization;
use App\Livewire\Viticulturist\DigitalNotebook\CreateHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\CreateIrrigation;
use App\Livewire\Viticulturist\DigitalNotebook\CreateObservation;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePhytosanitaryTreatment;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePostHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePruning;
use App\Livewire\Viticulturist\DigitalNotebook\CulturalWorkIndex;
use App\Livewire\Viticulturist\DigitalNotebook\EditHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\EditPostHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\EditPruning;
use App\Livewire\Viticulturist\DigitalNotebook\FertilizationIndex;
use App\Livewire\Viticulturist\DigitalNotebook\IrrigationIndex;
use App\Livewire\Viticulturist\DigitalNotebook\ObservationIndex;
use App\Livewire\Viticulturist\DigitalNotebook\PostHarvestIndex;
use App\Livewire\Viticulturist\DigitalNotebook\PruningIndex;
use App\Livewire\Viticulturist\DigitalNotebook\ShowHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\TreatmentIndex;
use App\Livewire\Viticulturist\Machinery\Create as MachineryCreate;
use App\Livewire\Viticulturist\Machinery\Edit as MachineryEdit;
use App\Livewire\Viticulturist\Machinery\Index as MachineryIndex;
use App\Livewire\Viticulturist\Machinery\Show as MachineryShow;
use App\Livewire\Viticulturist\Personal\Create as PersonalCreate;
use App\Livewire\Viticulturist\Personal\Edit as PersonalEdit;
use App\Livewire\Viticulturist\Personal\Show as PersonalShow;
use App\Livewire\Viticulturist\Personal\UnifiedIndex as PersonalUnifiedIndex;
use App\Livewire\Viticulturist\PhytosanitaryProducts\Create as PhytosanitaryProductsCreate;
use App\Livewire\Viticulturist\PhytosanitaryProducts\Edit as PhytosanitaryProductsEdit;
use App\Livewire\Viticulturist\PhytosanitaryProducts\Index as PhytosanitaryProductsIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:viticulturist,producer', 'check.beta'])
    ->prefix('viticulturist')
    ->name('viticulturist.')
    ->group(function () {

        // ── PLAN BÁSICO (gratis para viticultores vinculados a bodega) ────────────
        Route::get('/dashboard', \App\Livewire\Viticulturist\Dashboard::class)->name('dashboard');

        Route::get('/quick-entry', \App\Livewire\Viticulturist\QuickEntry::class)->name('quick-entry');
        Route::get('/calendar', Calendar::class)->name('calendar');
        Route::get('/settings', \App\Livewire\Viticulturist\Settings::class)->name('settings');
        Route::get('/settings/taxes', function () {
            return redirect()->route('viticulturist.settings', ['tab' => 'taxes']);
        });
        Route::get('/settings/invoicing', function () {
            return redirect()->route('viticulturist.settings', ['tab' => 'invoicing']);
        });

        Route::get('/winery-access', \App\Livewire\Viticulturist\WineryAccess\Index::class)->name('winery-access.index');
        Route::get('/winery-requests', \App\Livewire\Viticulturist\WineryRequests\Index::class)->name('winery-requests.index');
        Route::get('/announcements', \App\Livewire\Viticulturist\Announcements\Index::class)->name('announcements');
        Route::get('/denomination', \App\Livewire\Viticulturist\Denomination\Index::class)->name('denomination.index')->middleware('require.supervisor');

        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Support\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Support\CreateTicket::class)->name('create');
        });

        Route::prefix('campaign')->name('campaign.')->group(function () {
            Route::get('/', CampaignIndex::class)->name('index');
            Route::get('/create', CampaignCreate::class)->name('create');
            Route::get('/{campaign}', CampaignShow::class)->name('show');
            Route::get('/{campaign}/edit', CampaignEdit::class)->name('edit');
        });

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
            Route::get('/harvest/create', CreateHarvest::class)->name('harvest.create');
            Route::get('/harvest/{harvest}', ShowHarvest::class)->name('harvest.show');
            Route::get('/harvest/{harvest}/edit', EditHarvest::class)->name('harvest.edit');
            Route::get('/pruning', PruningIndex::class)->name('pruning.index');
            Route::get('/pruning/create', CreatePruning::class)->name('pruning.create');
            Route::get('/pruning/{activity}/edit', EditPruning::class)->name('pruning.edit');
            Route::get('/post-harvest', PostHarvestIndex::class)->name('post-harvest.index');
            Route::get('/post-harvest/create', CreatePostHarvest::class)->name('post-harvest.create');
            Route::get('/post-harvest/{activity}/edit', EditPostHarvest::class)->name('post-harvest.edit');
            Route::prefix('estimated-yields')->name('estimated-yields.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Create::class)->name('create');
                Route::get('/{estimatedYield}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Edit::class)->name('edit');
            });
        });

        Route::prefix('phenology')->name('phenology.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Phenology\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Phenology\Create::class)->name('create');
            Route::get('/{observation}/edit', \App\Livewire\Viticulturist\Phenology\Edit::class)->name('edit');
        });

        // Mis Entregas a Bodega — Consulta (plan básico, requiere bodega vinculada)
        Route::prefix('harvests')->name('harvests.')->middleware('require.winery')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Harvests\Index::class)->name('index');
            Route::get('/planting/{planting}', \App\Livewire\Viticulturist\Harvests\Show::class)->name('show');
        });

        // Gestión de Plagas y Enfermedades — solo lectura, plan básico
        Route::prefix('pest-management')->name('pest-management.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\PestManagement\Index::class)->name('index');
            Route::get('/{pest}', \App\Livewire\Viticulturist\PestManagement\Show::class)->name('show');
        });

        // ── PLAN COMPLETO (requiere suscripción) ─────────────────────────────────
        Route::middleware('require.complete')->group(function () {

            // Estadísticas Financieras
            Route::get('/financial-stats', \App\Livewire\Viticulturist\FinancialStats::class)->name('financial-stats');

            // Dashboard de Cumplimiento PAC (cuaderno)
            Route::get('/pac-compliance', \App\Livewire\Viticulturist\PacComplianceDashboard::class)->name('pac-compliance');

            // Módulo PAC — Solicitud Única y superficies
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

            // Mis Entregas a Bodega — Operaciones (plan completo + bodega vinculada)
            Route::prefix('harvests')->name('harvests.')->middleware('require.winery')->group(function () {
                Route::get('/export/pdf', \App\Http\Controllers\Viticulturist\HarvestsPdfController::class.'@export')->name('export-pdf');
                Route::get('/create-delivery', \App\Livewire\Viticulturist\Harvests\CreateDelivery::class)->name('delivery.create');
                Route::get('/{delivery}/edit-delivery', \App\Livewire\Viticulturist\Harvests\EditDelivery::class)->name('delivery.edit');
                Route::get('/{delivery}/albaran', \App\Http\Controllers\Viticulturist\HarvestDeliveryAlbaranController::class)->name('delivery.albaran');
            });

            // Aplicadores fitosanitarios (ROPO)
            Route::prefix('field-applicators')->name('field-applicators.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\FieldApplicators\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\FieldApplicators\Create::class)->name('create');
                Route::get('/{fieldApplicator}/edit', \App\Livewire\Viticulturist\FieldApplicators\Edit::class)->name('edit');
            });

            // Equipos de aplicación (ITB/ITEA)
            Route::prefix('field-equipment')->name('field-equipment.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\FieldEquipment\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\FieldEquipment\Create::class)->name('create');
                Route::get('/{fieldEquipment}/edit', \App\Livewire\Viticulturist\FieldEquipment\Edit::class)->name('edit');
            });

            // Cosecha Comercializada (requiere bodega vinculada)
            Route::prefix('marketed-harvests')->name('marketed-harvests.')->middleware('require.winery')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\MarketedHarvests\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\MarketedHarvests\Create::class)->name('create');
                Route::get('/{marketedHarvest}/edit', \App\Livewire\Viticulturist\MarketedHarvests\Edit::class)->name('edit');
            });

            // Análisis de Residuos Fitosanitarios
            Route::prefix('residue-analyses')->name('residue-analyses.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\ResidueAnalyses\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\ResidueAnalyses\Create::class)->name('create');
                Route::get('/{residueAnalysis}/edit', \App\Livewire\Viticulturist\ResidueAnalyses\Edit::class)->name('edit');
            });

            // Gestión de Residuos Agrícolas
            Route::prefix('residue-managements')->name('residue-managements.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\ResidueManagements\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\ResidueManagements\Create::class)->name('create');
                Route::get('/{residueManagement}/edit', \App\Livewire\Viticulturist\ResidueManagements\Edit::class)->name('edit');
            });

            // Asesores Técnicos
            Route::prefix('advisory-memberships')->name('advisory-memberships.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\AdvisoryMemberships\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\AdvisoryMemberships\Create::class)->name('create');
                Route::get('/{advisoryMembership}/edit', \App\Livewire\Viticulturist\AdvisoryMemberships\Edit::class)->name('edit');
            });

            // Documentos de Campaña
            Route::prefix('campaign-documents')->name('campaign-documents.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\CampaignDocuments\Index::class)->name('index');
                Route::get('/{document}/download', [\App\Http\Controllers\Viticulturist\CampaignDocumentController::class, 'download'])->name('download');
            });

            // Firma y Cierre de Campaña
            Route::prefix('campaign-sign')->name('campaign-sign.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\CampaignSign\Index::class)->name('index');
            });

            // ── Plan de Trabajos ──────────────────────────────────────────
            Route::prefix('planned-works')->name('planned-works.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\PlannedWorks\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\PlannedWorks\Create::class)->name('create');
                Route::get('/{plannedWork}/edit', \App\Livewire\Viticulturist\PlannedWorks\Edit::class)->name('edit');
            });

            // Almacén de Insumos (unificado: Fitosanitarios + Otros Insumos + Almacenes)
            Route::prefix('warehouse')->name('warehouse.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Warehouse\Index::class)->name('index');
                Route::get('/stock/analytics', \App\Livewire\Viticulturist\Inventory\Analytics::class)->name('stock.analytics');
                Route::get('/stock/export', \App\Http\Controllers\Viticulturist\InventoryExportController::class)->name('stock.export');
                Route::get('/stock/create', \App\Livewire\Viticulturist\Inventory\CreateStock::class)->name('stock.create');
                Route::get('/stock/{stock}/edit', \App\Livewire\Viticulturist\Inventory\EditStock::class)->name('stock.edit');
                Route::get('/stock/{stock}/consume', \App\Livewire\Viticulturist\Inventory\ConsumeStock::class)->name('stock.consume');
                Route::get('/stock/{stock}/movements', \App\Livewire\Viticulturist\Inventory\Movements::class)->name('stock.movements');
                Route::get('/supplies/create', \App\Livewire\Viticulturist\Supplies\Create::class)->name('supplies.create');
                Route::get('/supplies/{supply}/edit', \App\Livewire\Viticulturist\Supplies\Edit::class)->name('supplies.edit');
                Route::get('/warehouses/create', \App\Livewire\Viticulturist\Warehouses\Create::class)->name('warehouses.create');
                Route::get('/warehouses/{warehouse}/edit', \App\Livewire\Viticulturist\Warehouses\Edit::class)->name('warehouses.edit');
            });

            // Consumo Energético / Huella de carbono
            Route::prefix('energy-usages')->name('energy-usages.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\EnergyUsages\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\EnergyUsages\Create::class)->name('create');
                Route::get('/{energyUsage}/edit', \App\Livewire\Viticulturist\EnergyUsages\Edit::class)->name('edit');
            });

            // Autorizaciones Comerciales (DO, eco, plantación)
            Route::prefix('commercial-authorizations')->name('commercial-authorizations.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\CommercialAuthorizations\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\CommercialAuthorizations\Create::class)->name('create');
                Route::get('/{commercialAuthorization}/edit', \App\Livewire\Viticulturist\CommercialAuthorizations\Edit::class)->name('edit');
            });

            // Explotación Agraria (SIEX/REA + DGC)
            Route::prefix('exploitations')->name('exploitations.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Exploitations\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\Exploitations\Create::class)->name('create');
                Route::get('/{exploitation}/edit', \App\Livewire\Viticulturist\Exploitations\Edit::class)->name('edit');
            });

            // Exportaciones CUE / SIEX
            Route::prefix('cue-exports')->name('cue-exports.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\CueExports\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\CueExports\Create::class)->name('create');
                Route::get('/{cueExport}/edit', \App\Livewire\Viticulturist\CueExports\Edit::class)->name('edit');
            });

            // Entorno de Parcelas (zonas protegidas, captaciones)
            Route::prefix('plot-environments')->name('plot-environments.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\PlotEnvironments\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\PlotEnvironments\Create::class)->name('create');
                Route::get('/{plotEnvironment}/edit', \App\Livewire\Viticulturist\PlotEnvironments\Edit::class)->name('edit');
            });

            // Meteorología
            Route::get('/meteorology', \App\Livewire\Winery\Meteorology\Index::class)->name('meteorology.index');

            // Actividades de Campo
            Route::get('/field-activities', \App\Livewire\Winery\FieldActivities\Index::class)->name('field-activities.index');

            // Contenedores (requiere bodega vinculada)
            Route::prefix('containers')->name('containers.')->middleware('require.winery')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Containers\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\Containers\Create::class)->name('create');
                Route::get('/{id}', \App\Livewire\Viticulturist\Containers\Show::class)->name('show');
                Route::get('/{id}/edit', \App\Livewire\Viticulturist\Containers\Edit::class)->name('edit');
            });

            // Personal (Equipos y Personal unificado)
            // Redirigir viticultores a personal
            Route::prefix('viticulturists')->name('viticulturists.')->group(function () {
                Route::get('/', function () {
                    return redirect()->route('viticulturist.personal.index', ['viewMode' => 'personal']);
                })->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\Viticulturists\Create::class)->name('create');
            });

            Route::prefix('personal')->name('personal.')->group(function () {
                Route::get('/', PersonalUnifiedIndex::class)->name('index');
                Route::get('/create', PersonalCreate::class)->name('create');
                // Redirigir workers a personal
                Route::get('/workers', function () {
                    return redirect()->route('viticulturist.personal.index', ['viewMode' => 'personal']);
                })->name('workers');
                Route::get('/viticulturist/create', \App\Livewire\Viticulturist\Viticulturists\Create::class)->name('viticulturist.create');
                Route::get('/viticulturist/{viticulturist}/edit', \App\Livewire\Viticulturist\Viticulturists\Edit::class)->name('viticulturist.edit');
                Route::get('/viticulturist/download-credentials', [\App\Http\Controllers\Viticulturist\ViticulturistCredentialsController::class, 'download'])->name('viticulturist.download-credentials');
                Route::get('/{crew}', PersonalShow::class)->name('show');
                Route::get('/{crew}/edit', PersonalEdit::class)->name('edit');
            });

            // Maquinaria
            Route::prefix('machinery')->name('machinery.')->group(function () {
                Route::get('/', MachineryIndex::class)->name('index');
                Route::get('/create', MachineryCreate::class)->name('create');
                Route::get('/{machinery}', MachineryShow::class)->name('show');
                Route::get('/{machinery}/edit', MachineryEdit::class)->name('edit');
            });

            // Productos fitosanitarios
            Route::prefix('phytosanitary-products')->name('phytosanitary-products.')->group(function () {
                Route::get('/', PhytosanitaryProductsIndex::class)->name('index');
                Route::get('/create', PhytosanitaryProductsCreate::class)->name('create');
                Route::get('/{product}/edit', PhytosanitaryProductsEdit::class)->name('edit');
            });

            // Clientes (componentes compartidos role-aware: App\Livewire\Clients\*)
            Route::prefix('clients')->name('clients.')->group(function () {
                Route::get('/', \App\Livewire\Clients\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Clients\Create::class)->name('create');
                Route::get('/{client}', \App\Livewire\Clients\Show::class)->name('show');
                Route::get('/{client}/edit', \App\Livewire\Clients\Edit::class)->name('edit');
            });

            // Facturas/Pedidos
            Route::prefix('invoices')->name('invoices.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Invoices\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\Invoices\Create::class)->name('create');

                // Rutas estáticas primero (antes de rutas dinámicas)
                Route::get('/harvest', \App\Livewire\Viticulturist\Invoices\Harvest\Index::class)->name('harvest.index');

                // ── Liquidaciones de la bodega (solo lectura, requiere bodega vinculada)
                Route::get('/grape-purchase', \App\Livewire\Viticulturist\Invoices\GrapePurchase\Index::class)->name('grape-purchase.index')->middleware('require.winery');

                // ── Facturación de Vendimia (Cosecha Comercializada) ──────────────────────
                Route::prefix('harvest-sale')->name('harvest-sale.')->group(function () {
                    Route::get('/', \App\Livewire\Viticulturist\Billing\HarvestSale\Index::class)->name('index');
                    Route::get('/create', \App\Livewire\Viticulturist\Billing\HarvestSale\Create::class)->name('create');
                    Route::get('/{id}/edit', \App\Livewire\Viticulturist\Billing\HarvestSale\Edit::class)->name('edit');
                    Route::get('/{id}/pdf', [\App\Http\Controllers\Viticulturist\InvoicePdfController::class, 'invoice'])->name('pdf');
                    Route::get('/{id}/albaran-pdf', [\App\Http\Controllers\Viticulturist\InvoicePdfController::class, 'deliveryNote'])->name('delivery-note-pdf');
                });

                // Rutas dinámicas: más específicas primero
                Route::get('/{invoice}/edit', \App\Livewire\Viticulturist\Invoices\Edit::class)->name('edit');
                Route::get('/{invoice}/pdf', [\App\Http\Controllers\Viticulturist\InvoicePdfController::class, 'invoice'])->name('pdf');
                Route::get('/{invoice}/albaran-pdf', [\App\Http\Controllers\Viticulturist\InvoicePdfController::class, 'deliveryNote'])->name('delivery-note-pdf');
                Route::get('/{invoice}', \App\Livewire\Viticulturist\Invoices\Show::class)->name('show');
            });

            // Gestión de Envases Fitosanitarios (SIGFITO / FIELD)
            Route::prefix('container-returns')->name('container-returns.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\ContainerReturns\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\ContainerReturns\Create::class)->name('create');
                Route::get('/{containerReturn}/edit', \App\Livewire\Viticulturist\ContainerReturns\Edit::class)->name('edit');
            });

            // Subproductos de Vendimia
            Route::prefix('harvest-byproducts')->name('harvest-byproducts.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\HarvestByproducts\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\HarvestByproducts\Create::class)->name('create');
                Route::get('/{byproduct}/edit', \App\Livewire\Viticulturist\HarvestByproducts\Edit::class)->name('edit');
            });

            // Declaraciones de Vendimia
            Route::prefix('harvest-declarations')->name('harvest-declarations.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\HarvestDeclarations\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\HarvestDeclarations\Create::class)->name('create');
                Route::get('/{harvestDeclaration}/edit', \App\Livewire\Viticulturist\HarvestDeclarations\Edit::class)->name('edit');
            });

            // Informes Oficiales
            Route::prefix('official-reports')->name('official-reports.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\OfficialReports\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\OfficialReports\Create::class)->name('create');
                Route::get('/{report}/download', [\App\Http\Controllers\Viticulturist\OfficialReportController::class, 'download'])->name('download');
                Route::get('/{report}/preview', [\App\Http\Controllers\Viticulturist\OfficialReportController::class, 'preview'])->name('preview');
            });

            // ── Notificaciones ───────────────────────────────────────────
            Route::get('/notifications', \App\Livewire\Viticulturist\Notifications\Index::class)
                ->name('notifications.index');

            Route::get('/winery-messages', \App\Livewire\Viticulturist\WineryMessages\Index::class)
                ->name('winery-messages.index');

            // ── Subcontratación ───────────────────────────────────────────
            Route::prefix('subcontracting')->name('subcontracting.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Subcontracting\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\Subcontracting\Create::class)->name('create');
                Route::get('/{record}/edit', \App\Livewire\Viticulturist\Subcontracting\Edit::class)->name('edit');
            });

            // ── Seguros Agrarios ──────────────────────────────────────────
            Route::prefix('agri-insurance')->name('agri-insurance.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\AgriInsurance\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\AgriInsurance\Create::class)->name('create');
                Route::get('/{insurance}/edit', \App\Livewire\Viticulturist\AgriInsurance\Edit::class)->name('edit');
            });

            // ── Certificaciones y Sellos ──────────────────────────────────
            Route::prefix('certifications')->name('certifications.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\Certifications\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\Certifications\Create::class)->name('create');
                Route::get('/{certification}/edit', \App\Livewire\Viticulturist\Certifications\Edit::class)->name('edit');
            });

            // ── Costes por Parcela ────────────────────────────────────────
            Route::prefix('plot-costs')->name('plot-costs.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\PlotCosts\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\PlotCosts\Create::class)->name('create');
                Route::get('/{cost}/edit', \App\Livewire\Viticulturist\PlotCosts\Edit::class)->name('edit');
            });

            // ── Registro de Agua / Concesiones de Riego ──────────────────
            Route::prefix('water-concessions')->name('water-concessions.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\WaterConcessions\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\WaterConcessions\Create::class)->name('create');
                Route::get('/{concession}/edit', \App\Livewire\Viticulturist\WaterConcessions\Edit::class)->name('edit');
            });

            Route::get('/verifactu', \App\Livewire\Winery\Verifactu\Dashboard::class)->name('verifactu.index');

            // ── Plan de Fertilización / Gestión de Nitrógenos ────────────
            Route::prefix('fertilization-plans')->name('fertilization-plans.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\FertilizationPlans\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\FertilizationPlans\Create::class)->name('create');
                Route::get('/{plan}/edit', \App\Livewire\Viticulturist\FertilizationPlans\Edit::class)->name('edit');
            });

            // ── Alertas Fitosanitarias ─────────────────────────────────
            Route::prefix('phytosanitary-alerts')->name('phytosanitary-alerts.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\PhytosanitaryAlerts\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\PhytosanitaryAlerts\Create::class)->name('create');
                Route::get('/{phytosanitaryAlert}/edit', \App\Livewire\Viticulturist\PhytosanitaryAlerts\Edit::class)->name('edit');
            });

            // ── Análisis de Suelo ─────────────────────────────────────────
            Route::prefix('soil-analyses')->name('soil-analyses.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\SoilAnalyses\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\SoilAnalyses\Create::class)->name('create');
                Route::get('/{soilAnalysis}/edit', \App\Livewire\Viticulturist\SoilAnalyses\Edit::class)->name('edit');
            });

            // ── Biodiversidad y Cubiertas ─────────────────────────────────
            Route::prefix('biodiversity-records')->name('biodiversity-records.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\BiodiversityRecords\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\BiodiversityRecords\Create::class)->name('create');
                Route::get('/{biodiversityRecord}/edit', \App\Livewire\Viticulturist\BiodiversityRecords\Edit::class)->name('edit');
            });

            // ── Comparativa entre Campañas ────────────────────────────────
            Route::get('/campaign-comparison', \App\Livewire\Viticulturist\CampaignComparison\Index::class)->name('campaign-comparison');

            // ── Trazabilidad de Uva (requiere bodega vinculada) ────────────
            Route::get('/grape-traceability', \App\Livewire\Viticulturist\GrapeTraceability\Index::class)->name('grape-traceability')->middleware('require.winery');

        }); // end require.complete
    });
