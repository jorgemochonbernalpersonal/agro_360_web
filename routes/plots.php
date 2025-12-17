<?php

use App\Livewire\Plots\Index;
use App\Livewire\Plots\Create;
use App\Livewire\Plots\Edit;
use App\Livewire\Plots\Show;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,supervisor,winery,viticulturist'])
    ->prefix('plots')
    ->name('plots.')
    ->group(function () {
        Route::get('/', Index::class)->name('index');
        Route::get('/create', Create::class)->name('create')->middleware('can:create,App\Models\Plot');
        Route::get('/{plot}', Show::class)->name('show');
        Route::get('/{plot}/edit', Edit::class)->name('edit')->middleware('can:update,plot');
    });

