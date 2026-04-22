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
        Route::get('/oversight/growers/{viticulturist}', \App\Livewire\Supervisor\Oversight\Growers\Show::class)->name('oversight.growers.show');
        Route::get('/oversight/plots', \App\Livewire\Supervisor\Oversight\Plots\Index::class)->name('oversight.plots.index');
        Route::get('/oversight/notebook', \App\Livewire\Supervisor\Oversight\Notebook\Index::class)->name('oversight.notebook.index');
        Route::get('/oversight/pac', \App\Livewire\Supervisor\Oversight\Pac\Index::class)->name('oversight.pac.index');
        Route::get('/oversight/certifications', \App\Livewire\Supervisor\Oversight\Certifications\Index::class)->name('oversight.certifications.index');
        Route::get('/oversight/activity', \App\Livewire\Supervisor\Oversight\Activity\Index::class)->name('oversight.activity.index');

        // ── Qualification ─────────────────────────────────────────────
        Route::get('/qualification', \App\Livewire\Supervisor\Qualification\Index::class)->name('qualification.index');

        // ── Labels ────────────────────────────────────────────────────
        Route::get('/labels', \App\Livewire\Supervisor\Labels\Index::class)->name('labels.index');

        // ── Inspection ────────────────────────────────────────────────
        Route::get('/inspection', \App\Livewire\Supervisor\Inspection\Index::class)->name('inspection.index');

        // ── Solicitudes (bidireccional con bodegas) ───────────────────
        Route::get('/requests', \App\Livewire\Supervisor\Requests\Index::class)->name('requests.index');

        // ── Regulation ────────────────────────────────────────────────
        Route::get('/regulation', \App\Livewire\Supervisor\Regulation\Index::class)->name('regulation.index');

        // ── Documents (pliegos / reglamentos) ────────────────────────────
        Route::get('/documents', \App\Livewire\Supervisor\Documents\Index::class)->name('documents.index');

        // ── Territory ─────────────────────────────────────────────────
        Route::get('/territory', \App\Livewire\Supervisor\Territory\Index::class)->name('territory.index');

        // ── Statistics ────────────────────────────────────────────────
        Route::get('/statistics', \App\Livewire\Supervisor\Statistics\Index::class)->name('statistics.index');

        // ── Finance ───────────────────────────────────────────────────
        Route::get('/finance', \App\Livewire\Supervisor\Finance\Index::class)->name('finance.index');

        // ── Settings ──────────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Supervisor\Settings\Index::class)->name('settings.index');

        // ── Acceso al cuaderno ────────────────────────────────────────
        Route::get('/notebook-access', \App\Livewire\Supervisor\Notebook\Index::class)->name('notebook.index');
    });
