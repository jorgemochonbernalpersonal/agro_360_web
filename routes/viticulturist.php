<?php

use App\Livewire\Viticulturist\Campaign\Create as CampaignCreate;
use App\Livewire\Viticulturist\Campaign\Edit as CampaignEdit;
use App\Livewire\Viticulturist\Campaign\Index as CampaignIndex;
use App\Livewire\Viticulturist\Campaign\Show as CampaignShow;
use App\Livewire\Viticulturist\DigitalNotebook\CreateCulturalWork;
use App\Livewire\Viticulturist\DigitalNotebook\CreateFertilization;
use App\Livewire\Viticulturist\DigitalNotebook\CreateIrrigation;
use App\Livewire\Viticulturist\DigitalNotebook\CreateObservation;
use App\Livewire\Viticulturist\DigitalNotebook\CreatePhytosanitaryTreatment;
use App\Livewire\Viticulturist\Machinery\Create as MachineryCreate;
use App\Livewire\Viticulturist\Machinery\Edit as MachineryEdit;
use App\Livewire\Viticulturist\Machinery\Index as MachineryIndex;
use App\Livewire\Viticulturist\Machinery\Show as MachineryShow;
use App\Livewire\Viticulturist\Personal\Hierarchy\Index as HierarchyIndex;
use App\Livewire\Viticulturist\Personal\Workers\Index as WorkersIndex;
use App\Livewire\Viticulturist\Personal\Create as PersonalCreate;
use App\Livewire\Viticulturist\Personal\Edit as PersonalEdit;
use App\Livewire\Viticulturist\Personal\Index as PersonalIndex;
use App\Livewire\Viticulturist\Personal\Show as PersonalShow;
use App\Livewire\Viticulturist\Calendar;
use App\Livewire\Viticulturist\DigitalNotebook;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:viticulturist'])
    ->prefix('viticulturist')
    ->name('viticulturist.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('viticulturist.dashboard');
        })->name('dashboard');

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
            Route::get('/fertilization/create', CreateFertilization::class)->name('fertilization.create');
            Route::get('/irrigation/create', CreateIrrigation::class)->name('irrigation.create');
            Route::get('/cultural/create', CreateCulturalWork::class)->name('cultural.create');
            Route::get('/observation/create', CreateObservation::class)->name('observation.create');
        });

        // Personal (Cuadrillas)
        Route::prefix('personal')->name('personal.')->group(function () {
            Route::get('/', PersonalIndex::class)->name('index');
            Route::get('/create', PersonalCreate::class)->name('create');
            // Jerarquía y Workers deben ir antes de {crew} para evitar conflictos
            Route::get('/hierarchy', HierarchyIndex::class)->name('hierarchy');
            Route::get('/workers', WorkersIndex::class)->name('workers');
            Route::get('/viticulturist/create', \App\Livewire\Viticulturist\Personal\Viticulturist\Create::class)->name('viticulturist.create');
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
        Route::get('/calendar', Calendar::class)->name('calendar');
    });
