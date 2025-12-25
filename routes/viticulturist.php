<?php

use App\Livewire\Viticulturist\Campaign\Create as CampaignCreate;
use App\Livewire\Viticulturist\Campaign\Edit as CampaignEdit;
use App\Livewire\Viticulturist\Campaign\Index as CampaignIndex;
use App\Livewire\Viticulturist\Campaign\Show as CampaignShow;
use App\Livewire\Viticulturist\DigitalNotebook\CreateCulturalWork;
use App\Livewire\Viticulturist\DigitalNotebook\CreateFertilization;
use App\Livewire\Viticulturist\DigitalNotebook\CreateHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\EditHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\ShowHarvest;
use App\Livewire\Viticulturist\DigitalNotebook\CreateIrrigation;
use App\Livewire\Viticulturist\DigitalNotebook\CreateObservation;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePhytosanitaryTreatment;
use App\Livewire\Viticulturist\DigitalNotebook\Containers\Index as ContainersIndex;
use App\Livewire\Viticulturist\DigitalNotebook\Containers\Create as ContainersCreate;
use App\Livewire\Viticulturist\DigitalNotebook\Containers\Edit as ContainersEdit;
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
use App\Livewire\Viticulturist\Calendar;
use App\Livewire\Viticulturist\DigitalNotebook;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:viticulturist', 'check.beta'])
    ->prefix('viticulturist')
    ->name('viticulturist.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('viticulturist.dashboard');
        })->name('dashboard');

        // Estadísticas Financieras
        Route::get('/financial-stats', \App\Livewire\Viticulturist\FinancialStats::class)->name('financial-stats');
        
        // Dashboard de Cumplimiento PAC
        Route::get('/pac-compliance', \App\Livewire\Viticulturist\PacComplianceDashboard::class)->name('pac-compliance');
        
        // Gestión de Plagas y Enfermedades
        Route::prefix('pest-management')->name('pest-management.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\PestManagement\Index::class)->name('index');
            Route::get('/{pest}', \App\Livewire\Viticulturist\PestManagement\Show::class)->name('show');
        });

        // Campañas
        Route::prefix('campaign')->name('campaign.')->group(function () {
            Route::get('/', CampaignIndex::class)->name('index');
            Route::get('/create', CampaignCreate::class)->name('create');
            Route::get('/{campaign}', CampaignShow::class)->name('show');
            Route::get('/{campaign}/edit', CampaignEdit::class)->name('edit');
        });

        // Cuaderno Digital
        Route::get('/digital-notebook', DigitalNotebook::class)->name('digital-notebook');
        Route::prefix('digital-notebook')->name('digital-notebook.')->group(function () {
            Route::get('/treatment/create', CreatePhytosanitaryTreatment::class)->name('treatment.create');
            Route::get('/treatment/{activity}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EditPhytosanitaryTreatment::class)->name('treatment.edit');
            Route::get('/fertilization/create', CreateFertilization::class)->name('fertilization.create');
            Route::get('/irrigation/create', CreateIrrigation::class)->name('irrigation.create');
            Route::get('/cultural/create', CreateCulturalWork::class)->name('cultural.create');
            Route::get('/observation/create', CreateObservation::class)->name('observation.create');
            Route::get('/harvest/create', CreateHarvest::class)->name('harvest.create');
            Route::get('/harvest/{harvest}', ShowHarvest::class)->name('harvest.show');
            Route::get('/harvest/{harvest}/edit', EditHarvest::class)->name('harvest.edit');
            
            // Contenedores
            Route::prefix('containers')->name('containers.')->group(function () {
                Route::get('/', ContainersIndex::class)->name('index');
                Route::get('/create', ContainersCreate::class)->name('create');
                Route::get('/{container}/edit', ContainersEdit::class)->name('edit');
            });
            
            // Rendimientos Estimados
            Route::prefix('estimated-yields')->name('estimated-yields.')->group(function () {
                Route::get('/', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Index::class)->name('index');
                Route::get('/create', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Create::class)->name('create');
                Route::get('/{estimatedYield}/edit', \App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Edit::class)->name('edit');
            });
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
            Route::get('/viticulturist/download-credentials', function (\Illuminate\Http\Request $request) {
                $viticulturistId = $request->query('id');

                // Intentar obtener desde sesión primero
                $pdfPath = session('viticulturist_credentials_pdf');
                $viticulturistName = session('viticulturist_created_name');

                // Si no está en sesión pero tenemos ID, buscar el archivo más reciente
                if (!$pdfPath && $viticulturistId) {
                    $tempDir = storage_path('app/temp');
                    $pattern = $tempDir . '/credentials_' . $viticulturistId . '_*.pdf';
                    $files = glob($pattern);
                    if (!empty($files)) {
                        // Ordenar por fecha de modificación (más reciente primero)
                        usort($files, function ($a, $b) {
                            return filemtime($b) - filemtime($a);
                        });
                        $pdfPath = $files[0];

                        // Obtener nombre del viticultor
                        $viticulturist = \App\Models\User::find($viticulturistId);
                        if ($viticulturist) {
                            $viticulturistName = $viticulturist->name;
                        }
                    }
                }

                if (!$pdfPath || !file_exists($pdfPath)) {
                    return redirect()
                        ->route('viticulturist.personal.index')
                        ->with('error', 'El PDF de credenciales no está disponible. El archivo puede haber expirado.');
                }

                $filename = 'credenciales_' . \Str::slug($viticulturistName ?? 'viticultor') . '_' . now()->format('Y-m-d') . '.pdf';

                // Limpiar sesión antes de descargar
                session()->forget(['viticulturist_credentials_pdf', 'viticulturist_created_id', 'viticulturist_created_name']);

                return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
            })->name('viticulturist.download-credentials');
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

        Route::get('/calendar', Calendar::class)->name('calendar');

        // Clientes
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Clients\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Clients\Create::class)->name('create');
            Route::get('/{client}', \App\Livewire\Viticulturist\Clients\Show::class)->name('show');
            Route::get('/{client}/edit', \App\Livewire\Viticulturist\Clients\Edit::class)->name('edit');
        });

        // Facturas/Pedidos
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', \App\Livewire\Viticulturist\Invoices\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Viticulturist\Invoices\Create::class)->name('create');
            
            // Rutas estáticas primero (antes de rutas dinámicas)
            Route::get('/harvest', \App\Livewire\Viticulturist\Invoices\Harvest\Index::class)->name('harvest.index');
            
            // Rutas dinámicas: más específicas primero
            Route::get('/{invoice}/edit', \App\Livewire\Viticulturist\Invoices\Edit::class)->name('edit');
            Route::get('/{invoice}', \App\Livewire\Viticulturist\Invoices\Show::class)->name('show');
        });

        // Informes Oficiales
        Route::get('/official-reports', \App\Livewire\Viticulturist\OfficialReports\Index::class)->name('official-reports.index');
        Route::get('/official-reports/crear', \App\Livewire\Viticulturist\OfficialReports\Create::class)->name('official-reports.create');
        Route::get('/official-reports/{report}/download', function (\App\Models\OfficialReport $report) {
            // Verificar permisos
            if ($report->user_id !== auth()->id()) {
                abort(403, 'No tienes permiso para descargar este informe.');
            }

            $service = new \App\Services\OfficialReportService();
            return $service->downloadReport($report);
        })->name('official-reports.download');
        Route::get('/official-reports/{report}/preview', function (\App\Models\OfficialReport $report) {
            // Verificar permisos
            if ($report->user_id !== auth()->id()) {
                abort(403, 'No tienes permiso para ver este informe.');
            }

            if (!$report->pdfExists()) {
                abort(404, 'El archivo PDF no existe.');
            }

            // Obtener ruta completa del PDF
            $pdfPath = str_starts_with($report->pdf_path, storage_path()) 
                ? $report->pdf_path 
                : \Storage::disk('local')->path($report->pdf_path);

            // Devolver PDF para visualización (no descarga)
            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($report->pdf_filename ?? 'informe.pdf') . '"',
            ]);
        })->name('official-reports.preview');

        // Support / Soporte
        Route::get('/support', \App\Livewire\Viticulturist\Support\Index::class)->name('support.index');
        Route::get('/support/create', \App\Livewire\Viticulturist\Support\CreateTicket::class)->name('support.create');

        // Configuración
        Route::get('/settings', \App\Livewire\Viticulturist\Settings::class)->name('settings');
        
        // Rutas legacy - redirigen a settings con tab
        Route::get('/settings/taxes', function() {
            return redirect()->route('viticulturist.settings', ['tab' => 'taxes']);
        });
        Route::get('/settings/invoicing', function() {
            return redirect()->route('viticulturist.settings', ['tab' => 'invoicing']);
        });
    });
