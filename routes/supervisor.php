<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:supervisor'])
    ->prefix('supervisor')
    ->name('supervisor.')
    ->group(function () {

        Route::get('/dashboard', \App\Livewire\Supervisor\Dashboard::class)->name('dashboard');

        // ── Census ────────────────────────────────────────────────────
        Route::get('/census', \App\Livewire\Supervisor\Census\Index::class)->name('census.index');

        // ── Growers (own DO growers) ───────────────────────────────────
        Route::get('/growers', \App\Livewire\Supervisor\Growers\Index::class)->name('growers.index');

        // ── Campaigns ─────────────────────────────────────────────────
        Route::get('/campaigns', \App\Livewire\Supervisor\Campaigns\Index::class)->name('campaigns.index');

        // ── Oversight ─────────────────────────────────────────────────
        Route::get('/oversight/wineries', \App\Livewire\Supervisor\Oversight\Wineries\Index::class)->name('oversight.wineries.index');
        Route::get('/oversight/wineries/{winery}', \App\Livewire\Supervisor\Oversight\Wineries\Show::class)->name('oversight.wineries.show');
        Route::get('/oversight/growers', \App\Livewire\Supervisor\Oversight\Growers\Index::class)->name('oversight.growers.index');

        // ── Qualification ─────────────────────────────────────────────
        Route::get('/qualification', \App\Livewire\Supervisor\Qualification\Index::class)->name('qualification.index');

        // ── Labels ────────────────────────────────────────────────────
        Route::get('/labels', \App\Livewire\Supervisor\Labels\Index::class)->name('labels.index');

        // ── Inspection ────────────────────────────────────────────────
        Route::get('/inspection', \App\Livewire\Supervisor\Inspection\Index::class)->name('inspection.index');

        // ── Regulation ────────────────────────────────────────────────
        Route::get('/regulation', \App\Livewire\Supervisor\Regulation\Index::class)->name('regulation.index');

        // ── Territory ─────────────────────────────────────────────────
        Route::get('/territory', \App\Livewire\Supervisor\Territory\Index::class)->name('territory.index');

        // ── Statistics ────────────────────────────────────────────────
        Route::get('/statistics', \App\Livewire\Supervisor\Statistics\Index::class)->name('statistics.index');

        // ── Finance ───────────────────────────────────────────────────
        Route::get('/finance', \App\Livewire\Supervisor\Finance\Index::class)->name('finance.index');

        // ── Settings ──────────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Supervisor\Settings\Index::class)->name('settings.index');
    });
