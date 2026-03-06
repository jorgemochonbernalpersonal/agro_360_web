<?php

use Illuminate\Support\Facades\Route;

// Ruta helper: redirige al dashboard (usada por stubs mientras se implementa el módulo)
$stub = fn() => redirect()->route('winery.dashboard');

Route::middleware(['role:winery'])
    ->prefix('winery')
    ->name('winery.')
    ->group(function () use ($stub) {

        Route::get('/dashboard', function () {
            return view('winery.dashboard');
        })->name('dashboard');

        // ── Viticultores ─────────────────────────────────────────────
        Route::get('/viticulturists', \App\Livewire\Winery\Viticulturists\Index::class)->name('viticulturists.index');
        Route::get('/viticulturists/create', \App\Livewire\Winery\Viticulturists\Create::class)->name('viticulturists.create');
        Route::get('/viticulturists/invite', \App\Livewire\Winery\Viticulturists\Invite::class)->name('viticulturists.invite');
        Route::get('/viticulturists/{viticulturist}', \App\Livewire\Winery\Viticulturists\Show::class)->name('viticulturists.show');
        Route::get('/viticulturists/{viticulturist}/edit', \App\Livewire\Winery\Viticulturists\Edit::class)->name('viticulturists.edit');

        // ── Parcelas (reutiliza Plots\* role-aware) ───────────────────
        Route::get('/plots', \App\Livewire\Plots\Index::class)->name('plots.index');
        Route::get('/plots/{plot}', \App\Livewire\Plots\Show::class)->name('plots.show');

        // ── Campañas (gestionadas automáticamente por añada) ──────────
        Route::get('/campaigns', fn() => redirect()->route('winery.grape-reception.index'))->name('campaigns.index');
        Route::get('/campaigns/create', fn() => redirect()->route('winery.grape-reception.index'))->name('campaigns.create');

        // ── Recepción de uva ──────────────────────────────────────────
        Route::get('/grape-reception', \App\Livewire\Winery\Harvest\Reception\Index::class)->name('grape-reception.index');
        Route::get('/grape-reception/create', \App\Livewire\Winery\Harvest\Reception\Create::class)->name('grape-reception.create');

        // ── Contenedores de bodega ────────────────────────────────────
        Route::get('/containers', \App\Livewire\Winery\Cellar\Containers\Index::class)->name('containers.index');
        Route::get('/containers/create', \App\Livewire\Winery\Cellar\Containers\Create::class)->name('containers.create');
        Route::get('/containers/{container}/edit', \App\Livewire\Winery\Cellar\Containers\Edit::class)->name('containers.edit');

        // ── Asignación recepción → contenedor ────────────────────────
        Route::get('/grape-reception/{harvest}/assign', \App\Livewire\Winery\Harvest\Reception\Assign::class)->name('grape-reception.assign');

        // ── Lotes de vino ────────────────────────────────────────────
        Route::get('/wine-lots', \App\Livewire\Winery\Cellar\WineLots\Index::class)->name('wine-lots.index');
        Route::get('/wine-lots/create', \App\Livewire\Winery\Cellar\WineLots\Create::class)->name('wine-lots.create');
        Route::get('/wine-lots/{lot}/edit', \App\Livewire\Winery\Cellar\WineLots\Edit::class)->name('wine-lots.edit');

        // ── Clientes ──────────────────────────────────────────────────
        Route::get('/clients', \App\Livewire\Winery\Clients\Index::class)->name('clients.index');
        Route::get('/clients/create', \App\Livewire\Winery\Clients\Create::class)->name('clients.create');
        Route::get('/clients/{client}/edit', \App\Livewire\Winery\Clients\Edit::class)->name('clients.edit');

        // ── Facturación: venta de vino ────────────────────────────────
        Route::get('/invoices/wine-sale', \App\Livewire\Winery\Billing\WineSale\Index::class)->name('invoices.wine-sale.index');
        Route::get('/invoices/wine-sale/create', \App\Livewire\Winery\Billing\WineSale\Create::class)->name('invoices.wine-sale.create');
        Route::get('/invoices/wine-sale/{id}/edit', \App\Livewire\Winery\Billing\WineSale\Edit::class)->name('invoices.wine-sale.edit');
        Route::get('/invoices/wine-sale/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->defaults('type', 'wine_sale')->name('invoices.wine-sale.pdf');
        Route::get('/invoices/wine-sale/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->defaults('type', 'wine_sale')->name('invoices.wine-sale.delivery-note-pdf');
        Route::get('/invoices/wine-sale/{id}/albaran-valorado-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'valoradoNote'])->defaults('type', 'wine_sale')->name('invoices.wine-sale.valorado-pdf');

        // ── Facturación: liquidación de vendimia ──────────────────────
        Route::get('/invoices/grape-purchase', \App\Livewire\Winery\Billing\GrapePurchase\Index::class)->name('invoices.grape-purchase.index');
        Route::get('/invoices/grape-purchase/create', \App\Livewire\Winery\Billing\GrapePurchase\Create::class)->name('invoices.grape-purchase.create');
        Route::get('/invoices/grape-purchase/{id}/edit', \App\Livewire\Winery\Billing\GrapePurchase\Edit::class)->name('invoices.grape-purchase.edit');
        Route::get('/invoices/grape-purchase/{id}/pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'invoice'])->defaults('type', 'grape_purchase')->name('invoices.grape-purchase.pdf');
        Route::get('/invoices/grape-purchase/{id}/albaran-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'deliveryNote'])->defaults('type', 'grape_purchase')->name('invoices.grape-purchase.delivery-note-pdf');
        Route::get('/invoices/grape-purchase/{id}/albaran-valorado-pdf', [\App\Http\Controllers\Winery\InvoicePdfController::class, 'valoradoNote'])->defaults('type', 'grape_purchase')->name('invoices.grape-purchase.valorado-pdf');

        // ── Elaboración de vino ───────────────────────────────────────
        Route::get('/wine-process', $stub)->name('wine-process.index');

        // ── Análisis de laboratorio ───────────────────────────────────
        Route::get('/wine-analysis', $stub)->name('wine-analysis.index');

        // ── Inventario de insumos de bodega ───────────────────────────
        Route::get('/inventory', $stub)->name('inventory.index');

        // ── Proveedores ───────────────────────────────────────────────
        Route::get('/suppliers', $stub)->name('suppliers.index');

        // ── SILICIE ───────────────────────────────────────────────────
        Route::get('/silicie', $stub)->name('silicie.dashboard');
        Route::get('/silicie/movements', $stub)->name('silicie.movements.index');

        // ── Documentos ────────────────────────────────────────────────
        Route::get('/documents', $stub)->name('documents.index');

        // ── Configuración ─────────────────────────────────────────────
        Route::get('/settings', \App\Livewire\Winery\Settings::class)->name('settings');
    });
