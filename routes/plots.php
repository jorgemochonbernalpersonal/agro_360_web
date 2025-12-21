<?php

use App\Livewire\Plots\Index;
use App\Livewire\Plots\Create;
use App\Livewire\Plots\Edit;
use App\Livewire\Plots\Show;
use App\Livewire\Plots\Plantings\Index as PlantingsIndex;
use App\Livewire\Plots\Plantings\Create as PlantingCreate;
use App\Livewire\Plots\Plantings\Edit as PlantingEdit;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,supervisor,winery,viticulturist', 'check.beta'])
    ->prefix('plots')
    ->name('plots.')
    ->group(function () {
        Route::get('/', Index::class)->name('index');
        // Índice global de plantaciones
        Route::get('/plantings', PlantingsIndex::class)->name('plantings.index');
        // Edición de plantación (no depende de capturar la parcela en la URL)
        Route::get('/plantings/{planting}/edit', PlantingEdit::class)->name('plantings.edit');
        Route::get('/create', Create::class)->name('create')->middleware('can:create,App\Models\Plot');
        Route::get('/{plot}', Show::class)->name('show');
        Route::get('/{plot}/edit', Edit::class)->name('edit')->middleware('can:update,plot');

        Route::get('/{plot}/plantings/create', PlantingCreate::class)
            ->name('plantings.create')
            ->middleware('can:update,plot');
    });

