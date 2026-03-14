<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:supervisor'])
    ->prefix('supervisor')
    ->name('supervisor.')
    ->group(function () {

        Route::get('/dashboard', \App\Livewire\DenominacionOrigen\Dashboard::class)->name('dashboard');

        // ── Census ────────────────────────────────────────────────────
        Route::get('/census', \App\Livewire\DenominacionOrigen\Census\Index::class)->name('census.index');

        // ── Growers (own DO growers) ───────────────────────────────────
        Route::get('/growers', \App\Livewire\DenominacionOrigen\Growers\Index::class)->name('growers.index');

        // ── Campaigns ─────────────────────────────────────────────────
        Route::get('/campaigns', \App\Livewire\DenominacionOrigen\Campaigns\Index::class)->name('campaigns.index');

        // ── Oversight ─────────────────────────────────────────────────
        Route::get('/oversight/wineries', \App\Livewire\DenominacionOrigen\Oversight\Wineries\Index::class)->name('oversight.wineries.index');
        Route::get('/oversight/growers', \App\Livewire\DenominacionOrigen\Oversight\Growers\Index::class)->name('oversight.growers.index');

        // ── Qualification ─────────────────────────────────────────────
        Route::get('/qualification', \App\Livewire\DenominacionOrigen\Qualification\Index::class)->name('qualification.index');

        // ── Labels ────────────────────────────────────────────────────
        Route::get('/labels', \App\Livewire\DenominacionOrigen\Labels\Index::class)->name('labels.index');

        // ── Inspection ────────────────────────────────────────────────
        Route::get('/inspection', \App\Livewire\DenominacionOrigen\Inspection\Index::class)->name('inspection.index');

        // ── Regulation ────────────────────────────────────────────────
        Route::get('/regulation', \App\Livewire\DenominacionOrigen\Regulation\Index::class)->name('regulation.index');

        // ── Territory ─────────────────────────────────────────────────
        Route::get('/territory', \App\Livewire\DenominacionOrigen\Territory\Index::class)->name('territory.index');

        // ── Statistics ────────────────────────────────────────────────
        Route::get('/statistics', \App\Livewire\DenominacionOrigen\Statistics\Index::class)->name('statistics.index');

        // ── Finance ───────────────────────────────────────────────────
        Route::get('/finance', \App\Livewire\DenominacionOrigen\Finance\Index::class)->name('finance.index');

        // ── Settings ──────────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\DenominacionOrigen\Settings\Index::class)->name('settings.index');
    });
